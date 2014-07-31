<?php

// PHP 5 php_strip_whitespace
error_reporting(0);
define('ROOT_PATH', str_replace('\\', '/', dirname(__FILE__)) . '/');



$externalUrlPath = '../../../../';
$externalUrlPath = str_replace('\\', '/', realpath ($externalUrlPath) );
$backendImagePath = '../'.'img/';



$files = array();

function DirToArray($sPath) {
   global $files;
   $handle = opendir($sPath);
   while ($arrDir[] = readdir($handle)) {}
   closedir($handle);

   foreach($arrDir as $file) {
	 if (!preg_match("/^\.{1,2}/", $file) and strlen($file))  {
		if (is_dir($sPath."/".$file) && $file != '__________backups' ) {
			DirToArray($sPath."/".$file);
		} else {
			#if ( substr($file, 0, 1) != '_' ) continue;	
			$newname = $file;
			$files[ str_replace(ROOT_PATH, '', str_replace('\\', '/', $sPath))."/".$file] = $newname;
		}
     } /* end if */
  } /* end foreach */

  return $files;
}

$files = DirToArray(ROOT_PATH);

$externalUrlPath = str_replace( str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ), '',  $externalUrlPath ).'/dcms/';


	
$sellist = '<option value="">----------- TEMPLATE -----------<option>';
foreach ($files as $path => $name )
{
	$path = substr($path, 1);
	$sel = ( $_POST['file'] == $path ? ' selected="selected"' : '');
	$sellist .= '<option value="'.$path.'"'.$sel.'>'.$path.'</option>';
}



if ( !empty($_POST['send']) )
{
	$newcontent =  $_POST['send'] ;
	
	//ISO 8859-1 to UTF-8
	$newcontent = preg_replace("/([\xC2\xC3])([\x80-\xBF])/e", "chr(ord('\\1')<<6&0xC0|ord('\\2')&0x3F)", $newcontent);
	$newcontent = preg_replace("/([\x80-\xFF])/e","chr(0xC0|ord('\\1')>>6).chr(0x80|ord('\\1')&0x3F)", $newcontent);
//die( ROOT_PATH. $_POST['file']);
	try {
		$fp = fopen( ROOT_PATH. $_POST['file'], 'w');
		fwrite($fp, $newcontent );
		fclose($fp);
	}
	catch (Exception $e)
	{
		echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
	
	die("");
	exit;
}








if ( !empty($_POST['file']) )
{
	$content = htmlspecialchars( implode('', file(ROOT_PATH. $_POST['file']) )  );
}


//ISO 8859-1 to UTF-8
$content = preg_replace("/([\xC2\xC3])([\x80-\xBF])/e",
	"chr(ord('\\1')<<6&0xC0|ord('\\2')&0x3F)",
	 $content);
// $this->html_code = preg_replace("/([\xC2\xC3])([\x80-\xBF])/e", "chr(ord('\\1')<<6&0xC0|ord('\\2')&0x3F)", $this->html_code);
$content = preg_replace("/([\x80-\xFF])/e","chr(0xC0|ord('\\1')>>6).chr(0x80|ord('\\1')&0x3F)", $content);






	
$externalUrl = 'http://'.$_SERVER['HTTP_HOST'];
	
$cssPreviewUrl = "../../../../html/style/default/css/new_css_valid_css2-1.css";
	

$html = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<html>
 <head>
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
	<title>DEV Template Editor</title>
	<meta http-equiv="cache-control" content="no-cache" />
	<meta http-equiv="expires" content="0" />
	<meta http-equiv="Content-Language" content="de"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="{$cssPreviewUrl}" type="text/css" />
	
	<script type="text/javascript" src="../../../../html/js/jquery/jquery.js"></script>
	<script type="text/javascript" src="{$externalUrl}{$externalUrlPath}external/codemirror/js/codemirror.js"></script>
	<script type="text/javascript" src="{$externalUrl}{$externalUrlPath}external/codemirror/js/mirrorframe.js"></script>
	
</head>
<body>


<form method="post" id="formedit" action="edit_templates.php" class="p3">
<div class="tblborder" class="mb5"><div class="p3">
<select name="file" id="file">
	{$sellist}
</select> <input id="editfile" type="button" value="Auswahl bearbeiten" /> <input id="save" type="button" value="Speichern" /> <input id="send" type="hidden" name="send" value="" />  <span id="saving"></span>
	</div>
</div>
<p/>
<div class="tblborder" class="mt5">
	<div class="p3">
		<textarea id="code" name="code" cols="120" rows="30">{$content}</textarea>
		 
	</div>
</div>
</form>

<script type="text/javascript" language="javascript">
//<![CDATA[

$(document).ready(function() {
	$('#send').val('');
	jQuery(function()
	{
		var textarea = document.getElementById('code');
		
		if ( textarea )
		{
			var xeditor = new MirrorFrame(CodeMirror.replace(textarea), {
				height: "650px",
				content: textarea.value,
				parserfile: ["parsexml.js", "parsecss.js", "tokenizejavascript.js", "parsejavascript.js", "parsehtmlmixed.js"],
				stylesheet: ["{$externalUrlPath}external/codemirror/css/xmlcolors.css", "{$externalUrlPath}external/codemirror/css/jscolors.css", "{$externalUrlPath}external/codemirror/css/csscolors.css", "{$externalUrlPath}external/codemirror/css/dcmscolors.css"],
				path: "{$externalUrlPath}external/codemirror/js/",
				continuousScanning:600,
				passTime: 50,
				lineNumberTime: 90,
				lineNumbers: true,
				showStatusBar: true,
				imagePath: '{$backendImagePath}sourceedit/',
				indentUnit: 4
			});

			$('#editfile').click( function()
			{
				$('#send').val( '' );
				if ( $('#file').val() != '' )
				{			
					$('#formedit').submit();
				}		
			})
			
			$('#save').click( function()
			{
				$('#saving').html('<img src="../img/loading.gif"/> Speichern...');				
				$('#send').val( xeditor.gettext() );
				
				var post = $('#formedit').serialize();
				$.post("edit_templates.php", post, function(data) {
				
					if ( data != '' )
					{
						$('#saving').html('<b>Fehler: '+ data +'</b>');
					}
					else
					{
						$('#saving').html('');
					}					
				}, 'text');
				
			});
			
			
		}
		
		
	});
});

  
//]]>
</script>
</body>
</html>

EOF;

				
@header("HTTP/1.0 200 OK");
@header("HTTP/1.1 200 OK");
@header("Content-type: text/html; charset=UTF-8");
				
echo $html;


?>