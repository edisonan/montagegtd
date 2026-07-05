<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\NotificationChannelService;
use Illuminate\Support\Facades\Log;

class FocusNotify implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	protected $user;
	protected $message;
	
	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($user, $message) {
		$this->user = $user;
		$this->message = $message;
	}
	
	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle() {
		$needFocus = \Cache::store ( 'file' )->pull ( 'NEED_POMO' . $this->user->id );
		
		if (! empty ( $needFocus )) {
			$notifyResult = app(NotificationChannelService::class)->sendToUser($this->user, '做专注', $this->message, config('app.url'));
			Log::info ( 'notify result:' . json_encode($notifyResult) . '|message:' . $this->message . '|user:' . $this->user->name );
		}
		return true;
	}
}
