<?php

namespace App\Models;

use App\Node;
use Auth;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Cookie;

class User extends Authenticatable
{
    use Notifiable;

    const STATE_CANCEL = 0;
    const STATE_NORMAL = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'password', 'state', 'site_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function stateName()
    {
        switch ($this->state) {
            case static::STATE_CANCEL:
                return '已注销';
                break;
            case static::STATE_NORMAL:
                return '正常';
                break;
        }
    }

    public function scopeOwns($query)
    {
        $query->where('site_id', Auth::user()->site_id);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function getSiteIdAttribute($value)
    {
        return $value;
    }

    public static function getTree($user_id)
    {
        $categories = Category::where('site_id', Auth::user()->site_id)->orderBy('sort')->get();

        $root = new Node();
        $root->id = 0;

        $user = User::find($user_id);
        $category_ids = $user->categories->pluck('id')->toArray();

        static::getNodes($root, $categories, $category_ids);

        return $root->nodes;
    }

    public static function getNodes($parent, $categories, $category_ids)
    {
        foreach ($categories as $category) {
            if ($category->parent_id == $parent->id) {
                $node = new Node();
                $node->id = $category->id;
                $node->text = $category->name;

                if (in_array($node->id, $category_ids)) {
                    $node->state = (object)[
                        'selected' => true,
                    ];
                }

                $parent->nodes[] = $node;
                static::getNodes($node, $categories, $category_ids);
            }
        }
    }

    //关联角色
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    // 判断用户是否具有 某个或某些 角色
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        return !!$role->intersect($this->roles)->count();
    }

    // 判断用户是否具有某权限
    public function hasPermission($permission)
    {
        return $this->hasRole($permission->roles);
    }

    // 给用户分配角色
    public function assignRole($role)
    {
        return $this->roles()->save(
            Role::whereName($role)->firstOrFail()
        );
    }

    //分配角色
    public function attachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->roles()->attach($role);
    }

    //解除角色
    public function detachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->roles()->detach($role);
    }

    //分配多个角色
    public function attachRoles($roles)
    {
        foreach ($roles as $role) {
            $this->attachRole($role);
        }
    }

    //解除多个角色
    public function detachRoles($roles)
    {
        foreach ($roles as $role) {
            $this->detachRole($role);
        }
    }
}
