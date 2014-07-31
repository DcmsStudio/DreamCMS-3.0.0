<?php

@set_time_limit(360);
// utility file to scan for PHP files containing a Byte Order Mark
define('PUBLIC_PATH', str_replace('\\', '/', dirname(__FILE__)) . '/');
define('ROOT_PATH', substr(PUBLIC_PATH, 0, -7));



$path = '';
if (!empty($_GET['path']) )
{
    $path = $_GET['path'];
}







$directory = new RecursiveDirectoryIterator(realpath(ROOT_PATH . $path));
$iterator = new RecursiveIteratorIterator($directory);


$ext = 'php';

if (!empty($_GET['mode']) && $_GET['mode'] == 'js')
{
    $ext = 'js';
}
elseif (!empty($_GET['mode']) && $_GET['mode'] == 'php')
{
    $ext = 'php';
}
elseif (!empty($_GET['mode']) && $_GET['mode'] == 'html')
{
    $ext = 'html';
}


$regex = new RegexIterator($iterator, '/^.+\.(' . $ext . ')$/i', RecursiveRegexIterator::GET_MATCH);

$bom = pack("CCC", 0xef, 0xbb, 0xbf);



foreach ($regex as $match)
{
    $file = $match[0];


	if ( strpos($file, '/wordpress') !== false ) {
		continue;
	}



    $detected = false;
    $contents = file_get_contents($file);


    if (0 == strncmp($contents, $bom, 3))
    {
        echo "BOM detected - file is UTF-8 (" . $file . ")<br>\n";
        $contents = substr($contents, 3);
        $detected = true;
    }


    if (strstr($contents, ' '))
    {
        echo "BOM detected (" . $file . ")<br>\n";
        $contents = str_replace(' ', ' ', $contents);
        $detected = true;
    }


    if ( !empty($_GET['repair']) && $detected )
    {
      #  @copy($file, $file.'.bak');
        @unlink($file);
        
        file_put_contents($file, $contents);
    }    
    else if (empty($_GET['repair']) && $detected)
    {
        echo "Skip repair for  (" . $file . ")<br><br>\n";
    }
}

echo '<p>Done</p>';
?>