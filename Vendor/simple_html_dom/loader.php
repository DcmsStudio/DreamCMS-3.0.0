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
 * @package
 * @version      3.0.0 Beta
 * @category
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         loader.php
 */


/**
 * All of the Defines for the classes below.
 *
 * @author S.C. Chen <me578022@gmail.com>
 */
define( 'HDOM_TYPE_ELEMENT', 1 );
define( 'HDOM_TYPE_COMMENT', 2 );
define( 'HDOM_TYPE_TEXT', 3 );
define( 'HDOM_TYPE_ENDTAG', 4 );
define( 'HDOM_TYPE_ROOT', 5 );
define( 'HDOM_TYPE_UNKNOWN', 6 );
define( 'HDOM_QUOTE_DOUBLE', 0 );
define( 'HDOM_QUOTE_SINGLE', 1 );
define( 'HDOM_QUOTE_NO', 3 );
define( 'HDOM_INFO_BEGIN', 0 );
define( 'HDOM_INFO_END', 1 );
define( 'HDOM_INFO_QUOTE', 2 );
define( 'HDOM_INFO_SPACE', 3 );
define( 'HDOM_INFO_TEXT', 4 );
define( 'HDOM_INFO_INNER', 5 );
define( 'HDOM_INFO_OUTER', 6 );
define( 'HDOM_INFO_ENDSPACE', 7 );
define( 'DEFAULT_TARGET_CHARSET', 'UTF-8' );
define( 'DEFAULT_BR_TEXT', "\r\n" );
define( 'DEFAULT_SPAN_TEXT', " " );
define( 'MAX_FILE_SIZE', 600000 );


class SimpleHTMLDom
{

	public function __construct ()
	{

	}
	// helper functions
	// -----------------------------------------------------------------------------
	// get html dom from file
	// $maxlen is defined in the code as PHP_STREAM_COPY_ALL which is defined as -1.
	/**
	 * get html dom from file
	 *
	 * @param        $url
	 * @param bool   $use_include_path
	 * @param null   $context
	 * @param        $offset
	 * @param        $maxLen is defined in the code as PHP_STREAM_COPY_ALL which is defined as -1.
	 * @param bool   $lowercase
	 * @param bool   $forceTagsClosed
	 * @param string $target_charset
	 * @param bool   $stripRN
	 * @param string $defaultBRText
	 * @param string $defaultSpanText
	 * @return bool|simple_html_dom
	 */
	public function getFileHtml ( $url, $use_include_path = false, $context = null, $offset = -1, $maxLen = -1, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT )
	{

		// We DO force the tags to be terminated.
		$dom = new simple_html_dom( null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText );
		// For sourceforge users: uncomment the next line and comment the retreive_url_contents line 2 lines down if it is not already done.
		$contents = file_get_contents($url, $use_include_path, $context, $offset);
		// Paperg - use our own mechanism for getting the contents as we want to control the timeout.
		//$contents = retrieve_url_contents($url);
		if ( empty( $contents ) || strlen($contents) > MAX_FILE_SIZE )
		{
			return false;
		}
		// The second parameter can force the selectors to all be lowercase.
		$dom->load($contents, $lowercase, $stripRN);

		return $dom;
	}

	// get html dom from string
	/**
	 * get html dom from string
	 *
	 * @param        $str
	 * @param bool   $lowercase
	 * @param bool   $forceTagsClosed
	 * @param string $target_charset
	 * @param bool   $stripRN
	 * @param string $defaultBRText
	 * @param string $defaultSpanText
	 * @return bool|simple_html_dom
	 */
	public function getStrHtml ( $str, $lowercase = true, $forceTagsClosed = true, $stripRN = true, $target_charset = DEFAULT_TARGET_CHARSET, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT )
	{

		$dom = new simple_html_dom( null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText );
		if ( empty( $str ) || strlen($str) > MAX_FILE_SIZE )
		{
			$dom->clear();

			return false;
		}
		$dom->load($str, $lowercase, $stripRN);

		return $dom;
	}

	// dump html dom tree
	/**
	 * dump html dom tree
	 *
	 * @param      $node
	 * @param bool $show_attr
	 * @param int  $deep
	 */
	public function dump ( $node, $show_attr = true, $deep = 0 )
	{

		$node->dump($node);
	}
}


/**
 * Autoloader class
 *
 */
class SimpleHTMLDom_Autoloader
{
    protected $path = '';

	/**
	 * Constructor
	 */
	public function __construct ()
	{
		$this->path = dirname(__FILE__);
	}

	/**
	 * Autoloader
	 *
	 * @param string $class The name of the class to attempt to load.
	 */
	public function autoload ( $class )
	{
		if ( substr($class, 0, 11) !== 'simple_html' || class_exists($class, false) )
		{
			return;
		}

		$filename = $this->path . '/simple_html_dom.php';

		include $filename;
	}
}

// autoloader
spl_autoload_register(array (
                            new SimpleHTMLDom_Autoloader(),
                            'autoload'
                      ));

if ( !class_exists('simple_html_dom_node') )
{
	trigger_error('Autoloader not registered properly', E_USER_ERROR);
}




