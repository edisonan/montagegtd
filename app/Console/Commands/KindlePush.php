<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\KindleService;

/**
 * 推送rss内容到kindle
 *
 * @author edison.an
 *        
 */
class KindlePush extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'kindle_push';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Push Kindle File Daily';
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		/**
		 *
		 * @var KindleService $kindleService
		 */
		$kindleService = app ( KindleService::class );
		$kindleService->scheduleKindlePush ();
	}
}
