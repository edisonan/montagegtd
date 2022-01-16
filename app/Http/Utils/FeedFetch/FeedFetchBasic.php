<?php

namespace App\Http\Utils\FeedFetch;

use Illuminate\Support\Facades\Log;
use App\Models\Feed;

interface FeedFetchBasic {
	function getInfos();
}
