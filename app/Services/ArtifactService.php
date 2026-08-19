<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\Artifact;
use App\Models\Article;
use App\Models\Mind;
use App\Repositories\ArtifactRepository;

class ArtifactService
{
    protected $artifactRepository;
    protected $llmStructuredTaskService;
    protected $articleAiRenderService;

    /**
     * 生成器注册表：relatedType:artifactType => method
     */
    protected $generators = array(
        'article:visual_reading' => 'generateArticleVisualReading',
        'article:mind_map' => 'generateArticleMindMap',
        'article:key_points' => 'generateArticleKeyPoints',
        'note:key_points' => 'generateNoteKeyPoints',
        'note:mind_map' => 'generateNoteMindMap',
        'note:visual_reading' => 'generateNoteVisualReading',
    );

    public function __construct(
        ArtifactRepository $artifactRepository,
        LlmStructuredTaskService $llmStructuredTaskService,
        ArticleAiRenderService $articleAiRenderService
    ) {
        $this->artifactRepository = $artifactRepository;
        $this->llmStructuredTaskService = $llmStructuredTaskService;
        $this->articleAiRenderService = $articleAiRenderService;
    }

    /**
     * 获取某实体的制品列表
     */
    public function listByRelated($userId, $relatedType, $relatedId)
    {
        return $this->artifactRepository->listByRelated($userId, $relatedType, $relatedId);
    }

    public function paginateByRelated($userId, $relatedType, $relatedId, $perPage = 50)
    {
        return $this->artifactRepository->paginateByRelated($userId, $relatedType, $relatedId, $perPage);
    }

    /**
     * 管理页：按实体聚合的制品列表。
     * 每个实体卡片包含已生成制品类型、原文标题/链接，以及该实体下所有制品详情。
     *
     * @return array ['entities' => array, 'total' => int, 'current_page' => int, 'last_page' => int]
     */
    public function searchForManage($userId, array $filters = array(), $perPage = 18, $page = 1)
    {
        $result = $this->artifactRepository->paginateRelatedEntities($userId, $filters, $perPage, $page);

        $entities = array();
        foreach ($result['items'] as $entity) {
            $entity['related_title'] = null;
            $entity['related_url'] = null;
            $this->attachEntityTitle($entity);

            $artifacts = $this->artifactRepository->listByRelated($userId, $entity['related_type'], $entity['related_id']);
            $entity['artifacts'] = $artifacts;
            $entities[] = $entity;
        }

        return array(
            'entities' => $entities,
            'total' => $result['total'],
            'per_page' => $result['per_page'],
            'current_page' => $result['current_page'],
            'last_page' => $result['last_page'],
        );
    }

    /**
     * 给实体补原始标题与链接
     */
    protected function attachEntityTitle(array &$entity)
    {
        if ($entity['related_type'] === 'article') {
            $article = \App\Models\Article::select('id', 'subject')->find($entity['related_id']);
            if ($article) {
                $entity['related_title'] = $article->subject;
                $entity['related_url'] = '/article/view/' . $article->id;
            }
        } elseif ($entity['related_type'] === 'note') {
            $note = \App\Models\Note::select('id', 'name', 'content')->find($entity['related_id']);
            if ($note) {
                $entity['related_title'] = $note->name ?: mb_substr(strip_tags((string)$note->content), 0, 40);
                $entity['related_url'] = '/notes/manage';
            }
        } elseif ($entity['related_type'] === 'mind') {
            $mind = \App\Models\Mind::select('id', 'name')->find($entity['related_id']);
            if ($mind) {
                $entity['related_title'] = $mind->name;
                $entity['related_url'] = '/mind/' . $mind->id;
            }
        }

        if (empty($entity['related_title'])) {
            $entity['related_title'] = '#' . $entity['related_type'] . '-' . $entity['related_id'];
        }
    }

    /**
     * 获取制品详情（归属校验由上层完成）
     */
    public function getById($userId, $id)
    {
        return $this->artifactRepository->findByUserIdAndId($userId, $id);
    }

    /**
     * 确保制品存在：已有成功制品且非 force 时直接复用，否则生成并 upsert。
     *
     * @return array ['artifact' => Artifact, 'generated' => bool]
     */
    public function ensure($userId, $relatedType, $relatedId, $artifactType, array $options = array())
    {
        $existing = $this->artifactRepository->findByUniqueKey($userId, $relatedType, $relatedId, $artifactType);
        $force = !empty($options['force']);

        if ($existing && $existing->status === Artifact::STATUS_SUCCESS && !$force) {
            return array(
                'artifact' => $existing,
                'generated' => false,
            );
        }

        $generatorKey = $relatedType . ':' . $artifactType;
        if (empty($this->generators[$generatorKey])) {
            throw new CustomException('不支持的制品类型：' . $artifactType . '（关联 ' . $relatedType . '）');
        }

        $method = $this->generators[$generatorKey];
        $result = call_user_func(array($this, $method), $relatedId, $options);

        $data = array(
            'user_id' => $userId,
            'name' => $result['name'] ?? $this->defaultName($artifactType),
            'file_type' => $result['file_type'] ?? Artifact::FILE_TEXT,
            'artifact_type' => $artifactType,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'content' => $result['content'] ?? null,
            'status' => !empty($result['status']) ? $result['status'] : Artifact::STATUS_SUCCESS,
            'model_name' => $result['model_name'] ?? null,
            'prompt_version' => $result['prompt_version'] ?? null,
            'generated_at' => $result['generated_at'] ?? date('Y-m-d H:i:s'),
            'error_message' => $result['error_message'] ?? null,
            // 统计真实调用生成的次数（定时任务「每篇最多试 N 次」用）
            'attempt_count' => ((int)($existing->attempt_count ?? 0)) + 1,
        );

        if ($existing) {
            $artifact = $this->artifactRepository->update($existing, $data);
        } else {
            try {
                $artifact = $this->artifactRepository->create($data);
            } catch (\Illuminate\Database\QueryException $e) {
                // 并发兜底：唯一键冲突说明另一个请求已写入，重查后覆盖
                $artifact = $this->artifactRepository->findByUniqueKey($userId, $relatedType, $relatedId, $artifactType);
                if ($artifact) {
                    $artifact = $this->artifactRepository->update($artifact, $data);
                } else {
                    throw $e;
                }
            }
        }

        return array(
            'artifact' => $artifact,
            'generated' => true,
        );
    }

    /**
     * 删除制品（归属校验由上层完成）
     */
    public function delete(Artifact $artifact)
    {
        return $this->artifactRepository->delete($artifact);
    }

    /**
     * 把 mind_map 制品落库为 minds 节点树。
     *
     * @param Artifact $artifact
     * @param int $userId
     * @return Mind 根节点
     */
    public function toMindTree(Artifact $artifact, $userId)
    {
        if ($artifact->artifact_type !== Artifact::TYPE_MIND_MAP) {
            throw new CustomException('仅思维导图制品可保存为思维导图');
        }

        $nodeTree = $this->parseNodeTree($artifact->content);
        if (empty($nodeTree)) {
            throw new CustomException('制品内容不是有效的思维导图数据');
        }

        $root = $this->createMindNode($userId, $nodeTree['topic'], null, $artifact);
        $this->createMindChildren($userId, $nodeTree['children'] ?? array(), $root->id, $artifact, 1);

        return $root;
    }

    /**
     * 生成器：文章可视化阅读（复用 ArticleAiRenderService 管线）
     */
    protected function generateArticleVisualReading($relatedId, array $options = array())
    {
        $article = Article::with('feed')->find($relatedId);
        if (empty($article)) {
            return array(
                'status' => Artifact::STATUS_FAILED,
                'error_message' => '文章不存在',
            );
        }

        $render = $this->articleAiRenderService->ensureRender($article, array(
            'force' => !empty($options['force']),
            'template_style' => !empty($options['template_style']) ? (string)$options['template_style'] : 'magazine',
            'custom_prompt' => !empty($options['custom_prompt']) ? (string)$options['custom_prompt'] : '',
        ));

        if ($render && $render->status === 'success' && !empty($render->html_content)) {
            return array(
                'name' => '文章可视化阅读',
                'file_type' => Artifact::FILE_HTML,
                'content' => $render->html_content,
                'status' => Artifact::STATUS_SUCCESS,
                'model_name' => $render->model_name,
                'prompt_version' => $this->limitPromptVersion('article_visual_reading:' . ($render->prompt_version ?: 'v1')),
                'generated_at' => $render->generated_at ? $render->generated_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
                'error_message' => $render->error_message,
            );
        }

        return array(
            'name' => '文章可视化阅读',
            'file_type' => Artifact::FILE_HTML,
            'content' => null,
            'status' => Artifact::STATUS_FAILED,
            'model_name' => $render->model_name ?? 'fallback-local',
            'prompt_version' => $this->limitPromptVersion('article_visual_reading:' . ($render->prompt_version ?: 'v1')),
            'generated_at' => date('Y-m-d H:i:s'),
            'error_message' => $render->error_message ?? '可视化阅读生成失败',
        );
    }

    /**
     * 生成器：文章思维导图（LLM 生成 node_tree JSON）
     */
    protected function generateArticleMindMap($relatedId, array $options = array())
    {
        $article = Article::with('feed')->find($relatedId);
        if (empty($article)) {
            return array(
                'status' => Artifact::STATUS_FAILED,
                'error_message' => '文章不存在',
            );
        }

        $articleText = $this->buildArticleText($article);
        if ($articleText === '') {
            return array(
                'status' => Artifact::STATUS_FAILED,
                'error_message' => '文章内容为空',
            );
        }

        $customPrompt = trim((string)($options['custom_prompt'] ?? ''));
        $promptVersion = $customPrompt !== '' ? 'article_mind_map:custom-v1' : 'article_mind_map:v1';

        $messages = array(
            array(
                'role' => 'system',
                'content' => '你是一个知识结构化助手，且不会有冗长的内心思考，看到文章后立即直接输出结果。你的工作是把文章内容提炼为思维导图的节点树，帮助用户快速抓住文章结构。'
                    . ' 必须只输出 JSON，不要 markdown，不要解释。输出 JSON 字段固定为 format, data。'
                    . ' format 固定为 "node_tree"。data 结构为 {"id":"root","topic":"<根节点标题>","children":[{"id":"1","topic":"<主分支>","children":[{"id":"1-1","topic":"<子节点>"}]}]}。'
                    . ' 要求：根节点用一句话概括全文主题；3-6 个主分支；每个主分支 2-4 个子节点；节点 id 必须全局唯一且为字符串；'
                    . ' 节点主题必须是能独立成句的要点，保留原文关键数字、专有名词、公司名、产品名，使用中文；'
                    . ' 严禁编造原文没有的事实，严禁出现空泛标题（如"概述""要点"），每个节点都要有实质信息；'
                    . ' 全部节点（含根）总数不超过 30 个，深度不超过 3 层。'
            ),
            array(
                'role' => 'user',
                'content' => "请将下面文章提炼为思维导图节点树并返回 JSON：\n"
                    . ($customPrompt !== '' ? "用户自定义补充要求：\n" . $customPrompt . "\n" : '')
                    . $articleText,
            ),
        );

        $llmResult = $this->llmStructuredTaskService->runTask(
            'artifact_article_mind_map',
            $messages,
            array(
                'timeout' => 240,
                'max_tokens' => 8192,
                'force_model' => 'deepseek-v4-flash',
            )
        );

        $parsed = null;
        if (!empty($llmResult['success']) && !empty($llmResult['content'])) {
            $parsed = $this->parseStructuredJson($llmResult['content']);
        }

        $nodeTree = $this->normalizeNodeTree($parsed['data'] ?? ($parsed ?? array()));

        if (is_array($parsed) && !empty($nodeTree['topic'])) {
            $jsonData = array(
                'format' => 'node_tree',
                'data' => $nodeTree,
            );

            return array(
                'name' => '文章思维导图',
                'file_type' => Artifact::FILE_JSON,
                'content' => json_encode($jsonData, JSON_UNESCAPED_UNICODE),
                'status' => Artifact::STATUS_SUCCESS,
                'model_name' => $llmResult['meta']['model_name'] ?? 'unknown',
                'prompt_version' => $promptVersion,
                'generated_at' => date('Y-m-d H:i:s'),
                'error_message' => $llmResult['error'] ?? null,
            );
        }

        $errorMessage = !empty($llmResult['error']) ? $llmResult['error'] : 'LLM 返回内容无法解析为思维导图数据';
        $fallback = $this->buildFallbackNodeTree($article);

        return array(
            'name' => '文章思维导图',
            'file_type' => Artifact::FILE_JSON,
            'content' => json_encode(array('format' => 'node_tree', 'data' => $fallback), JSON_UNESCAPED_UNICODE),
            'status' => Artifact::STATUS_FAILED,
            'model_name' => !empty($llmResult['meta']['model_name']) ? $llmResult['meta']['model_name'] : 'fallback-local',
            'prompt_version' => $promptVersion,
            'generated_at' => date('Y-m-d H:i:s'),
            'error_message' => $errorMessage,
        );
    }

    // ---------- 内部工具 ----------

    protected function defaultName($artifactType)
    {
        $names = array(
            Artifact::TYPE_VISUAL_READING => '可视化阅读',
            Artifact::TYPE_MIND_MAP => '思维导图',
            Artifact::TYPE_KEY_POINTS => 'AI 关键信息',
            Artifact::TYPE_BRIEFING_LATEST => '最新简报',
            Artifact::TYPE_BRIEFING_FOLLOWED => '关注简报',
            Artifact::TYPE_NOTE_MIND_MAP => '笔记思维导图',
        );

        return $names[$artifactType] ?? ('制品 - ' . $artifactType);
    }

    /**
     * prompt_version 字段长度 32 截断
     */
    protected function limitPromptVersion($version)
    {
        $version = trim((string)$version);
        if ($version === '') {
            return null;
        }

        return mb_substr($version, 0, 32);
    }

    protected function buildArticleText(Article $article)
    {
        $subject = trim((string)$article->subject);
        $content = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$article->content)));
        $content = mb_substr($content, 0, 5000);
        $feedName = '';
        $categoryName = '';

        if (!empty($article->feed)) {
            $feedName = (string)$article->feed->feed_name;
            if (!empty($article->feed->category)) {
                $categoryName = (string)$article->feed->category->name;
            }
        }

        $parts = array_filter(array(
            $subject !== '' ? '标题：' . $subject : '',
            $feedName !== '' ? '来源：' . $feedName : '',
            $categoryName !== '' ? '分类：' . $categoryName : '',
            !empty($article->published) ? '发布时间：' . $article->published : '',
            $content !== '' ? '正文：' . $content : '',
        ));

        return trim(implode("\n", $parts));
    }

    protected function parseStructuredJson($content)
    {
        $content = trim((string)$content);
        $content = preg_replace('/^```json\s*/', '', $content);
        $content = preg_replace('/^```\s*/', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);

        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        $jsonStart = strpos($content, '{');
        $jsonEnd = strrpos($content, '}');
        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $content = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * 规范化 node_tree：id 唯一、topic 非空、深度/数量限制。
     */
    protected function normalizeNodeTree($data, $depth = 0, &$counter = 0)
    {
        if (!is_array($data)) {
            return null;
        }

        $topic = trim((string)($data['topic'] ?? ''));
        if ($topic === '' && $depth === 0) {
            return null;
        }
        if ($topic === '' || $counter >= 30 || $depth > 3) {
            return null;
        }

        $counter++;
        $counterMarker = $counter;

        $node = array(
            'id' => $depth === 0 ? 'root' : ('n' . $counterMarker),
            'topic' => mb_substr($topic, 0, 60),
        );

        $children = array();
        foreach ((array)($data['children'] ?? array()) as $child) {
            $normalized = $this->normalizeNodeTree($child, $depth + 1, $counter);
            if ($normalized) {
                $children[] = $normalized;
            }
            if ($counter >= 30) {
                break;
            }
        }

        if (!empty($children)) {
            $node['children'] = $children;
        }

        return $node;
    }

    /**
     * 解析制品 content 为 node_tree（data 部分）
     */
    protected function parseNodeTree($content)
    {
        $decoded = json_decode((string)$content, true);
        if (!is_array($decoded)) {
            return null;
        }

        $data = $decoded['data'] ?? $decoded;

        $topic = trim((string)($data['topic'] ?? ''));
        if ($topic === '') {
            return null;
        }

        return $data;
    }

    protected function buildFallbackNodeTree(Article $article)
    {
        $segments = $this->splitArticleSegments($article);
        $rootTopic = trim((string)$article->subject);
        if ($rootTopic === '') {
            $rootTopic = '文章结构';
        }

        $children = array();
        foreach (array_slice($segments, 0, 5) as $i => $segment) {
            $children[] = array(
                'id' => 'f' . ($i + 1),
                'topic' => mb_substr($segment, 0, 36),
            );
        }

        if (empty($children)) {
            $children[] = array('id' => 'f1', 'topic' => '核心观点');
            $children[] = array('id' => 'f2', 'topic' => '关键细节');
            $children[] = array('id' => 'f3', 'topic' => '延伸思考');
        }

        return array(
            'id' => 'root',
            'topic' => mb_substr($rootTopic, 0, 60),
            'children' => $children,
        );
    }

    protected function splitArticleSegments(Article $article)
    {
        $text = trim(strip_tags((string)$article->content));
        $text = preg_replace('/\r\n|\r/u', "\n", $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text);
        $segments = preg_split('/(?<=[。！？；.!?])\s+|\n+/u', $text);
        $result = array();

        foreach ((array)$segments as $segment) {
            $segment = trim(preg_replace('/\s+/u', ' ', $segment));
            if ($segment !== '') {
                $result[] = $segment;
            }
        }

        return $result;
    }

    protected function createMindNode($userId, $name, $parentMindId, Artifact $artifact)
    {
        $mind = new Mind();
        $mind->name = htmlspecialchars($name);
        $mind->parent_mind_id = $parentMindId;
        $mind->is_root = empty($parentMindId) ? 1 : 0;
        $mind->user_id = $userId;
        $mind->source_type = $artifact->related_type;
        $mind->source_id = (int)$artifact->related_id;
        $mind->save();

        return $mind;
    }

    protected function createMindChildren($userId, array $children, $parentMindId, Artifact $artifact, $depth)
    {
        if ($depth > 3) {
            return;
        }

        foreach ($children as $child) {
            $topic = trim((string)($child['topic'] ?? ''));
            if ($topic === '') {
                continue;
            }

            $node = $this->createMindNode($userId, $topic, $parentMindId, $artifact);
            $this->createMindChildren($userId, $child['children'] ?? array(), $node->id, $artifact, $depth + 1);
        }
    }

    // ============ AI 关键信息（key_points，markdown）============

    /**
     * 生成器：文章 AI 关键信息（markdown list）
     */
    protected function generateArticleKeyPoints($relatedId, array $options = array())
    {
        $article = Article::with('feed')->find($relatedId);
        if (empty($article)) {
            return array('status' => Artifact::STATUS_FAILED, 'error_message' => '文章不存在');
        }

        return $this->runKeyPointsLlm('article', $this->buildArticleText($article), $options);
    }

    /**
     * 生成器：笔记 AI 关键信息（markdown list）
     */
    protected function generateNoteKeyPoints($relatedId, array $options = array())
    {
        $note = \App\Models\Note::find($relatedId);
        if (empty($note)) {
            return array('status' => Artifact::STATUS_FAILED, 'error_message' => '笔记不存在');
        }

        $text = trim(strip_tags((string)$note->content));
        if ($text === '') {
            $text = trim((string)$note->name);
        }
        $prefixed = ($note->name ? '标题：' . $note->name . "\n" : '') . '正文：' . $text;

        return $this->runKeyPointsLlm('note', $prefixed, $options);
    }

    /**
     * 统一的「AI 关键信息」markdown 生成
     */
    protected function runKeyPointsLlm($sourceType, $text, array $options = array())
    {
        $text = mb_substr(trim(preg_replace('/\s+/u', ' ', $text)), 0, 6000);
        if ($text === '') {
            return array('status' => Artifact::STATUS_FAILED, 'error_message' => '内容为空');
        }

        $customPrompt = trim((string)($options['custom_prompt'] ?? ''));
        $promptVersion = 'key_points:' . ($customPrompt !== '' ? 'custom-v1' : 'v1');

        $messages = array(
            array(
                'role' => 'system',
                'content' => '你是一个要点提炼助手，且不会有冗长的内心思考，看到内容后立即直接输出结果。把下面文本提炼为一组「AI 生成的关键信息」，输出 Markdown 列表。'
                    . ' 要求：'
                    . ' 1. 用无序列表 - 呈现 5-12 条关键信息，每条是一句能独立成句的要点；'
                    . ' 2. 支持用 **加粗** 强调核心术语、数字、结论、专有名词；'
                    . ' 3. 可在合适处用 ## 一级小标题分组（如 ## 核心结论、## 关键数据、## 行动建议）；'
                    . ' 4. 内容必须忠实原文，严禁编造；删除套话；'
                    . ' 5. 只输出 Markdown 正文，不要输出其他任何文字、不要 json、不要代码块包裹。'
            ),
            array(
                'role' => 'user',
                'content' => "请提炼下面内容的关键信息为 Markdown 列表：\n"
                    . ($customPrompt !== '' ? "用户补充要求：\n" . $customPrompt . "\n" : '')
                    . $text,
            ),
        );

        $llmResult = $this->llmStructuredTaskService->runTask(
            'artifact_' . $sourceType . '_key_points',
            $messages,
            array(
                'timeout' => 180,
                'max_tokens' => 8192,
                'force_model' => 'deepseek-v4-flash',
            )
        );

        $content = '';
        if (!empty($llmResult['success']) && !empty($llmResult['content'])) {
            $content = trim((string)$llmResult['content']);
        }

        if ($content !== '' && mb_strlen($content) > 20) {
            return array(
                'name' => 'AI 关键信息',
                'file_type' => Artifact::FILE_MARKDOWN,
                'content' => $content,
                'status' => Artifact::STATUS_SUCCESS,
                'model_name' => $llmResult['meta']['model_name'] ?? 'unknown',
                'prompt_version' => $this->limitPromptVersion($promptVersion),
                'generated_at' => date('Y-m-d H:i:s'),
                'error_message' => $llmResult['error'] ?? null,
            );
        }

        return array(
            'name' => 'AI 关键信息',
            'file_type' => Artifact::FILE_MARKDOWN,
            'content' => null,
            'status' => Artifact::STATUS_FAILED,
            'model_name' => !empty($llmResult['meta']['model_name']) ? $llmResult['meta']['model_name'] : 'fallback-local',
            'prompt_version' => $this->limitPromptVersion($promptVersion),
            'generated_at' => date('Y-m-d H:i:s'),
            'error_message' => !empty($llmResult['error']) ? $llmResult['error'] : 'AI 关键信息生成失败',
        );
    }

    // ============ 笔记思维导图 / 可视化 ============

    /**
     * 生成器：笔记思维导图（node_tree）
     */
    protected function generateNoteMindMap($relatedId, array $options = array())
    {
        $note = \App\Models\Note::find($relatedId);
        if (empty($note)) {
            return array('status' => Artifact::STATUS_FAILED, 'error_message' => '笔记不存在');
        }
        $text = trim(strip_tags((string)$note->content));
        if ($text === '') {
            $text = trim((string)$note->name);
        }
        $text = ($note->name ? '标题：' . $note->name . "\n" : '') . '正文：' . $text;

        return $this->runMindMapLlm($text, 'note', $note->name, $options);
    }

    /**
     * 统一的思维导图 node_tree 生成（文章/笔记共用）
     */
    protected function runMindMapLlm($articleText, $sourceType, $defaultTitle, array $options = array())
    {
        $articleText = mb_substr(trim(preg_replace('/\s+/u', ' ', $articleText)), 0, 6000);
        if ($articleText === '') {
            return array('status' => Artifact::STATUS_FAILED, 'error_message' => '内容为空');
        }

        $customPrompt = trim((string)($options['custom_prompt'] ?? ''));
        $promptVersion = $customPrompt !== '' ? ($sourceType . '_mind_map:custom-v1') : ($sourceType . '_mind_map:v1');

        $messages = array(
            array(
                'role' => 'system',
                'content' => '你是一个知识结构化助手，且不会有冗长的内心思考，看到内容后立即直接输出结果。把下面内容提炼为思维导图的节点树。'
                    . ' 必须只输出 JSON，不要 markdown，不要解释。输出 JSON 字段固定为 format, data。'
                    . ' format 固定为 "node_tree"。data 结构为 {"id":"root","topic":"<根节点标题>","children":[{"id":"1","topic":"<主分支>","children":[{"id":"1-1","topic":"<子节点>"}]}]}。'
                    . ' 要求：根节点用一句话概括主题；3-6 个主分支；每个主分支 2-4 个子节点；id 全局唯一且为字符串；节点主题必须是能独立成句的要点，保留关键数字与专有名词；'
                    . ' 严禁编造内容没有的事实，严禁空泛标题；全部节点不超过 30 个，深度不超过 3 层。'
            ),
            array(
                'role' => 'user',
                'content' => "请将下面内容提炼为思维导图节点树并返回 JSON：\n"
                    . ($customPrompt !== '' ? "用户自定义补充要求：\n" . $customPrompt . "\n" : '')
                    . $articleText,
            ),
        );

        $llmResult = $this->llmStructuredTaskService->runTask(
            'artifact_' . $sourceType . '_mind_map',
            $messages,
            array(
                'timeout' => 240,
                'max_tokens' => 8192,
                'force_model' => 'deepseek-v4-flash',
            )
        );

        $parsed = null;
        if (!empty($llmResult['success']) && !empty($llmResult['content'])) {
            $parsed = $this->parseStructuredJson($llmResult['content']);
        }

        $nodeTree = $this->normalizeNodeTree($parsed['data'] ?? ($parsed ?? array()));
        if (is_array($parsed) && !empty($nodeTree['topic'])) {
            return array(
                'name' => ($sourceType === 'note' ? '笔记' : '文章') . '思维导图',
                'file_type' => Artifact::FILE_JSON,
                'content' => json_encode(array('format' => 'node_tree', 'data' => $nodeTree), JSON_UNESCAPED_UNICODE),
                'status' => Artifact::STATUS_SUCCESS,
                'model_name' => $llmResult['meta']['model_name'] ?? 'unknown',
                'prompt_version' => $this->limitPromptVersion($promptVersion),
                'generated_at' => date('Y-m-d H:i:s'),
                'error_message' => $llmResult['error'] ?? null,
            );
        }

        $errorMessage = !empty($llmResult['error']) ? $llmResult['error'] : 'LLM 返回内容无法解析为思维导图数据';
        $title = trim((string)$defaultTitle);
        $fallback = array('id' => 'root', 'topic' => mb_substr($title !== '' ? $title : '内容结构', 0, 60));
        $fallback['children'] = array(array('id' => 'f1', 'topic' => '核心观点'), array('id' => 'f2', 'topic' => '关键细节'));

        return array(
            'name' => ($sourceType === 'note' ? '笔记' : '文章') . '思维导图',
            'file_type' => Artifact::FILE_JSON,
            'content' => json_encode(array('format' => 'node_tree', 'data' => $fallback), JSON_UNESCAPED_UNICODE),
            'status' => Artifact::STATUS_FAILED,
            'model_name' => !empty($llmResult['meta']['model_name']) ? $llmResult['meta']['model_name'] : 'fallback-local',
            'prompt_version' => $this->limitPromptVersion($promptVersion),
            'generated_at' => date('Y-m-d H:i:s'),
            'error_message' => $errorMessage,
        );
    }

    /**
     * 生成器：笔记可视化阅读（HTML）
     */
    protected function generateNoteVisualReading($relatedId, array $options = array())
    {
        $note = \App\Models\Note::find($relatedId);
        if (empty($note)) {
            return array('status' => Artifact::STATUS_FAILED, 'error_message' => '笔记不存在');
        }
        $text = trim(strip_tags((string)$note->content));
        if ($text === '') {
            $text = trim((string)$note->name);
        }
        $text = ($note->name ? '标题：' . $note->name . "\n" : '') . '正文：' . $text;

        return $this->runVisualReadingLlm($text, 'note', $note->name, $options);
    }

    /**
     * 统一的可视化阅读 HTML 生成（文章/笔记共用；文章走原有 ArticleAiRender 管线）
     */
    protected function runVisualReadingLlm($text, $sourceType, $defaultTitle, array $options = array())
    {
        $text = mb_substr(trim(preg_replace('/\s+/u', ' ', $text)), 0, 6000);
        if ($text === '') {
            return array('status' => Artifact::STATUS_FAILED, 'error_message' => '内容为空');
        }

        $customPrompt = trim((string)($options['custom_prompt'] ?? ''));
        $promptVersion = $customPrompt !== '' ? ($sourceType . '_visual_reading:custom-v1') : ($sourceType . '_visual_reading:v10-ppt');

        $messages = array(
            array(
                'role' => 'system',
                'content' => '你是一个高级 PPT 视觉设计师，且不会有冗长的内心思考，看到内容后立即直接输出结果。把下面内容做成「重点突出」的 HTML 卡片式阅读页。'
                    . ' 必须只输出一个 JSON 对象，字段固定为 title, subtitle, summary, outline, html。不要输出其他文字，不要 markdown 代码块。'
                    . ' html 用 Tailwind class 做卡片式排版：开头标题横幅(h2)，中间 3-4 张卡片每个版块(表头 h3 + 要点 ul/li)+色块左边框强调，关键数字/结论用 <strong><mark>。'
                    . ' 整体 700-1200 字符。属性用单引号。只允许 section, div, h2, h3, h4, p, ul, ol, li, blockquote, strong, b, em, code, hr, mark, span，禁 style/script/img/a/button/form/input。内容忠实原文，不编造，不用 emoji。'
            ),
            array(
                'role' => 'user',
                'content' => "请将下面内容生成卡片式阅读页，返回 JSON：\n"
                    . ($customPrompt !== '' ? "用户补充要求：\n" . $customPrompt . "\n" : '')
                    . $text,
            ),
        );

        $llmResult = $this->llmStructuredTaskService->runTask(
            'artifact_' . $sourceType . '_visual_reading',
            $messages,
            array(
                'timeout' => 240,
                'max_tokens' => 8192,
                'force_model' => 'deepseek-v4-flash',
            )
        );

        $parsed = null;
        if (!empty($llmResult['success']) && !empty($llmResult['content'])) {
            $parsed = $this->parseStructuredJson($llmResult['content']);
        }

        if (is_array($parsed) && !empty($parsed['html'])) {
            return array(
                'name' => ($sourceType === 'note' ? '笔记' : '文章') . '可视化阅读',
                'file_type' => Artifact::FILE_HTML,
                'content' => $this->sanitizeArtifactHtml($parsed['html']),
                'status' => Artifact::STATUS_SUCCESS,
                'model_name' => $llmResult['meta']['model_name'] ?? 'unknown',
                'prompt_version' => $this->limitPromptVersion($promptVersion),
                'generated_at' => date('Y-m-d H:i:s'),
                'error_message' => $llmResult['error'] ?? null,
            );
        }

        return array(
            'name' => ($sourceType === 'note' ? '笔记' : '文章') . '可视化阅读',
            'file_type' => Artifact::FILE_HTML,
            'content' => null,
            'status' => Artifact::STATUS_FAILED,
            'model_name' => !empty($llmResult['meta']['model_name']) ? $llmResult['meta']['model_name'] : 'fallback-local',
            'prompt_version' => $this->limitPromptVersion($promptVersion),
            'generated_at' => date('Y-m-d H:i:s'),
            'error_message' => !empty($llmResult['error']) ? $llmResult['error'] : 'LLM 返回内容无法解析为包含 html 字段的 JSON',
        );
    }

    /**
     * 清理制品 HTML（去掉危险标签/属性）
     */
    protected function sanitizeArtifactHtml($html)
    {
        $clean = trim((string)$html);
        $clean = strip_tags($clean, '<section><div><h2><h3><h4><p><ul><ol><li><blockquote><strong><b><em><code><hr><table><thead><tbody><tr><th><td><dl><dt><dd><mark><span>');
        $clean = preg_replace_callback('/\sclass=("|\')(.*?)\1/i', function ($matches) {
            $classes = preg_split('/\s+/', trim($matches[2]));
            $classes = array_filter($classes, function ($class) {
                return $class !== '' && preg_match('/^[A-Za-z0-9\-\[\]\/_:.,()%]+$/', $class);
            });
            return empty($classes) ? '' : ' class="' . implode(' ', $classes) . '"';
        }, $clean);
        $clean = preg_replace('/\s(on\w+|style|id|data-[a-z0-9\-_]+)=("|\').*?("|\')/i', '', $clean);
        $clean = preg_replace('/\s(?!class\b)[a-z0-9\-_:]+=("|\')[^"\']*\1/i', '', $clean);
        return trim($clean);
    }
}