<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Application;
use App\Models\Code;
use App\Models\CodeHistory;
use App\Services\AppVirtualTableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

/**
 * 应用工作台管理接口（CLI / 自动化用）
 *
 * 安全模型：
 * 1. 路由挂 hybrid.token:write；
 * 2. 控制器内再做“应用管理者白名单”二次校验（config/app_manage.php：
 *    APP_MANAGE_ALLOWED_EMAILS / APP_MANAGE_ALLOWED_USER_IDS）。
 *    普通用户即使自建 admin PAT 也不在白名单内，一律 403。
 * 3. 出于安全考虑，API 层禁止创建/更新 type=1(PHP) 文件——PHP 文件会被
 *    eval 执行，只允许在后台人工维护。
 */
class AppManageController extends Controller
{
    /** 允许的管理文件类型：2=html 3=js 4=css 5=json（禁止 1=php） */
    const ALLOWED_CODE_TYPES = array(2, 3, 4, 5);

    /** @var AppVirtualTableService */
    protected $virtualTableService;

    public function __construct(AppVirtualTableService $virtualTableService)
    {
        $this->virtualTableService = $virtualTableService;
    }

    // ------------------------------------------------------------------
    // 权限
    // ------------------------------------------------------------------

    protected function assertAppManager(Request $request)
    {
        $user = $this->getAuthUser($request);
        if (!$user) {
            return $this->forbidden('未认证用户');
        }

        $emails = config('app_manage.allowed_emails', array());
        $userIds = config('app_manage.allowed_user_ids', array());

        $email = strtolower(trim((string) $user->email));
        $allowedEmails = array_values(array_filter(array_map(function ($item) {
            return strtolower(trim((string) $item));
        }, $emails)));

        $matched = false;
        if (in_array($email, $allowedEmails, true)) {
            $matched = true;
        }
        $uid = (int) $user->id;
        if (!$matched && $uid > 0 && in_array($uid, $userIds, true)) {
            $matched = true;
        }

        if (!$matched) {
            return $this->forbidden('当前用户不在应用管理者白名单内');
        }

        return null;
    }

    protected function forbidden($message)
    {
        return response()->json(array(
            'code' => 403,
            'msg' => $message,
            'result' => array(),
        ), 403);
    }

    protected function fail($message, $status = 422, $code = 1001)
    {
        return response()->json(array(
            'code' => $code,
            'msg' => $message,
            'result' => array(),
        ), $status);
    }

    protected function ok(Request $request, $result = array())
    {
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($result));
    }

    // ------------------------------------------------------------------
    // 应用
    // ------------------------------------------------------------------

    public function index(Request $request)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $applications = Application::query()
            ->withCount('codes')
            ->orderBy('id', 'desc')
            ->get();

        return $this->ok($request, array(
            'applications' => $applications->map(function (Application $application) {
                return $this->serializeApplication($application);
            })->values(),
        ));
    }

    public function show(Request $request, $id)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $application = Application::query()
            ->with(array('codes' => function ($query) {
                $query->orderByRaw("CASE WHEN path IN ('index.html', '/index.html', 'index.php', '/index.php') THEN 0 ELSE 1 END")
                    ->orderBy('path')
                    ->orderBy('id');
            }))
            ->findOrFail($id);

        return $this->ok($request, array(
            'application' => $this->serializeApplication($application),
            'files' => $application->codes->map(function (Code $code) use ($application) {
                return $this->serializeCode($code, $application);
            })->values(),
            'preview_url' => $this->buildPreviewUrl($application->slug, 'index.html'),
        ));
    }

    public function store(Request $request)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $data = $this->validate($request, array(
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:applications,slug|regex:/^[a-z0-9][a-z0-9-]*$/',
            'description' => 'nullable|string',
            'status' => 'nullable|integer|in:1,2,3,4',
            'auth_mode' => 'nullable|string|in:public,login,whitelist,pat',
        ));

        $application = Application::create(array(
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 1,
            'auth_mode' => $data['auth_mode'] ?? 'public',
        ));

        return $this->ok($request, array(
            'application' => $this->serializeApplication($application),
            'preview_url' => $this->buildPreviewUrl($application->slug, 'index.html'),
        ));
    }

    public function update(Request $request, $id)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $application = Application::findOrFail($id);

        $data = $this->validate($request, array(
            'name' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:100|unique:applications,slug,' . $application->id . '|regex:/^[a-z0-9][a-z0-9-]*$/',
            'description' => 'nullable|string',
            'status' => 'nullable|integer|in:1,2,3,4',
            'auth_mode' => 'nullable|string|in:public,login,whitelist,pat',
        ));

        foreach (array('name', 'slug', 'description', 'status', 'auth_mode') as $field) {
            if (array_key_exists($field, $data)) {
                $application->{$field} = $data[$field];
            }
        }
        $application->save();

        return $this->ok($request, array(
            'application' => $this->serializeApplication($application->fresh()),
        ));
    }

    public function destroy(Request $request, $id)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $application = Application::findOrFail($id);
        $application->delete();

        return $this->ok($request, array(
            'deleted' => true,
        ));
    }

    // ------------------------------------------------------------------
    // 文件（codes）
    // ------------------------------------------------------------------

    public function storeCode(Request $request, $id)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $application = Application::findOrFail($id);

        $data = $this->validate($request, array(
            'name' => 'required|string|max:255',
            'path' => 'required|string|max:500',
            'type' => 'required|integer|in:2,3,4,5',
            'status' => 'nullable|integer|in:1,2',
            'auth_mode' => 'nullable|string|in:public,login,whitelist,pat',
            'content' => 'nullable|string',
        ));

        $normalizedPath = $this->normalizePath($data['path']);
        $exists = Code::query()
            ->where('app_id', $application->id)
            ->where(function ($query) use ($normalizedPath) {
                $query->where('path', $normalizedPath)
                    ->orWhere('path', '/' . $normalizedPath);
            })
            ->exists();

        if ($exists) {
            return $this->fail('文件路径已存在', 422, 1001);
        }

        $code = Code::create(array(
            'app_id' => $application->id,
            'name' => $data['name'],
            'path' => $normalizedPath,
            'type' => (int) $data['type'],
            'status' => $data['status'] ?? 1,
            'auth_mode' => $data['auth_mode'] ?? null,
            'content' => array_key_exists('content', $data) ? (string) $data['content'] : '',
        ));

        return $this->ok($request, array(
            'file' => $this->serializeCode($code->fresh(), $application),
        ));
    }

    public function updateCode(Request $request, $id, $codeId)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $application = Application::findOrFail($id);
        $code = Code::query()
            ->where('app_id', $application->id)
            ->findOrFail($codeId);

        $this->assertApiAllowedType($code->type);

        $data = $this->validate($request, array(
            'name' => 'nullable|string|max:255',
            'path' => 'nullable|string|max:500',
            'type' => 'nullable|integer|in:2,3,4,5',
            'status' => 'nullable|integer|in:1,2',
            'auth_mode' => 'nullable|string|in:public,login,whitelist,pat',
            'content' => 'nullable|string',
        ));

        $originalContent = (string) $code->content;

        foreach (array('name', 'path', 'type', 'status', 'auth_mode', 'content') as $field) {
            if (array_key_exists($field, $data)) {
                $code->{$field} = $data[$field];
            }
        }
        if (array_key_exists('path', $data)) {
            $code->path = $this->normalizePath($data['path']);
        }
        $code->save();

        if ($originalContent !== (string) $code->content
            && \Illuminate\Support\Facades\Schema::hasTable((new CodeHistory())->getTable())) {
            CodeHistory::create(array(
                'code_id' => $code->id,
                'content' => (string) $code->content,
            ));
        }

        return $this->ok($request, array(
            'file' => $this->serializeCode($code->fresh(), $application),
        ));
    }

    public function destroyCode(Request $request, $id, $codeId)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $application = Application::findOrFail($id);
        $code = Code::query()
            ->where('app_id', $application->id)
            ->findOrFail($codeId);

        $this->assertApiAllowedType($code->type);
        $code->delete();

        return $this->ok($request, array(
            'deleted' => true,
        ));
    }

    public function codeHistory(Request $request, $id, $codeId)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $application = Application::findOrFail($id);
        $code = Code::query()
            ->where('app_id', $application->id)
            ->findOrFail($codeId);

        $history = array();
        if (\Illuminate\Support\Facades\Schema::hasTable((new CodeHistory())->getTable())) {
            $history = CodeHistory::query()
                ->where('code_id', $code->id)
                ->orderBy('created_at', 'desc')
                ->get(array('id', 'created_at'))
                ->map(function ($item) {
                    return array(
                        'id' => (int) $item->id,
                        'created_at' => optional($item->created_at)->toDateTimeString(),
                    );
                })
                ->values();
        }

        return $this->ok($request, array(
            'history' => $history,
            'current' => array(
                'content' => (string) $code->content,
                'updated_at' => optional($code->updated_at)->toDateTimeString(),
            ),
        ));
    }

    public function rollbackCode(Request $request, $id, $codeId, $historyId)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $application = Application::findOrFail($id);
        $code = Code::query()
            ->where('app_id', $application->id)
            ->findOrFail($codeId);

        $this->assertApiAllowedType($code->type);

        if (!\Illuminate\Support\Facades\Schema::hasTable((new CodeHistory())->getTable())) {
            return $this->fail('历史功能不可用', 422, 1002);
        }

        $history = CodeHistory::query()
            ->where('code_id', $code->id)
            ->findOrFail($historyId);

        CodeHistory::create(array(
            'code_id' => $code->id,
            'content' => (string) $code->content,
        ));

        $code->content = (string) $history->content;
        $code->save();

        return $this->ok($request, array(
            'file' => $this->serializeCode($code->fresh(), $application),
        ));
    }

    // ------------------------------------------------------------------
    // 虚拟数据表
    // ------------------------------------------------------------------

    public function virtualTables(Request $request, $id)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $application = Application::findOrFail($id);

        $tables = $this->virtualTableService->listTablesForApplication($application)
            ->map(function ($table) {
                return $this->virtualTableService->serializeTable($table);
            })
            ->values();

        return $this->ok($request, array(
            'tables' => $tables,
        ));
    }

    public function storeVirtualTable(Request $request, $id)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $application = Application::findOrFail($id);

        $data = $this->validate($request, array(
            'name' => 'required|string|max:120',
            'slug' => 'required|string|max:80|regex:/^[a-z0-9][a-z0-9_]*$/',
            'description' => 'nullable|string',
            'status' => 'nullable|integer|in:0,1',
        ));

        try {
            $table = $this->virtualTableService->createTable($application, $data)->load('fields');
            return $this->ok($request, array(
                'table' => $this->virtualTableService->serializeTable($table),
            ));
        } catch (InvalidArgumentException $e) {
            return $this->fail($e->getMessage(), 422, 1001);
        } catch (Throwable $e) {
            Log::error('app-manage create virtual table failed: ' . $e->getMessage());
            return $this->fail('创建虚拟表失败', 500, 1000);
        }
    }

    public function storeVirtualField(Request $request, $id, $tableId)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $application = Application::findOrFail($id);

        $data = $this->validate($request, array(
            'name' => 'required|string|max:120',
            'slug' => 'required|string|max:80|regex:/^[a-z0-9][a-z0-9_]*$/',
            'type' => 'required|string|in:string,text,integer,decimal,boolean,date,datetime,json',
            'length' => 'nullable|integer|min:1|max:1000',
            'nullable' => 'nullable|integer|in:0,1',
            'default_enabled' => 'nullable|integer|in:0,1',
            'default_value' => 'nullable|string|max:255',
            'indexed' => 'nullable|integer|in:0,1',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'nullable|integer|in:0,1',
        ));

        try {
            $table = $this->virtualTableService->findTableForApplication($application, $tableId);
            $field = $this->virtualTableService->createField($table, $data);
            $table = $table->fresh()->load('fields');

            return $this->ok($request, array(
                'field' => $this->virtualTableService->serializeField($field),
                'table' => $this->virtualTableService->serializeTable($table),
            ));
        } catch (InvalidArgumentException $e) {
            return $this->fail($e->getMessage(), 422, 1001);
        } catch (Throwable $e) {
            Log::error('app-manage create virtual field failed: ' . $e->getMessage());
            return $this->fail('添加字段失败', 500, 1000);
        }
    }

    public function virtualTableRecords(Request $request, $id, $tableId)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $application = Application::findOrFail($id);

        try {
            $table = $this->virtualTableService->findTableForApplication($application, $tableId);
            $records = $this->virtualTableService->listRecords(
                $table,
                $request->query('page', 1),
                $request->query('per_page', 100)
            );

            return $this->ok($request, array(
                'records' => $records,
            ));
        } catch (InvalidArgumentException $e) {
            return $this->fail($e->getMessage(), 422, 1001);
        } catch (Throwable $e) {
            Log::error('app-manage list records failed: ' . $e->getMessage());
            return $this->fail('加载记录失败', 500, 1000);
        }
    }

    public function storeVirtualRecord(Request $request, $id, $tableId)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $application = Application::findOrFail($id);

        try {
            $table = $this->virtualTableService->findTableForApplication($application, $tableId);
            $record = $this->virtualTableService->createRecord($table, $request->except(array('_token', '_method')));

            return $this->ok($request, array(
                'record' => $record,
            ));
        } catch (InvalidArgumentException $e) {
            return $this->fail($e->getMessage(), 422, 1001);
        } catch (Throwable $e) {
            Log::error('app-manage store record failed: ' . $e->getMessage());
            return $this->fail('保存记录失败', 500, 1000);
        }
    }

    public function updateVirtualRecord(Request $request, $id, $tableId, $recordId)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $application = Application::findOrFail($id);

        try {
            $table = $this->virtualTableService->findTableForApplication($application, $tableId);
            $record = $this->virtualTableService->updateRecord($table, $recordId, $request->except(array('_token', '_method')));

            return $this->ok($request, array(
                'record' => $record,
            ));
        } catch (InvalidArgumentException $e) {
            return $this->fail($e->getMessage(), 422, 1001);
        } catch (Throwable $e) {
            Log::error('app-manage update record failed: ' . $e->getMessage());
            return $this->fail('保存记录失败', 500, 1000);
        }
    }

    public function deleteVirtualRecord(Request $request, $id, $tableId, $recordId)
    {
        $denied = $this->assertAppManager($request);
        if ($denied) {
            return $denied;
        }

        $application = Application::findOrFail($id);

        try {
            $table = $this->virtualTableService->findTableForApplication($application, $tableId);
            $deleted = $this->virtualTableService->deleteRecord($table, $recordId);

            return $this->ok($request, array(
                'deleted' => (int) $deleted,
            ));
        } catch (InvalidArgumentException $e) {
            return $this->fail($e->getMessage(), 422, 1001);
        } catch (Throwable $e) {
            Log::error('app-manage delete record failed: ' . $e->getMessage());
            return $this->fail('删除记录失败', 500, 1000);
        }
    }

    // ------------------------------------------------------------------
    // 内部工具
    // ------------------------------------------------------------------

    protected function assertApiAllowedType($type)
    {
        if (!in_array((int) $type, self::ALLOWED_CODE_TYPES, true)) {
            abort(403, 'API 层禁止管理 PHP 类型文件，请使用后台维护');
        }
    }

    protected function serializeApplication(Application $application)
    {
        return array(
            'id' => (int) $application->id,
            'name' => $application->name,
            'slug' => $application->slug,
            'description' => $application->description,
            'status' => (int) $application->status,
            'auth_mode' => $application->auth_mode ?: 'public',
            'updated_at' => optional($application->updated_at)->toDateTimeString(),
            'codes_count' => (int) $application->codes_count,
        );
    }

    protected function serializeCode(Code $code, Application $application)
    {
        $normalizedPath = ltrim((string) $code->path, '/');
        $segments = $normalizedPath === '' ? array() : explode('/', $normalizedPath);
        $basename = count($segments) ? end($segments) : '';

        return array(
            'id' => (int) $code->id,
            'name' => $code->name,
            'path' => $code->path,
            'basename' => $basename,
            'type' => (int) $code->type,
            'status' => (int) $code->status,
            'auth_mode' => $code->auth_mode,
            'content' => (string) $code->content,
            'updated_at' => optional($code->updated_at)->toDateTimeString(),
            'preview_url' => $this->buildPreviewUrl($application->slug, $code->path),
        );
    }

    protected function buildPreviewUrl($appSlug, $path)
    {
        return config('app.url') . '/app/' . trim((string) $appSlug, '/') . '/' . ltrim((string) $path, '/');
    }

    protected function normalizePath($path)
    {
        return ltrim(trim((string) $path), '/');
    }
}