<?php
require_once 'Http.php';
require_once 'simple_html_dom.php';

$url = "";

$result = request($url);

if(empty($result)){
	exit('empty response!');
}

processData($result);

function processData($result){
	$html = str_get_html($result);
	foreach($html->find('.col li') as $article) {
		$time = $article->find('.pushtime', 0)->time;
		 
		$params = array();
		$params['article_type'] = $type;
		$params['title'] = $article->find('h2', 0)->plaintext;
		$params['url'] = $article->find('a', 0)->href;
		$params['intro'] = $article->find('p', 0)->plaintext;
		$params['content'] = '';
		$params['article_hash'] = md5($params['title']);
		$params['release_date'] = date('Y-m-d',$time);
		$params['release_time'] = date('H:i:s',$time);
		
		saveArticle($params);
	}
}

function saveArticle($params){
	$dsn="mysql:dbname=test;host=localhost";
	$db_user='root';
	$db_pass='admin';
	
	try{
		$pdo=new PDO($dsn,$db_user,$db_pass);
	}catch(PDOException $e){
		echo '数据库连接失败'.$e->getMessage();
	}
	
	$table_name = "t_articles_spider";
	
	$pstmt = $pdo->prepare ( 'REPLACE INTO $table_name (article_type,title,url,intro,content,article_hash,release_date,release_time) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?)' );
	$pstmt->bindParam ( ':article_type', $params['article_type'] );
	$pstmt->bindParam ( ':title', $params['title'] );
	$pstmt->bindParam ( ':url', $params['url'] );
	$pstmt->bindParam ( ':intro', $params['intro'] );
	$pstmt->bindParam ( ':content', $params['content'] );
	$pstmt->bindParam ( ':article_hash', $params['article_hash'] );
	$pstmt->bindParam ( ':release_date', $params['release_date'] );
	$pstmt->bindParam ( ':release_time', $params['release_time'] );
	$pstmt->execute ();
	
}

function request($url){
	$http = new Http(1,60,60,60);
	//尝试抓取数据内容
	$try_count = 5;
	while($try_count--) {
		$result = $http->request($url);
		if(empty($result)){
			continue;
		} else {
			break;
		}
	}
}