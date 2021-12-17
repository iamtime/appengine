<?php
	
	if(!function_exists('mysql_real_escape_string')){
		function mysql_real_escape_string($str){
			return db::clean($str);
		}
	}
	
	function now($dateformat = 'Y-m-d H:i:s'){
		return date($dateformat);
	}
	
	function email_user($to_email, $from_email, $from_name='', $subject='', $body='',$charset='UTF-8')
	{
		$mailer = new Mail();
		$mailer->setSender($from_name,$from_email);
		$mailer->setReceiver($to_email,$to_email);
		$mailer->addCC($from_name,$from_email);
		$mailer->subject = $subject;
		$mailer->setHtmlMessage($body);
		
		$result = $mailer->send($charset);
		return $result;
	}
	
	function email_user_smtp($to_email, $from_email, $from_name='', $subject='', $body='',$charset='UTF-8')
	{
		$settings = App::$settings;
		
		$mailer = new Mail();
		$mailer->setSender($from_name,$from_email);
		$mailer->setReceiver($to_email,$to_email);
		$mailer->addCC($from_name,$from_email);
		$mailer->subject = $subject;
		$mailer->setHtmlMessage($body);
		
		$result = $mailer->setSMTP( $settings['smtp_host'], $settings['smtp_port'], $settings['smtp_username'], $settings['smtp_password']);
		$result = $mailer->send($charset);
		return $result;
	}
	
	function mkdir_r( $dir_name, $rights=0777 ) {
	    $dirs = explode( "/", $dir_name );
	    $dir = "";
		foreach ( $dirs as $part ) {
			$dir .= $part . "/";
			if ( !is_dir( $dir ) && strlen( $dir ) > 0 ){
				mkdir( $dir, $rights );
			}
		}
	}
	
	function array_trim($array) {
		while (!empty($array) and strlen(reset($array)) === 0) {
			array_shift($array);
		}
		while (!empty($array) and strlen(end($array)) === 0) {
			array_pop($array);
		}
		return $array;
	}
	
	function clean($e,$withchars = true){
		
		if($withchars){
			return htmlspecialchars(db::clean($e));
		}
		return db::clean($e);
	}
	function searchInArrayByKey($search = '',$arr = array(),$key = 'id'){
		$success = false;
		if(sizeof($arr) > 0){
			foreach($arr as $k=>$v){
				if($v[$key] == $search){
					return $v;
				}
			}
		}
		return $success;
	}
	
	function searchInArrayByKeyRec($search = '',$arr = array(),$key = ''){
		$success = false;
		if(sizeof($arr) > 0){
			$i = 0;
			
			foreach($arr as $k=>$v){
				if(is_array($v)){
					$success = searchInArrayByKeyRec($search,$v,$key);
				}else{
					if($k == $key){
						if($v == $search){
							return true;
						}
					}
				}
				$i++;
			}
		}
		return $success;
	}
	
	function initLang(){
		global $conf;
		$langs = $conf['langs'];
    $firstLang = current($langs);
		$lang = (isset($_SESSION['lang']) ? $_SESSION['lang'] : $firstLang);
		$WORDS = array();
		if(!@include(APP_ROOT.'/_langs/'.$lang.'.php')){
			include(APP_ROOT.'/_langs/ru.php');
		}
		return $WORDS;
	}
?>