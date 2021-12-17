<?php
	
	function timerStart(){
		global $timer,$timer_stack;
		$timer = new Timer(); $timer->start(); $timer_stack = '';
	}
	
	/* TimerPoint */
	function timerPoint(){
		global $timer,$timer_stack;
		$timer->stop(); 
		$timer_stack .= '<script>console.log(\''.$i.': '.json_encode($timer->elapsed()).'\');</script>';
	}
	
	function timerEnd(){
		global $timer,$timer_stack;
		$timer->stop(); 
		$timer_stack .= '<script>console.log(\'End: '.json_encode($timer->elapsed()).'\');</script>';
	}
	
	function timerGet(){
		global $timer,$timer_stack;
		return $timer_stack;
	}
	
?>