<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Utils\ResponseDataUtil;
use App\Services\AccountService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function index(Request $request)
    {
        $oauthInfos = $this->accountService->getOauthInfos();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'oauths' => $oauthInfos,
        )));
    }
}

