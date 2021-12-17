<?php

spl_autoload_register('spl_autoload_custom');

function spl_autoload_custom($name){
	$rc = FALSE;
	// var_export($name);
	$exts = explode(',', spl_autoload_extensions());
	$sep = (substr(PHP_OS, 0, 3) == 'Win') ? ';' : ':';
	$paths = explode($sep, ini_get('include_path'));
	// var_export($paths);
	foreach($paths as $path) {
		foreach($exts as $ext) {
			$file = $path . DIRECTORY_SEPARATOR . $name . $ext;
			if(is_readable($file)) {
				require_once $file;
				$rc = $file;
				break;
			}
		}
	}
	
	return $rc;
}
class Autoloader {

    public static $loader;

    public static function init(){
        if (self::$loader == NULL)
            self::$loader = new self();
		
        return self::$loader;
    }

    public function __construct($class = ''){    
    	set_include_path(
    		 PATH_SEPARATOR . SYS_ROOT
    		. PATH_SEPARATOR . SYS_ROOT . 'cache'
    		. PATH_SEPARATOR . SYS_ROOT . 'collectors'
    		. PATH_SEPARATOR . SYS_ROOT . 'console'
    		. PATH_SEPARATOR . SYS_ROOT . 'db'
    		. PATH_SEPARATOR . SYS_ROOT . 'db/adapters'
    		. PATH_SEPARATOR . SYS_ROOT . 'ar'
    		. PATH_SEPARATOR . SYS_ROOT . 'debug'
    		. PATH_SEPARATOR . SYS_ROOT . 'views'
    		. PATH_SEPARATOR . SYS_ROOT . 'engine'
    		. PATH_SEPARATOR . SYS_ROOT . 'helpers'
    		. PATH_SEPARATOR . SYS_ROOT . 'view'
    		. PATH_SEPARATOR . SYS_ROOT . 'router'
    		. PATH_SEPARATOR . SYS_ROOT . 'Session'
    		. PATH_SEPARATOR . SYS_ROOT . 'i18n'
    		. PATH_SEPARATOR . SYS_ROOT . 'gzip'
    		. PATH_SEPARATOR . SYS_ROOT . 'functions'
    		. PATH_SEPARATOR . SYS_ROOT . 'files'
    		. PATH_SEPARATOR . APP_ROOT . '/_plugins'
    		. PATH_SEPARATOR . APP_ROOT . '/_libs/Form'
    		. PATH_SEPARATOR . APP_ROOT . '/_libs/Grid'
    		. PATH_SEPARATOR . APP_ROOT . '/_libs/'
    		. PATH_SEPARATOR . APP_ROOT . '/apps'
    		. PATH_SEPARATOR . APP_ROOT . '/models'
    		. PATH_SEPARATOR . APP_ROOT  
    	);
		
    	spl_autoload_extensions(".php,.class.php");
    	spl_autoload_register(array($this,'autoload'));
		
    }

    public function autoload($class){
		$load = spl_autoload($class);
        
    }
}

Autoloader::init();

include('vendor/autoload.php');