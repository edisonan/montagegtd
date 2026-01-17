<?php

namespace App\Admin\Controllers;

use App\Models\LlmAgent;
use App\Models\LlmModel;
use App\Models\User;
use App\Services\LlmAgentService;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class LlmAgentController extends Controller
{
    use ModelForm;

    protected $service;

    public function __construct(LlmAgentService $service)
    {
        $this->service = $service;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('LLM智能体管理');
            $content->description('智能体列表');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('LLM智能体管理');
            $content->description('编辑智能体');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('LLM智能体管理');
            $content->description('创建智能体');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(LlmAgent::class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            $grid->column('user.name', '创建用户');
            $grid->name('Agent名称');
            $grid->description('描述')->display(function ($description) {
                return str_limit($description, 50);
            });
            $grid->column('model.name', '默认模型');
            $grid->is_public('是否公开')->display(function ($is_public) {
                return $is_public ? '<span class="label label-success">是</span>' : '<span class="label label-default">否</span>';
            });
            $grid->is_active('是否启用')->display(function ($is_active) {
                return $is_active ? '<span class="label label-success">启用</span>' : '<span class="label label-warning">禁用</span>';
            });
            $grid->usage_count('使用次数');
            $grid->favorite_count('收藏次数');
            $grid->builtin_slug('内置标识')->display(function ($builtin_slug) {
                return $builtin_slug ?? '—';
            });
            $grid->created_at('创建时间')->sortable();
            $grid->updated_at('更新时间')->sortable();

            $grid->filter(function ($filter) {
                $filter->like('name', '名称');
                $filter->like('description', '描述');
                $filter->equal('is_public', '是否公开')->radio([
                    '' => '全部',
                    1 => '公开',
                    0 => '私有'
                ]);
                $filter->equal('is_active', '是否启用')->radio([
                    '' => '全部',
                    1 => '启用',
                    0 => '禁用'
                ]);
                $filter->equal('user_id', '创建用户')->select(User::pluck('name', 'id'));
                $filter->equal('model_id', '默认模型')->select(LlmModel::pluck('name', 'id'));
                $filter->like('builtin_slug', '内置标识');
            });

            $grid->actions(function ($actions) {
                $actions->disableView();
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(LlmAgent::class, function (Form $form) {
            $form->display('id', 'ID');
            
            // 只有管理员可以编辑用户ID
            if (Admin::user()->isRole('administrator') || in_array(Admin::user()->email, config('admin.super_users', []))) {
                $form->select('user_id', '创建用户')->options(User::pluck('name', 'id'))->rules('required');
            } else {
                $form->hidden('user_id')->value(auth()->id());
            }
            
            $form->text('name', 'Agent名称')->rules('required|max:100');
            $form->textarea('description', '描述')->rows(3);
            $form->text('avatar', '头像URL');
            $form->select('model_id', '默认模型')->options(LlmModel::pluck('name', 'id'))->rules('required');
            $form->textarea('system_prompt', '系统提示词')->rows(8)->rules('required');
            $form->slider('temperature', '温度参数')->min(0)->max(2)->step(0.01)->attribute('style', 'width: 200px;');
            $form->slider('top_p', 'Top P参数')->min(0)->max(1)->step(0.001)->attribute('style', 'width: 200px;');
            $form->number('max_tokens', '最大输出Tokens');
            $form->number('context_length', '上下文长度')->default(4000);
            $form->textarea('tools_config', '工具配置(JSON)')->placeholder('请输入JSON格式的工具配置');
            $form->switch('is_public', '是否公开');
            $form->switch('is_active', '是否启用')->default(1);
            $form->number('usage_count', '使用次数')->default(0);
            $form->number('favorite_count', '收藏次数')->default(0);
            
            // 只有管理员才能编辑内置标识
            if (Admin::user()->isRole('administrator') || in_array(Admin::user()->email, config('admin.super_users', []))) {
                $form->text('builtin_slug', '内置标识')->placeholder('仅管理员可设置，如：general-assistant、coding-helper等');
            } else {
                $form->text('builtin_slug', '内置标识')->disabled()->help('仅管理员可编辑');
            }

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
}