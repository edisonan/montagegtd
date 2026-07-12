<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Code;
use App\Services\AppCodeDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use ReflectionFunction;
use Throwable;

class ApplicationController extends Controller
{
    public function show(Request $request, $appSlug, $codePath)
    {
        $application = Application::whereIn('slug', array($appSlug, '/' . ltrim($appSlug, '/')))
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

        return $this->renderCodeResponse($request, $application, $code);
    }

    private function renderCodeResponse(Request $request, Application $application, Code $code)
    {
        if ((int)$code->type === 1) {
            return $this->executePhpCode($request, $application, $code);
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

    private function executePhpCode(Request $request, Application $application, Code $code)
    {
        try {
            $phpContent = $code->content;
            if (strpos($phpContent, '<?php') === 0) {
                $phpContent = substr($phpContent, 5);
            }
            error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
            @eval($phpContent);
            $db = new AppCodeDatabase($application);
            $resultData = null;
            if (function_exists('myFunction')) {
                $function = new ReflectionFunction('myFunction');
                $resultData = $function->getNumberOfParameters() >= 2
                    ? myFunction($request->all(), $db)
                    : myFunction($request->all());
            }

            return response()->json(array(
                'result_code' => '0000',
                'result_msg' => 'success',
                'result_data' => $resultData,
            ));
        } catch (Throwable $e) {
            Log::error('API application code execution failed: ' . $e->getMessage(), array(
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
