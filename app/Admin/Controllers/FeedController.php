<?php

namespace App\Admin\Controllers;

use App\Feed;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class FeedController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('header');
            $content->description('description');

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

            $content->header('header');
            $content->description('description');

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

            $content->header('header');
            $content->description('description');

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
        return Admin::grid(Feed::class, function (Grid $grid) {

            $grid->id('ID')->sortable();

            $grid->user()->name();
            $grid->feed_name();
            $grid->url();
            $grid->sub_count();
            $grid->is_recommend()->display(function ($is_recommend) {
			    return $is_recommend == 1?'是':'否';
			})->sortable();
			
            $grid->status()->display(function ($status) {
			    return $status == 1?'启用':'关闭';
			})->sortable();
            $grid->created_at();
            $grid->updated_at();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Feed::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->display('is_recommend', 'is_recommend');
            $form->display('lock_name', 'lock_name');
            $form->display('orders', 'orders');
            $form->display('type', 'type');
            $form->display('status', 'status');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
