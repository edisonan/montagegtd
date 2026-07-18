<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\Article;
use App\Models\ArticleSub;
use App\Models\Feed;
use App\Models\FeedSub;
use App\Models\WebpageRssSource;
use App\Services\LlmStructuredTaskService;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\CssSelector\CssSelectorConverter;

include_once app_path('Http/Utils/Other/simple_html_dom.php');

class WebpageRssService
{
    protected $llmStructuredTaskService;

    public function __construct(LlmStructuredTaskService $llmStructuredTaskService)
    {
        $this->llmStructuredTaskService = $llmStructuredTaskService;
    }

    public function normalizeConfig(array $data)
    {
        return array(
            'name' => trim((string)array_get($data, 'name', '')),
            'list_url' => trim((string)array_get($data, 'list_url', '')),
            'category_id' => (int)array_get($data, 'category_id', 0),
            'item_selector' => trim((string)array_get($data, 'item_selector', '')),
            'title_selector' => trim((string)array_get($data, 'title_selector', '')),
            'url_selector' => trim((string)array_get($data, 'url_selector', '')),
            'published_selector' => trim((string)array_get($data, 'published_selector', '')),
            'image_selector' => trim((string)array_get($data, 'image_selector', '')),
            'summary_source' => array_get($data, 'summary_source', 'list') === 'detail' ? 'detail' : 'list',
            'summary_selector' => trim((string)array_get($data, 'summary_selector', '')),
            'detail_enabled' => (int)(array_get($data, 'detail_enabled', 0) ? 1 : 0),
            'detail_summary_selector' => trim((string)array_get($data, 'detail_summary_selector', '')),
            'content_selector' => trim((string)array_get($data, 'content_selector', '')),
            'exclude_selector' => trim((string)array_get($data, 'exclude_selector', '')),
            'author_selector' => trim((string)array_get($data, 'author_selector', '')),
            'max_content_length' => max(500, min(50000, (int)array_get($data, 'max_content_length', 12000))),
            'failure_strategy' => in_array(array_get($data, 'failure_strategy'), array('fallback', 'skip', 'title_only')) ? array_get($data, 'failure_strategy') : 'fallback',
            'refresh_interval' => max(15, min(1440, (int)array_get($data, 'refresh_interval', 60))),
            'dedupe_key' => in_array(array_get($data, 'dedupe_key'), array('url', 'title_published', 'title')) ? array_get($data, 'dedupe_key') : 'url',
            'encoding' => in_array(array_get($data, 'encoding'), array('auto', 'UTF-8', 'GBK')) ? array_get($data, 'encoding') : 'auto',
        );
    }

    public function debug(array $config, $limit = 10)
    {
        $config = array_merge($this->debugDefaults(), $config);
        $this->validateDebugConfig($config);
        $startedAt = microtime(true);
        $htmlText = $this->request($config['list_url']);
        $pageTitle = $this->guessPageTitle($htmlText);
        $html = $this->makeHtml($htmlText, $config['encoding']);
        if (empty($html)) {
            throw new CustomException('列表页HTML解析失败');
        }

        $nodes = $this->findAll($html, $config['item_selector']);
        $items = array();
        $errors = array();
        $limit = max(1, min(30, (int)$limit));

        foreach ($nodes as $index => $node) {
            if (count($items) >= $limit) {
                break;
            }
            try {
                $item = $this->extractListItem($node, $config);
                if (empty($item['url']) || empty($item['subject'])) {
                    $errors[] = '第' . ($index + 1) . '条缺少标题或详情URL';
                    continue;
                }
                if ($config['detail_enabled'] || $config['summary_source'] === 'detail') {
                    $item = $this->extractDetail($item, $config);
                }
                $item['debug_index'] = $index + 1;
                $items[] = $item;
            } catch (\Throwable $e) {
                $errors[] = '第' . ($index + 1) . '条：' . $e->getMessage();
            }
        }

        if (method_exists($html, 'clear')) {
            $html->clear();
        }

        return array(
            'page_title' => $pageTitle,
            'page_url' => $config['list_url'],
            'config' => array(
                'item_selector' => $config['item_selector'],
                'title_selector' => $config['title_selector'],
                'url_selector' => $config['url_selector'],
                'published_selector' => $config['published_selector'],
                'image_selector' => $config['image_selector'],
                'summary_source' => $config['summary_source'],
                'summary_selector' => $config['summary_selector'],
                'detail_enabled' => (int)$config['detail_enabled'],
                'detail_summary_selector' => $config['detail_summary_selector'],
                'content_selector' => $config['content_selector'],
                'exclude_selector' => $config['exclude_selector'],
                'author_selector' => $config['author_selector'],
            ),
            'matched_count' => count($nodes),
            'valid_count' => count($items),
            'items' => $items,
            'errors' => $errors,
            'elapsed_ms' => (int)round((microtime(true) - $startedAt) * 1000),
        );
    }

    public function fetchPageSource(array $config)
    {
        $config = array_merge($this->debugDefaults(), $config);
        $this->validateDebugConfig($config);

        $htmlText = $this->request($config['list_url']);
        if ($config['encoding'] === 'GBK') {
            $htmlText = @mb_convert_encoding($htmlText, 'UTF-8', 'GBK,GB2312');
        } elseif ($config['encoding'] === 'auto') {
            $htmlText = @mb_convert_encoding($htmlText, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
        }

        return array(
            'page_url' => $config['list_url'],
            'page_title' => $this->guessPageTitle($htmlText),
            'html' => mb_substr($htmlText, 0, 600000, 'UTF-8'),
            'html_length' => mb_strlen($htmlText, 'UTF-8'),
        );
    }

    public function analyzeByAi(array $config, $modelId, $mode = 'balanced')
    {
        $config = array_merge($this->debugDefaults(), $config);
        $this->validateDebugConfig($config);

        $htmlText = $this->request($config['list_url']);
        $html = $this->makeHtml($htmlText, $config['encoding']);
        if (empty($html)) {
            throw new CustomException('列表页HTML解析失败');
        }

        $pageTitle = $this->guessPageTitle($htmlText);
        $sampleBlocks = $this->collectSampleBlocks($html);
        $plainText = trim((string)$html->plaintext);
        $profile = $this->getAiModeProfile($mode);
        $promptContext = array(
            'page_url' => $config['list_url'],
            'page_title' => $pageTitle,
            'html_preview' => $this->truncateText($htmlText, 12000),
            'text_preview' => $this->truncateText($plainText, 5000),
            'sample_blocks' => $sampleBlocks,
            'analysis_mode' => $profile['name'],
            'current_hints' => array(
                'item_selector' => $config['item_selector'],
                'title_selector' => $config['title_selector'],
                'url_selector' => $config['url_selector'],
                'published_selector' => $config['published_selector'],
                'image_selector' => $config['image_selector'],
                'summary_source' => $config['summary_source'],
                'summary_selector' => $config['summary_selector'],
                'detail_enabled' => (int)$config['detail_enabled'],
                'detail_summary_selector' => $config['detail_summary_selector'],
                'content_selector' => $config['content_selector'],
                'exclude_selector' => $config['exclude_selector'],
                'author_selector' => $config['author_selector'],
            ),
        );

        $messages = array(
            array(
                'role' => 'system',
                'content' => '你是网页转RSS规则分析器。你的任务是根据页面HTML和样本片段，推断出适合抓取列表页和详情页的CSS选择器。'
                    . ' 你必须只输出 JSON，不要 markdown，不要解释，不要代码块。'
                    . ' 返回字段固定为 item_selector, title_selector, url_selector, published_selector, image_selector, summary_source, summary_selector, detail_enabled, detail_summary_selector, content_selector, exclude_selector, author_selector, confidence, reason, notes。'
                    . ' 其中 summary_source 只能是 list 或 detail；detail_enabled 只能是 0 或 1；confidence 是 0 到 100 的整数。'
                    . ' 如果你无法确定某个字段，请给出保守默认值，不要编造。'
                    . ' 你要优先给出可直接复制到抓取器里的规则，而不是长篇说明。'
                    . ' 当前分析模式：' . $profile['name'] . '。' . $profile['instruction']
            ),
            array(
                'role' => 'user',
                'content' => "请根据以下页面内容推断网页转 RSS 规则，并返回 JSON：\n" . json_encode($promptContext, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            ),
        );

        $llmResult = $this->llmStructuredTaskService->runTask(
            'webpage_rss_rule_analyze',
            $messages,
            array(
                'model_id' => $modelId,
                'response_format' => array('type' => 'json_object'),
                'timeout' => 180,
            )
        );

        $parsed = null;
        if (!empty($llmResult['content'])) {
            $parsed = $this->parseStructuredJson($llmResult['content']);
        }

        if (empty($parsed) || !is_array($parsed)) {
            throw new CustomException(!empty($llmResult['error']) ? $llmResult['error'] : 'AI 返回内容无法解析');
        }

        $suggestion = $this->normalizeAiSuggestion($parsed);
        $suggestion['page_title'] = $pageTitle;
        $suggestion['page_url'] = $config['list_url'];
        $suggestion['sample_blocks'] = $sampleBlocks;
        $suggestion['current_hints'] = $promptContext['current_hints'];

        return array(
            'analysis' => $suggestion,
            'llm' => array(
                'model_id' => $llmResult['meta']['model_id'] ?? $modelId,
                'model_name' => $llmResult['meta']['model_name'] ?? null,
                'provider_id' => $llmResult['meta']['provider_id'] ?? null,
                'request_time' => $llmResult['meta']['request_time'] ?? null,
                'error' => $llmResult['error'] ?? null,
                'mode' => $profile['name'],
            ),
        );
    }

    public function save($userId, array $config)
    {
        $this->validateConfig($config);

        return DB::transaction(function () use ($userId, $config) {
            $source = null;
            if (!empty($config['source_id'])) {
                $source = WebpageRssSource::where('user_id', $userId)
                    ->where('id', (int)$config['source_id'])
                    ->where('status', 1)
                    ->first();
                if (empty($source)) {
                    throw new CustomException('配置不存在');
                }
                unset($config['source_id']);
            } else {
                $source = WebpageRssSource::where('user_id', $userId)
                    ->where('list_url', $config['list_url'])
                    ->where('status', 1)
                    ->first();
            }

            if (empty($source)) {
                $source = new WebpageRssSource();
                $source->user_id = $userId;
                $source->rss_token = $this->makeToken();
                $source->status = 1;
            }

            $source->fill($config);
            $source->save();

            $feed = $source->feed_id ? Feed::where('id', $source->feed_id)->first() : null;
            if (empty($feed)) {
                $feed = new Feed();
                $feed->user_id = $userId;
                $feed->sub_count = 0;
                $feed->type = 4;
                $feed->status = 1;
            }
            $feed->feed_name = $source->name;
            $feed->url = $this->rssUrl($source);
            $feed->category_id = $source->category_id;
            $feed->type = 4;
            $feed->save();

            $source->feed_id = $feed->id;
            $source->save();

            $feedSub = FeedSub::where('user_id', $userId)
                ->where('feed_id', $feed->id)
                ->where('status', 1)
                ->first();
            if (empty($feedSub)) {
                $feedSub = new FeedSub();
                $feedSub->user_id = $userId;
                $feedSub->feed_id = $feed->id;
                $feedSub->status = 1;
                $feed->sub_count = (int)$feed->sub_count + 1;
                $feed->save();
            }
            $feedSub->feed_name = $source->name;
            $feedSub->category_id = $source->category_id;
            $feedSub->save();

            $debug = $this->refreshSource($source, 20);

            return array(
                'source' => $source->fresh(),
                'feed' => $feed->fresh(),
                'rss_url' => $this->rssUrl($source),
                'debug' => $debug,
            );
        });
    }

    public function refreshSource(WebpageRssSource $source, $limit = 20)
    {
        $config = $this->sourceToConfig($source);
        $result = $this->debug($config, $limit);
        $saved = 0;

        foreach ($result['items'] as $item) {
            if ($this->syncArticle($source, $item)) {
                $saved++;
            }
        }

        $source->last_debug_result = json_encode($result, JSON_UNESCAPED_UNICODE);
        $source->last_error = count($result['errors']) ? implode("\n", $result['errors']) : null;
        $source->last_checked_at = date('Y-m-d H:i:s');
        $source->save();

        $result['saved_count'] = $saved;
        return $result;
    }

    public function buildRssXml(WebpageRssSource $source)
    {
        try {
            $this->refreshSource($source, 20);
        } catch (\Throwable $e) {
            Log::warning('webpage rss refresh failed:' . $source->id . '|' . $e->getMessage());
        }

        $articles = Article::where('feed_id', $source->feed_id)
            ->orderBy('published', 'desc')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0"><channel>';
        $xml .= '<title>' . $this->xml($source->name) . '</title>';
        $xml .= '<link>' . $this->xml($source->list_url) . '</link>';
        $xml .= '<description>' . $this->xml('网页转RSS：' . $source->name) . '</description>';
        $xml .= '<lastBuildDate>' . date(DATE_RSS) . '</lastBuildDate>';

        foreach ($articles as $article) {
            $xml .= '<item>';
            $xml .= '<title>' . $this->xml($article->subject) . '</title>';
            $xml .= '<link>' . $this->xml($article->url) . '</link>';
            $xml .= '<guid isPermaLink="false">' . $this->xml($source->id . '-' . md5($article->url)) . '</guid>';
            $xml .= '<pubDate>' . date(DATE_RSS, strtotime($article->published ?: $article->created_at)) . '</pubDate>';
            $xml .= '<description><![CDATA[' . (string)$article->content . ']]></description>';
            $xml .= '</item>';
        }

        $xml .= '</channel></rss>';
        return $xml;
    }

    private function validateConfig(array $config)
    {
        foreach (array('name', 'list_url', 'category_id', 'item_selector', 'title_selector', 'url_selector') as $field) {
            if (empty($config[$field])) {
                throw new CustomException('请填写必填项：' . $field);
            }
        }
        if (!preg_match('#^https?://#i', $config['list_url'])) {
            throw new CustomException('列表页地址必须以 http:// 或 https:// 开头');
        }
    }

    private function validateDebugConfig(array $config)
    {
        if (empty($config['list_url'])) {
            throw new CustomException('调试时请先填写列表页地址');
        }
        if (!preg_match('#^https?://#i', $config['list_url'])) {
            throw new CustomException('列表页地址必须以 http:// 或 https:// 开头');
        }
    }

    private function parseStructuredJson($content)
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

    private function normalizeAiSuggestion(array $parsed)
    {
        return array(
            'item_selector' => trim((string)array_get($parsed, 'item_selector', '')),
            'title_selector' => trim((string)array_get($parsed, 'title_selector', '')),
            'url_selector' => trim((string)array_get($parsed, 'url_selector', '')),
            'published_selector' => trim((string)array_get($parsed, 'published_selector', '')),
            'image_selector' => trim((string)array_get($parsed, 'image_selector', '')),
            'summary_source' => array_get($parsed, 'summary_source', 'list') === 'detail' ? 'detail' : 'list',
            'summary_selector' => trim((string)array_get($parsed, 'summary_selector', '')),
            'detail_enabled' => (int)(array_get($parsed, 'detail_enabled', 1) ? 1 : 0),
            'detail_summary_selector' => trim((string)array_get($parsed, 'detail_summary_selector', '')),
            'content_selector' => trim((string)array_get($parsed, 'content_selector', '')),
            'exclude_selector' => trim((string)array_get($parsed, 'exclude_selector', '')),
            'author_selector' => trim((string)array_get($parsed, 'author_selector', '')),
            'confidence' => max(0, min(100, (int)array_get($parsed, 'confidence', 0))),
            'reason' => trim((string)array_get($parsed, 'reason', '')),
            'notes' => trim((string)array_get($parsed, 'notes', '')),
        );
    }

    private function collectSampleBlocks($html)
    {
        $selectors = array('article', '.post-item', '.card', '.item', '.news-item', '.entry', '.list-item', 'li');
        $blocks = array();

        foreach ($selectors as $selector) {
            $nodes = @$html->find($selector);
            if (empty($nodes)) {
                continue;
            }

            foreach ($nodes as $node) {
                $blocks[] = array(
                    'selector' => $selector,
                    'tag' => isset($node->tag) ? $node->tag : '',
                    'class' => isset($node->class) ? $node->class : '',
                    'text' => $this->truncateText(trim(preg_replace('/\s+/u', ' ', $node->plaintext)), 220),
                    'html' => $this->truncateText($node->outertext, 800),
                );

                if (count($blocks) >= 12) {
                    break 2;
                }
            }
        }

        return $blocks;
    }

    private function debugDefaults()
    {
        return array(
            'name' => '调试订阅',
            'category_id' => 1,
            'item_selector' => '.post-item, article, .news-list li',
            'title_selector' => 'h2 a, .title a',
            'url_selector' => 'a@href',
            'published_selector' => 'time@datetime, .date',
            'image_selector' => 'img@src',
            'summary_source' => 'list',
            'summary_selector' => '.summary, .excerpt, p',
            'detail_enabled' => 1,
            'detail_summary_selector' => '.article-content p',
            'content_selector' => '.article-content, main article, #content',
            'exclude_selector' => '.ad, .share, script, style, nav, footer',
            'author_selector' => '.author, [rel=author]',
            'max_content_length' => 12000,
            'failure_strategy' => 'fallback',
            'refresh_interval' => 60,
            'dedupe_key' => 'url',
            'encoding' => 'auto',
        );
    }

    private function getAiModeProfile($mode)
    {
        $mode = in_array($mode, array('list_summary', 'detail_content', 'balanced'), true) ? $mode : 'balanced';

        $profiles = array(
            'list_summary' => array(
                'name' => '列表摘要优先',
                'instruction' => '优先把列表页中的标题、链接、发布时间和摘要提取稳定；只有在列表页摘要明显不足时才建议启用详情页抓取。summary_source 优先为 list，detail_enabled 优先为 0 或 1，取决于页面是否明显需要详情页。输出尽量保守，不要贪多。',
            ),
            'detail_content' => array(
                'name' => '详情正文优先',
                'instruction' => '优先启用详情页抓取，列表页只负责抓标题和链接；摘要可以从详情页正文前几段截取。summary_source 优先为 detail，detail_enabled 优先为 1。适合列表页信息很少、正文在详情页完整出现的站点。',
            ),
            'balanced' => array(
                'name' => '稳妥平衡',
                'instruction' => '在稳定和完整之间平衡。先给出列表页可直接工作的规则，如果详情页显著提升摘要质量，再建议开启详情页抓取。默认应尽量保持易用性，避免过度复杂的规则。',
            ),
        );

        return $profiles[$mode];
    }

    private function extractListItem($node, array $config)
    {
        $titleTrace = $this->traceExtractValue($node, $config['title_selector']);
        $urlTrace = $this->traceExtractValue($node, $config['url_selector']);
        $publishedTrace = $this->traceExtractValue($node, $config['published_selector']);
        $imageTrace = $this->traceExtractValue($node, $config['image_selector']);
        $summaryTrace = $this->traceExtractValue($node, $config['summary_selector']);
        $url = $this->resolveUrl($urlTrace['value'], $config['list_url']);
        $summary = $summaryTrace['value'];

        return array(
            'subject' => $this->cleanText($titleTrace['value']),
            'url' => $url,
            'published' => $this->normalizeDate($publishedTrace['value']),
            'image_url' => $this->resolveUrl($imageTrace['value'], $config['list_url']),
            'summary' => $this->cleanText($summary),
            'content' => $this->cleanText($summary),
            'detail_status' => '未抓取',
            'author' => '',
            'debug' => array(
                'list_html' => $this->truncateText($node->outertext, 1500),
                'fields' => array(
                    'title' => $titleTrace,
                    'url' => $urlTrace,
                    'published' => $publishedTrace,
                    'image' => $imageTrace,
                    'summary' => $summaryTrace,
                ),
            ),
        );
    }

    private function extractDetail(array $item, array $config)
    {
        try {
            $detailHtml = $this->makeHtml($this->request($item['url']), $config['encoding']);
            if (empty($detailHtml)) {
                throw new CustomException('详情页HTML解析失败');
            }

            $summaryTrace = $this->traceExtractValue($detailHtml, $config['detail_summary_selector']);
            $contentTrace = $this->traceExtractValue($detailHtml, $config['content_selector']);
            $authorTrace = $this->traceExtractValue($detailHtml, $config['author_selector']);
            $summary = $summaryTrace['value'];
            if ($config['summary_source'] === 'detail' && !empty($summary)) {
                $item['summary'] = $this->cleanText($summary);
            }

            $contentNode = $contentTrace['node'];
            if ($contentNode) {
                $this->removeNodes($contentNode, $config['exclude_selector']);
                $content = trim($contentNode->innertext);
                if (!empty($config['max_content_length'])) {
                    $content = mb_substr($content, 0, $config['max_content_length'], 'UTF-8');
                }
                $item['content'] = $content;
            } elseif ($config['failure_strategy'] === 'skip') {
                throw new CustomException('详情页正文选择器未匹配');
            }

            $item['author'] = $this->cleanText($authorTrace['value']);
            $item['detail_status'] = '成功';
            $item['debug']['detail_html'] = $this->truncateText($detailHtml->outertext, 1500);
            $item['debug']['detail_fields'] = array(
                'summary' => $summaryTrace,
                'content' => $contentTrace,
                'author' => $authorTrace,
            );

            if (method_exists($detailHtml, 'clear')) {
                $detailHtml->clear();
            }
        } catch (\Throwable $e) {
            if ($config['failure_strategy'] === 'skip') {
                throw $e;
            }
            if ($config['failure_strategy'] === 'title_only') {
                $item['content'] = '';
                $item['summary'] = '';
            }
            $item['detail_status'] = '失败：' . $e->getMessage();
            $item['debug']['detail_error'] = $e->getMessage();
        }

        return $item;
    }

    private function syncArticle(WebpageRssSource $source, array $item)
    {
        $article = Article::where('feed_id', $source->feed_id)->where('url', $item['url'])->first();
        $created = false;
        if (empty($article)) {
            $article = new Article();
            $article->feed_id = $source->feed_id;
            $article->user_id = $source->user_id;
            $article->status = 'unread';
            $article->url = $item['url'];
            $created = true;
        }

        $article->subject = $item['subject'];
        $article->image_url = $item['image_url'];
        $article->content = !empty($item['content']) ? $item['content'] : $item['summary'];
        $article->published = $item['published'];
        $article->save();

        $articleSub = ArticleSub::where('user_id', $source->user_id)->where('article_id', $article->id)->first();
        if (empty($articleSub)) {
            $articleSub = new ArticleSub();
            $articleSub->feed_id = $source->feed_id;
            $articleSub->user_id = $source->user_id;
            $articleSub->article_id = $article->id;
            $articleSub->status = 'unread';
        }
        $articleSub->published = $article->published;
        $articleSub->save();

        return $created;
    }

    private function sourceToConfig(WebpageRssSource $source)
    {
        return $this->normalizeConfig($source->toArray());
    }

    private function request($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MontageWebpageRss/1.0)');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($result === false || $status >= 400) {
            throw new CustomException('页面读取失败：' . ($error ?: ('HTTP ' . $status)));
        }
        return $result;
    }

    private function makeHtml($htmlText, $encoding)
    {
        if ($encoding === 'GBK') {
            $htmlText = @mb_convert_encoding($htmlText, 'UTF-8', 'GBK,GB2312');
        } elseif ($encoding === 'auto') {
            $htmlText = @mb_convert_encoding($htmlText, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
        }

        $html = @str_get_html($htmlText);
        if (!empty($html)) {
            return $html;
        }

        return new WebpageRssDomDocument($htmlText);
    }

    private function extractValue($context, $selector)
    {
        $trace = $this->traceExtractValue($context, $selector);
        return $trace['value'];
    }

    private function traceExtractValue($context, $selector)
    {
        $selector = trim((string)$selector);
        if ($selector === '') {
            return array(
                'selector' => '',
                'matched_selector' => '',
                'attribute' => 'plaintext',
                'value' => '',
                'found' => false,
                'node' => null,
            );
        }

        foreach ($this->splitSelectors($selector) as $part) {
            $attr = 'plaintext';
            $matchedSelector = $part;
            $cssSelector = $part;
            if (strpos($part, '@') !== false) {
                $pieces = explode('@', $part);
                $attr = trim(array_pop($pieces));
                $cssSelector = trim(implode('@', $pieces));
            }

            $candidate = @$context->find($cssSelector, 0);
            if (!$candidate) {
                continue;
            }

            $value = '';
            if ($attr === 'html' || $attr === 'innertext') {
                $value = $candidate->innertext;
            } elseif ($attr === 'text' || $attr === 'plaintext') {
                $value = $candidate->plaintext;
            } else {
                $value = isset($candidate->$attr) ? $candidate->$attr : '';
            }

            return array(
                'selector' => $selector,
                'matched_selector' => $matchedSelector,
                'attribute' => $attr,
                'value' => $value,
                'found' => true,
                'node' => $candidate,
            );
        }

        return array(
            'selector' => $selector,
            'matched_selector' => '',
            'attribute' => 'plaintext',
            'value' => '',
            'found' => false,
            'node' => null,
        );
    }

    private function findFirst($context, $selector)
    {
        foreach ($this->splitSelectors($selector) as $part) {
            $node = @$context->find($part, 0);
            if ($node) {
                return $node;
            }
        }
        return null;
    }

    private function findAll($context, $selector)
    {
        $nodes = array();
        foreach ($this->splitSelectors($selector) as $part) {
            $found = @$context->find($part);
            if (!empty($found)) {
                foreach ($found as $node) {
                    $nodes[] = $node;
                }
            }
        }
        return $nodes;
    }

    private function removeNodes($context, $selector)
    {
        foreach ($this->splitSelectors($selector) as $part) {
            $nodes = @$context->find($part);
            foreach ($nodes as $node) {
                $node->outertext = '';
            }
        }
    }

    private function splitSelectors($selector)
    {
        $parts = array();
        foreach (explode(',', (string)$selector) as $part) {
            $part = trim($part);
            if ($part !== '') {
                $parts[] = $part;
            }
        }
        return $parts;
    }

    private function normalizeDate($value)
    {
        $time = strtotime(trim((string)$value));
        if (!$time) {
            $time = time();
        }
        return date('Y-m-d H:i:s', $time);
    }

    private function resolveUrl($url, $baseUrl)
    {
        $url = trim((string)$url);
        if ($url === '' || preg_match('#^https?://#i', $url)) {
            return $url;
        }
        if (strpos($url, '//') === 0) {
            return parse_url($baseUrl, PHP_URL_SCHEME) . ':' . $url;
        }

        $parts = parse_url($baseUrl);
        $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
        if (strpos($url, '/') === 0) {
            return $origin . $url;
        }
        $path = isset($parts['path']) ? preg_replace('#/[^/]*$#', '/', $parts['path']) : '/';
        return $origin . $path . $url;
    }

    private function cleanText($value)
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string)$value), ENT_QUOTES, 'UTF-8')));
    }

    private function makeToken()
    {
        return bin2hex(random_bytes(24));
    }

    public function rssUrl(WebpageRssSource $source)
    {
        return url('/feeds/webpage-rss/rss/' . $source->rss_token);
    }

    private function xml($value)
    {
        return htmlspecialchars((string)$value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function truncateText($value, $limit)
    {
        $value = (string)$value;
        if (strlen($value) <= $limit) {
            return $value;
        }
        return substr($value, 0, $limit) . '...';
    }

    private function guessPageTitle($htmlText)
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $htmlText, $matches)) {
            return $this->cleanText($matches[1]);
        }
        return '';
    }
}

class WebpageRssDomDocument
{
    private $dom;
    private $xpath;
    private $converter;

    public function __construct($htmlText)
    {
        libxml_use_internal_errors(true);
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $payload = '<?xml encoding="UTF-8">' . $this->sanitizeHtml($htmlText);
        $this->dom->loadHTML($payload, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT);
        $this->xpath = new DOMXPath($this->dom);
        $this->converter = new CssSelectorConverter();
    }

    public function find($selector, $idx = null)
    {
        $nodes = $this->query($selector, $this->dom);
        if ($idx === null) {
            return $nodes;
        }
        return isset($nodes[$idx]) ? $nodes[$idx] : null;
    }

    public function clear()
    {
        $this->dom = null;
        $this->xpath = null;
    }

    public function __get($name)
    {
        if ($name === 'plaintext') {
            return $this->dom ? $this->dom->textContent : '';
        }
        if ($name === 'innertext' || $name === 'outertext') {
            return $this->dom ? $this->dom->saveHTML() : '';
        }
        if ($name === 'tag') {
            return '#document';
        }
        return null;
    }

    private function query($selector, $contextNode)
    {
        $selector = trim((string)$selector);
        if ($selector === '' || !$this->xpath) {
            return array();
        }

        $xpath = $this->converter->toXPath($selector);
        $nodes = $this->xpath->query($xpath, $contextNode);
        if (!$nodes || !$nodes->length) {
            return array();
        }

        $items = array();
        foreach ($nodes as $node) {
            $items[] = new WebpageRssDomNode($node, $this->xpath, $this->converter);
        }
        return $items;
    }

    private function sanitizeHtml($htmlText)
    {
        $htmlText = preg_replace('#<script\b[^>]*>.*?</script>#is', '', (string)$htmlText);
        $htmlText = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $htmlText);
        $htmlText = preg_replace('#<!--.*?-->#s', '', $htmlText);
        return $htmlText;
    }
}

class WebpageRssDomNode
{
    private $node;
    private $xpath;
    private $converter;

    public function __construct(\DOMNode $node, DOMXPath $xpath, CssSelectorConverter $converter)
    {
        $this->node = $node;
        $this->xpath = $xpath;
        $this->converter = $converter;
    }

    public function find($selector, $idx = null)
    {
        $nodes = $this->query($selector);
        if ($idx === null) {
            return $nodes;
        }
        return isset($nodes[$idx]) ? $nodes[$idx] : null;
    }

    public function __get($name)
    {
        if ($name === 'plaintext') {
            return $this->node ? $this->node->textContent : '';
        }
        if ($name === 'innertext') {
            return $this->innerHtml();
        }
        if ($name === 'outertext') {
            return $this->outerHtml();
        }
        if ($name === 'tag') {
            return $this->node ? strtolower($this->node->nodeName) : '';
        }
        if ($name === 'class') {
            return $this->attr('class');
        }
        if ($this->node instanceof \DOMElement) {
            return $this->node->getAttribute($name);
        }
        return null;
    }

    public function __isset($name)
    {
        if (in_array($name, array('plaintext', 'innertext', 'outertext', 'tag', 'class'), true)) {
            return true;
        }
        return $this->node instanceof \DOMElement ? $this->node->hasAttribute($name) : false;
    }

    public function __set($name, $value)
    {
        if ($name !== 'outertext' || $value !== '') {
            return;
        }
        if ($this->node && $this->node->parentNode) {
            $this->node->parentNode->removeChild($this->node);
        }
    }

    private function query($selector)
    {
        $selector = trim((string)$selector);
        if ($selector === '') {
            return array();
        }

        $xpath = $this->converter->toXPath($selector);
        $nodes = $this->xpath->query($xpath, $this->node);
        if (!$nodes || !$nodes->length) {
            return array();
        }

        $items = array();
        foreach ($nodes as $node) {
            $items[] = new self($node, $this->xpath, $this->converter);
        }
        return $items;
    }

    private function attr($name)
    {
        if ($this->node instanceof \DOMElement) {
            return $this->node->getAttribute($name);
        }
        return '';
    }

    private function innerHtml()
    {
        if (!$this->node || !$this->node->hasChildNodes()) {
            return '';
        }

        $html = '';
        foreach ($this->node->childNodes as $child) {
            $html .= $this->node->ownerDocument->saveHTML($child);
        }
        return $html;
    }

    private function outerHtml()
    {
        if (!$this->node) {
            return '';
        }
        return $this->node->ownerDocument->saveHTML($this->node);
    }
}
