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
		if(empty($parts)){
			return false;
		} else {
			return $parts['scheme'].'://'.$parts['host'];
		}
	}
	
	public static function formatContentHtml($content)
	{
		return App\Http\Utils\CommonUtil::HtmlClose(App\Http\Utils\CommonUtil::removeXSS($content));
	}
	
	function HtmlClose($body) {
		$strlen_var = strlen($body);
		// 不包含 html 标签
		if (strpos($body, '<') === false) {
			return $body;
		}
		// html 代码标记
		$html_tag = 0;
		// 摘要字符串
		$summary_string = '';
	  
		/**
		 * 数组用作记录摘要范围内出现的 html 标签
		 * 开始和结束分别保存在 left 和 right 键名下
		 * 如字符串为：<h3><p><b>a</b></h3>，假设 p 未闭合
		 * 数组则为：array('left' => array('h3', 'p', 'b'), 'right' => 'b', 'h3');
		 * 仅补全 html 标签，<? <% 等其它语言标记，会产生不可预知结果
		 */
		$html_array = array('left' => array(), 'right' => array());
		for ($i = 0; $i < $strlen_var; ++$i) {
			$current_var = substr($body, $i, 1);
	  
			if ($current_var == '<') {
				// html 代码开始
				$html_tag = 1;
				$html_array_str = '';
			} else if ($html_tag == 1) {
				// 一段 html 代码结束
				if ($current_var == '>') {
					/**
					 * 去除首尾空格，如 <br /  > < img src="" / > 等可能出现首尾空格
					 */
					$html_array_str = trim($html_array_str);
	  
					/**
					 * 判断最后一个字符是否为 /，若是，则标签已闭合，不记录
					 */
					if (substr($html_array_str, -1) != '/') {
	  
						// 判断第一个字符是否 /，若是，则放在 right 单元
						$f = substr($html_array_str, 0, 1);
						if ($f == '/') {
							// 去掉 /
							$html_array['right'][] = str_replace('/', '', $html_array_str);
						} else if ($f != '?') {
							// 判断是否为 ?，若是，则为 PHP 代码，跳过
	  
							/**
							 * 判断是否有半角空格，若有，以空格分割，第一个单元为 html 标签
							 * 如 <h2 class="a"> <p class="a">
							 */
							if (strpos($html_array_str, ' ') !== false) {
								// 分割成2个单元，可能有多个空格，如：<h2 class="" id="">
								$html_array['left'][] = strtolower(current(explode(' ', $html_array_str, 2)));
							} else {
								/**
								 * * 若没有空格，整个字符串为 html 标签，如：<b> <p> 等
								 * 统一转换为小写
								 */
								$html_array['left'][] = strtolower($html_array_str);
							}
						}
					}
	  
					// 字符串重置
					$html_array_str = '';
					$html_tag = 0;
				} else {
					/**
					 * 将< >之间的字符组成一个字符串
					 * 用于提取 html 标签
					 */
					$html_array_str .= $current_var;
				}
			} else {
				// 非 html 代码才记数
				--$size;
			}
	  
			$ord_var_c = ord($body{$i});
	  
			switch (true) {
				case (($ord_var_c & 0xE0) == 0xC0):
					// 2 字节
					$summary_string .= substr($body, $i, 2);
					$i += 1;
					break;
				case (($ord_var_c & 0xF0) == 0xE0):
	  
					// 3 字节
					$summary_string .= substr($body, $i, 3);
					$i += 2;
					break;
				case (($ord_var_c & 0xF8) == 0xF0):
					// 4 字节
					$summary_string .= substr($body, $i, 4);
					$i += 3;
					break;
				case (($ord_var_c & 0xFC) == 0xF8):
					// 5 字节
					$summary_string .= substr($body, $i, 5);
					$i += 4;
					break;
				case (($ord_var_c & 0xFE) == 0xFC):
					// 6 字节
					$summary_string .= substr($body, $i, 6);
					$i += 5;
					break;
				default:
					// 1 字节
					$summary_string .= $current_var;
			}
		}
	  
		if ($html_array['left']) {
			/**
			 * 比对左右 html 标签，不足则补全
			 */
			/**
			 * 交换 left 顺序，补充的顺序应与 html 出现的顺序相反
			 * 如待补全的字符串为：<h2>abc<b>abc<p>abc
			 * 补充顺序应为：</p></b></h2>
			 */
			$html_array['left'] = array_reverse($html_array['left']);
	  
			foreach ($html_array['left'] as $index => $tag) {
				// 判断该标签是否出现在 right 中
				$key = array_search($tag, $html_array['right']);
				if ($key !== false) {
					// 出现，从 right 中删除该单元
					unset($html_array['right'][$key]);
				} else {
					// 没有出现，需要补全
					$summary_string .= '</' . $tag . '>';
				}
			}
		}
		return $summary_string;
	}
}