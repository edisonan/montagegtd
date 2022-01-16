<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ThirdService;

/**
 * 定时推送消息到饭否（小工具）
 *
 * 环境变量配置 FANFOU_ID FANFOU_MESSAGE
 *
 * @author edison.an
 *        
 */
class FanfouPublish extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'fanfou_publish';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Publish Fanfou Daily';
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$third = app ( ThirdService::class );
		$third->sceduleFanfouFave ();
	}
}
