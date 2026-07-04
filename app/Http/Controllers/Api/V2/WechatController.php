<?php

namespace App\Http\Controllers\Api\V2;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Utils\CommonUtil;
use App\Http\Utils\ResponseDataUtil;
use App\Models\ArticleSub;
use App\Models\OauthInfo;
use App\Models\User;
use App\Services\AccountService;
use App\Services\Auth\UserTokenService;
use App\Services\NoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WechatController extends Controller
{
    protected $accountService;
    protected $userTokenService;
    protected $noteService;

    public function __construct(
        AccountService $accountService,
        UserTokenService $userTokenService,
        NoteService $noteService
    ) {
        $this->accountService = $accountService;
        $this->userTokenService = $userTokenService;
        $this->noteService = $noteService;
    }

    public function login(Request $request)
    {
        $this->validate($request, array(
            'code' => 'required|string',
        ));

        $apiUrl = 'https://api.weixin.qq.com/sns/jscode2session?appid='
            . config('services.wechatmini.client_id')
            . '&secret=' . config('services.wechatmini.client_secret')
            . '&js_code=' . $request->input('code')
            . '&grant_type=authorization_code';

        $result = @file_get_contents($apiUrl);
        if ($result === false) {
            throw new CustomException('wechat login request failed');
        }

        $wxRet = json_decode($result, true);
        $openid = isset($wxRet['openid']) ? $wxRet['openid'] : '';
        $sessionKey = isset($wxRet['session_key']) ? $wxRet['session_key'] : '';
        if ($openid === '' || $sessionKey === '') {
            throw new CustomException('invalid wechat login code');
        }

        $oauth = $this->accountService->forByThirdUidAndDriver($openid, 'wechatmini');
        if (empty($oauth)) {
            $user = User::create(array(
                'name' => 'wx_' . substr($openid, -8),
                'email' => 'taskcongcongus.' . time() . '.' . mt_rand(1000, 9999) . '@wechatmini.local',
                'password' => bcrypt(Str::random(32)),
                'last_login' => date('Y-m-d H:i:s'),
            ));

            OauthInfo::create(array(
                'third_uid' => $openid,
                'user_id' => $user->id,
                'driver' => 'wechatmini',
                'access_token' => $sessionKey,
                'expire' => '2038-01-01 00:00:00',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));
        } else {
            $oauth->update(array(
                'access_token' => $sessionKey,
                'updated_at' => date('Y-m-d H:i:s'),
            ));
            $user = User::find($oauth->user_id);
            if (!$user) {
                throw new CustomException('wechat user not found');
            }
        }

        $pair = $this->userTokenService->issueTokenPair($user, array('*'), 'wechat_mini', $openid);

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array(
            'openid' => $openid,
            'token_type' => $pair['token_type'],
            'access_token' => $pair['access_token'],
            'refresh_token' => $pair['refresh_token'],
            'access_expires_at' => $pair['access_expires_at'],
            'refresh_expires_at' => $pair['refresh_expires_at'],
            'capabilities' => $pair['capabilities'],
            'user' => $pair['user'],
            // legacy alias for old mini-app field name
            'token' => $pair['access_token'],
        )));
    }

    public function explorer(Request $request)
    {
        $feeds = DB::select('select id,feed_name,feed_desc,favicon from feeds where is_recommend = 1 order by rand() limit 10');

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($feeds));
    }

    public function articles(Request $request)
    {
        $userId = $this->getAuthUserId($request);
        $page = (int)$request->input('page', 0);
        $status = $request->input('status', 'read_later');

        $sql = 'select b.subject as title,b.image_url as image_url,b.published as published,a.id as article_sub_id, b.id as article_id,c.id as feed_id,c.feed_name as feed_name from article_subs a,articles b,feeds c where b.subject != "" and a.user_id=:user_id and a.article_id = b.id and b.feed_id = c.id and a.status=:status';
        $sqlParam = array(
            ':user_id' => $userId,
            ':status' => $status,
        );

        if ($request->filled('page_date')) {
            $sql .= ' and a.updated_at <= :page_date ';
            $sqlParam[':page_date'] = $request->input('page_date');
        }

        if ($request->filled('feed_id')) {
            $sql .= ' and c.feed_id = :feed_id ';
            $sqlParam[':feed_id'] = $request->input('feed_id');
        }

        $sql .= ' order by a.updated_at desc ';
        $sql .= ' limit ' . ($page * 10) . ',10';
        $articles = DB::select($sql, $sqlParam);
        foreach ($articles as $key => $val) {
            $val->published = CommonUtil::prettyDate($val->published);
            $articles[$key] = $val;
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($articles));
    }

    public function articleView(Request $request)
    {
        $this->validate($request, array(
            'article_id' => 'required|integer',
        ));

        $sql = 'select b.subject as title,b.content as content,b.published as published, b.id as article_id,c.id as feed_id,c.feed_name as feed_name from articles b,feeds c where  b.feed_id = c.id and b.id=:article_id limit 1';
        $rows = DB::select($sql, array(':article_id' => (int)$request->input('article_id')));
        if (count($rows) !== 1) {
            throw new CustomException('article not found');
        }

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($rows[0]));
    }

    public function notes(Request $request)
    {
        $userId = $this->getAuthUserId($request);
        $sql = 'select n.id as id,n.name as name,n.content as content,n.record_path as record_path,n.image_path as image_path,n.created_at as created_at,u.name as user_name from notes n,users u where n.user_id=u.id and n.user_id=:user_id order by n.updated_at desc limit 10';
        $notes = DB::select($sql, array(':user_id' => $userId));

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($notes));
    }

    public function addNote(Request $request)
    {
        if (trim($request->input('content', $request->input('name', ''))) === '') {
            throw new CustomException('笔记正文不能为空');
        }

        $status = $request->input('status', 1);
        if ($status === false || (string)$status === 'false') {
            $status = 2;
        } elseif ($status === true || (string)$status === 'true') {
            $status = 1;
        }

        $this->noteService->store(
            '',
            $request->input('content', $request->input('name')),
            (int)$status,
            '',
            '',
            (int)$request->input('source_type', 0),
            (int)$request->input('source_id', 0),
            $request->has('tags') ? $request->input('tags') : null
        );

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc());
    }

    public function articleSubStatus(Request $request, ArticleSub $articleSub = null)
    {
        $userId = $this->getAuthUserId($request);
        $status = $request->input('status', 'read');
        if (!in_array($status, array('read', 'unread', 'read_later', 'star'), true)) {
            throw new CustomException('status状态上送错误');
        }

        if ($request->filled('ids')) {
            $ids = array_filter(explode(',', (string)$request->input('ids')));
            foreach ($ids as $id) {
                $item = ArticleSub::where('id', $id)->where('user_id', $userId)->first();
                if ($item) {
                    $item->status = $status;
                    $item->updated_at = date('Y-m-d H:i:s');
                    $item->update();
                }
            }

            return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array()));
        }

        if ($request->filled('feed_id')) {
            ArticleSub::where('user_id', $userId)
                ->where('feed_id', $request->input('feed_id'))
                ->where('status', 'unread')
                ->update(array(
                    'status' => 'read',
                    'updated_at' => date('Y-m-d H:i:s'),
                ));

            return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc(array()));
        }

        if (!$articleSub) {
            throw new CustomException('article_sub required');
        }

        if ((int)$articleSub->user_id !== (int)$userId) {
            throw new CustomException('error user!');
        }

        $articleSub->status = $status;
        $articleSub->updated_at = date('Y-m-d H:i:s');
        $articleSub->update();

        return $this->jsonResponse($request, ResponseDataUtil::genSimpleSucc($articleSub->article));
    }
}
