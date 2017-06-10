<?php
namespace App\Http\Utils;

class CommonUtil{
	public static function page_title($url) {
        $fp = file_get_contents($url);
        if (!$fp) {
        	\Log::info("can not open".$url);
        	return null;
        }

        $res = preg_match("/<title>(.*)<\/title>/siU", $fp, $title_matches);
        if (!$res) {
        	\Log::info("can not preg".$url);
        	return null;
        }
            

        // Clean up title: remove EOL's and excessive whitespace.
        $title = preg_replace('/\s+/', ' ', $title_matches[1]);
        $title = trim($title);
        return $title;
    }
}