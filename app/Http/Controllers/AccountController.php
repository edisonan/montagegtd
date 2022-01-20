<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 账户管理控制器
 *
 * @author edison.an
 *
 */
class AccountController extends Controller
{

    /**
     * AccountService 实例.
     *
     * @var AccountService
     */
    protected $accountService;

    /**
     * 构造方法
     *
     * @param AccountService $accountService
     * @return void
     */
    public function __construct(AccountService $accountService)
    {
        $this->middleware('auth');

        $this->accountService = $accountService;
    }

    /**
     * 用户Oauth账户信息列表
     *
     * @param Request $request
     * @return view
     */
    public function index(Request $request)
    {
        $oauthInfos = $this->accountService->getOauthInfos();
        return view('accounts.index', [
            'oauths' => $oauthInfos
        ]);
    }
}
