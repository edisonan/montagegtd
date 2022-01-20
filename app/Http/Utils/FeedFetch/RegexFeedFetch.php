<?php

namespace App\Http\Utils\FeedFetch;

use ArandiLopez\Feed\Factories\FeedFactory;
use Illuminate\Support\Facades\Log;

class RegexFeedFetch implements FeedFetchBasic
{
    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
    }

    public function getInfos()
    {
        $infos = array();

        $previousweek = date('Y-m-j H:i:s', strtotime('-7 days'));

        $infos = array(
            'basic' => array(
                'feed_desc' => '',
                'favicon' => ''
            ),
            'list' => array()
        );
        // foreach ( xxxxx as $item ) {
        // $url = $item->get_permalink () ;
        // $subject = $item->get_title () ;
        // $content = $item->get_description ();
        // $published = $item->get_date ( 'Y-m-j H:i:s' );
        // $imageUrl = CommonUtil::getImageFromHtmlText ( $content );

        // if (strtotime ( $published ) < strtotime ( $previousweek )) {
        // continue;
        // }

        // $infos['list'][] = array(
        // 'url' => $url,
        // 'subject' => $subject,
        // 'content' => $content,
        // 'published' => $published,
        // 'image_url' => $imageUrl,
        // );
        // }
        return $infos;
    }
}