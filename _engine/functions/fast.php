<?php
	
	// function log($what){
		// var_export($what);
		// var_export("\n\r");
	// }
	 
	function U($q){
		if(isset($_SESSION['user'])){
			if(isset($_SESSION['user'][$q])){
				return $_SESSION['user'][$q];
			}
		}
		return false;
	}
	
	function M($model,$prefix = 'Models',$func = 'i'){
		$class = ($prefix ? $prefix.'\\' : null).$model.'::'.$func;
		return call_user_func($class);
	}

	function S(){
		return Session::i();
	}
	
	function Sess(){
		return S();
	}
	
	function Session(){
		return S();
	}
	
	
?>