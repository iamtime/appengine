<?php
// db::

// selectq();
// setQueryOption();
// insert();
// update();
// delete();

// build()
// find();
// getAll();
// get();

// select();
// where();
// andWhere();
// orWhere();
// join();
// left_join();
// right_join();
// inner_join();


// tableExists()


// use PDO;
// use PDOException;
// use Closure;

class db
{

    protected static $instances;
    public static $prefix = '';
    public static $lastQuery = '';
    public static $lastError = '';
    public static $lastErrno = '';
    public static $lastInsertId = false;
	public static $log = [];
	
	protected $pdo;
	
	
    public static $numQuery = 0; 
    public $count = 0;  /** Variable which holds an amount of returned rows during get/getOne/select queries @var string*/   
    public $totalCount = 0; /** Variable which holds an amount of returned rows during get/getOne/select queries with withTotalCount() @var string*/
	
	 static $PDO_OPTIONS = array(
				PDO::ATTR_CASE => PDO::CASE_LOWER,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
				PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
				PDO::ATTR_STRINGIFY_FETCHES => false
			);
		
		
	public static function Start($string_or_array = DB_TYPE.'://'.DB_USER.':'.DB_PASS.'@'.DB_HOST.'/'.DB_NAME.';charset='.DB_CHAR,$connection_name = 'default'){
		// var_export($string_or_array);
		if(is_array($string_or_array)){
			foreach($string_or_array as $key=>$val){
				self::i($key,$val);
			}
		}else{
			self::i($connection_name,$string_or_array);
		}
	}
	
	
	public function __call($method, $args)
    {
		if (is_callable(array($this->pdo, $method))){
			return call_user_func_array(array($this->pdo, $method), $args);
		}
    }
	
	final public static function i($connection_name = 'default',$params = '')
	{
		
		if (!isset(self::$instances[$connection_name])){
			
			$connection_string = $params;
			
			$info = static::parse_connection_url($connection_string);
			$adapter_class = static::load_adapter_class($info->protocol);
			
			try {
				
				$connection = new $adapter_class($info);
				$connection->protocol = $info->protocol;
				
				if (isset($info->charset)){
					$connection->set_encoding($info->charset);
				}
				
				self::$instances[$connection_name] = $connection;
				
			} catch (PDOException $e) {
				db::Err($e);
			}
			
		}
		
		return self::$instances[$connection_name];
	}
	
	
	
	public static function foundRows($sth,$index = 'default')
	{
		return self::i($index)->foundRows($sth,$index);
	}
	
	public static function pdo($connection_name = 'default'){
		return self::i($connection_name)->pdo;
	}
	
	public static function getLog($echo = false){
		if($echo){
			echo var_export(self::$log,true);
		}
		return self::$log;
	}
	
	public static function log($e,$type = 0){
		if(!isset(self::$log[$type])){
			self::$log[$type] = [];
		}
		self::$log[$type][] = $e;
	}
	
	protected function __construct($info)
	{
		try {
			// unix sockets start with a /
			if ($info->host[0] != '/'){
				$host = "host=$info->host";

				if (isset($info->port)){
					$host .= ";port=$info->port";
				}
			}else{
				$host = "unix_socket=$info->host";
			}

			$this->pdo = new PDO("$info->protocol:$host;dbname=$info->db", $info->user, $info->pass, static::$PDO_OPTIONS);
		} catch (PDOException $e) {
			db::Err($e);
		}
	}
	
	public function run($sql, $args = [])
    {
        if (!$args){
            // return $this->query($sql);
			$args = [];
        }
		
		self::$numQuery++;
		
        $stmt = $this->pdo->prepare($sql);
		
        $tmp = $stmt->execute($args);
		self::$lastError = print_r($stmt->errorInfo()[2],true);
		self::$lastErrno = $stmt->errorCode();
		
		if(!$tmp){
			$log = 'Ошибка в запросе: '.$sql.'<br>Сообщение ошибки('.$stmt->errorCode().'): '.print_r($stmt->errorInfo()[2],true).'<br>';
			self::log($log,0);
			
			return $tmp;
		}
		
        return $stmt;
    }
	
	
	public static function error(){
		return self::$lastError;
	}
	
	public static function getError(){
		return self::$lastError;
	}
	
	public static function getErrno(){
		return self::$lastErrno;
	}
	
	public static function insertId(){
		return self::pdo()->lastInsertId();
	}
	
	public static function delete($table,$what = '')
	{
		$where = '';
		$tmp = $qset = [];
		if(is_array($what)){
			if(sizeof($what) > 0){
				foreach($what as $k=>$v){
					$tmp[] = ''.$k.' = ?';
					$qset[] = $v;
				}
				
				$where = 'WHERE '.implode(' AND ',$tmp);
			}
		}else{
			if(trim($what)){
				$where = 'WHERE '.$what;
			}
		}
	
		$_table = self::parseTableName($table);
		
		$query = 'DELETE FROM '.$_table.' '.$where;
		
		self::$lastQuery = self::interpolateQuery($query,$qset);
		
		$query = self::i()->run($query,$qset);
		
		if($query){
			return $query;
		}
		
		return false;
	}
	
	public $_tmpq = '';
	public $_tmparr = [];
	
	 protected function reset()
    {
        if ($this->traceEnabled) {
            $this->trace[] = array($this->_lastQuery, (microtime(true) - $this->traceStartQ), $this->_traceGetCaller());
        }

        $this->_where = array();
        $this->_having = array();
        $this->_join = array();
        $this->_joinAnd = array();
        $this->_orderBy = array();
        $this->_groupBy = array();
        $this->_bindParams = array(''); // Create the empty 0 index
        $this->_query = null;
        $this->_queryOptions = array();
        $this->returnType = 'array';
        $this->_nestJoin = false;
        $this->_forUpdate = false;
        $this->_lockInShareMode = false;
        $this->_tableName = '';
        $this->_lastInsertId = null;
        $this->_updateColumns = null;
        $this->_mapKey = null;
        if(!$this->_transaction_in_progress ) {
            $this->defConnectionName = 'default';
        }
        $this->autoReconnectCount = 0;
        return $this;
    }

    /**
     * Helper function to create dbObject with JSON return type
     *
     * @return MysqliDb
     */
    public function jsonBuilder()
    {
        $this->returnType = 'json';
        return $this;
    }

    /**
     * Helper function to create dbObject with array return type
     * Added for consistency as that's default output type
     *
     * @return MysqliDb
     */
    public function arrayBuilder()
    {
        $this->returnType = 'array';
        return $this;
    }

    /**
     * Helper function to create dbObject with object return type.
     *
     * @return MysqliDb
     */
    public function objectBuilder()
    {
        $this->returnType = 'object';
        return $this;
    }

    /**
     * Method to set a prefix
     *
     * @param string $prefix Contains a table prefix
     *
     * @return MysqliDb
     */
    public function setPrefix($prefix = '')
    {
        self::$prefix = $prefix;
        return $this;
    }

	
    /**
     * Pushes a unprepared statement to the mysqli stack.
     * WARNING: Use with caution.
     * This method does not escape strings by default so make sure you'll never use it in production.
     *
     * @author Jonas Barascu
     *
     * @param  [[Type]] $query [[Description]]
     *
     * @return bool|mysqli_result
     * @throws Exception
     */
	private function queryUnprepared($query)
	{
        // Execute query
        $stmt = $this->pdo->query($query);

        // Failed?
        if ($stmt !== false)
            return $stmt;

        if ($this->pdo->errno === 2006 && $this->autoReconnect === true && $this->autoReconnectCount === 0) {
            // $this->connect($this->defConnectionName);
            $this->autoReconnectCount++;
            return $this->queryUnprepared($query);
        }

        throw new Exception(sprintf('Unprepared Query Failed, ERRNO: %u (%s)', $this->pdo->errno, $this->pdo->error), $this->pdo->errno);
    }

    /**
     * Execute raw SQL query.
     *
     * @param string $query      User-provided query to execute.
     * @param array  $bindParams Variables array to bind to the SQL statement.
     *
     * @return array Contains the returned rows from the query.
     * @throws Exception
     */
    public function rawQuery($query, $bindParams = null)
    {
        $params = array(''); // Create the empty 0 index
        $this->_query = $query;
        $stmt = $this->_prepareQuery();

        if (is_array($bindParams) === true) {
            foreach ($bindParams as $prop => $val) {
                $params[0] .= $this->_determineType($val);
                array_push($params, $bindParams[$prop]);
            }

            call_user_func_array(array($stmt, 'bind_param'), $this->refValues($params));
        }

        $stmt->execute();
        $this->count = $stmt->affected_rows;
        $this->_stmtError = $stmt->error;
        $this->_stmtErrno = $stmt->errno;
        $this->_lastQuery = $this->replacePlaceHolders($this->_query, $params);
        $res = $this->_dynamicBindResults($stmt);
        $this->reset();

        return $res;
    }

    /**
     * Helper function to execute raw SQL query and return only 1 row of results.
     * Note that function do not add 'limit 1' to the query by itself
     * Same idea as getOne()
     *
     * @param string $query      User-provided query to execute.
     * @param array  $bindParams Variables array to bind to the SQL statement.
     *
     * @return array|null Contains the returned row from the query.
     * @throws Exception
     */
    public function rawQueryOne($query, $bindParams = null)
    {
        $res = $this->rawQuery($query, $bindParams);
        if (is_array($res) && isset($res[0])) {
            return $res[0];
        }

        return null;
    }

    /**
     * Helper function to execute raw SQL query and return only 1 column of results.
     * If 'limit 1' will be found, then string will be returned instead of array
     * Same idea as getValue()
     *
     * @param string $query      User-provided query to execute.
     * @param array  $bindParams Variables array to bind to the SQL statement.
     *
     * @return mixed Contains the returned rows from the query.
     * @throws Exception
     */
    public function rawQueryValue($query, $bindParams = null)
    {
        $res = $this->rawQuery($query, $bindParams);
        if (!$res) {
            return null;
        }

        $limit = preg_match('/limit\s+1;?$/i', $query);
        $key = key($res[0]);
        if (isset($res[0][$key]) && $limit == true) {
            return $res[0][$key];
        }

        $newRes = Array();
        for ($i = 0; $i < $this->count; $i++) {
            $newRes[] = $res[$i][$key];
        }
        return $newRes;
    }
 /**
     * A method to perform select query
     *
     * @param string    $query   Contains a user-provided select query.
     * @param int|array $numRows Array to define SQL limit in format Array ($offset, $count)
     *
     * @return array Contains the returned rows from the query.
     * @throws Exception
     */
    public function _query($query, $numRows = null)
    {
        $this->_query = $query;
        $stmt = $this->_buildQuery($numRows);
        $stmt->execute();
        $this->_stmtError = $stmt->error;
        $this->_stmtErrno = $stmt->errno;
        $res = $this->_dynamicBindResults($stmt);
        $this->reset();

        return $res;
    }

    /**
     * This method allows you to specify multiple (method chaining optional) options for SQL queries.
     *
     * @uses $MySqliDb->setQueryOption('name');
     *
     * @param string|array $options The options name of the query.
     *
     * @throws Exception
     * @return MysqliDb
     */
    public function setQueryOption($options)
    {
        $allowedOptions = Array('ALL', 'DISTINCT', 'DISTINCTROW', 'HIGH_PRIORITY', 'STRAIGHT_JOIN', 'SQL_SMALL_RESULT',
            'SQL_BIG_RESULT', 'SQL_BUFFER_RESULT', 'SQL_CACHE', 'SQL_NO_CACHE', 'SQL_CALC_FOUND_ROWS',
            'LOW_PRIORITY', 'IGNORE', 'QUICK', 'MYSQLI_NESTJOIN', 'FOR UPDATE', 'LOCK IN SHARE MODE');

        if (!is_array($options)) {
            $options = Array($options);
        }

        foreach ($options as $option) {
            $option = strtoupper($option);
            if (!in_array($option, $allowedOptions)) {
                throw new Exception('Wrong query option: ' . $option);
            }

            if ($option == 'MYSQLI_NESTJOIN') {
                $this->_nestJoin = true;
            } elseif ($option == 'FOR UPDATE') {
                $this->_forUpdate = true;
            } elseif ($option == 'LOCK IN SHARE MODE') {
                $this->_lockInShareMode = true;
            } else {
                $this->_queryOptions[] = $option;
            }
        }

        return $this;
    }

    /**
     * Function to enable SQL_CALC_FOUND_ROWS in the get queries
     *
     * @return MysqliDb
     * @throws Exception
     */
    public function withTotalCount()
    {
        $this->setQueryOption('SQL_CALC_FOUND_ROWS');
        return $this;
    }

	
    /**
     * A convenient SELECT * function.
     *
     * @param string    $tableName   The name of the database table to work with.
     * @param int|array $numRows     Array to define SQL limit in format Array ($offset, $count)
     *                               or only $count
     * @param string    $columns     Desired columns
     *
     * @return array|MysqliDb Contains the returned rows from the select query.
     * @throws Exception
     */
    public function get($tableName, $numRows = null, $columns = '*')
    {
        if (empty($columns)) {
            $columns = '*';
        }

        $column = is_array($columns) ? implode(', ', $columns) : $columns;

        if (strpos($tableName, '.') === false) {
            $this->_tableName = self::$prefix . $tableName;
        } else {
            $this->_tableName = $tableName;
        }

        $this->_query = 'SELECT ' . implode(' ', $this->_queryOptions) . ' ' .
            $column . " FROM " . $this->_tableName;
        $stmt = $this->_buildQuery($numRows);

        if ($this->isSubQuery) {
            return $this;
        }

        $stmt->execute();
        $this->_stmtError = $stmt->error;
        $this->_stmtErrno = $stmt->errno;
        $res = $this->_dynamicBindResults($stmt);
        $this->reset();

        return $res;
    }

    /**
     * A convenient SELECT * function to get one record.
     *
     * @param string $tableName The name of the database table to work with.
     * @param string $columns   Desired columns
     *
     * @return array Contains the returned rows from the select query.
     * @throws Exception
     */
    public function getOne($tableName, $columns = '*')
    {
        $res = $this->get($tableName, 1, $columns);

        if ($res instanceof MysqliDb) {
            return $res;
        } elseif (is_array($res) && isset($res[0])) {
            return $res[0];
        } elseif ($res) {
            return $res;
        }

        return null;
    }

    /**
     * A convenient SELECT COLUMN function to get a single column value from one row
     *
     * @param string $tableName The name of the database table to work with.
     * @param string $column    The desired column
     * @param int    $limit     Limit of rows to select. Use null for unlimited..1 by default
     *
     * @return mixed Contains the value of a returned column / array of values
     * @throws Exception
     */
    public function getValue($tableName, $column, $limit = 1)
    {
        $res = $this->ArrayBuilder()->get($tableName, $limit, "{$column} AS retval");

        if (!$res) {
            return null;
        }

        if ($limit == 1) {
            if (isset($res[0]["retval"])) {
                return $res[0]["retval"];
            }
            return null;
        }

        $newRes = Array();
        for ($i = 0; $i < $this->count; $i++) {
            $newRes[] = $res[$i]['retval'];
        }
        return $newRes;
    }

	
	
	public static function select($table,$what = '*',$args = ''){
		
		self::i()->_tmpq .= 'SELECT * FROM '.$table.' WHERE 1=1';
		
		// self::i()->_tmparr[] = $args;
		
		return self::i();
	}
	
	public function where($where,$args = false){
		
		self::i()->_tmpq .= ' AND '.$where.' = ?';
		if($args){
			self::i()->_tmparr[] = $args;
		}
		
		
		return self::i();
	}
	public function orWhere($where,$args = false){
		
		self::i()->_tmpq .= ' OR ('.$where.')';
		
		if($args){
			self::i()->_tmparr[] = $args;
		}
		
		return self::i();
	}
	
	function one(){
		$query = self::i()->_tmpq;
		$args = self::i()->_tmparr;
		// var_export($query);
		// var_export($args);
		$query = self::i()->run($query,$args);
		self::$lastQuery = $query;

		if($query){
			$result = $query->fetch();
		}else{
			var_export(self::error());
		}
		
		return $result;
	}
	
	function all(){
		
		$query = self::i()->run(self::i()->_tmpq,self::i()->_tmparr);
		self::$lastQuery = $sql;
		
		$result = $query->fetchAll();
		
		return $result;
	}
	
	function count(){
		/* TODO */
	}
	
	function max(){
		/* TODO */
	}
	
	function sum(){
		/* TODO */
	}
	
	public static function update($table,$datas,$where,$return_query = false)
	{
		
		$set = $qset = [];
		$wh = (((is_array($where) AND sizeof($where) > 0) OR (strlen($where) > 0)) ? 'WHERE ' : '');
		
		if(sizeof($datas) > 0){
			foreach($datas as $k=>$v){
				switch($v){
					case 'NULL':
						$set[] = ''.$k.' = '.$v.'';
					break;
					default:
						$set[] = ''.$k.' = ?';
						$qset[] = $v;
				}
			}
		}
		
		if(is_array($where)){
			if(sizeof($where) > 0){
				$i = 0;
				foreach($where as $k=>$v){
					if($i > 0){
						$wh .= ' AND ';
					}
					$wh .= ''.$k.' = ? ';
					$qset[] = $v;
					$i++;
				}
			}
		}else{
			$wh .= $where;
		}
		
		
		$_table = self::parseTableName($table);
		
		$query = 'UPDATE '.$_table.' SET '.implode(',',$set).' '.$wh;
		
		self::$lastQuery = self::interpolateQuery($query,$qset);
		
		
		$query = self::i()->run($query,$qset);
		
		if($query){
			if($return_query){
				return $query;
			}else{
				return true;
			}
		}
		
		return false;
	}
	
	public static function insert($table,$data)
	{
		
			$keys = $values = $qvalues = array();
			if(sizeof($data) > 0){
				foreach($data as $k=>$v){
					$keys[] = $k;
					$values[] = $v;
					$qvalues[] = '?';
				}
			}
		
			$_table = self::parseTableName($table);
			
			$query = 'INSERT INTO '.$_table.' ('.implode(",",$keys).') VALUES ('.implode(",",$qvalues).')';
			
			self::$lastQuery = self::interpolateQuery($query,$values);
			
			
			$query = self::i()->run($query,$values);
			
			if($query){
				// self::$lastInsertId = self::pdo()->lastInsertId();
				
				return $query;
			}
			
		return false;
	}
	
	public static function query($sql, $args = false,$index = 'default')
    {
		
		 if (!$args){
            // return $this->query($sql);
			$args = [];
        }
		
		if(!is_array($args)){
		   $args = explode('|',$args);
		}
		
		self::$numQuery++;
		
        $stmt = self::i()->run($sql);
		self::$lastQuery = $sql;
		
		return $stmt;
	}
	
	
	
	public static function lastQuery(){
		if(trim(self::$lastQuery)){
			return self::$lastQuery;
		}
		
		return false;
	}
	
	
	public static function selectq($sql, $args = false,$one = false,$null = [])
    {
		
		if(!is_array($args) && $args != false){
		   $args = explode('|',$args);
		}
		
		$query = self::i()->run($sql,$args);
		self::$lastQuery = self::interpolateQuery($sql,$args);
		if($query){
			if($one){
				$result = $query->fetch();
			}else{
				$result = $query->fetchAll();
			}
		}else{
			// log($query);
			if(DEV){
				echo 'ERROR: "'.self::$lastQuery.'" '."\r\n".db::error();
			}
			$result = $null;
		}
		
		
        return $result;
    }
	
	public static function interpolateQuery($query, $params) {
		$keys = array();
		
		if(is_array($params) AND sizeof($params) > 0){
			# build a regular expression for each parameter
			foreach ($params as $key => $value) {
				if (is_string($key)) {
					$keys[] = '/:'.$key.'/';
				} else {
					$keys[] = '/[?]/';
				}
			}

			$query = preg_replace($keys, $params, $query, 1, $count);
		}
		#trigger_error('replaced '.$count.' keys');

		return $query;
	}
	
	public static function parseTableName($table)
	{
		$_exp = explode('.',$table);
		$_table = '';
		$_i = 1;
		foreach($_exp as $e){
			$_table.= ''.$e.'';
			if($_i != count($_exp)){
				$_table.= '.';
			}
			$_i++;
		}
		return $_table;
	}
	/**
	 * Loads the specified class for an adapter.
	 *
	 * @param string $adapter Name of the adapter.
	 * @return string The full name of the class including namespace.
	 */
	private static function load_adapter_class($adapter)
	{
		$class = ucwords($adapter) . 'Adapter';
		$fqclass = '' . $class;
		$source = __DIR__ . "/adapters/$class.php";

		if (!is_file($source))
			self::Err("$fqclass not found!");

		require_once($source);
		return $fqclass;
	}
	
	public static function parse_connection_url($connection_url)
	{
		$url = @parse_url($connection_url);

		if (!isset($url['host']))
			self::Err('Database host must be specified in the connection string. If you want to specify an absolute filename, use e.g. sqlite://unix(/path/to/file)');

		$info = new \stdClass();
		$info->protocol = $url['scheme'];
		$info->host = $url['host'];
		$info->db = isset($url['path']) ? substr($url['path'], 1) : null;
		$info->user = isset($url['user']) ? $url['user'] : null;
		$info->pass = isset($url['pass']) ? $url['pass'] : null;

		$allow_blank_db = ($info->protocol == 'sqlite');

		if ($info->host == 'unix(')
		{
			$socket_database = $info->host . '/' . $info->db;

			if ($allow_blank_db)
				$unix_regex = '/^unix\((.+)\)\/?().*$/';
			else
				$unix_regex = '/^unix\((.+)\)\/(.+)$/';

			if (preg_match_all($unix_regex, $socket_database, $matches) > 0)
			{
				$info->host = $matches[1][0];
				$info->db = $matches[2][0];
			}
		} elseif (substr($info->host, 0, 8) == 'windows(')
		{
			$info->host = urldecode(substr($info->host, 8) . '/' . substr($info->db, 0, -1));
			$info->db = null;
		}

		if ($allow_blank_db && $info->db)
			$info->host .= '/' . $info->db;

		if (isset($url['port']))
			$info->port = $url['port'];

		if (strpos($connection_url, 'decode=true') !== false)
		{
			if ($info->user)
				$info->user = urldecode($info->user);

			if ($info->pass)
				$info->pass = urldecode($info->pass);
		}

		if (isset($url['query']))
		{
			foreach (explode('/&/', $url['query']) as $pair) {
				list($name, $value) = explode('=', $pair);

				if ($name == 'charset')
					$info->charset = $value;
			}
		}

		return $info;
	}
	//Err
	public static function Err($e){
		echo $e;
		exit;
	}
	//Clean
	public static function clean($value){
		$search = array("\\",  "\x00",  "'",  '"', "\x1a");
    $replace = array("\\\\","\\0", "\'", '\"', "\\Z");

    $search2 = array("\\\\","\\'", '\\"', );
		$replace2 = ["","\'",'\"'];

		if(is_array($value)){
			$out = [];
			foreach($value as $k=>$v){
				$out[$k] = self::clean($v);
			}
			return $out;
		}else{
      $replacementMap = [
          "\0" => "\\0",
          "\n" => "\\n",
          "\r" => "\\r",
          "\t" => "\\t",
          chr(26) => "\\Z",
          chr(8) => "\\b",
          '"' => '\"',
          "'" => "\'",
          '_' => "\_",
          "%" => "\%",
          '\\' => '\\\\'
      ];

      return \strtr($unescaped_string, $replacementMap);
			// $value = str_replace($search, $replace, $value);
			// return str_replace($search2, $replace2, $value);
			return $value;
		}
	}
	//Escape
	public static function escape($value){ return self::clean($value); }
	
	
	
}
