<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Models\Third;
use App\Services\ThirdService;
use Illuminate\Http\Request;

class ThirdController extends Controller
{
    protected $thirdService;

    public function __construct(ThirdService $thirdService)
    {
        $this->thirdService = $thirdService;
    }

    public function index(Request $request)
    {
        $userId = (int)$this->getAuthUserId($request);
        $thirds = Third::where('user_id', $userId)->get();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'thirds' => $thirds,
        )));
    }

    public function fanfouRequest(Request $request)
    {
        $url = $this->thirdService->fanfouRequest($request);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'auth_url' => (string)$url,
        )));
    }

    public function testFave(Request $request)
    {
        $message = $request->input('message', 'test!!!robot dog!!!');
        $result = $this->thirdService->testFave($this->getAuthUser($request), $message);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'result' => $result,
        )));
    }
}

