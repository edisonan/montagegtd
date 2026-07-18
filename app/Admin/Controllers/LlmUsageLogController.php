<?php

namespace App\Admin\Controllers;

use App\Models\LlmUsageLog;
use App\Models\LlmProvider;
use App\Models\LlmModel;
use App\Models\User;
use App\Services\LlmUsageLogService;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class LlmUsageLogController extends Controller
{
    use ModelForm;

    protected $service;

    public function __construct(LlmUsageLogService $service)
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
            $content->header('LLM使用记录管理');
            $content->description('使用记录列表');

            $content->body($this->grid());
        });
    }

    /**
     * Show interface.
     *
     * @param $id
     * @return Content
     */
    public function show($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('LLM使用记录管理');
            $content->description('查看使用记录');

            $content->body($this->detail($id));
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(LlmUsageLog::class, function (Grid $grid) {
            $query = $grid->model()->with(['provider', 'model', 'user'])->orderBy('created_at', 'desc');
            
            // 如果当前用户不是管理员，只显示该用户的数据
            if (!Admin::user()->isAdministrator()) {
                $query->where('user_id', Admin::user()->id);
            }

            $grid->id('ID')->sortable();
            $grid->column('provider.name', '供应商');
            $grid->column('model.name', '模型');
            $grid->column('user.name', '用户');
            $grid->input_tokens('输入tokens');
            $grid->output_tokens('输出tokens');
            $grid->total_tokens('总tokens');
            $grid->cost('成本');
            $grid->status('状态');
            $grid->created_at('创建时间');

            $grid->filter(function ($filter) {
                $filter->equal('provider_id', '供应商')->select(LlmProvider::pluck('name', 'id'));
                $filter->equal('model_id', '模型')->select(LlmModel::pluck('name', 'id'));
                $filter->equal('user_id', '用户')->select(User::pluck('name', 'id'));
                $filter->equal('status', '状态')->select([
                    'success' => '成功',
                    'failed' => '失败',
                    'rate_limited' => '频率限制'
                ]);
                $filter->between('created_at', '创建时间')->datetime();
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new \Encore\Admin\Show(LlmUsageLog::with(['provider', 'model', 'user'])->findOrFail($id));

        $show->id('ID');
        $show->field('provider.name', '供应商');
        $show->field('model.name', '模型');
        $show->field('user.name', '用户');
        $show->input_tokens('输入tokens');
        $show->output_tokens('输出tokens');
        $show->total_tokens('总tokens');
        $show->cost('成本');
        $show->request_time('请求耗时(秒)');
        $show->status('状态');
        $show->error_message('错误信息');
        $formatJson = function ($value) {
            if (is_null($value)) {
                return '';
            }

            if (!is_array($value) && !is_object($value)) {
                return e((string) $value);
            }

            return '<pre style="white-space: pre-wrap; word-break: break-word;">'
                . e(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT))
                . '</pre>';
        };
        $show->request_data('请求数据')->as($formatJson);
        $show->response_data('响应数据')->as($formatJson);
        $show->created_at('创建时间');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(LlmUsageLog::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->select('provider_id', '供应商')->options(LlmProvider::pluck('name', 'id'))->rules('required');
            $form->select('model_id', '模型')->options(LlmModel::pluck('name', 'id'))->rules('required');
            $form->select('user_id', '用户')->options(User::pluck('name', 'id'));
            $form->number('input_tokens', '输入tokens');
            $form->number('output_tokens', '输出tokens');
            $form->number('total_tokens', '总tokens');
            $form->decimal('cost', '成本');
            $form->decimal('request_time', '请求耗时(秒)');
            
            $statusOptions = [
                'success' => '成功',
                'failed' => '失败',
                'rate_limited' => '频率限制'
            ];
            $form->select('status', '状态')->options($statusOptions)->default('success');
            $form->textarea('error_message', '错误信息');
            $form->json('request_data', '请求数据');
            $form->json('response_data', '响应数据');
            
            $form->display('created_at', '创建时间');
        });
    }
}
