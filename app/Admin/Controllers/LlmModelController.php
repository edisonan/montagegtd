<?php

namespace App\Admin\Controllers;

use App\Models\LlmModel;
use App\Models\LlmProvider;
use App\Services\LlmModelService;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class LlmModelController extends Controller
{
    use ModelForm;

    protected $service;

    public function __construct(LlmModelService $service)
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
            $content->header('LLM模型管理');
            $content->description('模型列表');

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
            $content->header('LLM模型管理');
            $content->description('编辑模型');

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
            $content->header('LLM模型管理');
            $content->description('创建模型');

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
        return Admin::grid(LlmModel::class, function (Grid $grid) {
            $query = $grid->model()->with(['provider'])->orderBy('sort_order', 'asc')->orderBy('provider_id')->orderBy('name');
            
            // 如果当前用户不是管理员，只显示该用户的数据
            if (!Admin::user()->isAdministrator()) {
                $query->where(function($q) {
                    $q->where('user_id', Admin::user()->id)
                      ->orWhereNull('user_id');
                });
            }

            $grid->id('ID')->sortable();
            $grid->column('provider.name', '供应商');
            $grid->name('模型名称');
            $grid->display_name('显示名称');
            $grid->model_type('模型类型');
            $grid->is_active('是否启用')->display(function ($value) {
                return $value ? '是' : '否';
            });
            $grid->sort_order('排序');
            $grid->created_at('创建时间');
            $grid->updated_at('更新时间');

            $grid->filter(function ($filter) {
                $filter->like('name', '模型名称');
                $filter->equal('provider_id', '供应商')->select(LlmProvider::pluck('name', 'id'));
                $filter->equal('model_type', '模型类型')->select([
                    'chat' => '对话',
                    'completion' => '补全',
                    'embedding' => '嵌入',
                    'image' => '图像'
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
        return Admin::form(LlmModel::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->hidden('user_id')->value(auth()->id());
            $form->select('provider_id', '供应商')->options(LlmProvider::pluck('name', 'id'))->rules('required');
            $form->text('name', '模型名称')->rules('required|max:100');
            $form->text('display_name', '显示名称')->rules('max:100');
            
            $modelTypes = [
                'chat' => '对话',
                'completion' => '补全',
                'embedding' => '嵌入',
                'image' => '图像'
            ];
            $form->select('model_type', '模型类型')->options($modelTypes)->rules('required');
            
            $form->number('context_length', '上下文长度');
            $form->number('max_tokens', '最大输出tokens');
            $form->decimal('input_price_per_1k', '输入价格/1K tokens');
            $form->decimal('output_price_per_1k', '输出价格/1K tokens');
            $form->switch('is_active', '是否启用')->default(1);
            $form->json('capabilities', '能力配置');
            $form->number('sort_order', '排序')->default(0);
            
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
}