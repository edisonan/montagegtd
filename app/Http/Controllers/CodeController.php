<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\OnlineInfo;
use App\Models\Code;
use Throwable;

/**
 * 代码服务
 *
 */
class CodeController extends Controller
{

    public function view(Request $request, Code $codeInfo)
    {
        $result = [
            'result_code' => '9999',
            'result_msg' => 'unknown',
            'result_data' => []
        ];

        $data = $request->all();
        Log::info('input data:'. serialize($data));

        if(empty($codeInfo)) {
            $result['result_code'] = '0001';
            $result['result_msg'] = 'code not exist';
            return response()->json($result, 404);
        }

        if($codeInfo->status!= 1) {
            $result['result_code'] = '0001';
            $result['result_msg'] = 'code is disabled';
            return response()->json($result, 403);
        }

        if($codeInfo->type == 1) {
            try {
                $phpContent = $codeInfo->content;
                if (strpos($phpContent, "<?php") === 0) {
                    $phpContent = str_replace("<?php", "", $phpContent);
                }
                error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
                // 对于 eval 的使用，尽量避免，因为它可能带来安全风险
                // 以下是一种临时处理方式，更安全的做法是将代码逻辑封装在类或函数中调用
                @eval($phpContent);
                $resultData = myFunction($data);
                $result['result_data'] = $resultData;
                $result['result_code'] = '0000';
                $result['result_msg'] = 'success';
                return response()->json($result);
            } catch (Throwable $e) {
                Log::error('Error occurred during eval: '. $e->getMessage());
                $result['result_code'] = '0002';
                $result['result_msg'] = 'Error occurred during code execution';
                $result['error'] = $e->getMessage();
                return response()->json($result, 500);
            }
        } else {
            $htmlContent = $codeInfo->content;
            return $htmlContent;
        }
    }
}