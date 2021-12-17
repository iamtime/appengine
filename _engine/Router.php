<?php
class Router extends Singleton{
	
	public static $section;
	public static $action;
	public static $operation;
	
	public static $app;
		
	public static $continue_segment = ['ajax','admin'];
	
	public static $stdVars = [
						'section'=>0,
						'action'=>1,
						'operation'=>2,
						'dop'=>3,
					]; //$_GET[$var]=>$segment_num
	
	public static function Start(){
		$request_uri = false;
		
		$http = isset($_SERVER["https"]) ? 'https' : 'http';
		$domain = $_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT'] != '80' ? ':'.$_SERVER['SERVER_PORT'] : '');
		$original_url = $http.'://'.$domain.$_SERVER['REQUEST_URI'];
		
		preg_match('/([a-zA-Z0-9]+)/',APP_ROOT,$m);
		if(sizeof($m) > 1){
			self::$app = $m[1];
		}else{
			$ar = APP_ROOT;
			$ar = str_replace(ROOT,'',$ar);
			preg_match_all('%([a-zA-Z0-9]+)%',$ar,$m);
			if(sizeof($m) > 1){
				self::$app = $m[1];
			}else{
				self::$app = 'default';
			}
		}
		
		
		preg_match('%('.$http.'://'.$domain.')%',$original_url,$m);
		
		if(sizeof($m) > 0){
			$exp = explode($m[0],$original_url);
			
			if(sizeof($exp) > 1){
				$request_uri = $exp[1];
			}else{
				$request_uri = preg_replace('/'.$m[0].'/','',$original_url);
			}
		}
		
		if($request_uri){
			self::PrepareGet($request_uri);
		}
		
	}
	
	public static function reload(){
		self::Start();
	}
	
	
	public static function ParseParams($url){
		preg_match('%([^\?/&=#]+)=([^&#]*)%',$url,$q);
		if(sizeof($q) > 2){
			$_GET[$q[1]] = $q[2];
			$url = str_replace($q[0],'',$url);
			return self::ParseParams($url);
		}else{
			return $url;
		}
	}
	
	public static function PrepareGet($url){
		
		if(preg_match('%(section=)%',$url)){
			echo 'Ok.you found it.';
			exit;
		}
		
		$url = self::ParseParams($url);
		
		$tmp = preg_split('/[\s!?]/u', $url, -1, PREG_SPLIT_NO_EMPTY);

		$explode = explode('/',$url);
		
		$data = array();
		if(sizeof($explode) > 0){
			foreach($explode as $d){
				if(trim($d)){
					$data[] = $d;
				}
			}
		}
		// $_GET = [];
		if(sizeof($data) > 0){
			$i = 0;
			if($data[0] == 'ajax'){
				$_GET['is_ajax'] = true;
			}
			
			foreach($data as $segment){
				/* Лучше не трогать эту порнографию */
				if(preg_match('%([=&])%',$segment)){
					$exp = explode('&',$segment);
					
					foreach($exp as $g){
						$x = explode('=',$g);
						if(sizeof($x) > 1){
							if(preg_match('%\[%',$x[0])){
								$c = explode('[',$x[0]);
								if(isset($c[1])){
									$_GET[$c[0]][str_replace(']','',$c[1])] = $x[1];
								}else{
									$_GET[$c[0]][] = $x[1];
								}
							}else{
								$_GET[$x[0]] = $x[1];
							}
						}else{
							$_GET[$x[0]] = '';
						}
					}
				}else{
					if(in_array($segment,self::$continue_segment)){
						continue;
					}
					
					$stdVars = self::$stdVars;
					
					foreach($stdVars as $var=>$key){
						if($i == $key AND !isset($_GET[$var])){
							$_GET[$var] = $segment;
						}
					}
					
					$i++;
				}
				
			}
			// var_export($_GET);
		}
	}
	
}
?>