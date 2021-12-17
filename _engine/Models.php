<?php

class Models extends Singleton {
	
	public $title;
	// public $content;
	// public $crumbs;
	
	public $vars;

	public function init(){}
		
	function __construct($obj = false){
		parent::__construct();
		
		if(is_array($obj)){
			foreach($obj as $k=>$v){
				$this->$k = $v;
			}
		}
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