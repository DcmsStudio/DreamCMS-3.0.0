<?php

// PHP 5 php_strip_whitespace

define('ROOT_PATH', str_replace('\\', '/', getcwd()) . '/');


$files = array();

function DirToArray($sPath) {
   global $files;
   $handle = opendir($sPath);
   while ($arrDir[] = readdir($handle)) {}
   closedir($handle);

   foreach($arrDir as $file) {
	 if (!preg_match("/^\.{1,2}/", $file) and strlen($file))  {
		if (is_dir($sPath."/".$file) && $file != 'backup' ) {
			DirToArray($sPath."/".$file);
		} else {
			if ( substr($file, 0, 1) != '_' ) continue;
			
			
		#	$newname = substr($file, 5);			
			$newname = substr($file, 1);
		#	$newname .= 'html';
			
			
			rename($sPath."/".$file, $sPath."/".$newname);
			
			$files[] = $newname;
		}
     } /* end if */
  } /* end foreach */

  return $files;
}


$files = DirToArray(ROOT_PATH.'');
print_r($files);
?>