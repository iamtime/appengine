<?php

	class Apps extends Singleton{
		
		public static $name = '';
		public static $layout = 'overall';
		public static $appIndex = 'home';
		public static $appIndexAction = 'Index';
		
		public static $section;
		public static $action;
		public static $operation;
		public static $settings;
		
		public $app;
		
		public $title;
		public $content;
		
		public $description;
		public $keywords;
		
		public static $allowed = [];
		
		public $version;
		public $_locals;
		
		public $alias;
		public $dopstruct;
		public $onstart;
		
		public $lang;
		public $langs;
		
		
		public $DefaultController = 'SiteController';
		
		protected static $models = [];
		
		protected static $instance; 
		
		function __construct($alias = 'apps',$dopstruct = false,$onstart = false){
			$this->alias = $alias;
			$this->dopstruct = $dopstruct;
			$this->onstart = $onstart;
			
			global $conf;
			$langs = $conf['langs'];
			$flang = ''; foreach($langs as $l=>$n){ $flang = $l; break; }
			$this->lang = (isset($_SESSION['user']['lang']) ? $_SESSION['user']['lang'] : $flang);
			$this->langs = $langs;
			
		}
		
		function run($app = false,$loadStruct = false){
			
			$this->beforeStart();
			
			$controller = $this->Load($loadStruct);

			if(!$controller){
				$controller = new $this->DefaultController();
				$controller->Index();
			}
			
			$this->Start($controller);
			
			if($app instanceof Closure){
				$app($controller);
			}else{
				$this->Display();
			}
			
			$this->afterStart();
			
			$this->End();
		}
		
		function Start($app){
			
			$this->app = $app;
			
			if($this->onstart instanceof Closure){
				
				$onstart = $this->onstart;
				$onstart($app,$this);
				
			}else{
				
				$this->title = $app->title;
				$this->description = $app->description;
				$this->keywords = $app->keywords;
				
				if(count($app->vars) > 0){
					View::addVars($app->vars);
				}
				
				if(count($this->_locals()) > 0){
					View::addVars($this->_locals());
				}
				
				if(isset($app->output) AND !trim($app->content)){
					$app->content = $app->output;
				}
				
				View::addVars([
					'title'=>$app->title,
					'content'=>$app->content,
					'description'=>$app->description,
					'keywords'=>$app->keywords,
				]);
			}
			
		}
		
		function Display(){
			if(!DEV){
				$smarty = View::Engine();
				$smarty->loadFilter("output", "trimwhitespace");
				$smarty->loadFilter("output", "packjs");
				$smarty->loadFilter("output", "addcopy");
			}
			View::display(App::$layout);
		}
		
		function End(){
			// db::close();
		}
		
		public function _locals(){
			if(count($this->_locals) > 0){ return $this->_locals; }
			
			$this->_locals = []; return $this->_locals;
		}
		
		function afterStart(){}
		
		function beforeStart(){}
		
		function getVersion(){
			return 'AppEngine 0.1';
		}
		
		public static function getSetting($name){
			$data = [];
			
			if(!isset($_SESSION['site_settings'])){
				$all = db::selectq('SELECT * FROM site_settings',[]);
				if($all){
					$data = $all;
					$_SESSION['site_settings'] = $data;
				}
			}else{
				$data = $_SESSION['site_settings'];
			}
			
			foreach($data as $s){
				if($s['name'] == $name){
					return $s['value'];
				}
			}
			
			return false;
		}
		
		public static function allowOnly($arr){
			self::$allowed = $arr;
		}
		
		public static function Action($section,$action,$alias = 'apps'){
			
			$actions = ($action == self::$appIndexAction ? [self::$appIndexAction, ucfirst(self::$appIndexAction)] : ['a'.$action, 'a'.ucfirst($action)]);
			
			return self::i()->Load([
				APP_ROOT.'/'.$alias.'/'. $section .'.php', 
				$section, 
				$actions
			]);
			
		}
		
		function Load($structOrAlias = false){
			
			self::$section = $section = (!ifGet('section') ? self::$appIndex : Get('section'));
			self::$action = $action = (!ifGet('action') ? self::$appIndexAction : Get('action'));
						
			$alias = $this->alias;
			
			
			if(is_array($structOrAlias) AND count($structOrAlias) > 0){
				
				if(count($structOrAlias) == 3 AND !is_array($structOrAlias[0])){
					$struct = [];
					$struct[] = $structOrAlias;
				}else{
					$struct = $structOrAlias;
				}
			}else{
				$struct = [];
				
				/* Default Stuct $alias/$section.php:$section@$action */
				$actions = ($action == self::$appIndexAction ? [self::$appIndexAction, ucfirst(self::$appIndexAction)] : ['a'.$action, 'a'.ucfirst($action)]);
				$struct[] = [ 
							APP_ROOT.'/'.$alias.'/'. $section .'.php', 
							$section, 
							$actions
						];
						
				// $struct[] = [ 
							// APP_ROOT.'/modules/'. $section .'/' . $section . '.php', 
							// $section, 
							// $actions
						// ];	
				/* Another struct /$alias/$section/$action.php:$section_$action@$action */
				// $struct[] = [
							// APP_ROOT.'/'.$alias.'/'. $section .'/'. $action .'.php', 
							// $section.'_'.$action,
							// $actions
						// ];
						
			}
      
			if($this->dopstruct AND is_array($this->dopstruct)){
				$struct = array_merge($struct,$this->dopstruct);
			}
      
			if(count(self::$allowed) > 0 AND is_array(self::$allowed)){
				if(!in_array($section,self::$allowed)){
					
					$section = self::$allowed[0];
					
					$actions = ($action == self::$appIndexAction ? [self::$appIndexAction, ucfirst(self::$appIndexAction)] : ['a'.$action, 'a'.ucfirst($action)]);
					
					$struct = [
						[ 
							APP_ROOT.'/'.$alias.'/'. $section .'.php', 
							$section, 
							$actions
						]
					];
				}
			}
			
			$found = false;
			if(!isset($file)){
				foreach($struct as $data){
					
					$filepath = $data[0];
					$classname = $data[1];
					$methodname = $data[2];
					
					if(is_file($filepath)){
						if(require($filepath)){
							
							$controller = $classname;
							$method = '';
							
							if(is_array($methodname)){
								
								foreach($methodname as $m){

									if(is_callable(array($controller,$m))){
										$method = $m;
										$found = true;
										break;
									}else{
										continue;
									}
								}
								
								if(!$found){
									$method = App::$appIndexAction;
									if(is_callable(array($controller,$method))){
										$found = true;
									}
								}
								
							} else {
								
								$method = $methodname;
								
								if(is_callable(array($controller,$method))){
									$found = true;
								}else{
									
									$method = App::$appIndexAction;
									if(is_callable(array($controller,$method))){
										$found = true;
									}
								}
							}
							
						}else{
							continue;
						}
						
					}else{
						continue;
					}
					
				}
			}else{
				if(is_file($file)){
					$controller = $classname;
					$method = $methodname;
					
					if(is_callable(array($controller,$method))){
						$found = true;
					}else{
						
						$method = App::$appIndexAction;
						if(is_callable(array($controller,$method))){
							$found = true;
						}
					}
				}
			}
			
			if($found){
				$m = new $controller;
				$return = $m->$method(getId());
				
				if(!is_null($return)){
					$m->content = $return;
				}
				
				if(isset($m) AND is_object($m)){
					return $m;
				}else{
					return false;
				}
			}else{
				return false;
			}
			
		}
		
		
	}
	