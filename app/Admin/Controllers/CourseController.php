<?php

namespace App\Admin\Controllers;

use App\Models\Course;
use App\Services\CourseService;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class CourseController extends Controller
{
    use ModelForm;

    protected $service;

    public function __construct(CourseService $service)
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
            $content->header('课程管理');
            $content->description('课程列表');

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
            $content->header('课程管理');
            $content->description('编辑课程');

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
            $content->header('课程管理');
            $content->description('创建课程');

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
        return Admin::grid(Course::class, function (Grid $grid) {
            $grid->model()->orderBy('created_at', 'desc');

            $grid->id('ID')->sortable();
            $grid->title('课程标题');
            $grid->instructor('讲师');
            $grid->platform('平台');
            $grid->difficulty('难度');
            $grid->estimated_hours('预计时长(小时)');
            $grid->column('public_status', '审核状态')->display(function ($value) {
                switch ($value) {
                    case 1:
                        return '<span class="label label-default">私有</span>';
                    case 2:
                        return '<span class="label label-warning">待审核</span>';
                    case 3:
                        return '<span class="label label-success">已审核</span>';
                    default:
                        return '<span class="label label-default">未知</span>';
                }
            });
            $grid->created_at('创建时间');

            $grid->filter(function ($filter) {
                $filter->like('title', '课程标题');
                $filter->like('instructor', '讲师');
                $filter->equal('platform', '平台')->select([
                    'Coursera' => 'Coursera',
                    'Udemy' => 'Udemy',
                    'B站' => 'B站',
                    'YouTube' => 'YouTube',
                    '其他' => '其他'
                ]);
                $filter->equal('difficulty', '难度')->select([
                    'beginner' => '初级',
                    'intermediate' => '中级',
                    'advanced' => '高级'
                ]);
                $filter->equal('public_status', '审核状态')->select([
                    1 => '私有',
                    2 => '待审核',
                    3 => '已审核'
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
        return Admin::form(Course::class, function (Form $form) {
            $form->display('id', 'ID');
            $form->text('title', '课程标题')->rules('required|max:255');
            $form->text('instructor', '讲师');
            $form->select('platform', '平台')->options([
                'Coursera' => 'Coursera',
                'Udemy' => 'Udemy',
                'B站' => 'B站',
                'YouTube' => 'YouTube',
                '其他' => '其他'
            ]);
            $form->url('public_url', '课程链接');
            $form->text('cover_image_url', '封面图片URL');
            $form->textarea('description', '描述');
            $form->select('difficulty', '难度')->options([
                'beginner' => '初级',
                'intermediate' => '中级',
                'advanced' => '高级'
            ])->default('beginner');
            $form->number('estimated_hours', '预计学习时长(小时)');
            $form->tags('tags', '标签');
            $form->select('public_status', '审核状态')->options([
                1 => '私有',
                2 => '待审核',
                3 => '已审核'
            ])->default(2);
            
            $form->display('created_by', '创建者ID');
            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
        });
    }
}