<?php

namespace App\Admin\Controllers;

use App\Models\LlmProvider;
use App\Services\LlmProviderService;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class LlmProviderController extends Controller
{
    use ModelForm;

    protected $service;

    public function __construct(LlmProviderService $service)
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
            $content->header('LLM供应商管理');
            $content->description('供应商列表');

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
            $content->header('LLM供应商管理');
            $content->description('编辑供应商');

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
            $content->header('LLM供应商管理');
            $content->description('创建供应商');

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
        return Admin::grid(LlmProvider::class, function (Grid $grid) {
            $query = $grid->model()->orderBy('priority', 'desc')->orderBy('name');
            
            // 如果当前用户不是管理员，只显示该用户的数据
            if (!Admin::user()->isAdministrator()) {
                $query->where(function($q) {
                    $q->where('user_id', Admin::user()->id)
                      ->orWhereNull('user_id');
                });
            }

            $grid->id('ID')->sortable();
            $grid->name('名称');
            $grid->slug('标识符');
            $grid->api_type('API类型');
            $grid->is_active('是否启用')->display(function ($value) {
                return $value ? '是' : '否';
            });
            $grid->priority('优先级');
            $grid->created_at('创建时间');
            $grid->updated_at('更新时间');

            $grid->filter(function ($filter) {
                $filter->like('name', '名称');
                $filter->equal('api_type', 'API类型');
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
        return Admin::form(LlmProvider::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->hidden('user_id')->value(auth()->id());
            $form->text('name', '名称')->rules('required|max:100');
            $form->text('slug', '标识符')->rules('required|max:50|unique:llm_providers,slug');
            $form->textarea('description', '描述');
            $form->text('base_url', 'API基础URL');
            $form->password('api_key', 'API Key')->help('供应商下所有模型共用；编辑时留空表示保持原值。');
            
            $apiTypes = [
                'openai' => 'OpenAI',
                'anthropic' => 'Anthropic', 
                'custom' => '自定义'
            ];
            $form->select('api_type', 'API类型')->options($apiTypes)->rules('required');
            
            $form->switch('is_active', '是否启用')->default(1);
            $form->number('priority', '优先级')->default(0);
            $form->json('config_schema', '配置项JSON Schema');
            $form->number('rate_limit_per_minute', '每分钟请求限制');
            $form->number('concurrent_limit', '并发限制')->default(10);
            
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
}
