<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Category;
use App\Models\FeedSub;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $categories = Category::where('user_id', $userId)
            ->orderBy('category_order')
            ->orderBy('id', 'desc')
            ->get();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($categories));
    }

    public function show(Request $request, Category $category)
    {
        $userId = (int)$this->getAuthUserId($request);
        if ((int)$category->user_id !== $userId) {
            throw new CustomException('分类不存在');
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($category));
    }

    public function store(Request $request)
    {
        $this->validate($request, array(
            'name' => 'required|string|max:100',
        ));

        $userId = (int)$this->getAuthUserId($request);
        $count = Category::where('user_id', $userId)->count();
        if ($count >= 20) {
            throw new CustomException('超过分类数量，最多20个');
        }

        $category = Category::create(array(
            'name' => (string)$request->input('name'),
            'user_id' => $userId,
            'category_order' => (int)Category::where('user_id', $userId)->max('category_order') + 1,
        ));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($category));
    }

    public function update(Request $request, Category $category)
    {
        $this->validate($request, array(
            'name' => 'required|string|max:100',
        ));

        $userId = (int)$this->getAuthUserId($request);
        if ((int)$category->user_id !== $userId) {
            throw new CustomException('分类不存在');
        }

        $category->name = (string)$request->input('name');
        $category->save();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($category->fresh()));
    }

    public function destroy(Request $request, Category $category)
    {
        $userId = (int)$this->getAuthUserId($request);
        if ((int)$category->user_id !== $userId) {
            throw new CustomException('分类不存在');
        }

        $hasFeeds = FeedSub::where('user_id', $userId)
            ->where('category_id', $category->id)
            ->where('status', 1)
            ->exists();
        if ($hasFeeds) {
            throw new CustomException('该分类下仍有订阅，无法删除');
        }

        $category->delete();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function sort(Request $request)
    {
        $this->validate($request, array(
            'category_ids' => 'required',
        ));

        $this->categoryService->setCategorySort(explode(',', (string)$request->input('category_ids')));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }
}
