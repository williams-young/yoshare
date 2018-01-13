<?php

namespace App\Http\Controllers;

use App\Events\UserLogEvent;
use App\Jobs\PublishPage;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Domain;
use App\Models\Module;
use App\Models\UserLog;
use App\Models\Member;
use App\Models\Goods;
use Auth;
use Carbon\Carbon;
use Gate;
use Request;
use Response;

/**
 * 购物车
 */
class CartController extends Controller
{
    protected $base_url = '/admin/carts';
    protected $view_path = 'admin.carts';
    protected $module;

    public function __construct()
    {
        $this->module = Module::where('name', 'Cart')->first();
    }

    public function show(Domain $domain, $id)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $cart = Cart::find($id);
        if (empty($cart)) {
            return abort(404);
        }
        $cart->incrementClick();

        return view('themes.' . $domain->theme->name . '.carts.detail', ['site' => $domain->site, 'cart' => $cart]);
    }

    public function slug(Domain $domain, $slug)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $cart = Cart::where('slug', $slug)
            ->first();
        if (empty($cart)) {
            return abort(404);
        }
        $cart->incrementClick();

        return view('themes.' . $domain->theme->name . '.carts.detail', ['site' => $domain->site, 'cart' => $cart]);
    }

    public function lists(Domain $domain)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $carts = Cart::where('state', Cart::STATE_PUBLISHED)
            ->orderBy('sort', 'desc')
            ->get();

        return view('themes.' . $domain->theme->name . '.carts.index', ['site' => $domain->site, 'module' => $this->module, 'carts' => $carts]);
    }

    public function index()
    {
        if (Gate::denies('@cart')) {
            return abort(403);
        }

        $module = Module::transform($this->module->id);

        return view($this->view_path . '.index', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function create()
    {
        if (Gate::denies('@cart-create')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $module = Module::transform($this->module->id);

        return view('admin.contents.create', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function edit($id)
    {
        if (Gate::denies('@cart-edit')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $module = Module::transform($this->module->id);

        $cart = call_user_func([$this->module->model_class, 'find'], $id);
        $cart->images = null;
        $cart->videos = null;
        $cart->audios = null;
        $cart->tags = $cart->tags()->pluck('name')->toArray();

        return view('admin.contents.edit', ['module' => $module, 'content' => $cart, 'base_url' => $this->base_url]);
    }

    public function store()
    {
        $input = Request::all();
        $input['site_id'] = Auth::user()->site_id;
        $input['user_id'] = Auth::user()->id;

        $validator = Module::validate($this->module, $input);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $cart = Cart::stores($input);

        event(new UserLogEvent(UserLog::ACTION_CREATE . '购物车', $cart->id, $this->module->model_class));

        \Session::flash('flash_success', '添加成功');
        return redirect($this->base_url);
    }

    public function update($id)
    {
        $input = Request::all();

        $validator = Module::validate($this->module, $input);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $cart = Cart::updates($id, $input);

        event(new UserLogEvent(UserLog::ACTION_UPDATE . '购物车', $cart->id, $this->module->model_class));

        \Session::flash('flash_success', '修改成功!');
        return redirect($this->base_url);
    }

    public function comments($refer_id)
    {
        $refer_type = $this->module->model_class;
        return view('admin.comments.list', compact('refer_id', 'refer_type'));
    }

    public function save($id)
    {
        $cart = Cart::find($id);

        if (empty($cart)) {
            return;
        }

        $cart->update(Request::all());
    }

    public function sort()
    {
        return Cart::sort();
    }

    public function top($id)
    {
        $cart = Cart::find($id);
        $cart->top = !$cart->top;
        $cart->save();
    }

    public function tag($id)
    {
        $tag = request('tag');
        $cart = Cart::find($id);
        if ($cart->tags()->where('name', $tag)->exists()) {
            $cart->tags()->where('name', $tag)->delete();
        } else {
            $cart->tags()->create([
                'site_id' => $cart->site_id,
                'name' => $tag,
                'sort' => strtotime(Carbon::now()),
            ]);
        }
    }

    public function state()
    {
        $input = request()->all();
        Cart::state($input);

        $ids = $input['ids'];
        $stateName = Cart::getStateName($input['state']);

        //记录日志
        foreach ($ids as $id) {
            event(new UserLogEvent('变更' . '购物车' . UserLog::ACTION_STATE . ':' . $stateName, $id, $this->module->model_class));
        }

        //发布页面
        $site = auth()->user()->site;
        if ($input['state'] == Cart::STATE_PUBLISHED) {
            foreach ($ids as $id) {
                $this->dispatch(new PublishPage($site, $this->module, $id));
            }
        }
    }

    public function table()
    {
        return Cart::table();
    }

    public function categories()
    {
        return Response::json(Category::tree('', 0, $this->module->id));
    }


    public function cart(Domain $domain)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $mark = 'cart';
        $member_id = Member::getMember()->id;
        $goods_ids = Cart::where('member_id', $member_id)
            ->pluck('goods_id')
            ->toArray();

        $goodses = Goods::whereIn('id', $goods_ids)
                    ->get();

        $numbers = Cart::where('member_id', $member_id)
            ->pluck('number')
            ->toArray();

        $number = !empty($numbers) ? array_sum($numbers) : 0;

        return view('themes.' . $domain->theme->name . '.cart.index', ['number' => $number, 'mark' => $mark, 'goodses' => $goodses]);
    }

    public function add($goods_id)
    {
        $input = Request::all();

        try {
            $member = Auth::guard('web')->user();

            if (!$member) {
                return $this->responseError('登录已失效,请重新登录');
            }
        } catch (Exception $e) {
            return $this->responseError('登录已失效,请重新登录');
        }

        try {
            $input['goods_id'] = $goods_id;
            $input['site_id'] = Member::getMember()->site_id;
            $input['member_id'] = Member::getMember()->id;
            $type = Member::getMember()->type;

            //查询此用户会员等级，普通=0（额外0），黄金=1（额外1），钻石=2（额外2）；非普通会员，购物车是否已有此盘，如果有则更新数量+1，没有则添加购物车记录；
            $numbers = Cart::where('member_id', $input['member_id'])
                ->pluck('number')
                ->toArray();

            $number = !empty($numbers) ? array_sum($numbers) : 0;

            if($number == $type+1){
                return $this->responseError('已达到您的租盘上限！');
            }

            if($number > 0 && $number < $type+1 ){
                Cart::where('goods_id', $goods_id)
                    ->where('member_id', $input['member_id'])
                    ->increment('number');

                $carts = Cart::where('member_id', $input['member_id'])
                    ->get();
            } else{
                Cart::stores($input);

                $carts = Cart::where('member_id', $input['member_id'])
                    ->get();
            }

            return $this->responseSuccess($carts);
        } catch (Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
