<?
	class Helper{
		
		public static function paging($result, $page=1, $page_href, $show=10, $page_name='page',$class_name = 'std_paging'){
			$output = '';
			$rows = $result;
			$numPages = ceil($rows / $show);
			$separator = ' ';
			
			if(strpos($page_href,'=') !== false){
				$page_name = '&'.$page_name;
			}
			
			if($rows > $show){
				$output .='
				<nav>
					<ul class="pagination abr-pagination '.$class_name.'">';
					($rows % $show) != 0 ? $pages = floor($rows/$show) + 1 : $pages = floor($rows/$show);
					$neighbour = 4;
					($page - $neighbour) <= 0 ?  $starter = 1 : $starter = $page - $neighbour;
					($page + $neighbour) >= $pages ? $ender = $pages : $ender = $page + $neighbour + 1;
					
					// $output .= '<li><a href="'.$page_href.''.$page_name.'=1" style="padding: 5px;">'.$page.'/'.$pages.'</a></li>';
					
					if($page > 1){
						$output .= '<li class="page-item"><a class="page-link" href="'.$page_href.''.$page_name.'='.($page - 1).'"><span aria-hidden="true">«</span><span class="sr-only">Следующая</span></a></li>';
					}
					
					if ($starter > 2){
						$output .= '<li class="page-item"><a class="page-link" href="'.$page_href.''.$page_name.'=1" title="1">1</a></li>';
					}
					elseif ($starter == 2){
						$output .= '<li class="page-item"><a class="page-link" href="'.$page_href.''.$page_name.'=1" title="1">1</a></li>';
					}
					for ($i = $starter; $i < $ender; $i++){
						$output .= '<li class="page-item'.($i == $page ? ' active' : '').'"><a class="page-link" href="'.$page_href.''.$page_name.'='.$i.'" title="'.$i.'">'.$i.'</a></li>';
					}
					if (($pages - $page) > 5){
						$output .= '<li class="page-item'.($pages == $page ? ' active' : '').'"><a class="page-link" href="'.$page_href.''.$page_name.'='.$pages.'" title="'.$pages.'">'.$pages.'</a></li>';
					}
					else {
						$output .= '<li class="page-item'.($pages == $page ? ' active' : '').'"><a class="page-link" href="'.$page_href.''.$page_name.'='.$pages.'" title="'.$pages.'">'.$pages.'</a></li>';
					}
					
					if($page < $pages){
						$output .= '<li class="page-item"><a class="page-link" href="'.$page_href.''.$page_name.'='.($page + 1).'"><span aria-hidden="true">»</span><span class="sr-only">Следующая</span></a></li>';
					}else{
						$output .= '<li class="page-item"><a class="page-link" href="'.$page_href.''.$page_name.'=1" class="disable"><span aria-hidden="true">&laquo;</span><span class="sr-only">Первая</span></a></li>';
					}
					
						
				$output .= '</ul>
						</nav>';
						
			}
			return $output;
		}
		
	}

?>