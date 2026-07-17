<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\Course;
use App\Models\CourseGenerationRun;
use Illuminate\Support\Str;

class CourseContentService
{
    protected $courseService;
    protected $llmStructuredTaskService;

    public function __construct(CourseService $courseService, LlmStructuredTaskService $llmStructuredTaskService)
    {
        $this->courseService = $courseService;
        $this->llmStructuredTaskService = $llmStructuredTaskService;
    }

    public function generate($courseId, $userId, array $data)
    {
        $course = $this->ownedCourse($courseId, $userId);
        $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : array();
        if (empty($items)) {
            $topic = trim((string)($data['topic'] ?? $course->title));
            $prompt = '请为课程“' . $course->title . '”生成一组适合学习的课程章节。主题：' . $topic . '。返回严格JSON：{"items":[{"title":"","description":"","content":"","item_type":"chapter","order_index":0}],每个章节内容清晰、可学习，不要输出Markdown代码围栏。';
            $result = $this->llmStructuredTaskService->runTask('course_content_generation', array(
                array('role' => 'system', 'content' => '你是课程设计师，只输出合法JSON。'),
                array('role' => 'user', 'content' => $prompt),
            ), array('response_format' => array('type' => 'json_object'), 'timeout' => 120));
            if (empty($result['success'])) {
                throw new CustomException($result['error'] ?: '课程内容生成失败');
            }
            $payload = $this->decodeJsonContent($result['content']);
            $items = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : array();
        }
        if (empty($items)) {
            throw new CustomException('没有生成有效课程章节');
        }
        return $this->storeItems($course, $items, 'platform_ai');
    }

    public function fetch($courseId, $userId, array $data)
    {
        $course = $this->ownedCourse($courseId, $userId);
        $url = trim((string)($data['source_url'] ?? $data['url'] ?? ''));
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new CustomException('source_url 格式错误');
        }
        $context = stream_context_create(array('http' => array('timeout' => 20, 'user_agent' => 'MontageCourseFetcher/1.0')));
        $raw = @file_get_contents($url, false, $context);
        if ($raw === false || trim($raw) === '') {
            throw new CustomException('抓取来源失败');
        }
        $content = trim(preg_replace('/\s+/u', ' ', strip_tags($raw)));
        $content = Str::limit($content, 30000, '');
        if ($content === '') {
            throw new CustomException('来源没有可用正文');
        }
        return $this->storeItems($course, array(array(
            'title' => isset($data['title']) ? $data['title'] : $course->title . ' - ' . parse_url($url, PHP_URL_HOST),
            'description' => isset($data['description']) ? $data['description'] : '抓取来源：' . $url,
            'content' => $content,
            'item_type' => 'reading',
            'source_url' => $url,
            'source_key' => $url,
        )), 'platform_fetch');
    }

    public function runScheduled($course)
    {
        $config = is_array($course->automation_config) ? $course->automation_config : array();
        $run = CourseGenerationRun::create(array(
            'course_id' => $course->id,
            'mode' => $config['mode'] ?? 'ai',
            'status' => 'running',
            'source_url' => $config['source_url'] ?? null,
            'started_at' => now(),
        ));
        try {
            $items = ($config['mode'] ?? 'ai') === 'fetch'
                ? $this->fetch($course->id, $course->created_by, $config)
                : $this->generate($course->id, $course->created_by, $config);
            $run->status = 'success';
            $run->items_count = count($items);
            $run->finished_at = now();
            $run->save();
            return $items;
        } catch (\Throwable $e) {
            $run->status = 'failed';
            $run->error = Str::limit($e->getMessage(), 1000, '');
            $run->finished_at = now();
            $run->save();
            throw $e;
        }
    }

    protected function storeItems(Course $course, array $items, $sourceType)
    {
        $stored = array();
        foreach (array_values($items) as $index => $item) {
            if (empty($item['title'])) continue;
            $sourceKey = isset($item['source_key']) ? $item['source_key'] : ($sourceType . ':' . $course->id . ':' . md5($item['title'] . '|' . ($item['content'] ?? '')));
            $content = (string)($item['content'] ?? $item['description'] ?? '');
            $stored[] = $this->courseService->createCourseItem(array(
                'course_id' => $course->id,
                'parent_id' => isset($item['parent_id']) ? $item['parent_id'] : null,
                'title' => $item['title'],
                'item_type' => isset($item['item_type']) ? $item['item_type'] : 'chapter',
                'duration' => isset($item['duration']) ? $item['duration'] : null,
                'external_url' => isset($item['source_url']) ? $item['source_url'] : ($item['external_url'] ?? null),
                'description' => $item['description'] ?? null,
                'content' => $content,
                'order_index' => $item['order_index'] ?? $index,
                'source_type' => $sourceType,
                'source_key' => $sourceKey,
                'content_hash' => hash('sha256', $content),
                'generated_at' => now(),
                'content_status' => 'draft',
            ));
        }
        return $stored;
    }

    protected function ownedCourse($courseId, $userId)
    {
        $course = Course::where('id', $courseId)->where('created_by', $userId)->first();
        if (!$course) throw new CustomException('课程不存在或无权限');
        return $course;
    }

    protected function decodeJsonContent($content)
    {
        $content = trim((string)$content);
        $content = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $content);
        $data = json_decode($content, true);
        return is_array($data) ? $data : array();
    }
}
