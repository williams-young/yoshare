<?php

namespace App\Models;

use App\Helpers\CodeBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Request;
use Response;
use Schema;
use Validator;

class Module extends Model
{
    const ID_ARTICLE = 1;

    const STATE_DISABLE = 0;
    const STATE_ENABLE = 1;

    const STATES = [
        0 => '已禁用',
        1 => '已启用',
    ];

    protected $fillable = [
        'name',
        'title',
        'table_name',
        'groups',
        'is_lock',
        'state',
    ];

    public function getModelNameAttribute()
    {
        return $this->name;
    }

    public function getModelClassAttribute()
    {
        return __NAMESPACE__ . '\\' . $this->name;
    }

    public function getControllerNameAttribute()
    {
        return $this->name . 'Controller';
    }

    public function getPluralAttribute()
    {
        return str_plural(strtolower($this->name));
    }

    public function getSingularAttribute()
    {
        return str_singular(strtolower($this->name));
    }

    public function getPathAttribute()
    {
        return str_plural(strtolower($this->name));
    }

    public function fields()
    {
        return $this->hasMany(ModuleField::class);
    }

    public function stateName()
    {
        return array_key_exists($this->state, static::STATES) ? static::STATES[$this->state] : '';
    }

    public static function stores($input)
    {
        $input['state'] = self::STATE_ENABLE;

        $module = self::create($input);

        \Session::flash('flash_success', '添加成功');
        return true;
    }

    public static function updates($id, $input)
    {
        $module = self::find($id);

        if ($module == null) {
            \Session::flash('flash_warning', '无此记录');
            return false;
        }
        $module->update($input);

        \Session::flash('flash_success', '修改成功');
        return true;
    }

    public static function table()
    {
        $offset = Request::get('offset') ? Request::get('offset') : 0;
        $limit = Request::get('limit') ? Request::get('limit') : 20;

        $ds = new DataSource();
        $modules = static::orderBy('id')
            ->skip($offset)
            ->limit($limit)
            ->get();

        $ds->total = static::count();

        $modules->transform(function ($module) {
            return [
                'id' => $module->id,
                'name' => $module->name,
                'title' => $module->title,
                'table_name' => $module->table_name,
                'groups' => $module->groups,
                'is_lock' => $module->is_lock,
                'sort' => $module->sort,
                'state' => $module->state,
                'state_name' => $module->stateName(),
                'created_at' => empty($module->created_at) ? '' : $module->created_at->toDateTimeString(),
                'updated_at' => empty($module->updated_at) ? '' : $module->updated_at->toDateTimeString()
            ];
        });

        $ds->data = $modules;

        return Response::json($ds);
    }

    /**
     * 转换结构
     *
     * @param $id
     * @return array|mixed
     */
    public static function transform($id)
    {
        $module = Module::find($id);
        $module = [
            'id' => $module->id,
            'name' => $module->name,
            'title' => $module->title,
            'table_name' => $module->table_name,
            'model_class' => $module->model_class,
            'fa_icon' => $module->fa_icon,
            'groups' => explode(',', $module->groups),
            'columns' => $module->fields->map(function ($field) {
                return [
                    'id' => $field->id,
                    'name' => $field->name,
                    'title' => $field->title,
                    'label' => $field->label,
                    'type' => $field->type,
                    'show' => $field->column_show,
                    'align' => $field->column_align,
                    'width' => $field->column_width,
                    'editable' => $field->column_editable,
                    'formatter' => $field->column_formatter,
                    'index' => $field->column_index,

                ];
            }),
            'editors' => $module->fields->map(function ($field) {
                return [
                    'id' => $field->id,
                    'name' => $field->name,
                    'title' => $field->title,
                    'label' => $field->label,
                    'type' => $field->editor_type,
                    'show' => $field->editor_show,
                    'options' => $field->editor_options,
                    'columns' => $field->editor_columns,
                    'rows' => $field->editor_rows,
                    'required' => $field->required,
                    'readonly' => $field->editor_readonly,
                    'group' => $field->editor_group,
                    'index' => $field->editor_index,
                ];
            }),
            'fields' => $module->fields->map(function ($field) {
                return [
                    'id' => $field->id,
                    'name' => $field->name,
                    'title' => $field->title,
                    'label' => $field->label,
                    'type' => $field->type,
                    'default' => $field->default,
                    'required' => $field->required,
                    'unique' => $field->unique,
                    'min_length' => $field->min_length,
                    'max_length' => $field->max_length,
                    'system' => $field->system,
                    'index' => $field->index,
                    'column' => [
                        'name' => $field->name,
                        'title' => $field->title,
                        'label' => $field->label,
                        'type' => $field->type,
                        'show' => $field->column_show,
                        'align' => $field->column_align,
                        'width' => $field->column_width,
                        'editable' => $field->column_editable,
                        'formatter' => $field->column_formatter,
                        'index' => $field->column_index,

                    ],
                    'editor' => [
                        'name' => $field->name,
                        'title' => $field->title,
                        'label' => $field->label,
                        'type' => $field->editor_type,
                        'show' => $field->editor_show,
                        'options' => $field->editor_options,
                        'columns' => $field->editor_columns,
                        'rows' => $field->editor_rows,
                        'required' => $field->required,
                        'readonly' => $field->editor_readonly,
                        'group' => $field->editor_group,
                        'index' => $field->editor_index,
                    ]
                ];
            }),
        ];

        $module = json_decode(json_encode($module));

        //数组转对象数组
        $groups = [];
        foreach ($module->groups as $group) {
            $groups[] = (object)['name' => $group];
        }

        $module->groups = $groups;

        //编辑器分组
        foreach ($module->groups as $group) {
            //过滤
            $group->editors = array_filter($module->editors, function ($editor) use ($group) {
                return $editor->show && $editor->group == $group->name;
            });

            //分组排序
            $group->editors = array_values(array_sort($group->editors, function ($editor) {
                return $editor->index;
            }));
        }

        //表格列过滤
        $module->columns = array_filter($module->columns, function ($column) {
            return $column->show;
        });

        //表格列排序
        $module->columns = array_values(array_sort($module->columns, function ($column) {
            return $column->index;
        }));

        return $module;
    }

    /**
     * 生成数据结构
     * @param $module
     */
    public static function migrate($module)
    {
        if (!Schema::hasTable($module->table_name)) {
            Schema::create($module->table_name, function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
            });
        }

        //删除字段
        $old_fields = Schema::getColumnListing($module->table_name);

        $new_fields = [];
        foreach ($module->fields as $field) {
            $new_fields[] = $field->name;
        }
        $fields = array_diff($old_fields, $new_fields);
        foreach ($fields as $field) {
            Schema::table($module->table_name, function (Blueprint $table) use ($field) {
                $table->dropColumn($field);
            });
        }

        foreach ($module->fields as $key => $field) {
            if ($key == 0) {
                continue;
            } else {
                $previous = $module->fields[$key - 1];
            }
            if (Schema::hasColumn($module->table_name, $field->name)) {
                //修改字段
                Schema::table($module->table_name, function (Blueprint $table) use ($field, $previous) {
                    switch ($field->type) {
                        case ModuleField::TYPE_INTEGER:
                        case ModuleField::TYPE_ENTITY:
                            $table->integer($field->name)->after($previous->name)->change();
                            break;
                        case ModuleField::TYPE_TEXT:
                        case ModuleField::TYPE_IMAGE:
                        case ModuleField::TYPE_AUDIO:
                        case ModuleField::TYPE_VIDEO:
                        case ModuleField::TYPE_IMAGES:
                        case ModuleField::TYPE_AUDIOS:
                        case ModuleField::TYPE_VIDEOS:
                            $table->text($field->name)->after($previous->name)->change();
                            break;
                        case ModuleField::TYPE_LONG_TEXT:
                        case ModuleField::TYPE_HTML:
                            $table->longText($field->name)->after($previous->name)->change();
                            break;
                        case ModuleField::TYPE_FLOAT:
                            $table->float($field->name)->after($previous->name)->change();
                            break;
                        case ModuleField::TYPE_DATETIME:
                            $table->datetime($field->name)->nullable()->after($previous->name)->change();
                            break;
                    }
                });
            } else {
                //新增字段
                Schema::table($module->table_name, function (Blueprint $table) use ($field, $previous) {
                    switch ($field->type) {
                        case ModuleField::TYPE_INTEGER:
                        case ModuleField::TYPE_ENTITY:
                            $table->integer($field->name)->after($previous->name)->comment($field->title);
                            break;
                        case  ModuleField::TYPE_TEXT:
                        case ModuleField::TYPE_IMAGE:
                        case ModuleField::TYPE_AUDIO:
                        case ModuleField::TYPE_VIDEO:
                        case ModuleField::TYPE_IMAGES:
                        case ModuleField::TYPE_AUDIOS:
                        case ModuleField::TYPE_VIDEOS:
                            $table->text($field->name)->after($previous->name)->comment($field->title);
                            break;
                        case ModuleField::TYPE_LONG_TEXT:
                        case ModuleField::TYPE_HTML:
                            $table->text($field->name)->after($previous->name)->comment($field->title);
                            break;
                        case ModuleField::TYPE_FLOAT:
                            $table->float($field->name)->after($previous->name)->comment($field->title);
                            break;
                        case ModuleField::TYPE_DATETIME:
                            $table->datetime($field->name)->nullable()->after($previous->name)->comment($field->title);
                            break;
                    }
                });
            }
        }
    }

    /**
     * 生成模块代码
     *
     * @param $module
     */
    public static function generate($module)
    {
        //检查代码是否已生成

        //生成model
        CodeBuilder::createModel($module);

        //生成controller
        CodeBuilder::createController($module);

        //生成view
        CodeBuilder::createViews($module);

        //生成route
        CodeBuilder::createRoute($module);

        //生成permission
        CodeBuilder::appendPermissions($module);
    }

    /**
     * 验证
     *
     * @param $module
     * @param $input
     * @return \Illuminate\Validation\Validator
     */
    public static function validate($module, $input)
    {
        $rules = [];
        $messages = [];
        foreach ($module->fields as $field) {
            $rule = '';
            if ($field->required) {
                $rule .= 'required|';
                $messages[$field->name . '.required'] = ($field->type == ModuleField::TYPE_ENTITY ? '请选择' : '请输入') . $field->label;
            }
            if ($field->min_length > 0) {
                $rule .= 'min:' . $field->min_length . '|';
                $messages[$field->name . '.min'] = $field->label . '至少' . $field->min_length . '个字符';
            }
            if ($field->max_length > 0) {
                $rule .= 'max:' . $field->max_length . '|';
                $messages[$field->name . '.max'] = $field->label . '最多' . $field->max_length . '个字符';
            }
            if ($field->unique) {
                $rule .= 'unique:' . $module->table_name;
                $messages[$field->name . '.unique'] = $field->label . '已存在';
            }
            if (in_array($field->type, [ModuleField::TYPE_INTEGER, ModuleField::TYPE_FLOAT])) {
                $rule .= 'digits|';
                $messages[$field->name . '.unique'] = $field->label . '请输入数字';
            }
            if ($rule != "") {
                $rules[$field->name] = trim($rule, '|');
            }
        }

        return Validator::make($input, $rules, $messages);
    }
}