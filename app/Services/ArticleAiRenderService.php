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
        $promptVersion = $customPrompt !== '' ? 'article_reader_render:custom-v1' : 'article_reader_render:v10-ppt';
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
                'content' => '你是一个高级 PPT 视觉设计师，且不会有冗长的内心思考，看到文章后立即直接输出结果。把下面的文章做成一份「重点突出、层次分明」的 HTML 卡片/幻灯片式阅读页，让读者不用看全文就能抓住重点。'
                    . ' 必须只输出一个 JSON 对象，字段固定为 title, subtitle, summary, outline, html。不要输出其他任何文字，不要用 markdown，不要代码块包裹。'
                    . ' title=一句话概况主题；subtitle=说清这篇文章解决什么问题；summary=1-2 句导读；outline=3-5 条核心要点；html=完整 HTML 片段。'
                    . ' html 设计规范（模仿精美 PPT / 信息图）：'
                    . ' 1. 不要在 html 里再放文章的大标题横幅：文章标题由页面标题栏单独展示，html 直接用 3-4 张「卡片」呈现各板块内容即可；每张卡片配色块表头(h3)和要点列表(ul/li)。'
                    . ' 2. 大量使用 Tailwind class 做视觉层次：如 bg-sky-50, text-sky-900, border-l-4, border-sky-500, rounded-xl, shadow-sm, px-4, py-3, font-bold, text-sm, text-slate-600, space-y-2 等。'
                    . ' 3. 关键数字、核心结论、专有名词用 <strong> 或 <mark> 高亮。'
                    . ' 4. 可在合适处用 <blockquote> 做金句/引语强调；有对比时用简单列表。'
                    . ' 5. html 整体控制在 700-1200 个字符，够做 3-5 张卡片。'
                    . ' 6. html 内所有属性用单引号，例如 class=\'bg-white rounded-xl\'；只允许标签 section, div, h2, h3, h4, p, ul, ol, li, blockquote, strong, b, em, code, hr, table, thead, tbody, tr, th, td, mark, span；绝不要使用 style, script, img, a, button, form, input。'
                    . ' 7. 内容必须忠实原文，禁止编造原文没有的事实；不要使用 emoji 或装饰性特殊符号。'
            ),
            array(
                'role' => 'user',
                'content' => "请将下面文章转换为精美卡片式阅读页，返回 JSON：\n"
                    . ($customPrompt !== '' ? "用户自定义补充要求：\n" . $customPrompt . "\n" : '')
                    . $articleText,
            ),
        );

        $llmResult = $this->llmStructuredTaskService->runTask(
            'article_reader_render',
            $messages,
            array(
                'timeout' => 240,
                'max_tokens' => (int)($options['max_tokens'] ?? 8192),
                'force_model' => $this->pickRenderModel($options),
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

    /**
     * 选择可视化阅读可用的模型名。
     * 优先用自建网关背后更稳/更强的模型（minimax），其次保留默认；也可通过 options['model'] 覆盖。
     */
    protected function pickRenderModel(array $options = array())
    {
        // 兼容 options 显式指定完整模型名
        $explicit = trim((string)($options['model'] ?? $options['model_name'] ?? ''));
        if ($explicit !== '') {
            return $explicit;
        }

        // 默认在自建网关背后选一个解析能力强于 gemma-free 的模型
        return 'deepseek-v4-flash';
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

        $cleanHtml = $this->stripLeadingTitleBanner($cleanHtml);

        return trim($cleanHtml);
    }

    /**
     * 去掉 html 开头的大标题横幅（由页面标题栏单独展示，避免出现两个标题）。
     * 只处理最开头的标题块：wrapper div 内含 h2（+可选 subtitle p），或裸 h2。
     * 卡片自身的 h3 表头不受影响。
     */
    protected function stripLeadingTitleBanner($html)
    {
        $html = trim((string)$html);
        $consumedSection = false;

        // 情况 A：<section> 包裹标题块（<div…><h2>…[<p>副标题]…</div> 或 裸 <h2>[+<p>]）
        if (preg_match('/\A\s*<section\b[^>]*>\s*(?:<div\b[^>]*>\s*<h2\b[^>]*>.*?<\/h2>(?:\s*<p\b[^>]*>.*?<\/p>)?\s*<\/div>|<h2\b[^>]*>.*?<\/h2>(?:\s*<p\b[^>]*>.*?<\/p>)?)/is', $html)) {
            $html = preg_replace('/\A\s*<section\b[^>]*>\s*(?:<div\b[^>]*>\s*<h2\b[^>]*>.*?<\/h2>(?:\s*<p\b[^>]*>.*?<\/p>)?\s*<\/div>|<h2\b[^>]*>.*?<\/h2>(?:\s*<p\b[^>]*>.*?<\/p>)?)\s*/is', '', $html, 1);
            $consumedSection = true;
        } else {
            // 情况 B：直接以 <div 包装><h2>…</div> 开头
            $html = preg_replace('/\A\s*<div\b[^>]*>\s*<h2\b[^>]*>.*?<\/h2>(?:\s*<p\b[^>]*>.*?<\/p>)?\s*<\/div>\s*/is', '', $html, 1);
            // 情况 C：直接以裸 <h2>[+<p>] 开头
            $html = preg_replace('/\A\s*<h2\b[^>]*>.*?<\/h2>(?:\s*<p\b[^>]*>.*?<\/p>)?\s*/is', '', $html, 1);
        }

        // 若开头 <section> 被消费，移除结尾对应的 </section>，保持标签平衡
        if ($consumedSection) {
            $html = preg_replace('/<\/section>\s*\z/is', '', $html, 1);
        }

        return trim((string)$html);
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
