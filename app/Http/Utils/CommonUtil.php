<?php
namespace App\Http\Utils;

class CommonUtil{
	public static function page_title($url) {
        $fp = @file_get_contents($url);
        if (!$fp) {
        	\Log::info("can not open".$url);
        	return '';
        }

        $res = preg_match("/<title>(.*)<\/title>/siU", $fp, $title_matches);
        if (!$res) {
        	\Log::info("can not preg".$url);
        	return '';
        }
            

        // Clean up title: remove EOL's and excessive whitespace.
        $title = preg_replace('/\s+/', ' ', $title_matches[1]);
        $title = trim($title);
        return $title;
    }
    
    public static function isUrl($s)
    {
    	return preg_match('/^http[s]?:\/\/'.
    			'(([0-9]{1,3}\.){3}[0-9]{1,3}'. // IP形式的URL- 199.194.52.184
    			'|'. // 允许IP和DOMAIN（域名）
    			'([0-9a-z_!~*\'()-]+\.)*'. // 三级域验证- www.
    			'([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.'. // 二级域验证
    			'[a-z]{2,6})'.  // 顶级域验证.com or .museum
    			'(:[0-9]{1,4})?'.  // 端口- :80
    			'((\/\?)|'.  // 如果含有文件对文件部分进行校验
    			'(\/[0-9a-zA-Z_!~\*\'\(\)\.;\?:@&=\+\$,%#-\/]*)?)$/',
    			$s) == 1;
    }
    
    public static function removeXSS($val) {  
	   // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed  
	   // this prevents some character re-spacing such as <java\0script>  
	   // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs  
	   $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);  
	
	   // straight replacements, the user should never need these since they're normal characters  
	   // this prevents like <IMG SRC=@avascript:alert('XSS')>  
	   $search = 'abcdefghijklmnopqrstuvwxyz'; 
	   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';  
	   $search .= '1234567890!@#$%^&*()'; 
	   $search .= '~`";:?+/={}[]-_|\'\\'; 
	   for ($i = 0; $i < strlen($search); $i++) { 
	      // ;? matches the ;, which is optional 
	      // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars 
	
	      // @ @ search for the hex values 
	      $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ; 
	      // @ @ 0{0,7} matches '0' zero to seven times  
	      $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ; 
	   } 
	
	   // now the only remaining whitespace attacks are \t, \n, and \r 
	   $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base'); 
	   $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'); 
	   $ra = array_merge($ra1, $ra2); 
	
	   $found = true; // keep replacing as long as the previous round replaced something 
	   while ($found == true) { 
	      $val_before = $val; 
	      for ($i = 0; $i < sizeof($ra); $i++) { 
	         $pattern = '/'; 
	         for ($j = 0; $j < strlen($ra[$i]); $j++) { 
	            if ($j > 0) { 
	               $pattern .= '(';  
	               $pattern .= '(&#[xX]0{0,8}([9ab]);)'; 
	               $pattern .= '|';  
	               $pattern .= '|(&#0{0,8}([9|10|13]);)'; 
	               $pattern .= ')*'; 
	            } 
	            $pattern .= $ra[$i][$j]; 
	         } 
	         $pattern .= '/i';  
	         $replacement = substr($ra[$i], 0, 2).'__'.substr($ra[$i], 2); // add in <> to nerf the tag  
	         $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags  
	         if ($val_before == $val) {  
	            // no replacements were made, so exit the loop  
	            $found = false;  
	         }  
	      }  
	   }  
	   return $val;  
	}  
	
	public function auto_link_text($text) {
	    $pattern  = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
	   $callback = create_function('$matches', '
	       $url       = array_shift($matches);
	 
	       $text = parse_url($url, PHP_URL_SCHEME) . "://" . parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);
	 
	       return sprintf(\'<a rel="nowfollow" href="%s">%s</a>\', $url, $text);
	   ');
	 
	   return preg_replace_callback($pattern, $callback, $text);
	}
	
	public static function formatTime($startTime, $endTime = '')
	{
		$format_time = '';
		if(!empty($startTime) && !empty($endTime)){
			$return_str = '';
			$startFormat = date('m月d日', strtotime($startTime));
			$endFormat = date('m月d日', strtotime($endTime));
			
			if($startFormat == $endFormat){
				return $startFormat.' '.date('h时i分', strtotime($startTime)).'至'.date('h时i分', strtotime($endTime));
			} else {
				return $startFormat.' '.date('h时i分', strtotime($startTime)).'至'.$endFormat.' '.date('h时i分', strtotime($endTime));
			}
		} else if(!empty($startTime)){
			return date('Y年m月d日 h时i分', strtotime($startTime));
		} else {
			return false;
		}
	}
	
	public static function hostUrl($url)
	{
		$parts = parse_url($url);
		if(empty($parts) || !isset($parts['scheme']) || !isset($parts['host'])){
			return false;
		} else {
			return $parts['scheme'].'://'.$parts['host'];
		}
	}
	
	public static function formatContentHtml($content)
	{
		return CommonUtil::HtmlClose(CommonUtil::removeXSS($content));
	}
	
	public static function HtmlClose($html) {
		preg_match_all('#<(?!meta|img|br|hr|inputb)b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
		 $openedtags = $result[1];
		 preg_match_all('#</([a-z]+)>#iU', $html, $result);
		 $closedtags = $result[1];
		 $len_opened = count($openedtags);
		 if (count($closedtags) == $len_opened) {
			return $html;
		 }
		 $openedtags = array_reverse($openedtags);
		 for ($i=0; $i < $len_opened; $i++) {
			if (!in_array($openedtags[$i], $closedtags)) {
			 $html .= '</'.$openedtags[$i].'>';
			}else {
			 unset($closedtags[array_search($openedtags[$i], $closedtags)]);
			}
		 }
		 return $html;
	}
}