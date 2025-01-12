<?php
namespace App\Admin\Controllers;

use App\Code;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

/**
 * 代码管理
 *
 */
class CodeController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('代码管理')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('详情')
            ->description('description')
            ->body($this->detail($id));
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
        return $content
            ->header('编辑')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('创建')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Code);
        $grid->model()->orderBy('id', 'desc');

        $grid->id('Id');
        $grid->name('名称');
        $grid->type('类型');
        $grid->content('代码内容')->limit ( 50 );
        $grid->status('状态');
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');

        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Code::findOrFail($id));

        $show->id('Id');
        $show->name('名称');
        $show->type('类型');
        $show->content('代码内容');
        $show->status('状态');
        $show->created_at('创建时间');
        $show->updated_at('更新时间');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Code);

        $form->text('name', '名称');
        $form->radio('type', '类型')->options(array(1=>'php',2=>'html'))->default(1);
        $form->textarea('content', '代码内容');
        $form->radio('status', '状态')->options(array(1=>'开启',2=>'关闭'))->default(1);
        return $form;
    }
}
