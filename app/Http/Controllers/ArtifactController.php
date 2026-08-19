<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Artifact;
use App\Models\Article;
use App\Models\ArticleSub;
use App\Services\ArtifactService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArtifactController extends Controller
{
    protected $artifactService;

    public function __construct(ArtifactService $artifactService)
    {
        $this->artifactService = $artifactService;
    }

    /**
     * 文章制品页：展示某篇文章的全部制品
     * GET /article/{article}/artifacts
     */
    public function articleArtifacts(Request $request, Article $article)
    {
        $article->load('feed');
        $artifacts = Auth::check()
            ? $this->artifactService->listByRelated(Auth::id(), 'article', $article->id)
            : collect();

        return view('artifacts.index', compact('article', 'artifacts'));
    }

    /**
     * 制品库管理页：卡片列表 + 搜索 + 筛选
     * GET /artifacts
     */
    public function manage(Request $request)
    {
        if (!Auth::check()) {
            abort(403, '请先登录');
        }

        $filters = array(
            'keyword' => trim((string)$request->input('keyword', '')),
            'related_type' => trim((string)$request->input('related_type', '')),
            'related_id' => (int)$request->input('related_id', 0),
            'artifact_type' => trim((string)$request->input('artifact_type', '')),
            'status' => trim((string)$request->input('status', '')),
        );

        $page = max(1, (int)$request->input('page', 1));
        $perPage = max(6, min(60, (int)$request->input('per_page', 18)));

        $result = $this->artifactService->searchForManage(Auth::id(), $filters, $perPage, $page);

        return view('artifacts.manage', array(
            'entities' => $result['entities'],
            'total' => $result['total'],
            'current_page' => $result['current_page'],
            'last_page' => $result['last_page'],
            'filters' => $filters,
        ));
    }

    /**
     * 制品查看页：按 file_type 渲染
     * GET /artifacts/{artifact}
     */
    public function show(Request $request, Artifact $artifact)
    {
        if (!Auth::check() || (int)$artifact->user_id !== (int)Auth::id()) {
            abort(403, '无权查看该制品');
        }

        $nodeTree = null;
        if ($artifact->file_type === Artifact::FILE_JSON && $artifact->artifact_type === Artifact::TYPE_MIND_MAP) {
            $decoded = json_decode((string)$artifact->content, true);
            if (is_array($decoded)) {
                $nodeTree = $decoded['data'] ?? $decoded;
            }
        }

        return view('artifacts.view', compact('artifact', 'nodeTree'));
    }

    /**
     * 生成制品（Web 表单入口）
     * POST /article/{article}/artifacts/generate
     */
    public function generateWeb(Request $request, Article $article)
    {
        if (!Auth::check()) {
            abort(403, '请先登录');
        }

        $this->resolveOwnedArticleSub($article);

        $artifactType = (string)$request->input('artifact_type', 'visual_reading');
        $this->artifactService->ensure(Auth::id(), 'article', $article->id, $artifactType, array(
            'force' => (int)$request->input('force', 0) === 1,
            'custom_prompt' => (string)$request->input('custom_prompt', ''),
            'template_style' => (string)$request->input('template_style', 'magazine'),
        ));

        return redirect('/article/' . $article->id . '/artifacts?generated=1');
    }

    /**
     * 保存制品为思维导图（Web 表单入口）
     * POST /artifacts/{artifact}/to-mind
     */
    public function toMindWeb(Request $request, Artifact $artifact)
    {
        if (!Auth::check() || (int)$artifact->user_id !== (int)Auth::id()) {
            abort(403, '无权操作该制品');
        }

        $root = $this->artifactService->toMindTree($artifact, Auth::id());

        return redirect('/mind/' . $root->id);
    }

    /**
     * 删除制品（Web 表单入口）
     * POST /artifacts/{artifact}/delete
     */
    public function destroyWeb(Request $request, Artifact $artifact)
    {
        if (!Auth::check() || (int)$artifact->user_id !== (int)Auth::id()) {
            abort(403, '无权操作该制品');
        }

        $relatedId = (int)$artifact->related_id;
        $this->artifactService->delete($artifact);

        return redirect('/article/' . $relatedId . '/artifacts?deleted=1');
    }

    protected function resolveOwnedArticleSub(Article $article)
    {
        if (!Auth::check()) {
            abort(403, '请先登录');
        }

        $articleSub = ArticleSub::where('article_id', $article->id)
            ->where('user_id', Auth::id())
            ->first();
        if (!$articleSub) {
            abort(403, '未找到当前文章的阅读记录');
        }

        return $articleSub;
    }
}