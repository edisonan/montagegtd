<?php
namespace App\Admin\Controllers;

use App\Models\Code;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

/**
 * 代码管理
 *
 */
class CodeController extends Controller
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
        return Admin::content ( function (Content $content) {

            $content->header ( 'header' );
            $content->description ( 'description' );

            $content->body ( $this->grid () );
        } );
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
        return Admin::content ( function (Content $content) use ($id) {

            $content->header ( 'header' );
            $content->description ( 'description' );

            $content->body ( $this->form ()->edit ( $id ) );
        } );
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return Admin::content ( function (Content $content) {

            $content->header ( 'header' );
            $content->description ( 'description' );

            $content->body ( $this->form () );
        } );
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Code::class, function (Grid $grid) {
            $grid->model()->orderBy('id', 'desc');

            $grid->id('Id');
            $grid->column('名称和类型')->display(function () {
                $typeText = '';
                $type = $this->type;
                $typeMap = array(1=>'php',2=>'html');
                if (isset($typeMap[$type])) {
                    $typeText = $typeMap[$type];
                }
                return '【' . $typeText . '】'.$this->name;
            });
            $grid->content('代码内容')->limit(50);
            $grid->status('状态')->display(function ($value) {
                return $value == 1? '启用' : '禁用';
            });
            $grid->created_at('创建时间');
            $grid->updated_at('更新时间');

            $grid->actions(function ($actions) {
                $actions->disableDelete();
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
        return Admin::form ( Code::class, function (Form $form) {
            $form->text('name', '名称');
            $form->radio('type', '类型')->options(array(1=>'php',2=>'html'))->default(1);
            $form->textarea('content', '代码内容');
            $form->radio('status', '状态')->options(array(1=>'开启',2=>'关闭'))->default(1);
        });
    }
}
