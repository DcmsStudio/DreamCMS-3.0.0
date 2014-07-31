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
 * @package      Main
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Lang.php
 */
class Main_Action_Lang extends Controller_Abstract
{

	public function execute ()
	{
		$etag = md5($this->Env->requestUri());

		if ( (isset($_SERVER[ 'HTTP_IF_NONE_MATCH' ]) && str_replace( '"', '', stripslashes( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) ) == $etag) )
		{
			// Datei/ETag im Browser Cache vorhanden?
		#	header('Content-Type', 'application/javascript', true);
         #   header("Cache-Control: max-age=5184000", true);
			header('HTTP/1.0 304 Not Modified'); // entsprechenden Header senden => Datei wird nicht geladen

			exit();
		}

		$jsCode = $this->Template->process('js_strings');
		$clean = ob_get_clean();


        // little utf-8 patch
        $jsCode = str_replace('-l-t-', '<', $jsCode);
        $jsCode = str_replace('-g-t-', '>', $jsCode);
        $jsCode = Strings::mbConvertTo( $jsCode, 'UTF-8' );


		$output = new Output();
		$output->setMode( Output::JAVASCRIPT );

		$output->addHeader( 'Content-Type', 'application/javascript' );
        $output->addHeader( "Cache-Control", "max-age=5184000");
		$output->addHeader( 'Last-Modified', gmdate( "D, d M Y H:i:s" ) . " GMT" );
		$output->addHeader( 'Etag', '"'.$etag .'"');
		$output->addHeader( 'Expires', gmdate("D, d M Y H:i:s", time() + (60 * 60 * 24 * 14)) . " GMT");

		// Add json body
		$f = Compiler_Functions::getSystemFunctions('');
		$output->appendOutput( '/* LANG */'."\n". Strings::fixUtf8($jsCode) ."\n\n".'/* Template Functions */'."\ndocument.templateFunctions = ['". str_replace('Compiler_Functions::', '', implode('\',\'', $f)) ."']" );

		// Send
		$output->sendOutput();
		exit;


	}

}

?>