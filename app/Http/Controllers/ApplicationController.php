<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApplicationController extends Controller
{
    /**
     * 根据应用标识和代码路径显示代码内容
     * 
     * @param string $appSlug 应用标识
     * @param string $codePath 代码文件路径
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $appSlug, $codePath)
    {
        $application = Application::where('slug', $appSlug)
            ->where('status', '<', 4)
            ->firstOrFail();

        $normalizedPath = ltrim(urldecode((string)$codePath), '/');
        $candidates = array_values(array_unique(array(
            $codePath,
            $normalizedPath,
            '/' . $normalizedPath,
        )));

        $code = Code::where('app_id', $application->id)
            ->whereIn('path', $candidates)
            ->where('status', 1)
            ->orderByRaw("CASE WHEN path = ? THEN 0 WHEN path = ? THEN 1 ELSE 2 END", array($normalizedPath, '/' . $normalizedPath))
            ->firstOrFail();

        return $this->renderCodeResponse($request, $code);
    }

    private function renderCodeResponse(Request $request, Code $code)
    {
        if ((int)$code->status !== 1) {
            abort(403, 'code is disabled');
        }

        if ((int)$code->type === 1) {
            return $this->executePhpCode($request, $code);
        }
        if ((int)$code->type === 2) {
            return response($code->content)->header('Content-Type', 'text/html; charset=UTF-8');
        }
        if ((int)$code->type === 3) {
            return response($code->content)->header('Content-Type', 'application/javascript; charset=UTF-8');
        }
        if ((int)$code->type === 4) {
            return response($code->content)->header('Content-Type', 'text/css; charset=UTF-8');
        }
        if ((int)$code->type === 5) {
            return response($code->content)->header('Content-Type', 'application/json; charset=UTF-8');
        }

        return response($code->content)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    private function executePhpCode(Request $request, Code $code)
    {
        try {
            $phpContent = $code->content;
            if (strpos($phpContent, '<?php') === 0) {
                $phpContent = substr($phpContent, 5);
            }
            error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
            @eval($phpContent);
            $resultData = function_exists('myFunction') ? myFunction($request->all()) : null;

            return response()->json(array(
                'result_code' => '0000',
                'result_msg' => 'success',
                'result_data' => $resultData,
            ));
        } catch (Throwable $e) {
            Log::error('Application code execution failed: ' . $e->getMessage(), array(
                'code_id' => $code->id,
                'path' => $code->path,
            ));

            return response()->json(array(
                'result_code' => '0002',
                'result_msg' => 'Error occurred during code execution',
                'error' => $e->getMessage(),
            ), 500);
        }
    }
}
