<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;

/**
 * 订阅分类控制器
 *
 * @author edison.an
 *
 */
class CategoryController extends Controller
{

    /**
     * CategoryService 实例.
     *
     * @var CategoryService
     */
    protected $categoryService;

    /**
     * 构造方法
     *
     * @param CategoryService $categoryService
     * @return void
     */
    public function __construct(CategoryService $categoryService)
    {
        $this->middleware('auth');

        $this->categoryService = $categoryService;
    }

    /**
     * 展示分类首页
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        $categorys = $this->categoryService->getList();

        return view('categorys.index', [
            'categorys' => $categorys
        ]);
    }

    /**
     * 保存分类
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);
        $categorys = $this->categoryService->getList();
        if (count($categorys) > 20) {
            throw new CustomException("超过分类数量，最多20个");
        }
        $category = $request->user()->categorys()->create(array(
            'name' => $request->name
        ));

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc($category), '/categorys');
    }

    /**
     * 删除分类
     *
     * @param Request $request
     * @param Category $category
     */
    public function destroy(Request $request, Category $category)
    {
        $this->authorize('destroy', $category);

        if (empty ($category->feeds) || count($category->feeds) == 0) {
            $category->delete();
        } else {
            throw new CustomException ('This category has Feeds! cannot delete!');
        }

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc($category), '/categorys');
    }

    /**
     * 更新分类
     *
     * @param Request $request
     * @param Category $category
     * @return
     *
     */
    public function update(Request $request, Category $category)
    {
        $this->authorize('destroy', $category);

        if ($request->method() == 'GET') {
            return view('categorys.update', array(
                'category' => $category
            ));
        }

        $this->validate($request, [
            'name' => 'required'
        ]);

        $category->update(array(
            'name' => $request->name
        ));

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc($category), '/categorys');
    }

    /**
     * 设置订阅分类的排序
     *
     * @param Request $request
     * @return
     *
     */
    public function sort(Request $request)
    {
        $this->validate($request, [
            'category_ids' => 'required'
        ]);

        $this->categoryService->setCategorySort(explode(',', $request->category_ids));

        return $this->jsonAndRedirectAutoResponse($request, ResponseDataUtil::genSimpleSucc($category), '/categorys');
    }
}
