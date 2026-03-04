<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Services\HelpService;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    protected $helpService;

    public function __construct(HelpService $helpService)
    {
        $this->helpService = $helpService;
    }

    public function about(Request $request)
    {
        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'title' => 'about',
            'path' => '/about',
        )));
    }

    public function feedbackStore(Request $request)
    {
        $this->validate($request, array(
            'content' => 'required',
        ));

        $from = $request->input('from', '');
        $content = $request->input('content');
        $this->helpService->storeFeedback($from, $content);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }
}

