<?php
namespace App\Http\Utils;

use Illuminate\Http\Request;

class PaginationHelper
{
    /**
     * 获取每页数量
     *
     * @param Request $request
     * @param int $default 默认值
     * @param int $max 最大值
     * @return int
     */
    public static function getPageSize(Request $request = null, int $default = 20, int $max = 100): int
    {
        $request = $request ?: request();

        $perPage = $request->input('page_size', $default);
        $perPage = (int) $perPage;

        // 验证参数
        if ($perPage <= 0) {
            return $default;
        }

        return min($max, $perPage);
    }

    /**
     * 获取当前页码
     *
     * @param Request $request
     * @param string $pageName 页码参数名
     * @return int
     */
    public static function getCurrentPage(Request $request = null, string $pageName = 'page'): int
    {
        $request = $request ?: request();

        $page = $request->input($pageName, 1);
        $page = (int) $page;

        return max(1, $page);
    }

    /**
     * 获取排序参数
     *
     * @param Request $request
     * @param string $defaultSort 默认排序字段
     * @param string $defaultOrder 默认排序方向
     * @return array ['field' => string, 'direction' => 'asc'|'desc']
     */
    public static function getSortParams(
        Request $request = null,
        string $defaultSort = 'id',
        string $defaultOrder = 'desc'
    ): array {
        $request = $request ?: request();

        $sortField = $request->input('sort_by', $defaultSort);
        $sortDirection = strtolower($request->input('sort_order', $defaultOrder));

        // 验证排序方向
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = $defaultOrder;
        }

        return [
            'field' => $sortField,
            'direction' => $sortDirection
        ];
    }

    /**
     * 构建分页响应数据
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Pagination\Paginator $paginator
     * @param array $additional 额外数据
     * @return array
     */
    public static function buildResponse($paginator, array $additional = []): array
    {
        $response = [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => method_exists($paginator, 'lastPage') ? $paginator->url($paginator->lastPage()) : null,
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ]
        ];

        // 如果是完整分页，添加总数信息
        if (method_exists($paginator, 'total')) {
            $response['meta']['total'] = $paginator->total();
            $response['meta']['last_page'] = $paginator->lastPage();
        }

        return array_merge($response, $additional);
    }

    /**
     * 验证并获取分页参数（一次性获取所有）
     *
     * @param Request $request
     * @param array $options
     * @return array
     */
    public static function getPaginationParams(Request $request = null, array $options = []): array
    {
        $defaults = [
            'default_per_page' => 20,
            'max_per_page' => 100,
            'default_sort' => 'created_at',
            'default_order' => 'desc',
            'page_name' => 'page'
        ];

        $config = array_merge($defaults, $options);

        return [
            'page' => self::getCurrentPage($request, $config['page_name']),
            'per_page' => self::getPageSize($request, $config['default_per_page'], $config['max_per_page']),
            'sort' => self::getSortParams($request, $config['default_sort'], $config['default_order'])
        ];
    }
}