<?php

namespace App\Admin\Controllers;

use App\Models\PersonalAccessToken;
use App\Services\PersonalAccessTokenService;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class PersonalAccessTokenController extends Controller
{
    use ModelForm;

    protected $tokenService;

    public function __construct(PersonalAccessTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('Personal Access Tokens');
            $content->description('管理用户的个人访问令牌');

            $content->body($this->grid());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(PersonalAccessToken::class, function (Grid $grid) {
            $grid->model()->orderBy('created_at', 'desc');

            $grid->id('ID')->sortable();
            $grid->column('user.name', '用户')->display(function ($name) {
                return $name ?: '未知用户';
            });
            $grid->name('令牌名称');
            $grid->column('scopes', '权限范围')->display(function ($scopes) {
                $scopes = is_array($scopes) ? $scopes : json_decode($scopes, true);
                if (empty($scopes)) {
                    return '<span class="label label-default">无权限</span>';
                }
                $html = '';
                foreach ($scopes as $scope) {
                    $html .= '<span class="label label-success" style="margin-right: 5px;">' . e($scope) . '</span>';
                }
                return $html;
            });
            $grid->last_used_at('最后使用时间');
            $grid->expires_at('过期时间');
            $grid->created_at('创建时间');

            $grid->filter(function ($filter) {
                $filter->like('name', '令牌名称');
                $filter->equal('user_id', '用户ID')->select('/admin/api/users');
                $filter->between('expires_at', '过期时间')->datetime();
                $filter->between('created_at', '创建时间')->datetime();
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
        return Admin::form(PersonalAccessToken::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->select('user_id', '用户')->options('/admin/api/users')->required();
            $form->text('name', '令牌名称')->rules('required|max:255');
            $form->textarea('token', '令牌值')->readonly();
            $form->multipleSelect('scopes', '权限范围')->options([
                'read' => '读取',
                'write' => '写入',
                'delete' => '删除',
                'admin' => '管理'
            ]);
            $form->datetime('expires_at', '过期时间')->format('YYYY-MM-DD HH:mm:ss');
            
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
}