<?php

namespace App\Http\Utils\FeedFetch;

use App\Http\Utils\CommonUtil;
use App\Http\Utils\OAuth1\FFClient;
use App\Models\Feed;
use App\Models\User;
use App\Repositories\ThirdRepository;
use ArandiLopez\Feed\Factories\FeedFactory;
use Illuminate\Support\Facades\Log;

class FanfouFeedFetch implements FeedFetchBasic
{
    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
    }

    public function getInfos()
    {
        $infos = array();

        $config = config('services.fanfou');

        $user = new User ();
        $user->id = $this->feed->user_id;
        $thirdRepository = app(ThirdRepository::class);
        $third = $thirdRepository->forUserSource($user);
        if (empty ($third)) {
            return;
        }

        $oauthToken = $third ['token_value'];
        $oauthTokenSecret = $third ['token_secret'];

        $ffUser = new FFClient (config("services.fanfou.client_id"), config("services.fanfou.client_secret"), $oauthToken, $oauthTokenSecret);

        $items = $ffUser->friends_timeline(1, 50);
        $items = json_decode($items, true);

        if (empty ($items) || isset ($items ['error'])) {
            return false;
        }

        $previousweek = date('Y-m-j H:i:s', strtotime('-7 days'));

        $infos = array(
            'basic' => array(
                'feed_desc' => '',
                'favicon' => ''
            ),
            'list' => array()
        );

        foreach ($items as $item) {
            if (isset ($item ['repost_status'])) {
                $item = $item ['repost_status'];
            }

            $published = date('Y-m-d H:i:s', strtotime($item ['created_at']));
            if (strtotime($published) < strtotime($previousweek)) {
                continue;
            }
            $content = $item ['text'] . "&nbsp;&nbsp; ";

            if (isset ($item ['user'])) {
                $content = "<a href='http://fanfou.com/{$item['user']['unique_id']}'>@{$item['user']['name']}</a> &nbsp; $content";
            }

            if (isset ($item ['photo'])) {
                $content = "$content<br><img width='' src='{$item['photo']['largeurl']}'/><a href='{$item['photo']['largeurl']}' target='_blank'>大图</a>";
            }

            $url = 'http://fanfou.com/statuses/' . $item ['id'];
            $subject = '';
            $imageUrl = CommonUtil::getImageFromHtmlText($content);

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
