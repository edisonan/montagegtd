<?php

namespace App\Services;

use App\Models\User;
use App\Models\DailySummary;
use App\Models\DailySummary;

/**
 * 业务逻辑
 *
 * @author edison.an
 *        
 */
class DailySummaryService {

    public function getList() {
        return DailySummary::where ( 'user_id', Auth::id() )->where ( 'status', 1 ) ->orderBy ( 'id', 'desc' ) ->paginate ( 20 );
	}
}
