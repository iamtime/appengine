<?php
	
	//Go to page function
	//Usage: gotoPage(url)
	//Params: url (string)
	//Returns: Header or JavaScript
	function gotoPage($page = ''){
		if(!headers_sent()){
			header('Location: '.$page.'');
		}else{
			$out = '<script language="JavaScript">
					window.location = \''.$page.'\';
				</script>';
			echo $out;
		}
	}
	
	//ifGet function
	//Usage: ifGet(q)
	//Params: q (string)	
	//Returns: true|false
	function ifGet($q){
		if(isset($_GET[$q])){
			if(is_array($_GET[$q])){
				if(sizeof($_GET[$q]) > 0){
					return true;
				}
			}else{
				if(strlen($_GET[$q]) > 0){
					return true;
				}
			}
			
		}
		return false;
	}
	
	//ifPost function
	//Usage: ifPost(q)
	//Params: q (string)	
	//Returns: true|false
	function ifPost($q){
		if(isset($_POST[$q])){
			if(is_array($_POST[$q])){
				if(sizeof($_POST[$q]) > 0){
					return true;
				}
			}else{
				if(strlen($_POST[$q]) > 0){
					return true;
				}
				
			}
		}
		
		return false;
	}
  
  //Post function
	//Usage: Get(q,$null = '')
	//Params: q (string) , $null = returns defalut null value
	//Returns: $_GET[q] | $null
	function Post($q,$null=''){
		if(isset($_POST[$q])){
			
			return ((is_array($_POST[$q]) AND sizeof($_POST[$q]) > 0) ? db::clean($_POST[$q]) : ((strlen($_POST[$q]) > 0) ? db::clean($_POST[$q]) : $null));
			/* 
			if(is_array($_POST[$q])){
				if(sizeof($_POST[$q]) > 0){
					return db::clean($_POST[$q]);
				}else{
					return $null;
				}
			}else{
				if(strlen($_POST[$q]) > 0){
					return db::clean($_POST[$q]);
				}else{
					return $null;
				}
			} */
		}else{
			return $null;
		}
	}
	
	//Get function
	//Usage: Get(q,$null = '')
	//Params: q (string) , $null = returns defalut null value
	//Returns: $_GET[q] | $null
	function Get($q,$null=''){
		return (ifGet($q) ? db::clean($_GET[$q]) : $null);
	}
	
	function getId($id = 'id',$null = 0){
		return ((isset($_GET[$id]) AND is_numeric($_GET[$id]) AND $_GET[$id] > 0) ? $_GET[$id] : $null);
	}
	
	function getIds($id = 'id',$null = 0,$return_array = false){
		if(isset($_GET[$id])){
			$tmp = [];
			if(!is_array($_GET[$id])){
				$tmp = explode(',',$_GET[$id]);
			}
			
			$get = '';
			$arr = [];
			
			foreach($tmp as $k=>$v){
				if(is_numeric($v)){
					$arr[] = $v;
				}
			}
			
			if($return_array){
				return $arr;
			}else{
				return implode(',',$arr);
			}
		}
		return $null;
	}
	
	function PostId($id = 'id',$null = 0){
		return ((isset($_POST[$id]) AND is_numeric($_POST[$id]) AND $_POST[$id] > 0) ? $_POST[$id] : $null);
	}
	
	function Page($q = 'page'){
		return (((isset($_GET[$id]) AND isInt($q)) AND $q > 1) ? Get($q) : 1);
	}
	
	function isInt($var){
		return ((isset($var) AND is_numeric($var) AND $var > 0) ? $var : false);
	}
	
	function isNull($q){
		$check = false;
		
		if(is_array($q)){
			if(sizeof($q) > 0){
				$check = true;
			}
		}else{
			if(is_object($q)){
				if($q !== false){
					$check = true;
				}
			}else{
				if(strlen($q) > 0 AND $q !== false){
					$check = true;
				}
			}
		}
		
		return $check;
	}
	
	function seoUrl($text){
		$text = translitIt($text);
		$text = preg_replace('/\W+/', '-', $text);
		$text = strtolower(trim($text, '-'));
		return $text;
	}
	
	function getClientBrowser($returnArray = true){
		$u_agent = $_SERVER['HTTP_USER_AGENT'];
		$bname = 'Unknown';
		$platform = 'Unknown';
		$version= "";

		if (preg_match('/linux/i', $u_agent)) {
			$platform = 'Linux';
		}
		elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'Mac OS';
		}
		elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'Windows';
		}
	   
		if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
		{
			$bname = 'Internet Explorer';
			$ub = "MSIE";
		}
		elseif(preg_match('/Firefox/i',$u_agent))
		{
			$bname = 'Mozilla Firefox';
			$ub = "Firefox";
		}
		elseif(preg_match('/Chrome/i',$u_agent))
		{
			$bname = 'Google Chrome';
			$ub = "Chrome";
		}
		elseif(preg_match('/Safari/i',$u_agent))
		{
			$bname = 'Apple Safari';
			$ub = "Safari";
		}
		elseif(preg_match('/Opera/i',$u_agent))
		{
			$bname = 'Opera';
			$ub = "Opera";
		}
		elseif(preg_match('/Netscape/i',$u_agent))
		{
			$bname = 'Netscape';
			$ub = "Netscape";
		}
	   
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) .
		')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}
	   
		$i = count($matches['browser']);
		if ($i != 1) {
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
				$version= $matches['version'][0];
			}
			else {
				$version= $matches['version'][1];
			}
		}
		else {
			$version= $matches['version'][0];
		}
	   
		if ($version==null || $version=="") {$version="?";}
	   
		if($returnArray){
			return array(
				'userAgent' => $u_agent,
				'name'      => $bname,
				'version'   => $version,
				'platform'  => $platform
			);   
		}else{
			return $u_agent.' '.$bname.' '.$version.' '.$platform;
		}
		
	}
	
	function getClientIp(){
	  if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"),"unknown")){
      $ip = getenv("HTTP_CLIENT_IP");
    } elseif (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")){
      $ip = getenv("HTTP_X_FORWARDED_FOR");
    } elseif (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
      $ip = getenv("REMOTE_ADDR");
    } elseif (!empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")){
      $ip = $_SERVER['REMOTE_ADDR'];
    } else {
      $ip = "unknown";
    }
	  return $ip;
	}
?>