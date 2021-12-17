<?php

function toArr($data,$addarr = false){
	$out = [];
	
	if(is_array($data)){
		if(sizeof($data) > 0){
			foreach($data as $k=>$d){
				$out[$k] = $d->to_array();
			}
		}
	}elseif(is_object($data)){
		if($addarr){
			$out[] = $data->to_array();
		}else{
			$out = $data->to_array();
		}
	}else{
		$out = $data;
	}
	
	return $out;
}

function toJson($data){
	$out = false;
	
	if($tmp = toArr($data)){
		$out = json_encode($tmp);
	}
	return $out;
}

class ARModel extends ActiveRecord\Model{
	
	private static $instances = array();
	private static $lastObject;

	/**
	 * Static method for instantiating a singleton object.
	 *
	 * @return object
	 */
	final public static function i()
	{
		$class_name = get_called_class();
		if (!isset(self::$instances[$class_name])){
			self::$instances[$class_name] = new $class_name();
		}

		return self::$instances[$class_name];
	}
	
	
	public static function switch_connection($name) {

		$cfg = ActiveRecord\Config::instance();    
		$valid = $cfg->get_connections();
		if ( ! isset($valid[$name])) {
			throw new ActiveRecord\DatabaseException('Invalid connection specified');
		}

		$old = self::$connection;

		$cm = ActiveRecord\ConnectionManager::instance();
		$conn = $cm::get_connection($name);
		static::table()->conn = $conn;

		return $old;
	}
   
	function arr(){
		return $this->to_array();
	}
	/* Get All data with model getData function 
		return Array;
	*/
	public static function all()
	{
		$data = call_user_func_array('static::find',array_merge(array('all'),func_get_args()));
		self::$lastObject = $data;
		$tmp = static::getData($data);
		
		return $tmp;
	}
	/* Get One data with model getData function 
		return Array;
	*/
	public static function one()
	{
		$data = call_user_func_array('static::find',array_merge(array('all'),func_get_args()));
		if($data){
			self::$lastObject = $data;
			$tmp = static::getData($data);
			$data = current($tmp);
		}
		
		return $data;
	}
	
	public function __call($method, $args){
		return self::_call($method,$args);
    }

    public static function __callStatic($method, $args) {
        return self::_call($method, $args);
    }
	
	public static function _call($method, $args){
		
		$options = static::extract_and_validate_options($args);
		$create = false;
		
		if (substr($method,0,17) == 'find_or_create_by')
		{
			$attributes = substr($method,17);

			// can't take any finders with OR in it when doing a find_or_create_by
			if (strpos($attributes,'_or_') !== false)
				throw new ActiveRecordException("Cannot use OR'd attributes in find_or_create_by");

			$create = true;
			$method = 'find_by' . substr($method,17);
		}
		
		if (substr($method,0,2) === 'by')
		{
			$attributes = substr($method,2);
			$options['conditions'] = ActiveRecord\SQLBuilder::create_conditions_from_underscored_string(static::connection(),$attributes,$args,static::$alias_attribute);

			if (!($ret = static::find('first',$options)) && $create){
				return static::create(ActiveRecord\SQLBuilder::create_hash_from_underscored_string($attributes,$args,static::$alias_attribute));
			}
			
			if(!is_null($ret)){
				self::$lastObject = $ret;
				$tmp[] = $ret;
				$ret = static::getData($tmp);
			}
			return $ret;
		}
		elseif (substr($method,0,5) === 'allBy')
		{
			$options['conditions'] = ActiveRecord\SQLBuilder::create_conditions_from_underscored_string(static::connection(),substr($method,5),$args,static::$alias_attribute);
			$ret = static::find('all',$options);
			
			return $ret;
		}
		elseif (substr($method,0,7) === 'countBy')
		{
			$options['conditions'] = ActiveRecord\SQLBuilder::create_conditions_from_underscored_string(static::connection(),substr($method,7),$args,static::$alias_attribute);
			return static::count($options);
		}
	}
	
	public static function lastObject(){
		if(isset(self::$lastObject)){
			return self::$lastObject;
		}
		return false;
	}

	public static function lastQuery()
	{
		return self::connection()->last_query;
	}

}

?>