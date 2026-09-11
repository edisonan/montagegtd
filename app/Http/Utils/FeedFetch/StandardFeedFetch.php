<?php

namespace App\Http\Utils\FeedFetch;

use App\Http\Utils\CommonUtil;
use App\Models\Feed;
use ArandiLopez\Feed\Factories\FeedFactory;
use Illuminate\Support\Facades\Log;

class StandardFeedFetch implements FeedFetchBasic
{
    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
    }

    public function getInfos()
    {
        $feed = $this->feed;
        $infos = array();
        $feedFactory = new FeedFactory ([
            'cache.enabled' => false
        ]);
        $feeder = $feedFactory->make($feed->url);
        $simplePieInstance = $feeder->getRawFeederObject();

        if (empty ($simplePieInstance)) {
            Log::info("获取失败 empty simple pie instance");
            return $infos;
        }

        $previousweek = date('Y-m-j H:i:s', strtotime('-7 days'));

        $infos = array(
            'basic' => array(
                'feed_desc' => $simplePieInstance->get_description(),
                'favicon' => $simplePieInstance->get_image_url()
            ),
            'list' => array()
        );
        foreach ($simplePieInstance->get_items() as $item) {
            $url = $item->get_permalink();
            $subject = $item->get_title();
            $content = $item->get_description();
            try {
                $published = $item->get_date('Y-m-j H:i:s');
            } catch (\Throwable $e) {
                // 部分订阅源包含无法解析的日期（SimplePie 解析结果为 null 时 date() 会报错），
                // 这类条目直接跳过，避免污染日志。
                Log::info('feed item has invalid date, skipped', array(
                    'feed_id' => $feed->id,
                    'url' => $url,
                    'error' => $e->getMessage(),
                ));
                continue;
            }
            $imageUrl = CommonUtil::getImageFromHtmlText($content);

            if (empty($published) || strtotime($published) < strtotime($previousweek)) {
                continue;
            }

            $infos ['list'] [] = array(
                'url' => $url,
                'subject' => $subject,
                'content' => $content,
                'published' => $published,
                'image_url' => $imageUrl
            );
        }
        return $infos;
    }
}
