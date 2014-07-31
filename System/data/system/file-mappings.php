<?php

// Type mappings
// this file contains type mappings for the file manager
// mediatype is used when adding to media library - items without mediatype may not be added

$types = array('folders' => array(), 'files' => array());

// folder types
$types['folders']['__default'] = array('icon' => 'folder', 'view' => false, 'edit' => false, 'mediatype' => false);

$types['folders']['css'] = array('icon' => 'folder_css', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['style'] = array('icon' => 'folder_css', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['styles'] = array('icon' => 'folder_css', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['stylesheets'] = array('icon' => 'folder_css', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['js'] = array('icon' => 'folder_js', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['script'] = array('icon' => 'folder_js', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['scripts'] = array('icon' => 'folder_js', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['javascript'] = array('icon' => 'folder_js', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['images'] = array('icon' => 'folder_img', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['img'] = array('icon' => 'folder_img', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['gfx'] = array('icon' => 'folder_img', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['pictures'] = array('icon' => 'folder_img', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['photos'] = array('icon' => 'folder_img', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['media'] = array('icon' => 'folder_media', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['assets'] = array('icon' => 'folder_media', 'view' => false, 'edit' => false, 'mediatype' => false);

// Fruml CMS folders
$types['folders']['apps'] = array('icon' => 'folder_app', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['core'] = array('icon' => 'folder_core', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['data'] = array('icon' => 'folder_data', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['devkit'] = array('icon' => 'folder_dev', 'view' => false, 'edit' => false, 'mediatype' => false);
$types['folders']['fruml'] = array('icon' => 'folder_fruml', 'view' => false, 'edit' => false, 'mediatype' => false);

// file types (based on extension)
$types['files']['__default'] = array('icon' => 'file', 'view' => false, 'edit' => false, 'mediatype' => false);

// image files
$types['files']['png'] = array('icon' => 'file_img', 'view' => 'image', 'edit' => false, 'mediatype' => 'image', 'mimetype' => 'image/png');
$types['files']['gif'] = array('icon' => 'file_img', 'view' => 'image', 'edit' => false, 'mediatype' => 'image', 'mimetype' => 'image/gif');
$types['files']['jpg'] = array('icon' => 'file_img', 'view' => 'image', 'edit' => false, 'mediatype' => 'image', 'mimetype' => 'image/jpeg');
$types['files']['jpeg'] = array('icon' => 'file_img', 'view' => 'image', 'edit' => false, 'mediatype' => 'image', 'mimetype' => 'image/jpeg');
$types['files']['jpe'] = array('icon' => 'file_img', 'view' => 'image', 'edit' => false, 'mediatype' => 'image', 'mimetype' => 'image/jpeg');
$types['files']['bmp'] = array('icon' => 'file_img', 'view' => 'image', 'edit' => false, 'mediatype' => 'image', 'mimetype' => 'image/bmp');
$types['files']['tiff'] = array('icon' => 'file_img', 'view' => 'image', 'edit' => false, 'mediatype' => 'image', 'mimetype' => 'image/tiff');
$types['files']['psd'] = array('icon' => 'file_img', 'view' => 'image', 'edit' => false, 'mediatype' => 'image', 'mimetype' => 'image/vnd.adobe.photoshop');

// text files
$types['files']['txt'] = array('icon' => 'file_txt', 'view' => 'text', 'edit' => 'text', 'mediatype' => 'text', 'mimetype' => 'text/plain');
$types['files']['wri'] = array('icon' => 'file_txt', 'view' => 'text', 'edit' => 'text', 'mediatype' => 'text', 'mimetype' => 'application/x-mswrite');
$types['files']['nfo'] = array('icon' => 'file_txt', 'view' => 'text', 'edit' => 'text', 'mediatype' => 'text', 'mimetype' => 'text/plain');

// archive files
$types['files']['zip'] = array('icon' => 'file_arc', 'view' => false, 'edit' => false, 'mediatype' => 'archive', 'mimetype' => 'application/zip');
$types['files']['rar'] = array('icon' => 'file_arc', 'view' => false, 'edit' => false, 'mediatype' => 'archive', 'mimetype' => 'application/x-rar-compressed');
$types['files']['tar'] = array('icon' => 'file_arc', 'view' => false, 'edit' => false, 'mediatype' => 'archive', 'mimetype' => 'application/x-tar');
$types['files']['gz'] = array('icon' => 'file_arc', 'view' => false, 'edit' => false, 'mediatype' => 'archive', 'mimetype' => 'application/x-gzip');
$types['files']['sitx'] = array('icon' => 'file_arc', 'view' => false, 'edit' => false, 'mediatype' => 'archive', 'mimetype' => 'application/x-stuffitx');
$types['files']['sit'] = array('icon' => 'file_arc', 'view' => false, 'edit' => false, 'mediatype' => 'archive', 'mimetype' => 'application/zip');
$types['files']['hqx'] = array('icon' => 'file_arc', 'view' => false, 'edit' => false, 'mediatype' => 'archive', 'mimetype' => 'application/zip');

// audio files
$types['files']['mp3'] = array('icon' => 'file_snd', 'view' => 'audio', 'edit' => false, 'mediatype' => 'sound', 'mimetype' => 'audio/mpeg');
$types['files']['wav'] = array('icon' => 'file_snd', 'view' => false, 'edit' => false, 'mediatype' => 'sound', 'mimetype' => 'audio/x-wav');
$types['files']['aif'] = array('icon' => 'file_snd', 'view' => false, 'edit' => false, 'mediatype' => 'sound', 'mimetype' => 'audio/x-aiff');
$types['files']['ogg'] = array('icon' => 'file_snd', 'view' => false, 'edit' => false, 'mediatype' => 'sound', 'mimetype' => 'audio/ogg');
$types['files']['wma'] = array('icon' => 'file_snd', 'view' => false, 'edit' => false, 'mediatype' => 'sound', 'mimetype' => 'audio/x-ms-wma');

// movie files
$types['files']['3gp'] = array('icon' => 'file_vid', 'view' => false, 'edit' => false, 'mediatype' => 'movie', 'mimetype' => 'video/3gpp');
$types['files']['avi'] = array('icon' => 'file_vid', 'view' => false, 'edit' => false, 'mediatype' => 'movie', 'mimetype' => 'video/x-msvideo');
$types['files']['mp4'] = array('icon' => 'file_vid', 'view' => false, 'edit' => false, 'mediatype' => 'movie', 'mimetype' => 'video/mp4');
$types['files']['mov'] = array('icon' => 'file_vid', 'view' => 'video', 'edit' => false, 'mediatype' => 'movie', 'mimetype' => 'video/quicktime');
$types['files']['flv'] = array('icon' => 'file_vid', 'view' => 'video', 'edit' => false, 'mediatype' => 'movie', 'mimetype' => 'video/flv');
$types['files']['mpg'] = array('icon' => 'file_vid', 'view' => false, 'edit' => false, 'mediatype' => 'movie', 'mimetype' => 'video/mpg');

// shell script files
$types['files']['sh'] = array('icon' => 'file_sh', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'application/x-sh');
$types['files']['bat'] = array('icon' => 'file_sh', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'application/x-msdownload');
$types['files']['cmd'] = array('icon' => 'file_sh', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'text/plain');

// web/ programming files
$types['files']['htm'] = array('icon' => 'file_html', 'view' => 'text', 'edit' => 'text', 'mediatype' => 'html', 'mimetype' => 'text/html');
$types['files']['html'] = array('icon' => 'file_html', 'view' => 'text', 'edit' => 'text', 'mediatype' => 'html', 'mimetype' => 'text/html');
$types['files']['xhtml'] = array('icon' => 'file_html', 'view' => 'text', 'edit' => 'text', 'mediatype' => 'html', 'mimetype' => 'application/xhtml+xml');
$types['files']['shtml'] = array('icon' => 'file_html', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'text/html');
$types['files']['php'] = array('icon' => 'file_php', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'application/x-httpd-php');
$types['files']['phtml'] = array('icon' => 'file_php', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'application/x-httpd-php');
$types['files']['asp'] = array('icon' => 'file_html', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '');
$types['files']['jsp'] = array('icon' => 'file_java', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '');
$types['files']['cfm'] = array('icon' => 'file_html', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '');
$types['files']['js'] = array('icon' => 'file_js', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'application/javascript');
$types['files']['xml'] = array('icon' => 'file_html', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'text/xml');
$types['files']['css'] = array('icon' => 'file_css', 'view' => 'text', 'edit' => 'text', 'mediatype' => 'stylesheet', 'mimetype' => 'text/css');
$types['files']['pl'] = array('icon' => 'file_js', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '');
$types['files']['py'] = array('icon' => 'file_js', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '');
$types['files']['swf'] = array('icon' => 'file_swf', 'view' => false, 'edit' => false, 'mediatype' => 'flash', 'mimetype' => 'application/x-shockwave-flash');
$types['files']['rb'] = array('icon' => 'file_rb', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '');
$types['files']['lol'] = array('icon' => 'file_lol', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '');

// other/ misc files
$types['files']['doc'] = array('icon' => 'file_doc', 'view' => false, 'edit' => false, 'mediatype' => 'document', 'mimetype' => 'application/msword');
$types['files']['xls'] = array('icon' => 'file_xls', 'view' => false, 'edit' => false, 'mediatype' => 'spreadsheet', 'mimetype' => 'application/vnd.ms-excel');
$types['files']['ppt'] = array('icon' => 'file_ppt', 'view' => false, 'edit' => false, 'mediatype' => 'presentation', 'mimetype' => 'application/vnd.ms-powerpoint');
$types['files']['docx'] = array('icon' => 'file_doc', 'view' => false, 'edit' => false, 'mediatype' => 'document', 'mimetype' => 'application/msword');
$types['files']['xlsx'] = array('icon' => 'file_xls', 'view' => false, 'edit' => false, 'mediatype' => 'spreadsheet', 'mimetype' => 'application/vnd.ms-excel');
$types['files']['pptx'] = array('icon' => 'file_ppt', 'view' => false, 'edit' => false, 'mediatype' => 'presentation', 'mimetype' => 'application/vnd.ms-powerpoint');
$types['files']['pdf'] = array('icon' => 'file_pdf', 'view' => false, 'edit' => false, 'mediatype' => 'pdf', 'mimetype' => 'application/pdf');
$types['files']['exe'] = array('icon' => 'file_exe', 'view' => false, 'edit' => false, 'mediatype' => false, 'mimetype' => 'application/x-msdownload');
$types['files']['dll'] = array('icon' => 'file_exe', 'view' => false, 'edit' => false, 'mediatype' => false, 'mimetype' => 'application/x-msdownload');
$types['files']['elf'] = array('icon' => 'file_exe', 'view' => false, 'edit' => false, 'mediatype' => false, 'mimetype' => '');
$types['files']['htaccess'] = array('icon' => 'file_js', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '');
$types['files']['java'] = array('icon' => 'file_java', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'text/x-java-source');
$types['files']['sql'] = array('icon' => 'file_sql', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '');
$types['files']['data'] = array('icon' => 'file_sql', 'view' => false, 'edit' => false, 'mediatype' => false, 'mimetype' => '');
$types['files']['bak'] = array('icon' => 'file_bak', 'view' => false, 'edit' => false, 'mediatype' => false, 'mimetype' => '');
$types['files']['otf'] = array('icon' => 'file', 'view' => false, 'edit' => false, 'mediatype' => 'font', 'mimetype' => '');
$types['files']['ttf'] = array('icon' => 'file', 'view' => false, 'edit' => false, 'mediatype' => 'font', 'mimetype' => '');
?>
