<?php

namespace App\Services;

use App\Models\Feedback;

/**
 * 帮助管理业务逻辑
 *
 * @author edison.an
 *        
 */
class HelpService {
	
	/**
	 *
	 * @param string $from        	
	 * @param string $content        	
	 */
	public function storeFeedback($from, $content) {
		$feedback = new Feedback ();
		$feedback->user_id = \Auth::check () ? \Auth::id () : null;
		$feedback->from = $from;
		$feedback->content = $content;
		$feedback->save ();
	}
}
