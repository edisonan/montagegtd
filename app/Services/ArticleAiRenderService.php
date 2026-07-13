<?php

namespace App\Services;

use App\Http\Utils\CommonUtil;
use App\Models\Article;
use App\Repositories\ArticleAiRenderRepository;

class ArticleAiRenderService
{
    protected $renderRepository;
    protected $llmStructuredTaskService;

    public function __construct(
        ArticleAiRenderRepository $renderRepository,
        LlmStructuredTaskService $llmStructuredTaskService
    ) {
        $this->renderRepository = $renderRepository;
        $this->llmStructuredTaskService = $llmStructuredTaskService;
    }

    public function getRenderByArticleId($articleId)
    {
        return $this->renderRepository->findByArticleId($articleId);
    }

    public function ensureRender(Article $article, array $options = array())
    {
        $existing = $this->getRenderByArticleId($article->id);
        $customPrompt = trim((string)($options['custom_prompt'] ?? ''));
        if ($existing && !empty($existing->html_content) && $existing->status === 'success' && empty($options['force']) && $customPrompt === '') {
            return $existing;
        }

        $templateStyle = !empty($options['template_style']) ? (string)$options['template_style'] : 'magazine';
        $promptVersion = $customPrompt !== '' ? 'article_reader_render:custom-v1' : 'article_reader_render:v8';
        $articleText = $this->buildArticleText($article);

        if ($articleText === '') {
            return $this->renderRepository->updateOrCreateByArticleId($article->id, array(
                'status' => 'failed',
                'render_mode' => 'visual_story',
                'template_style' => $templateStyle,
                'summary' => $this->buildFallbackSummary($article),
                'outline_json' => $this->buildFallbackOutline($article),
                'html_content' => null,
                'model_name' => 'fallback-local',
                'prompt_version' => $promptVersion,
                'generated_at' => date('Y-m-d H:i:s'),
                'error_message' => '文章内容为空',
            ));
        }

        $messages = array(
            array(
                'role' => 'system',
                'content' => '你是一个面向学习场景的文章可视化编辑器。你的工作是把原文重组为更容易理解的 HTML 片段，而不是套固定模板。必须只输出 JSON，不要 markdown，不要解释。'
                    . ' 输出 JSON 字段固定为 title, subtitle, summary, outline, html。'
                    . ' 输出语言使用中文；保留必要英文专有名词、公司名、产品名和原文数字。'
                    . ' title 要概括主题，不要照抄长标题；subtitle 用一句话说明这篇文章解决什么理解问题；summary 只写 1-2 句导读。'
                    . ' outline 必须是 3-7 条真正的要点，每条都要包含原文中的实质信息，禁止写“导读”“关键要点”“收束思考”这类空泛标题。'
                    . ' html 是一个可直接嵌入页面的 HTML 片段，由你按文章内容自由设计结构。允许使用 main, article, section, header, footer, aside, div, h2, h3, h4, p, ul, ol, li, blockquote, strong, b, em, code, hr, table, thead, tbody, tr, th, td, dl, dt, dd, figure, figcaption, mark, span。'
                    . ' html 字符串内部的 HTML 属性必须使用单引号，例如 class=\'rounded-lg p-4\'，不要在 html 字符串内部使用未转义双引号。'
                    . ' 可以使用 Tailwind 风格 class 来表达视觉层次，例如卡片、列表、提示块、对比块、步骤块、数据块；但只有在内容真的适合时才使用。'
                    . ' 严禁硬凑模块：没有案例就不要做案例卡，没有有意义的指标就不要做数字卡，没有步骤就不要做步骤条。年份只有在表达趋势阶段时才能作为信息，不要把年份当成指标。'
                    . ' 每个区块必须提供新的信息，不能重复 summary；删除套话、空话、说明性废话。'
                    . ' 结构选择规则：报告/趋势文章优先展示核心判断、分类框架、证据数据、组织影响、行动建议；教程文章优先展示步骤、注意事项、示例；观点文章优先展示论点、论据、反方或边界。'
                    . ' 严禁补全原文没有给出的细节。如果原文只说有某类趋势，但没有列出具体趋势名称或例子，只能写“原文将其归为某类”，不能自行编造该类下的示例。'
                    . ' 所有卡片、列表、表格、数据块都必须能在原文中找到对应依据；找不到依据就删掉该区块。可以压缩、重组、对比原文信息，但不能扩写事实。'
                    . ' html 中必须忠实原文，不要编造事实。不要使用 emoji 或装饰性特殊符号。不要输出 script/style/iframe/img/audio/video/button/form/input。'
            ),
            array(
                'role' => 'user',
                'content' => "请将下面文章转换为适合学习理解的可视化 HTML 页面，并返回 JSON：\n"
                    . "默认要求：先按文章所属分类和 Feed 判断主题，再将内容拆成 3-5 个有信息量的小节，每个小节用 3-5 句话概括；页面要优先帮助用户快速理解文章，不要重复原文。\n"
                    . ($customPrompt !== '' ? "用户自定义补充要求：\n" . $customPrompt . "\n" : '')
                    . $articleText,
            ),
        );

        $llmResult = $this->llmStructuredTaskService->runTask(
            'article_reader_render',
            $messages,
            array(
                'timeout' => 150,
            )
        );

        $parsed = null;
        if (!empty($llmResult['success']) && !empty($llmResult['content'])) {
            $parsed = $this->parseStructuredJson($llmResult['content']);
        }

        if (is_array($parsed) && !empty($parsed['html'])) {
            $outline = $this->normalizeOutline($parsed['outline'] ?? array());
            if (empty($outline)) {
                $outline = $this->buildFallbackOutline($article);
            }

            return $this->renderRepository->updateOrCreateByArticleId($article->id, array(
                'status' => 'success',
                'render_mode' => 'visual_story',
                'template_style' => $templateStyle,
                'summary' => $this->limitText($parsed['summary'] ?? $this->buildFallbackSummary($article), 220),
                'outline_json' => $outline,
                'html_content' => $this->sanitizeReaderHtml($parsed['html']),
                'model_name' => $llmResult['meta']['model_name'] ?? 'unknown',
                'prompt_version' => $promptVersion,
                'generated_at' => date('Y-m-d H:i:s'),
                'error_message' => $llmResult['error'] ?? null,
            ));
        }

        $errorMessage = $llmResult['error'] ?? null;
        if ($errorMessage === null) {
            $errorMessage = 'LLM 返回内容无法解析为包含 html 字段的 JSON';
        }

        return $this->renderRepository->updateOrCreateByArticleId($article->id, array(
            'status' => 'failed',
            'render_mode' => 'visual_story',
            'template_style' => $templateStyle,
            'summary' => $this->buildFallbackSummary($article),
            'outline_json' => $this->buildFallbackOutline($article),
            'html_content' => null,
            'model_name' => !empty($llmResult['meta']['model_name']) ? $llmResult['meta']['model_name'] : 'fallback-local',
            'prompt_version' => $promptVersion,
            'generated_at' => date('Y-m-d H:i:s'),
            'error_message' => $errorMessage,
        ));
    }

    public function generateWorkbenchDigest(array $articles, $customPrompt = '')
    {
        $sections = array();
        foreach ($articles as $article) {
            $category = '未分类';
            $feedName = '';
            if ($article->relationLoaded('feed') && $article->feed) {
                $feedName = (string)$article->feed->feed_name;
                if ($article->feed->relationLoaded('category') && $article->feed->category) {
                    $category = (string)$article->feed->category->name;
                }
            }
            $sections[$category][] = array(
                'title' => (string)$article->subject,
                'feed' => $feedName,
            );
        }

        $source = '';
        foreach ($sections as $category => $items) {
            $source .= "\n【分类：" . $category . "】\n";
            foreach ($items as $item) {
                $source .= "标题：" . $item['title'] . "\n来源：" . $item['feed'] . "\n\n";
            }
        }
        $customPrompt = trim((string)$customPrompt);

        $messages = array(
            array(
                'role' => 'system',
                'content' => '你是文章阅读整理助手。必须只输出 JSON，不要 markdown，不要解释。'
                    . '输出字段固定为 title, subtitle, summary, outline, html。'
                    . '请只根据文章标题、分类和 Feed 元信息，把输入中的多篇文章按分类合并成一个统一的辅助阅读页面。每个分类必须单独成为一个模块，并用3-5句话概括标题体现出的共同主题、关注方向和差异；绝对不要要求或假设存在正文，也不要编造标题之外的事实。'
                    . 'html 必须是可嵌入页面的 HTML 片段，只允许使用 main, article, section, header, footer, aside, div, h2, h3, h4, p, ul, ol, li, blockquote, strong, b, em, code, hr, table, thead, tbody, tr, th, td, dl, dt, dd, figure, figcaption, mark, span。'
                    . '必须忠实输入，不得编造事实；不要输出 script/style/iframe/img/audio/video/button/form/input，不要使用 emoji。'
            ),
            array(
                'role' => 'user',
                'content' => '请整理下面当前页的全部文章：' . "\n"
                    . ($customPrompt !== '' ? '用户补充要求：' . $customPrompt . "\n" : '')
                    . $source,
            ),
        );

        $result = $this->llmStructuredTaskService->runTask('article_workbench_digest', $messages, array('timeout' => 180));
        $parsed = !empty($result['success']) && !empty($result['content'])
            ? $this->parseStructuredJson($result['content'])
            : null;

        if (is_array($parsed) && !empty($parsed['html'])) {
            return array(
                'status' => 'success',
                'summary' => $this->limitText($parsed['summary'] ?? '', 500),
                'outline' => $this->normalizeOutline($parsed['outline'] ?? array()),
                'html_content' => $this->sanitizeReaderHtml($parsed['html']),
                'error_message' => null,
            );
        }

        return array(
            'status' => 'failed',
            'summary' => '',
            'outline' => array(),
            'html_content' => null,
            'error_message' => !empty($result['error']) ? $result['error'] : 'AI 返回内容无法解析',
        );
    }

    protected function buildArticleText(Article $article)
    {
        $subject = trim((string)$article->subject);
        $content = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$article->content)));
        $content = mb_substr($content, 0, 5000);
        $feedName = '';
        $categoryName = '';

        if ($article->relationLoaded('feed') && $article->feed) {
            $feedName = (string)$article->feed->feed_name;
            if ($article->feed->relationLoaded('category') && $article->feed->category) {
                $categoryName = (string)$article->feed->category->name;
            }
        } elseif ($article->feed) {
            $feedName = (string)$article->feed->feed_name;
            if ($article->feed->category) {
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

            $content = preg_replace('/\\\\(?!["\\\\\/bfnrtu])/', '', $content);
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            $decoded = $this->parseLooseStructuredJson($content);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    protected function parseLooseStructuredJson($content)
    {
        $result = array();
        foreach (array('title', 'subtitle', 'summary') as $field) {
            if (preg_match('/"' . $field . '"\s*:\s*"((?:\\\\.|[^"\\\\])*)"/s', $content, $matches)) {
                $result[$field] = $this->decodeJsonStringFragment($matches[1]);
            }
        }

        if (preg_match('/"outline"\s*:\s*(\[[\s\S]*?\])\s*,\s*"html"/s', $content, $matches)) {
            $outline = json_decode($matches[1], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($outline)) {
                $result['outline'] = $outline;
            }
        }

        $htmlKey = strpos($content, '"html"');
        if ($htmlKey !== false) {
            $colon = strpos($content, ':', $htmlKey);
            $start = $colon === false ? false : strpos($content, '"', $colon);
            $end = strrpos(rtrim($content), '"');
            if ($start !== false && $end !== false && $end > $start) {
                $result['html'] = $this->decodeJsonStringFragment(substr($content, $start + 1, $end - $start - 1));
            }
        }

        return !empty($result['html']) ? $result : null;
    }

    protected function decodeJsonStringFragment($value)
    {
        $decoded = json_decode('"' . str_replace('"', '\"', $value) . '"');
        if (json_last_error() === JSON_ERROR_NONE && is_string($decoded)) {
            return $decoded;
        }

        return str_replace(array('\\n', '\\r', '\\t', '\\/', '\\"'), array("\n", "\r", "\t", '/', '"'), (string)$value);
    }

    protected function sanitizeReaderHtml($html)
    {
        $cleanHtml = CommonUtil::removeXSS((string)$html);
        $cleanHtml = strip_tags($cleanHtml, '<main><article><section><header><footer><aside><div><h2><h3><h4><p><ul><ol><li><blockquote><strong><b><em><code><hr><table><thead><tbody><tr><th><td><dl><dt><dd><figure><figcaption><mark><span>');
        $cleanHtml = preg_replace_callback('/\sclass=("|\')(.*?)\1/i', function ($matches) {
            $classes = preg_split('/\s+/', trim($matches[2]));
            $classes = array_filter($classes, function ($class) {
                return $class !== '' && preg_match('/^[A-Za-z0-9\-\[\]\/_:.,()%]+$/', $class);
            });

            if (empty($classes)) {
                return '';
            }

            return ' class="' . implode(' ', $classes) . '"';
        }, $cleanHtml);
        $cleanHtml = preg_replace('/\s(on\w+|style|id|data-[a-z0-9\-_]+)=("|\').*?("|\')/i', '', $cleanHtml);
        $cleanHtml = preg_replace('/\s(?!class\b)[a-z0-9\-_:]+=("|\')[^"\']*\1/i', '', $cleanHtml);

        return trim($cleanHtml);
    }

    protected function normalizeOutline($outline)
    {
        $result = array();
        foreach ((array)$outline as $item) {
            $text = $this->limitText($item, 60);
            if ($text !== '') {
                $result[] = $text;
            }
            if (count($result) >= 6) {
                break;
            }
        }

        return $result;
    }

    protected function buildFallbackSummary(Article $article)
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$article->content)));
        if ($text === '') {
            $text = trim((string)$article->subject);
        }

        return $this->limitText($text, 140);
    }

    protected function buildFallbackOutline(Article $article)
    {
        $segments = $this->splitArticleSegments($article);
        $result = array();

        foreach ((array)$segments as $segment) {
            $clean = $this->limitText($segment, 36);
            if ($clean !== '') {
                $result[] = $clean;
            }
            if (count($result) >= 5) {
                break;
            }
        }

        if (empty($result)) {
            $result[] = '核心观点';
            $result[] = '关键细节';
            $result[] = '延伸思考';
        }

        return $result;
    }

    protected function splitArticleSegments(Article $article)
    {
        $text = trim(strip_tags((string)$article->content));
        $text = preg_replace('/\r\n|\r/u', "\n", $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text);
        $segments = preg_split('/(?<=[。！？；.!?])\s+|\n+/u', $text);
        $result = array();

        foreach ((array)$segments as $segment) {
            $segment = $this->limitText($segment, 120);
            if ($segment !== '') {
                $result[] = $segment;
            }
        }

        return $result;
    }
    protected function limitText($text, $max)
    {
        $text = trim(preg_replace('/\s+/u', ' ', (string)$text));
        if ($text === '') {
            return '';
        }

        return mb_substr($text, 0, $max);
    }
}
