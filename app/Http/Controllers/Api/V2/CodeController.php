<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CodeController extends Controller
{
    public function view(Request $request, Code $codeInfo)
    {
        $result = array(
            'result_code' => '9999',
            'result_msg' => 'unknown',
            'result_data' => array(),
        );

        $data = $request->all();
        Log::info('input data:' . serialize($data));

        if (empty($codeInfo)) {
            $result['result_code'] = '0001';
            $result['result_msg'] = 'code not exist';
            return response()->json($result, 404);
        }

        if ((int)$codeInfo->status !== 1) {
            $result['result_code'] = '0001';
            $result['result_msg'] = 'code is disabled';
            return response()->json($result, 403);
        }

        if ((int)$codeInfo->type === 1) {
            try {
                $phpContent = $codeInfo->content;
                if (strpos($phpContent, "<?php") === 0) {
                    $phpContent = str_replace("<?php", "", $phpContent);
                }
                error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
                @eval($phpContent);
                $resultData = myFunction($data);
                $result['result_data'] = $resultData;
                $result['result_code'] = '0000';
                $result['result_msg'] = 'success';
                return response()->json($result);
            } catch (Throwable $e) {
                Log::error('Error occurred during eval: ' . $e->getMessage());
                $result['result_code'] = '0002';
                $result['result_msg'] = 'Error occurred during code execution';
                $result['error'] = $e->getMessage();
                return response()->json($result, 500);
            }
        }

        if ((int)$codeInfo->type === 3) {
            return response($codeInfo->content)->header('Content-Type', 'application/javascript');
        }
        if ((int)$codeInfo->type === 4) {
            return response($codeInfo->content)->header('Content-Type', 'text/css');
        }
        if ((int)$codeInfo->type === 5) {
            return response($codeInfo->content)->header('Content-Type', 'application/json');
        }

        return response($codeInfo->content);
    }
}

