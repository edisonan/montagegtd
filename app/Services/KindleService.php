<?php

namespace App\Services;

use App\Exceptions\CustomException;
use Develpr\Phindle\Phindle;
use Develpr\Phindle\Content;
use Illuminate\Support\Facades\Log;
use App\Repositories\SettingRepository;
use App\Repositories\ArticleSubRepository;
use App\Http\Utils\SpideUtil;
use App\Models\KindleLog;

/**
 * Kindle管理业务逻辑
 *
 * @author edison.an
 *        
 */
class KindleService {
	
	/**
	 * SettingService 实例.
	 *
	 * @var SettingService
	 */
	protected $settingService;
	
	/**
	 * ArticleSubRepository 实例.
	 *
	 * @var ArticleSubRepository
	 */
	protected $articleSubRepository;
	
	/**
	 * 创建Service
	 *
	 * @param SettingService $settingService        	
	 */
	public function __construct(SettingService $settingService, ArticleSubRepository $articleSubRepository) {
		$this->settingService = $settingService;
		$this->articleSubRepository = $articleSubRepository;
	}
	
	/**
	 * 测试kindle推送
	 * 
	 * @throws CustomException
	 */
	public function test() {
		$user = \Auth::user ();
		$setting = $this->settingService->getSettingInfo ( true );
		if (empty ( $setting->kindle_email )) {
			throw new CustomException ( "kindle email 地址为空" );
		}
		
		$kindleLog = $this->saveKindleLog ( $user->id, 1 );
		
		$kindleConfig = $this->buildKindleConfig ( '测试文件', $user->name, 'Test', '这是一个测试文件!' );
		$phindle = new Phindle ( $kindleConfig );
		
		$title = '测试文件';
		$html = '<meta http-equiv="Content-Type" content="text/html;charset=utf-8">';
		$html .= '<h3>' . $title . '</h3>';
		$html .= '当你收到此测试文件，说明你的配置正确，快来Montage GTD订阅你喜欢的文档吧!Montage GTD是个综合性的网站，在这里你还可以更高效的完成每一件事，快来体验吧。';
		
		$content = new Content ();
		$content->setHtml ( $html );
		$content->setTitle ( $title );
		$phindle->addContent ( $content );
		
		$phindle->process ();
		$path = $phindle->getMobiPath ();
		$this->processKindleLog ( $kindleLog, 2, $path );
		Log::info ( 'send to kindle test:' . $user->id . '|' . $path );
		
		Mail::send ( 'emails.kindle', [ 
				'user' => $user,
				'setting' => $setting,
				'path' => $path 
		], function ($m) use ($user, $setting, $path) {
			$m->to ( $setting->kindle_email, $user->name )->subject ( 'Send To Kindle' );
			$m->attach ( $path );
		} );
		$this->processKindleLog ( $kindleLog, 3, $path );
	}
	
	/**
	 * 定时任务kindle推送
	 */
	public function scheduleKindlePush() {
		// 获取开启推送到kindle的设置信息并遍历
		$settings = $this->settingService->getStartKindleAllList ();
		foreach ( $settings as $setting ) {
			$user = $setting->user;
			
			$kindleLog = $this->saveKindleLog ( $user->id, 2 );
			
			$phindle = new Phindle ( $this->buildKindleConfig ( '每日订阅推送-' . date ( 'Ymd' ), $user->name, 'Montage GTD每日订阅', 'Montage GTD每日订阅推送' . date ( 'Y-m-d' ) ) );
			
			$feedInfo = array ();
			
			$chapterCount = 0;
			$articleCount = 0;
			
			// 获取最近发布的未读文章
			$startTime = date ( 'Y-m-d H:i:s', strtotime ( date ( 'Y-m-d H:i:s' ) ) - 86400 );
			$endTime = date ( 'Y-m-d H:i:s' );
			$articleSubs = $this->articleSubRepository->getRecentPublishList ( $user, 'unread', $startTime, $endTime, 300 );
			foreach ( $articleSubs as $articleSub ) {
				$article = $articleSub->article;
				
				// 新章节（订阅源）
				if (! isset ( $feedInfo [$article->feed_id] )) {
					// article count set 0,chapter count +1,save chapter info
					$articleCount = 0;
					$chapterCount ++;
					$feedInfo [$article->feed_id] = $article->feed;
					
					$content = new Content ();
					$content->setHtml ( '<meta http-equiv="Content-Type" content="text/html;charset=utf-8"><h2>' . $article->feed->feed_name . '</h2>' . $article->feed->feed_desc );
					$content->setTitle ( $chapterCount . ' ' . $article->feed->feed_name );
					$content->setPosition ( $chapterCount * 1000 + $articleCount );
					$phindle->addContent ( $content );
				}
				
				// 每章节最多20篇
				if ($articleCount > 20) {
					continue;
				} else {
					$articleCount ++;
					
					// if need with image, do something img
					if ($setting->with_image_push == 1) {
						$spideUtil = new SpideUtil ();
						$articleContent = $spideUtil->processKindleImgContent ( $article->content, config ( "app.storage_path" ) . '/ebooks/temp' );
					} else {
						$articleContent = preg_replace ( "#<img.*>#iUs", "", $article->content ); // 无图
					}
					
					$content = new Content ();
					$content->setHtml ( '<meta http-equiv="Content-Type" content="text/html;charset=utf-8"><h3>' . $article->subject . '</h3>' . $articleContent . '<a href="' . $article->url . '">查看原文</a>' );
					$content->setTitle ( $chapterCount . '.' . $articleCount . ' ' . $article->subject );
					$content->setPosition ( $chapterCount * 1000 + $articleCount );
					$phindle->addContent ( $content );
				}
			}
			
			try {
				// 生成mobi文件
				$phindle->process ();
				$path = $phindle->getMobiPath ();
				$this->processKindleLog ( $kindleLog, 2, $path );
				
				// send to kindle address
				Log::info ( 'send to kindle:' . $user->id . '|' . count ( $articleSubs ) . '|' . $path );
				Mail::send ( 'emails.kindle', [ 
						'setting' => $setting,
						'path' => $path 
				], function ($m) use ($setting, $path) {
					$m->to ( $setting->kindle_email, 'user' )->subject ( 'Send To Kindle' );
					$m->attach ( $path );
				} );
				$this->processKindleLog ( $kindleLog, 3, $path );
			} catch ( Exception $e ) {
				Log::info ( 'ERROR:' . serialize ( $e ) . ':' . serialize ( $phindle ) );
			}
		}
	}
	
	/**
	 * 构建基础配置信息
	 * 
	 * @param string $title        	
	 * @param string $publisher        	
	 * @param string $creator        	
	 * @param string $subject        	
	 * @param string $description        	
	 * @param string $language        	
	 * @param string $isbn        	
	 */
	private function buildKindleConfig($title, $creator = '', $subject = '', $description = '', $downloadImages = true, $language = 'zh-CN', $isbn = '66666666', $publisher = 'Montage GTD') {
		$kindleConfig = array ();
		$kindleConfig ['title'] = 'MG - ' . $title;
		$kindleConfig ['publisher'] = $publisher;
		$kindleConfig ['creator'] = $creator;
		$kindleConfig ['subject'] = $subject; // @see https://www.bisg.org/complete-bisac-subject-headings-2013-edition
		$kindleConfig ['description'] = $description;
		$kindleConfig ['language'] = 'zh-CN';
		$kindleConfig ['isbn'] = $isbn;
		$kindleConfig ['path'] = config ( "app.storage_path" ) . '/ebooks'; // The path that temp files will be stored, as well as the location of the final ebook mobi file
		$kindleConfig ['staticResourcePath'] = config ( "app.storage_path" ) . '/ebooks/static'; // The absolute path to your static resources referenced in html (images, css, etc)
		$kindleConfig ['cover'] = 'cover.jpg'; // The relative path of your cover image
		$kindleConfig ['kindlegenPath'] = '/usr/local/bin/kindlegen'; // The path to the kindlegen utility
		$kindleConfig ['downloadImages'] = $downloadImages;
	}
	
	/**
	 * 保存kindle推送记录
	 * 
	 * @param int $userId        	
	 * @param unknown $type        	
	 * @return \App\Services\KindleLog
	 */
	private function saveKindleLog(int $userId, int $type) {
		$kindleLog = new KindleLog ();
		$kindleLog->user_id = $userId;
		$kindleLog->type = $type;
		$kindleLog->status = 1;
		$kindleLog->save ();
		return $kindleLog;
	}
	
	/**
	 * 记录kindle推送日志到处理中
	 * 
	 * @param KindleLog $kindleLog        	
	 * @param string $path        	
	 */
	private function processKindleLog(KindleLog $kindleLog, int $status, string $path) {
		$kindleLog->status = 2;
		$kindleLog->path = $path;
		$kindleLog->save ();
	}
}
