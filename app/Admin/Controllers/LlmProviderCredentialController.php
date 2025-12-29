<?php

namespace App\Admin\Controllers;

use App\Models\LlmProviderCredential;
use App\Models\LlmProvider;
use App\Services\LlmProviderCredentialService;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class LlmProviderCredentialController extends Controller
{
    use ModelForm;

    protected $service;

    public function __construct(LlmProviderCredentialService $service)
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
            $content->header('LLM供应商凭据管理');
            $content->description('凭据列表');

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
            $content->header('LLM供应商凭据管理');
            $content->description('编辑凭据');

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
            $content->header('LLM供应商凭据管理');
            $content->description('创建凭据');

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
        return Admin::grid(LlmProviderCredential::class, function (Grid $grid) {
            $query = $grid->model()->with(['provider'])->orderBy('provider_id')->orderBy('is_default', 'desc')->orderBy('name');
            
            // 如果当前用户不是管理员，只显示该用户的数据
            if (!Admin::user()->isAdministrator()) {
                $query->where(function($q) {
                    $q->where('user_id', Admin::user()->id)
                      ->orWhereNull('user_id');
                });
            }

            $grid->id('ID')->sortable();
            $grid->column('provider.name', '供应商');
            $grid->name('凭据名称');
            $grid->is_default('默认凭据')->display(function ($value) {
                return $value ? '是' : '否';
            });
            $grid->is_active('是否启用')->display(function ($value) {
                return $value ? '是' : '否';
            });
            $grid->usage_count('使用次数');
            $grid->last_used_at('最后使用时间');
            $grid->created_at('创建时间');
            $grid->updated_at('更新时间');

            $grid->filter(function ($filter) {
                $filter->like('name', '凭据名称');
                $filter->equal('provider_id', '供应商')->select(LlmProvider::pluck('name', 'id'));
                $filter->equal('is_default', '默认凭据')->radio([
                    '' => '全部',
                    1 => '是',
                    0 => '否',
                ]);
                $filter->equal('is_active', '是否启用')->radio([
                    '' => '全部',
                    1 => '是',
                    0 => '否',
                ]);
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
        return Admin::form(LlmProviderCredential::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->hidden('user_id')->value(auth()->id());
            $form->select('provider_id', '供应商')->options(LlmProvider::pluck('name', 'id'))->rules('required');
            $form->text('name', '凭据名称')->rules('required|max:100');
            $form->password('api_key', 'API Key')->rules('required');
            $form->json('config', '额外配置');
            $form->switch('is_default', '是否为默认凭据');
            $form->switch('is_active', '是否启用')->default(1);
            $form->number('quota_limit', '配额限制');
            $form->number('quota_used', '已使用配额');
            
            $form->display('usage_count', '使用次数');
            $form->display('last_used_at', '最后使用时间');
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
}