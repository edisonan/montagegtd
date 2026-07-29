<?php

namespace App\Http\Utils\FeedFetch;

use App\Exceptions\CustomException;
use App\Models\Feed;
use Illuminate\Support\Facades\Log;

class FeedFetchFactory
{
    public function getFeedFetch(Feed $feed)
    {
        switch ($feed->type) {
            case 1 :
                return new StandardFeedFetch ($feed);
            case 2 :
                return new MafengwoFeedFetch ($feed);
            case 3 :
                return new FanfouFeedFetch ($feed);
            case 4 :
                // Webpage RSS sources expose a regular RSS endpoint after applying
                // their selector rules, so they should use the standard parser.
                return new StandardFeedFetch ($feed);
            default :
                throw new CustomException ("未找到此类型");
        }
    }
}
