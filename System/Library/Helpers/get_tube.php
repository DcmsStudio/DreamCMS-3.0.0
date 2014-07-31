<?php

/**
 * DreamCMS 3.0
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * 
 * PHP Version 5
 *
 * @package     Helpers
 * @version     3.0.0 Beta
 * @category    Helper Tools
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        get_tube.php
 */
$tubs = array( 'angryalien.com',
        'animevideotv.com',
        'artistdirect.com',
        'badjojo.com',
        'blennus.com',
        'blip.tv',
        'bofunk.com',
        'bolt.com',
        'break.com',
        'castpost.com',
        'cnn.com',
        'collegehumor.com',
        'crunchytv.com',
        'current.tv',
        'dailymotion.com',
        'dailysixer.com',
        'dachix.com',
        'danerd.com',
        'devilducky.com',
        'dumpzilla.com',
        'ejbdotcom.net',
        'evideoshare.com',
        'falundafa.org',
        'firefoxflicks.com',
        'furnitureporn.com',
        'goyk.com',
        'grinvi.com',
        'grouper.com',
        'hiphopdeal.com',
        'ifilm.com',
        'keiichianimeforever.com',
        'kontraband.com',
        'magnatune.com',
        'metacafe.com',
        'movies.yahoo.com',
        'myspace.com',
        'myvideo.de',
        'newgrounds.com',
        'nothingtoxic.com',
        'pcgamerpodcast.com',
        'peekvid.com',
        'pixparty.com',
        'plsthx.com',
        'putfile.com',
        'rai.it',
        'raiclicktv.it',
        'reuters.com',
        'revver.com',
        'sevenload.com/videos',
        'smithappens.com',
        'streetfire.net',
        'stupidvideos.com',
        'thatvideosite.com',
        'vidcrazy.com',
        'video.freevideoblog.com',
        'vimeo.com',
        'weebls-stuff.com',
        'web62.com',
        'yikers.com',
        'youtube.com/v',
        'youtube.com/watch',
        'zippyvideos.com' );


$tube_type    = $tubs[ intval( $cp->input[ 'tube' ] ) ];
$tube_typekey = base64_decode( $cp->input[ 'k' ] );


echo <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">
<html>
<head>
<title>Tube Reader</title>
<style type="text/css" >
body { background-color: #c0c0c0;}
</style>
</head>

<body><div style="text-align:center">
EOF;

switch ( $tube_type )
{
    case 'youtube.com/watch':
    case 'youtube.com/v':
        $media = preg_replace( '/(http:\/\/)(www.|[a-z]{1,}\.)youtube\.com\/watch\?v=/', '', $tube_typekey );



        echo <<<EOF

<object height="395" width="480">
<param name="movie" value="http://www.youtube.com/v/{$media}&rel=0&border=1"></param>
<param name="wmode" value="transparent"></param>
<embed src="http://www.youtube.com/v/{$media}&rel=0&border=1" type="application/x-shockwave-flash" wmode="transparent" height="395" width="480"></embed>
</object>

EOF;


        break;


    case 'myvideo.de':
        $media = preg_replace( '/(http:\/\/)(www\.)?myvideo\.de\/watch\//', '', $tube_typekey );
        echo <<<EOF
	<object style='width:470px;height:395px;' type='application/x-shockwave-flash' data='http://www.myvideo.de/movie/$media'>
<param name='movie' value='http://www.myvideo.de/movie/$media'/>
<param name='FlashVars' value='DESTSERVER=http://www.myvideo.de'/>
<param name='AllowFullscreen' value='true' />
</object>
EOF;
        break;


    case 'sevenload.com/videos':

        $media = preg_replace( '/.*sevenload\.com\/videos\/([^\/]+?)\/.+/is', '\\1', $tube_typekey );

        echo <<<EOF
<script type="text/javascript" src="http://de.sevenload.com/pl/$media/380x313"></script>
EOF;
        break;

    default:
        echo <<<EOF
			<b>Invalid Media connection!</b>
EOF;
        break;
}



echo <<<EOF
</div>
</body></html>
EOF;



exit;
?>