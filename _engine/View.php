<?php

class View extends Singleton{
	
	protected $engine;
	protected static $s_engine;
	
	public static function Start($opts = []){
		return self::i($opts);
	}
	
	public function __construct($opts = [])
	{
		$options = [
			'template_dir' 		=> 	APP_ROOT.'/views/',
			'compile_dir' 		=> 	ROOT.'/_tmp/smarty/'.App::$name.'/',
			'cache_dir'    		=> 	ROOT.'/_tmp/smarty/'.App::$name.'/cache/',
			'config_dir'   		=> 	APP_ROOT.'/_langs/',
			'force_compile'		=>	FORCE_COMPILE,
			'error_reporting'	=>	0,
		];
		
		$this->engine = new Smarty();
		
		View::setEngine($this->engine);
		
		if($opts AND is_array($opts)){
			$options = array_merge($options,$opts);
		}
		
		foreach($options as $name => $value){
			$this->engine->$name = $value;
		}
		
		self::setDefaults();
	}
	
	
	public static function setDefaults(){

		$vars['URL'] = URL;
		
		if(defined('ADM_URL')){
			$vars['ADM_URL'] = ADM_URL;
			$vars['base'] = ADM_URL;
			$vars['assets'] = ADM_URL.'/assets';
		}else{
			$vars['assets'] = URL.'/assets';
		}
		
		self::addVars($vars);
	}
	
	public function getEngine(){
		return $this->engine;
	}
	
	public static function setEngine($engine){
		self::$s_engine = $engine;
	}
	
	public static function Engine(){
		return self::$s_engine;
	}
	
	public static function addVars($vars){
		if(is_array($vars) AND sizeof($vars) > 0){
			foreach($vars as $k=>$v){
				self::Engine()->assign($k,$v);
			}
		}
	}
	
	public static function fetch($template, $params = array(),$dir = false){
		return self::parse($template,$params,$dir);
	}
	
	public static function parse($template, $params = array(),$dir = false){
		
		if(strpos($template,'.tpl') === false){
			$template = $template.'.tpl';
		}
		
		self::addVars($params);
		
		if($dir){
			$olddir = self::Engine()->template_dir;
			self::Engine()->template_dir = $dir;
		}
		
		$return = self::Engine()->fetch($template);
		
		if($dir){
			self::Engine()->template_dir = $olddir;
		}
		
		return $return;
    }
	
	
	public function __get($key){
		return $this->engine->$key;
	}

	public function __set($key, $value){
		$this->engine->$key = $value;
		return $this;
	}

	public function __call($method, $args){
		if(is_callable(array($this->engine, $method))){
			return call_user_func_array(array($this->engine, $method));
		}
	}

	public static function display($tpl){
		if(strpos($tpl,'.tpl') === false){
			$tpl = $tpl.'.tpl';
		}
		self::Engine()->display($tpl);
	}

}
?>