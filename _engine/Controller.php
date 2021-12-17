<?php

class Controller extends Singleton{
	
	public $title;
	public $content;
	
	public $tbl;
	public $CUDfields;
	public $CUDtypes;
	
	
	public $vars;

	public function init(){}
	
	function __construct(){
		global $conf;
		
		$this->lang = (isset($_SESSION['user']['lang']) ? $_SESSION['user']['lang'] : array_key_first($conf['langs']));
		
		$this->langs = $langs;
		
		$this->section = App::$section;
		$this->action = App::$action;
		$this->operation = App::$operation;
	}
	
	function _404(){
		$this->title = '404';
		header( "HTTP/1.1 404 Not Found" );
		unset($this->_tpl_dir);
		$this->view('404');
	}
	
	function model($model = ''){
		if(!is_object($model) AND trim($model)){
			$section = $model;
		}else{
			$section = (trim(get_called_class()) ? get_called_class() : Router::$appIndex);
		}
		
		$class = $section.'Model';
		
		$tmp = array_merge($this->vars,['title'=>$this->title,'crumbs'=>$this->crumbs]);
		
		if(!class_exists($class)){
			$file = APP_ROOT.'/models/'.$section.'.php';
			if(is_file($file)){
				if(class_exists($class)){
					$m = new $class($tmp);
				}
			}
		}else{
			$m = new $class($tmp);
		}
		
		return (isset($m) AND is_object($m)) ? $m : false;
		
	}
	
	function view($tpl,$vars = array(),$addbootstraplocals = true){
		
		View::Engine()->assign('l',$this->w);
		
		if(isset($this->_tpl_dir)){
			$tpl = $this->_tpl_dir.'/'.$tpl;
		}
		
		if($addbootstraplocals){
			$applocals = App::i()->_locals();
    		View::addVars($applocals);
        }
		
    	View::addVars($this->vars);
		
		$this->content = View::parse($tpl,$vars);
	}
	
	function parse($tpl,$vars = [],$addbootstraplocals = false){
		if($addbootstraplocals){
			$applocals = App::i()->_locals();
    		View::addVars($applocals);
        }
		
		return View::parse($tpl,$vars);
	}
	
	function Html($out){
		$this->content = $out;
	}
	
	function Out($out,$title = ''){
		$this->title = $title;
		$this->content = $out;
	}
	
	function Json($data = true){
		$out = [];
		
		if(is_array($data) AND sizeof($data) > 0){
			$out = $data;
		}else{
			$out = $data; 
		}
		
		if($data === true){
			$ex = ['langs','lang','section','action','operation'];
			$vars = [];
			if(sizeof($this->vars) > 0){
				foreach($this->vars as $key=>$val){
					if(in_array($key,$ex)){
						continue;
					}
					$vars[$key] = $val;
				}
			}
			$out = $vars;
		}	
		
		header('Content-Type: application/json');
		
		$this->content = json_encode($out,JSON_UNESCAPED_UNICODE);
	}
	
	function render($template, $vars = array(),$addthisvars = true,$addbootstraplocals = true){
		if(strpos($template,'.tpl') === false){
			$template = $template.'.tpl';
		}
		
		if($addthisvars){
			View::addVars($this->vars);
		}
		
		if($addbootstraplocals){
      View::addVars(App::i()->_locals());
    }
		
      return View::parse($tpl,$vars);
    }
	
	public function offsetSet($offset, $value) {
      if (is_null($offset)) {
        $this->vars[] = $value;
      } else {
        $this->vars[$offset] = $value;
      }
    }
	
    public function offsetExists($name) {
        return isset($this->vars[$name]);
    }
	
    public function offsetUnset($name) {
        unset($this->vars[$name]);
    }
	
    public function offsetGet($name) {
        return isset($this->vars[$name]) ? $this->vars[$name] : null;
    }

	public function __get($name){
	   return isset($this->vars[$name]) ? $this->vars[$name] : null;
	}

	public function __isset($name){
	   return isset($this->vars[$name]);
	}
	public function __unset($name){
	   unset($this->vars[$name]);
	}

	public function __set($key, $value){
		
		if (is_null($value)) {
			$this->vars[] = $key;
		} else {
			$this->vars[$key] = $value;
		}
	}
	
}
	
?>