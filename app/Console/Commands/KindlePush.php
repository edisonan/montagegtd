<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Setting;
use App\KindleLog;

use Develpr\Phindle\Phindle;
use Develpr\Phindle\Content;

use App\ArticleSub;
use App\Http\Utils\SpideUtil;
use App\Repositories\SettingRepository;

use Log;
use Mail;

/**
 * push the rss content to kindle
 * @author edison.an
 *
 */
class KindlePush extends Command
{
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
    public function handle()
    {
    		$settings = Setting::where('is_start_kindle',1)->get();
    		foreach ($settings as $setting){
    			$user = $setting->user;
    			
    			$kindleLog = new KindleLog();
    			$kindleLog->user_id = $user->id;
    			$kindleLog->type = 2;
    			$kindleLog->status = 1;
    			$kindleLog->save();
    			
    			$phindle = new Phindle(array(
    					'title' => "Montage GTD每日订阅推送".date('Y-m-d'),
    					'publisher' => "Montage GTD ".$user->id,
    					'creator' => $user->name,
    					'language' => 'zh-CN',
    					'subject' => 'Montage GTD每日订阅', //@see https://www.bisg.org/complete-bisac-subject-headings-2013-edition
    					'description' => 'Montage GTD每日订阅推送'.date('Y-m-d'),
    					'path'	=> config("app.storage_path") . '/ebooks', //The path that temp files will be stored, as well as the location of the final ebook mobi file
    					'isbn'  => '666666666666666',
    					'staticResourcePath' => config("app.storage_path").'/ebooks/static', //The absolute path to your static resources referenced in html (images, css, etc)
    					'cover'	=> '/images/cover.png' , //The relative path of your cover image
    					'kindlegenPath' => '/usr/local/bin/kindlegen', //The path to the kindlegen utility
    					'downloadImages' => true, //Should images be downloaded from the web if found in your html?
    			));
    			
    			$now = date('Y-m-d H:i:s');
    			$start_time = date('Y-m-d H:i:s',strtotime($now)-86400);
    			
    			$articleSubs = ArticleSub::where('user_id',$user->id)->where('status','unread')->where('published','<',$now)->where('published','>',$start_time)->orderBy('feed_id')->limit(300)->get();
    			$feed_info = array();
    			
    			$chapter_count = 0;
    			$article_count = 0;
    			
    			foreach($articleSubs as $articleSub)
    			{
    				$article = $articleSub->article;
    				if(!isset($feed_info[$article->feed_id])){
    					//文章数清零 章节数加1
    					$article_count = 0;
    					$chapter_count++;
    					$feed_info[$article->feed_id] = $article->feed;
    					
    					$content = new Content();
    					$content->setHtml('<meta http-equiv="Content-Type" content="text/html;charset=utf-8"><h2>'.$article->feed->feed_name.'</h2>'.$article->feed->feed_desc);
    					$content->setTitle($chapter_count.' '.$article->feed->feed_name);
    					$content->setPosition($chapter_count*1000+$article_count);
    					$phindle->addContent($content);
    				}
    				//文章数递增 大于20篇时不再执行
    				if($article_count > 20) continue;
    				$article_count++;
    				
    				if($setting->with_image_push == 1){
    					$spideUtil = new SpideUtil();
    					$article_content = $spideUtil->processKindleImgContent($article->content, config("app.storage_path").'/ebooks/temp');
    				} else {
    					$article_content = preg_replace("#<img.*>#iUs", "", $article->content); //无图
    				}
    				/** @var Illuminate\View\View $html */
    				$content = new Content();
    				$content->setHtml('<meta http-equiv="Content-Type" content="text/html;charset=utf-8"><h3>'.$article->subject.'</h3>'.$article_content.'<a href="'.$article->url.'">查看原文</a>');
    				$content->setTitle($chapter_count.'.'.$article_count.' '.$article->subject);
    				$content->setPosition($chapter_count*1000+$article_count);
    				$phindle->addContent($content);
    			}
    			
    			$phindle->process();
    			
    			$path = $phindle->getMobiPath();
    			
    			$kindleLog->path = $path;
    			$kindleLog->status = 2;
    			$kindleLog->save();
    			
    			Log::info('send to kindle:'.$user->id.'|'.count($articleSubs).'|'.$path);
    			Mail::send('emails.kindle', ['setting'=>$setting,'path'=>$path], function ($m) use ($setting,$path) {
    				$m->to($setting->kindle_email, 'user')->subject('Send To Kindle');
    				$m->attach($path);
    			});
    			
    			$kindleLog->status = 3;
    			$kindleLog->save();
    		}
    }
}
