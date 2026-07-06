<?php

namespace App\Admin\Controllers;

use App\Models\Setting;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class SettingController extends Controller {
	use ModelForm;
	
	/**
	 * Index interface.
	 *
	 * @return Content
	 */
	public function index() {
		return Admin::content ( function (Content $content) {
			
			$content->header ( 'header' );
			$content->description ( 'description' );
			
			$content->body ( $this->grid () );
		} );
	}
	
	/**
	 * Edit interface.
	 *
	 * @param
	 *        	$id
	 * @return Content
	 */
	public function edit($id) {
		return Admin::content ( function (Content $content) use ($id) {
			
			$content->header ( 'header' );
			$content->description ( 'description' );
			
			$content->body ( $this->form ()->edit ( $id ) );
		} );
	}
	
	/**
	 * Create interface.
	 *
	 * @return Content
	 */
	public function create() {
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
	protected function grid() {
		return Admin::grid ( Setting::class, function (Grid $grid) {
			$grid->model ()->orderBy ( 'id', 'desc' );
			
			$grid->id ( 'ID' )->sortable ();
			
			$grid->user ()->name ();
			$grid->pomo_config ( '番茄配置' )->display ( function ($config) {
				$config = Setting::normalizePomoConfig ( $config );
				return sprintf (
						'日/周/月目标：%d/%d/%d，专注/休息：%d/%d 分钟',
						$config['day_goal'],
						$config['week_goal'],
						$config['month_goal'],
						$config['focus_minutes'],
						$config['rest_minutes']
				);
			} );
			
			$grid->kindle_email ();
			
			$grid->created_at ();
			
			$grid->disableActions ();
			$grid->disableCreation ();
			$grid->disableRowSelector ();
                        $grid->filter ( function ($filter) {
                                $filter->equal ( 'user_id');
                        } );

		} );
	}
	
	/**
	 * Make a form builder.
	 *
	 * @return Form
	 */
	protected function form() {
		return Admin::form ( Setting::class, function (Form $form) {
			
			$form->display ( 'id', 'ID' );
			
			$form->display ( 'created_at', 'Created At' );
			$form->display ( 'updated_at', 'Updated At' );
		} );
	}
}
