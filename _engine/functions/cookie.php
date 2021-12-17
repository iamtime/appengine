<?php
	
	
	// Cookie
	function SetCL($name, $value='', $expire = 1, $path = '', $domain='', $secure=false, $httponly=false){
        $_COOKIE[$name] = $value;
        return setcookie($name, $value, time() + (3600 * $expire), $path, $domain, $secure, $httponly);
    }

    function DelCL($name,$h,$path, $domain, $secure=false, $httponly=false){
        unset($_COOKIE[$name]);
		$h = time() - (3600 * $h * 2);
        return setcookie($name, '', $h, $path, $domain, $secure, $httponly);
    }
	
	function GetCL($q = '',$null = false){
		return ((isset($_COOKIE[$q]) AND strlen($_COOKIE[$q]) > 0) ? addslashes($_COOKIE[$q]) : $null);
	}
	
	
	function ifCookieEnable(){
		setcookie('test_cookie', '1'); 
		if($_COOKIE['test_cookie'] == '1') { 
			$cookie_set = true; // cookie включены 
		} else { 
			$cookie_set = false; // cookie выключены 
		}
		return $cookie_set;
	}
	
	

?>