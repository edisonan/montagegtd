<?php

namespace App\Http\Utils\FeedFetch;

use Illuminate\Support\Facades\Log;
use App\Models\Feed;
use App\Exceptions\CustomException;

class FeedFetchFactory {
	public function getFeedFetch(Feed $feed) {
		switch ($feed->type) {
			case 1 :
				return new StandardFeedFetch ( $feed );
			case 2 :
				return new MafengwoFeedFetch ( $feed );
			case 3 :
				return new FanfouFeedFetch ( $feed );
			case 4 :
				return new RegexFeedFetch ( $feed );
			default :
				throw new CustomException ( "未找到此类型" );
		}
	}
}