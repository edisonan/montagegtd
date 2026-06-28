<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AdminLogService;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use InvalidArgumentException;

class SystemLogController extends Controller
{
    public function index(Request $request, Content $content)
    {
        $service = new AdminLogService();
        $files = $service->listFiles();
        $selectedFile = $request->query('file') ?: $service->defaultFile();
        $result = null;
        $error = null;

        if ($selectedFile) {
            try {
                $result = $service->read(
                    $selectedFile,
                    $request->query('lines', AdminLogService::DEFAULT_LINES),
                    $request->query('level'),
                    $request->query('keyword'),
                    $request->query('order', 'desc')
                );
                $selectedFile = $result['file'];
            } catch (InvalidArgumentException $e) {
                $error = $e->getMessage();
            }
        }

        return Admin::content(function (Content $content) use ($files, $selectedFile, $result, $error, $service) {
            $content->header('日志查询');
            $content->description('storage/logs');
            $content->body(view('admin.system_logs.index', array(
                'files' => $files,
                'selectedFile' => $selectedFile,
                'result' => $result,
                'error' => $error,
                'levels' => $service->levels(),
                'lineOptions' => array(100, 500, 1000, 2000),
            )));
        });
    }

    public function content(Request $request)
    {
        $service = new AdminLogService();

        try {
            $result = $service->read(
                $request->query('file') ?: $service->defaultFile(),
                $request->query('lines', AdminLogService::DEFAULT_LINES),
                $request->query('level'),
                $request->query('keyword'),
                $request->query('order', 'desc')
            );

            return response()->json(array(
                'code' => 9999,
                'message' => 'success',
                'data' => $result,
            ));
        } catch (InvalidArgumentException $e) {
            return response()->json(array(
                'code' => 1001,
                'message' => $e->getMessage(),
                'data' => array(),
            ), 422);
        }
    }
}
