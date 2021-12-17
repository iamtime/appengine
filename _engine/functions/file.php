<?php

	function scan_dir($rootDir, $allData = [], $exclude = []) {
		$invisibleFileNames = array(".","..", ".htaccess");
		$invisibleFileNames = array_merge($exclude,$invisibleFileNames);
		
		if (file_exists($rootDir)){
			$dirContent = scandir($rootDir);
			foreach($dirContent as $key => $content) {
				$path = $rootDir.'/'.$content;
				if(!in_array($content, $invisibleFileNames)) {
					if(is_file($path) && is_readable($path)) {
						$allData[] = $path;
					}elseif(is_dir($path) && is_readable($path)) {
						$allData = scan_dir($path, $allData);
					}
				}
			}
		}
		else $allData = array();
		return $allData;
	}
	
?>