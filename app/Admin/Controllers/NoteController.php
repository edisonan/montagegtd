<?php

namespace App\Admin\Controllers;

use App\Models\Note;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;

class NoteController extends Controller {
	use ModelForm;
	
	/**
	 * Index interface.
	 *
	 * @return Content
	 */
	public function index() {
		return Admin::content ( function (Content $content) {
			
			$content->header ( '笔记管理' );
			$content->description ( '笔记列表与公开审核' );
			
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
			
			$content->header ( '笔记管理' );
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
			
			$content->header ( '笔记管理' );
			$content->description ( 'description' );
			
			$content->body ( $this->form () );
		} );
	}
	
	/**
	 * 公开笔记审核：将 audit_status 置为 0（撤销）或 1（通过）。
	 *
	 * @param Request $request        	
	 * @param int $id        	
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function audit(Request $request, $id) {
		$note = Note::find ( $id );
		if (empty ( $note )) {
			admin_error ( '审核失败', '笔记不存在' );
			return back ();
		}
		
		$value = ( int ) $request->input ( 'value', 0 );
		if ($value !== 0 && $value !== 1) {
			admin_error ( '审核失败', '无效的审核状态' );
			return back ();
		}
		
		$note->audit_status = $value;
		$note->save ();
		
		admin_success ( '审核成功', $value === 1 ? '笔记已审核通过，将进入他人可见列表' : '笔记审核已撤销' );
		return back ();
	}
	
	/**
	 * Make a grid builder.
	 *
	 * @return Grid
	 */
	protected function grid() {
		return Admin::grid ( Note::class, function (Grid $grid) {
			$grid->model ()->orderBy ( 'id', 'desc' );
			
			$grid->id ( 'ID' )->sortable ();
			
			$grid->user ()->name ( '创建者' );
			$grid->column ( 'status', '笔记状态' )->display ( function ($value) {
				return ( int ) $value === 2 
					? '<span class="label label-success">公开</span>' 
					: '<span class="label label-default">私密</span>';
			} );
			$grid->column ( 'audit_status', '审核状态' )->display ( function ($value) {
				return ( int ) $value === 1 
					? '<span class="label label-success">已通过</span>' 
					: '<span class="label label-warning">待审核</span>';
			} );
			$grid->name ( '内容' );
			$grid->created_at ( '创建时间' );
			$grid->column ( '审核操作' )->display ( function ($value) {
				if (( int ) $this->status !== 2) {
					return '<span class="label label-default">私密笔记无需审核</span>';
				}
				$token = csrf_token ();
				$auditUrl = admin_url ( 'notes/audit/' . $this->id );
				if (( int ) $this->audit_status === 1) {
					return '<form method="post" action="' . $auditUrl . '" style="display:inline-block" onsubmit="return confirm(\'确认撤销审核？撤销后该公开笔记将不再出现在他人列表。\');">'
						. '<input type="hidden" name="_token" value="' . $token . '">'
						. '<input type="hidden" name="value" value="0">'
						. '<button type="submit" class="btn btn-xs btn-warning"><i class="fa fa-undo"></i> 撤销审核</button>'
						. '</form>';
				}
				return '<form method="post" action="' . $auditUrl . '" style="display:inline-block" onsubmit="return confirm(\'确认审核通过？通过后该公开笔记将出现在所有用户的笔记列表。\');">'
					. '<input type="hidden" name="_token" value="' . $token . '">'
					. '<input type="hidden" name="value" value="1">'
					. '<button type="submit" class="btn btn-xs btn-success"><i class="fa fa-check"></i> 审核通过</button>'
					. '</form>';
			} );
			
			$grid->disableActions ();
			$grid->disableCreation ();
			$grid->disableRowSelector ();
			$grid->filter ( function ($filter) {
				$filter->equal ( 'user_id', '用户ID' );
				$filter->equal ( 'status', '笔记状态' )->select ( [ 
						1 => '私密',
						2 => '公开' 
				] );
				$filter->equal ( 'audit_status', '审核状态' )->select ( [ 
						0 => '待审核',
						1 => '已通过' 
				] );
			} );
			
		} );
	}
	
	/**
	 * Make a form builder.
	 *
	 * @return Form
	 */
	protected function form() {
		return Admin::form ( Note::class, function (Form $form) {
			
			$form->display ( 'id', 'ID' );
			
			$form->display ( 'created_at', 'Created At' );
			$form->display ( 'updated_at', 'Updated At' );
		} );
	}
}