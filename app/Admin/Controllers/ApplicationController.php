<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AppVirtualTable;
use App\Models\ApplicationAllowedUser;
use App\Models\Code;
use App\Models\CodeHistory;
use App\Models\User;
use App\Services\AppVirtualTableService;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Throwable;

class ApplicationController extends Controller
{
    public function index(Content $content)
    {
        $applications = Application::query()
            ->withCount('codes')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function (Application $application) {
                $entry = $application->codes()
                    ->whereIn('path', array('index.html', '/index.html', 'index.php', '/index.php'))
                    ->where('status', 1)
                    ->orderBy('id')
                    ->first();

                return array(
                    'id' => $application->id,
                    'name' => $application->name,
                    'slug' => $application->slug,
                    'description' => $application->description,
                    'status' => (int) $application->status,
                    'auth_mode' => $application->auth_mode ?: 'public',
                    'codes_count' => (int) $application->codes_count,
                    'updated_at' => optional($application->updated_at)->toDateTimeString(),
                    'preview_url' => $entry ? $this->buildPreviewUrl($application->slug, $entry->path) : null,
                    'has_entry' => $entry ? true : false,
                    'entry_path' => $entry ? $entry->path : null,
                );
            });

        $stats = array(
            'total' => $applications->count(),
            'running' => $applications->where('status', 2)->count(),
            'draft' => $applications->where('status', 1)->count(),
            'files' => $applications->sum('codes_count'),
            'with_entry' => $applications->where('has_entry', true)->count(),
        );

        return Admin::content(function (Content $content) use ($applications, $stats) {
            $content->header('应用工作台');
            $content->description('卡片列表');
            $content->body(view('admin.applications.index', array(
                'applications' => $applications,
                'stats' => $stats,
                'statusOptions' => $this->statusOptions(),
                'authModeOptions' => $this->authModeOptions(),
            )));
        });
    }

    public function show(Request $request, $id, Content $content)
    {
        $application = Application::query()
            ->with(array('codes' => function ($query) {
                $query->select(array(
                    'id', 'app_id', 'name', 'path', 'type', 'status', 'updated_at'
                ));
                $query->orderByRaw("CASE WHEN path IN ('index.html', '/index.html', 'index.php', '/index.php') THEN 0 ELSE 1 END")
                    ->orderBy('path')
                    ->orderBy('id');
            }))
            ->findOrFail($id);

        $selectedCodeId = (int) $request->query('file_id');
        $selectedCode = $application->codes->first(function (Code $code) use ($selectedCodeId) {
            return $selectedCodeId > 0 && (int) $code->id === $selectedCodeId;
        }) ?: $application->codes->first();

        return Admin::content(function (Content $content) use ($application, $selectedCode) {
            $content->header($application->name ?: '应用工作台');
            $content->description($application->slug);
            $content->body(view('admin.applications.workspace', array(
                'application' => $this->serializeApplication($application),
                'files' => $application->codes->map(function (Code $code) use ($application) {
                    return $this->serializeCode($code, $application, false);
                })->values(),
                'selectedCodeId' => $selectedCode ? $selectedCode->id : null,
                'statusOptions' => $this->statusOptions(),
                'codeTypeOptions' => $this->codeTypeOptions(),
                'virtualTableFieldTypeOptions' => $this->virtualTableFieldTypeOptions(),
                'authModeOptions' => $this->authModeOptions(),
                'allowedUsernames' => $this->allowedUsernames($application),
            )));
        });
    }

    public function showCode($id, $codeId)
    {
        $application = Application::findOrFail($id);
        $code = Code::query()
            ->where('app_id', $application->id)
            ->findOrFail($codeId);

        return response()->json(array(
            'code' => 9999,
            'message' => 'success',
            'data' => array(
                'file' => $this->serializeCode($code, $application),
            ),
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, array(
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:applications,slug',
            'description' => 'nullable|string',
            'status' => 'required|integer|in:1,2,3,4',
            'auth_mode' => 'nullable|string|in:public,login,whitelist,pat',
        ));

        $data['auth_mode'] = $data['auth_mode'] ?? 'public';
        $application = Application::create($data);

        return response()->json(array(
            'code' => 9999,
            'message' => 'success',
            'data' => array(
                'application' => $this->serializeApplication($application),
                'workspace_url' => url('/admin/applications/' . $application->id),
            ),
        ));
    }

    public function workspaceData($id)
    {
        $application = Application::query()
            ->with(array('codes' => function ($query) {
                $query->orderByRaw("CASE WHEN path IN ('index.html', '/index.html', 'index.php', '/index.php') THEN 0 ELSE 1 END")
                    ->orderBy('path')
                    ->orderBy('id');
            }))
            ->findOrFail($id);

        return response()->json(array(
            'code' => 9999,
            'message' => 'success',
            'data' => array(
                'application' => $this->serializeApplication($application),
                'files' => $application->codes->map(function (Code $code) use ($application) {
                    return $this->serializeCode($code, $application);
                })->values(),
            ),
        ));
    }

    public function updateMeta(Request $request, $id)
    {
        $application = Application::findOrFail($id);

        $data = $this->validate($request, array(
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:applications,slug,' . $application->id,
            'description' => 'nullable|string',
            'status' => 'required|integer|in:1,2,3,4',
            'auth_mode' => 'nullable|string|in:public,login,whitelist,pat',
            'allowed_usernames' => 'nullable|string|max:5000',
        ));

        $allowedUsernames = $data['allowed_usernames'] ?? '';
        unset($data['allowed_usernames']);
        $data['auth_mode'] = $data['auth_mode'] ?? 'public';
        $application->fill($data);
        $application->save();
        $this->syncAllowedUsers($application, $allowedUsernames);

        return response()->json(array(
            'code' => 9999,
            'message' => 'success',
            'data' => array(
                'application' => $this->serializeApplication($application->fresh()),
            ),
        ));
    }

    public function storeCode(Request $request, $id)
    {
        $application = Application::findOrFail($id);

        $data = $this->validate($request, array(
            'name' => 'required|string|max:255',
            'path' => 'required|string|max:500',
            'type' => 'required|integer|in:1,2,3,4,5',
            'status' => 'required|integer|in:1,2',
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
            return response()->json(array(
                'code' => 1001,
                'message' => '文件路径已存在',
                'data' => array(),
            ), 422);
        }

        $code = Code::create(array(
            'app_id' => $application->id,
            'name' => $data['name'],
            'path' => $normalizedPath,
            'type' => $data['type'],
            'status' => $data['status'],
            'auth_mode' => $data['auth_mode'] ?? null,
            'content' => array_key_exists('content', $data) ? $data['content'] : '',
        ));

        return response()->json(array(
            'code' => 9999,
            'message' => 'success',
            'data' => array(
                'file' => $this->serializeCode($code, $application),
            ),
        ));
    }

    public function updateCode(Request $request, $id, $codeId)
    {
        $application = Application::findOrFail($id);
        $code = Code::query()
            ->where('app_id', $application->id)
            ->findOrFail($codeId);

        $data = $this->validate($request, array(
            'name' => 'required|string|max:255',
            'path' => 'required|string|max:500',
            'type' => 'required|integer|in:1,2,3,4,5',
            'status' => 'required|integer|in:1,2',
            'auth_mode' => 'nullable|string|in:public,login,whitelist,pat',
            'content' => 'nullable|string',
        ));

        $normalizedPath = $this->normalizePath($data['path']);
        $exists = Code::query()
            ->where('app_id', $application->id)
            ->where('id', '<>', $code->id)
            ->where(function ($query) use ($normalizedPath) {
                $query->where('path', $normalizedPath)
                    ->orWhere('path', '/' . $normalizedPath);
            })
            ->exists();

        if ($exists) {
            return response()->json(array(
                'code' => 1001,
                'message' => '文件路径已存在',
                'data' => array(),
            ), 422);
        }

        $originalContent = (string) $code->content;

        $code->name = $data['name'];
        $code->path = $normalizedPath;
        $code->type = $data['type'];
        $code->status = $data['status'];
        $code->auth_mode = $data['auth_mode'] ?? null;
        $code->content = array_key_exists('content', $data) ? $data['content'] : '';
        $code->save();

        if ($originalContent !== (string) $code->content && Schema::hasTable((new CodeHistory())->getTable())) {
            CodeHistory::create(array(
                'code_id' => $code->id,
                'content' => $code->content,
            ));
        }

        return response()->json(array(
            'code' => 9999,
            'message' => 'success',
            'data' => array(
                'file' => $this->serializeCode($code->fresh(), $application->fresh()),
            ),
        ));
    }

    public function generateCode(Request $request, $id, $codeId)
    {
        $application = Application::findOrFail($id);
        $code = Code::query()
            ->where('app_id', $application->id)
            ->findOrFail($codeId);

        $data = $this->validate($request, array(
            'mode' => 'required|string|in:generate,optimize',
            'prompt' => 'nullable|string',
        ));

        $prompt = trim((string) ($data['prompt'] ?? ''));
        $fileType = $this->codeTypeOptions()[(int) $code->type] ?? 'text';
        $instruction = $data['mode'] === 'optimize'
            ? '请优化下面这段代码，优先提升可读性、结构和健壮性，保留现有功能。'
            : '请根据需求为该文件生成可直接使用的完整代码。';

        if ($prompt === '' && $data['mode'] === 'generate') {
            $prompt = '请生成一个基础可用版本。';
        }

        $composedPrompt = $instruction
            . "\n文件类型：" . $fileType
            . "\n应用：" . $application->name . ' / ' . $application->slug
            . "\n文件路径：" . $code->path
            . "\n用户补充需求：" . ($prompt === '' ? '无' : $prompt)
            . "\n当前代码如下：\n" . (string) $code->content;

        $generatedCode = env('OPENAI_API_ENABLED', false)
            ? $this->callOpenAIApi($composedPrompt, $fileType)
            : $this->mockAiService($composedPrompt, (int) $code->type, $data['mode']);

        return response()->json(array(
            'code' => 9999,
            'message' => 'success',
            'data' => array(
                'content' => $generatedCode,
            ),
        ));
    }

    public function getCodeHistory($id, $codeId)
    {
        $application = Application::findOrFail($id);
        $code = Code::query()
            ->where('app_id', $application->id)
            ->findOrFail($codeId);

        if (!Schema::hasTable((new CodeHistory())->getTable())) {
            return response()->json(array(
                'code' => 9999,
                'message' => 'success',
                'data' => array(
                    'history' => array(),
                    'current' => array(
                        'content' => (string) $code->content,
                        'updated_at' => optional($code->updated_at)->toDateTimeString(),
                    ),
                ),
            ));
        }

        $history = CodeHistory::query()
            ->where('code_id', $code->id)
            ->orderBy('created_at', 'desc')
            ->get(array('id', 'content', 'created_at'));

        return response()->json(array(
            'code' => 9999,
            'message' => 'success',
            'data' => array(
                'history' => $history,
                'current' => array(
                    'content' => (string) $code->content,
                    'updated_at' => optional($code->updated_at)->toDateTimeString(),
                ),
            ),
        ));
    }

    public function rollbackCodeHistory(Request $request, $id, $codeId, $historyId)
    {
        $application = Application::findOrFail($id);
        $code = Code::query()
            ->where('app_id', $application->id)
            ->findOrFail($codeId);

        if (!Schema::hasTable((new CodeHistory())->getTable())) {
            return response()->json(array(
                'code' => 1002,
                'message' => '历史功能不可用',
                'data' => array(),
            ), 422);
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

        return response()->json(array(
            'code' => 9999,
            'message' => 'success',
            'data' => array(
                'file' => $this->serializeCode($code->fresh(), $application),
            ),
        ));
    }

    public function virtualTables($id)
    {
        $application = Application::findOrFail($id);
        $service = new AppVirtualTableService();

        $tables = $service->listTablesForApplication($application)
            ->map(function (AppVirtualTable $table) use ($service) {
                return $service->serializeTable($table);
            })
            ->values();

        return response()->json(array(
            'code' => 9999,
            'message' => 'success',
            'data' => array(
                'tables' => $tables,
            ),
        ));
    }

    public function storeVirtualTable(Request $request, $id)
    {
        $application = Application::findOrFail($id);
        $data = $this->validate($request, array(
            'name' => 'required|string|max:120',
            'slug' => 'required|string|max:80',
            'description' => 'nullable|string',
            'status' => 'nullable|integer|in:0,1',
        ));

        $service = new AppVirtualTableService();

        try {
            $table = $service->createTable($application, $data)->load('fields');
            return response()->json(array(
                'code' => 9999,
                'message' => 'success',
                'data' => array(
                    'table' => $service->serializeTable($table),
                ),
            ));
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->jsonError('创建虚拟表失败', 500, $e);
        }
    }

    public function storeVirtualField(Request $request, $id, $tableId)
    {
        $application = Application::findOrFail($id);
        $service = new AppVirtualTableService();

        $data = $this->validate($request, array(
            'name' => 'required|string|max:120',
            'slug' => 'required|string|max:80',
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
            $table = $service->findTableForApplication($application, $tableId);
            $field = $service->createField($table, $data);
            $table = $table->fresh()->load('fields');

            return response()->json(array(
                'code' => 9999,
                'message' => 'success',
                'data' => array(
                    'field' => $service->serializeField($field),
                    'table' => $service->serializeTable($table),
                ),
            ));
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->jsonError('添加字段失败', 500, $e);
        }
    }

    public function virtualTableRecords(Request $request, $id, $tableId)
    {
        $application = Application::findOrFail($id);
        $service = new AppVirtualTableService();

        try {
            $table = $service->findTableForApplication($application, $tableId);
            $records = $service->listRecords($table, $request->query('page', 1), $request->query('per_page', 100));

            return response()->json(array(
                'code' => 9999,
                'message' => 'success',
                'data' => $records,
            ));
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->jsonError('加载记录失败', 500, $e);
        }
    }

    public function storeVirtualRecord(Request $request, $id, $tableId)
    {
        $application = Application::findOrFail($id);
        $service = new AppVirtualTableService();

        try {
            $table = $service->findTableForApplication($application, $tableId);
            $record = $service->createRecord($table, $request->except(array('_token', '_method')));

            return response()->json(array(
                'code' => 9999,
                'message' => 'success',
                'data' => array(
                    'record' => $record,
                ),
            ));
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->jsonError('保存记录失败', 500, $e);
        }
    }

    public function updateVirtualRecord(Request $request, $id, $tableId, $recordId)
    {
        $application = Application::findOrFail($id);
        $service = new AppVirtualTableService();

        try {
            $table = $service->findTableForApplication($application, $tableId);
            $record = $service->updateRecord($table, $recordId, $request->except(array('_token', '_method')));

            return response()->json(array(
                'code' => 9999,
                'message' => 'success',
                'data' => array(
                    'record' => $record,
                ),
            ));
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->jsonError('保存记录失败', 500, $e);
        }
    }

    public function deleteVirtualRecord($id, $tableId, $recordId)
    {
        $application = Application::findOrFail($id);
        $service = new AppVirtualTableService();

        try {
            $table = $service->findTableForApplication($application, $tableId);
            $deleted = $service->deleteRecord($table, $recordId);

            return response()->json(array(
                'code' => 9999,
                'message' => 'success',
                'data' => array(
                    'deleted' => (int) $deleted,
                ),
            ));
        } catch (InvalidArgumentException $e) {
            return $this->jsonError($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->jsonError('删除记录失败', 500, $e);
        }
    }

    private function serializeApplication(Application $application)
    {
        return array(
            'id' => $application->id,
            'name' => $application->name,
            'slug' => $application->slug,
            'description' => $application->description,
            'status' => (int) $application->status,
            'auth_mode' => $application->auth_mode ?: 'public',
            'allowed_usernames' => $this->allowedUsernames($application),
            'status_text' => $this->statusOptions()[(int) $application->status] ?? '未知',
            'updated_at' => optional($application->updated_at)->toDateTimeString(),
        );
    }

    private function serializeCode(Code $code, Application $application, $includeContent = true)
    {
        $normalizedPath = ltrim((string) $code->path, '/');
        $segments = $normalizedPath === '' ? array() : explode('/', $normalizedPath);
        $basename = count($segments) ? end($segments) : '';
        $directory = count($segments) > 1 ? implode('/', array_slice($segments, 0, -1)) : '';
        $isEntry = in_array($normalizedPath, array('index.html', 'index.php'), true);

        return array(
            'id' => $code->id,
            'name' => $code->name,
            'path' => $code->path,
            'basename' => $basename,
            'directory' => $directory,
            'type' => (int) $code->type,
            'type_text' => $this->codeTypeOptions()[(int) $code->type] ?? 'text',
            'status' => (int) $code->status,
            'status_text' => (int) $code->status === 1 ? '启用' : '禁用',
            'auth_mode' => $code->auth_mode,
            'is_entry' => $isEntry,
            'content' => $includeContent ? (string) $code->content : null,
            'content_loaded' => (bool) $includeContent,
            'updated_at' => optional($code->updated_at)->toDateTimeString(),
            'preview_url' => $this->buildPreviewUrl($application->slug, $code->path),
            'is_previewable' => in_array((int) $code->type, array(2, 3, 4, 5), true),
        );
    }

    private function jsonError($message, $status = 422, Throwable $exception = null)
    {
        if ($exception) {
            \Log::error($message . ': ' . $exception->getMessage(), array(
                'exception' => get_class($exception),
            ));
        }

        return response()->json(array(
            'code' => 1001,
            'message' => $message,
            'data' => array(),
        ), $status);
    }

    private function buildPreviewUrl($appSlug, $path)
    {
        return url('/app/' . trim((string) $appSlug, '/') . '/' . ltrim((string) $path, '/'));
    }

    private function normalizePath($path)
    {
        return ltrim(trim((string) $path), '/');
    }

    private function codeTypeOptions()
    {
        return array(
            1 => 'php',
            2 => 'html',
            3 => 'js',
            4 => 'css',
            5 => 'json',
        );
    }

    private function authModeOptions()
    {
        return array(
            'public' => '公开访问',
            'login' => '登录用户',
            'whitelist' => '用户白名单',
            'pat' => 'PAT（code:execute）',
        );
    }

    private function allowedUsernames(Application $application)
    {
        return $application->allowedUsers()
            ->orderBy('users.name')
            ->get()
            ->map(function (User $user) {
                return $user->email ?: $user->name;
            })
            ->implode("\n");
    }

    private function syncAllowedUsers(Application $application, $rawUsers)
    {
        $identifiers = preg_split('/[\s,;]+/u', trim((string)$rawUsers), -1, PREG_SPLIT_NO_EMPTY);
        $userIds = User::query()
            ->where(function ($query) use ($identifiers) {
                if (empty($identifiers)) {
                    $query->whereRaw('1 = 0');
                    return;
                }
                $query->whereIn('email', $identifiers)->orWhereIn('name', $identifiers);
            })
            ->pluck('id')
            ->all();

        ApplicationAllowedUser::where('application_id', $application->id)->delete();
        foreach (array_unique($userIds) as $userId) {
            ApplicationAllowedUser::create(array(
                'application_id' => $application->id,
                'user_id' => $userId,
            ));
        }
    }

    private function virtualTableFieldTypeOptions()
    {
        return array(
            'string' => '短文本',
            'text' => '长文本',
            'integer' => '整数',
            'decimal' => '小数',
            'boolean' => '布尔值',
            'date' => '日期',
            'datetime' => '日期时间',
            'json' => 'JSON',
        );
    }

    private function statusOptions()
    {
        return array(
            1 => '开发中',
            2 => '运行中',
            3 => '已停止',
            4 => '已删除',
        );
    }

    private function mockAiService($prompt, $type, $mode)
    {
        if ($mode === 'optimize') {
            if ((int) $type === 5) {
                return "{\n    \"message\": \"optimized sample\",\n    \"updated_at\": \"" . date('c') . "\"\n}";
            }

            if ((int) $type === 2) {
                return "<!DOCTYPE html>\n<html lang=\"zh-CN\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>Optimized Page</title>\n    <style>\n        :root { color-scheme: light; }\n        body { margin: 0; font-family: \"Segoe UI\", sans-serif; background: #f7f4ec; color: #222; }\n        .container { max-width: 960px; margin: 0 auto; padding: 48px 24px; }\n        .panel { background: #fff; border-radius: 18px; padding: 24px; box-shadow: 0 12px 32px rgba(0,0,0,.08); }\n    </style>\n</head>\n<body>\n    <main class=\"container\">\n        <section class=\"panel\">\n            <h1>优化后的页面</h1>\n            <p>这里是根据现有结构整理后的版本，可以继续按业务细化。</p>\n        </section>\n    </main>\n</body>\n</html>";
            }

            return "// optimized by mock ai\n" . trim((string) $prompt);
        }

        if ((int) $type === 2) {
            return "<!DOCTYPE html>\n<html lang=\"zh-CN\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title>New App Page</title>\n    <style>\n        body { margin: 0; font-family: \"Segoe UI\", sans-serif; background: linear-gradient(135deg, #f4ead7, #e8f1ea); color: #223; }\n        .wrap { max-width: 960px; margin: 0 auto; padding: 56px 24px; }\n        .card { background: rgba(255,255,255,.92); border-radius: 20px; padding: 28px; box-shadow: 0 14px 40px rgba(0,0,0,.08); }\n        h1 { margin-top: 0; }\n    </style>\n</head>\n<body>\n    <div class=\"wrap\">\n        <div class=\"card\">\n            <h1>AI 生成页面</h1>\n            <p>根据你的需求生成的首版页面骨架。</p>\n        </div>\n    </div>\n</body>\n</html>";
        }

        if ((int) $type === 3) {
            return "const appState = {\n    ready: true,\n    generatedAt: '" . date('c') . "'\n};\n\nfunction bootstrapApp() {\n    console.log('app bootstrapped', appState);\n}\n\nbootstrapApp();";
        }

        if ((int) $type === 4) {
            return ":root {\n    --page-bg: #f7f3ea;\n    --text-color: #21302b;\n}\n\nbody {\n    margin: 0;\n    background: var(--page-bg);\n    color: var(--text-color);\n    font-family: \"Segoe UI\", sans-serif;\n}";
        }

        if ((int) $type === 5) {
            return "{\n    \"name\": \"generated-config\",\n    \"updated_at\": \"" . date('c') . "\"\n}";
        }

        return "<?php\n\nfunction myFunction(array \$input)\n{\n    return [\n        'ok' => true,\n        'input' => \$input,\n        'generated_at' => '" . date('c') . "',\n    ];\n}\n";
    }

    private function callOpenAIApi($prompt, $fileType)
    {
        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            return "// 错误：未配置OpenAI API密钥";
        }

        $systemMessage = "你是一个专业编程助手。你输出的必须是可运行代码，不要解释，不要 markdown 代码块。文件类型为 " . $fileType . "。";
        $data = array(
            'model' => env('OPENAI_API_MODEL'),
            'messages' => array(
                array('role' => 'system', 'content' => $systemMessage),
                array('role' => 'user', 'content' => $prompt),
            ),
            'temperature' => 0.4,
            'max_tokens' => 1800,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('OPENAI_API_URL'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if (isset($result['choices'][0]['message']['content'])) {
                return trim($result['choices'][0]['message']['content']);
            }
        }

        return "// AI 生成失败，HTTP " . $httpCode;
    }
}
