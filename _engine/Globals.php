<?php
abstract class Globals {
	
	public $crumbs = [];

    function __construct(){
		global $conf;
		 
		$langs = $conf['langs'];
		
		$flang = ''; foreach($langs as $l=>$n){ $flang = $l; break; }
		
		$this->lang = (isset($_SESSION['user']['lang']) ? $_SESSION['user']['lang'] : $flang);
		
		$this->langs = $langs;
		
    }
	
	function getCrumbs(){
		$out = '';
		if(isset($this->crumbs) AND is_array($this->crumbs) AND count($this->crumbs) > 0){
			
			$section = (App::$section == 'processForm' ? App::$action : App::$section);
			$i = 0;
			$out .= '
			<nav aria-label="breadcrumb" class="d-md-inline-block ml-md-4">
				<ol class="breadcrumb breadcrumb-links breadcrumb-dark">';
				
			foreach($this->crumbs as $k=>$c){
				
				if($c[0] !== false){
					if(strpos($c[0],'http') !== false){
						$url = $c[0];
					}else{
						$url = ADM_URL.'/#/'.$section.'/'.$c[0];
					}
					
					$out .= '<li class="breadcrumb-item"><a href="'.$url.'">'.$c[1].'</a></li>';
				}else{
					$out .= '<li class="breadcrumb-item">'.$c[1].'</li>'; 
				}
				$i++;
			}
			
			$out .= '<li class="breadcrumb-item active">'.$this->title.'</li>'; 
			
			$out .= '
				</ol>
			</nav>
			';
		}elseif(trim($this->title)){
			$out .= '<h6 class="h2 text-white d-inline-block mb-0">'.$this->title.'</h6>'; 
		}
		
		return $out;
	}
}


?>