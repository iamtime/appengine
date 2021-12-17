<?php
// if(!defined('NoName')) { die('Fuck you Spillberg!'); }
class Timer {
	public static $t_start = 0;
	public static $t_start_mem = 0;
	public static $t_stop = 0;
	public static $t_stop_mem = 0;
	public static $t_elapsed = 0;
	public static $t_elapsed_mem = 0;

	public static function start() { self::$t_start = microtime(true); self::$t_start_mem = memory_get_usage(); }
	public static function stop()  { self::$t_stop = microtime(true); self::$t_stop_mem = memory_get_usage(); }
	public static function elapsed($return_arr = false) {
		/* if (self::$t_elapsed) {
			return self::$t_elapsed;
		} else { */
			$start_mt = explode (" ", self::$t_start);
			$stop_mt = explode (" ", self::$t_stop);
			$start_total = doubleval($start_mt[0]) + $start_mt[1];
			$stop_total = doubleval($stop_mt[0]) + $stop_mt[1];
			self::$t_elapsed = $stop_total - $start_total;
			
			$start_mt = explode (" ", self::$t_start_mem);
			$stop_mt = explode (" ", self::$t_stop_mem);
			$start_total = doubleval($start_mt[0]) + $start_mt[1];
			$stop_total = doubleval($stop_mt[0]) + $stop_mt[1];
			self::$t_elapsed_mem = $stop_total - $start_total;
			if($return_arr){
				return [self::$t_elapsed,format_size(self::$t_elapsed_mem). ''];
			}else{
				return 'Time: '.self::$t_elapsed.' Mem:'.format_size(self::$t_elapsed_mem)."\n\r";
			}
			
		/* } */
	}
	
	public static function reset() {
		self::$t_start = microtime(true);
		self::$t_start_mem = memory_get_usage() - self::$t_elapsed_mem;
		self::$t_stop = 0;
		self::$t_stop_mem = 0;
		self::$t_elapsed = 0;
		self::$t_elapsed_mem = 0;
	}
};
?>