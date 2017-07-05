<?php
namespace App\Http\Utils;

use App\Article;
include 'simple_html_dom.php';

class SpideUtil{
	
	public function processFeed($feed) {
		echo 123;
		$result = $this->request($feed['url']);
		if(empty($result)){
			return false;
		}
		
		//get list
		$list = $this->getList($result,$feed->type);
		if(empty($list)){
			return false;
		}
		
		foreach ($list as $item){
			print_r($item);//delete
			$article = Article::where('feed_id',$feed->id)->where('user_id',$feed->user_id)->where('url',$item['url'])->first();
			if(empty($article)){
				$article = new Article();
				$article->feed_id = $feed->id;
				$article->user_id = $feed->user_id;
				$article->status = 1;
				$article->url = $item['url'];
				$article->subject = $item['subject'];
				$article->published = $item['published'];
				$article->save();
			}
			
			//get content
			if(empty($article->content)){
				$result = $this->request($item['url']);
				if(empty($result)){
					continue;
				}
				$params = $this->getContent($result,$feed->type);
				if(empty($params)){
					continue;
				}
				$article->content = $params['content'];
				$article->save();
			}
		}
	}
	
	public function getList($result,$type){
		$html = str_get_html($result);
		$list = array();
		
		if($type == 2){
			$articles = $html->find(".post-item");
			foreach ($articles as $article){
				$params = array();
				$url = $article->find("h2 a",-1)->href;
				$subject = $article->find("h2 a",-1)->plaintext;
				$time = $article->find(".comment-date",-1)->plaintext;
				if(empty($url)){
					continue;
				}
				$params['url'] = 'http://www.mafengwo.cn'.$url;
				$params['subject'] = $subject;
				$params['published'] = date('Y-m-d H:i:s',strtotime($time));
				
				$list[] = $params;
			}
		}
		return $list;
	}
	
	public function getContent($result,$type){
		$html = str_get_html($result);
		$params = array();
		
		if($type == 2){
			$article = $html->find(".view_con",0);
			if(empty($article)){
				return $params;
			}
			$params['content'] = $article->innertext;
		}
		return $params;
	}
	
	public function request($url){
		$try_count = 0;
		$result = '';
		while ($try_count < 3){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
			$result = curl_exec($ch);
			curl_close($ch);
			
			$try_count++;
		}
		return $result;
	}
}