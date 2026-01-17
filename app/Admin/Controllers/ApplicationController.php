<?php
namespace App\Admin\Controllers;

use App\Models\Application;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class ApplicationController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return Admin::content(function (Content $content) {
            $content->header('应用管理');
            $content->description('应用列表');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('应用管理');
            $content->description('编辑');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return Admin::content(function (Content $content) {
            $content->header('应用管理');
            $content->description('创建');

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
        return Admin::grid(Application::class, function (Grid $grid) {
            $grid->model()->orderBy('id', 'desc');

            $grid->id('ID')->sortable();
            $grid->column('应用名称')->display(function () {
                return $this->name.'<a href="/app/'.$this->slug.'"/index.html>访问</a>';;
            });
            $grid->column('应用标识')->display(function () {
                return $this->slug.' <a target="_blank" href="/admin/codes?app_id='.$this->id.'">管理</a>';
            });
            $grid->description('描述')->limit(50);
            $grid->status('状态')->display(function ($value) {
                $statusMap = [
                    1 => '开发中',
                    2 => '运行中', 
                    3 => '已停止',
                    4 => '已删除'
                ];
                return $statusMap[$value] ?? '未知';
            });
            $grid->created_at('创建时间');
            $grid->updated_at('更新时间');

            $grid->filter(function ($filter) {
                $filter->like('name', '应用名称');
                $filter->like('slug', '应用标识');
                $filter->equal('status', '状态')->select([
                    1 => '开发中',
                    2 => '运行中',
                    3 => '已停止',
                    4 => '已删除'
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
        return Admin::form(Application::class, function (Form $form) {
            $form->text('name', '应用名称')->rules('required|max:255');
            $form->text('slug', '应用标识')->rules('required|max:100|unique:applications,slug');
            $form->textarea('description', '应用描述');
            $form->radio('status', '状态')->options([
                1 => '开发中',
                2 => '运行中',
                3 => '已停止',
                4 => '已删除'
            ])->default(1);
        });
    }
}