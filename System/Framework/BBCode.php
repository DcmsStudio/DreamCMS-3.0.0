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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Framework
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         BBCode.php
 */
class BBCode
{

	private static $_coreBBCodes = array (
		'html',
		'css',
		'sql',
		'js',
		'code',
		'php',
		'list',
		'li',
		'img',
		'url',
		'b',
		'i',
		'u',
		'size',
		'sup',
		'sub',
		's',
		'center',
		'color',
		'map',
        'thumb',
		'video',
		'quote'
	); // in this tags will not show smilies

	private static $smilieLoad = false;

	private static $smilies = array ();

	// Allowed Guestbook BBCodes
	// Allowed Comment BBCodes
	// Allowed Forum BBCodes
	// Allowed Signatur BBCodes
	private static $allowBBCodes = true;

	private static $allowImg = true;

	private static $allowSmilies = true;

	private static $allowHTML = false;

	private static $parseLinks = true;

	private static $allowedBBCodes = null;

	private static $allowedCustomBBCodes = false;

	private static $maskedCode;

	private static $smilieLimit = 5; // max smilies in post :)

	private static $mapindex = 0;

	private static $_customBBcodes = null;

	private static $_customBBcodes_Data = null;

    /**
     * @param $code
     * @return mixed
     */
    private static function maskHTML ( $code )
	{
		$code = preg_replace('/<\w+?([^>]*)>/siU', '&lt;$1&gt;', $code);
		return preg_replace('/<([^>]*)>/siU', '&lt;$1&gt;', $code);
	}

	/**
	 *
	 */
	public static function freeMem ()
	{

		self::$smilies        = array ();
		self::$smilieLoad     = false;
		self::$allowedBBCodes = null;
	}

    /**
     * @return array
     */
    public static function getCoreBBCodes() {
		return self::$_coreBBCodes;
	}

	/**
	 * @param string $handler
	 */
	public static function setBBcodeHandler ( $handler = '' )
	{
		switch ( $handler )
		{
			case 'biobbcodes':
				self::$allowSmilies   = false;
				self::$allowedBBCodes = 'color,b,i,u,s,sup,sub';
				break;

			case 'userblog':
				self::$allowSmilies   = true;
				self::$allowedBBCodes = 'color,b,i,u,s,img,url,size,email,list.*,li,\*,quote,code,map,video,center,color,sup,sub';
				break;

			case 'commentbbcodes':
			default:
				self::$allowedBBCodes = 'color,b,i,u,s,img,url,size,email,list.*,li,\*,quote,code';
				break;

		}
	}

	/**
	 * @param bool $allowBBCodes
	 */
	public static function allowBBCodes ( $allowBBCodes = true )
	{

		self::$allowBBCodes = $allowBBCodes;
	}

    /**
     * @param bool|string $allowBBCodes
     */
	public static function allowedCustomBBCodes ( $allowBBCodes = false )
	{

		if ( $allowBBCodes )
		{
			self::$allowedCustomBBCodes = $allowBBCodes;
		}
	}

	/**
	 * @param bool $allowImg
	 */
	public static function allowImg ( $allowImg = true )
	{

		self::$allowImg = $allowImg;
	}

	/**
	 * @param bool $allowSmilies
	 */
	public static function allowSmilies ( $allowSmilies = true )
	{

		self::$allowSmilies = $allowSmilies;
	}

    /**
     * @param bool $allowHTML
     * @internal param bool $parseUrls
     */
	public static function allowHTML ( $allowHTML = false )
	{

		self::$allowHTML = $allowHTML;
	}

	/**
	 * @param bool $parseUrls
	 */
	public static function parseUrls ( $parseUrls = true )
	{

		self::$parseLinks = $parseUrls;
	}

	/**
	 * @param array $code
	 * @return string
	 */
	public static function fixHtmlChars ( $code )
	{

		return str_replace(' ', '', $code[ 0 ]);
	}

	private static function loadCustomBBcodes ()
	{

		if ( is_array(self::$_customBBcodes) )
		{
			return;
		}


		$all = Cache::get('bbcodes');
		if ( !is_array($all) )
		{
			self::$_customBBcodes = array ();
			$db                   = Database::getInstance();
			$result               = $db->query('SELECT * FROM %tp%bbcodes')->fetchAll();

			foreach ( $result as $r )
			{
				self::$_customBBcodes[ ] = $r[ 'bbcodetag' ];
			}
			self::$_customBBcodes_Data = $result;
			Cache::write('bbcodes', $result);
			unset( $result );
		}
		else
		{
			self::$_customBBcodes = array ();
			foreach ( $all as $r )
			{
				self::$_customBBcodes[ ] = $r[ 'bbcodetag' ];
			}
			self::$_customBBcodes_Data = $all;
			unset( $all );
		}
	}

	/**
	 * @param string $content
	 * @return string
	 */
	public static function toXHTML ( $content )
	{



		self::loadCustomBBcodes();
        $content = preg_replace('/\]([a-zA-Z0-9\.:\-_\?\(\)\*\+#]*)/s', '] $1', $content);
		$content = stripslashes($content);
		$content = str_replace(array (
		                             "\r\n",
		                             "\r"
		                       ), "\n", $content);
		$content = preg_replace('/<\s*<\s*</si', '&lt;&lt;&lt;', $content);


		if ( !self::$allowHTML )
		{
			$content = self::maskHTML($content);
		}

		if ( self::$allowedBBCodes !== null )
		{
			$items = explode(',', implode(',', self::$_coreBBCodes) . ',' . self::$allowedBBCodes . ( self::$allowedCustomBBCodes ? ',' . self::$allowedCustomBBCodes : '' ));

			// listitem
			$content = preg_replace('/(\[\s*\*\s*\])/siU', '@-BBCODE@*##BBCODE_END##', $content);

			/**
			 * mask all allowed bbcodes
			 */
			foreach ( $items as $item )
			{
				if ( $item )
				{
					$content = preg_replace('/(\[\s*(\/?\s*' . $item . '[^\]]*)\])/siU', '@-BBCODE@$2##BBCODE_END##', $content);
				}
			}

			preg_match_all('/(@-BBCODE@(.*)##BBCODE_END##)/siU', $content, $bbcodes);

			/**
			 * remove all not allowed bbcodes
			 */
			$content = preg_replace('/(\[\s*([^\]]*)\])/si', '', $content);
		}

		// Wrap Words
		//$content = self::longWords( $content );
		// $content = self::_maskCode( $content );




		$content = str_replace('<', '&lt;', $content);
		$content = str_replace('>', '&gt;', $content);

		$content = nl2br(trim($content));

		// Long word patch
		$content = preg_replace_callback('/(&\s*[^;]*\s*;)/', 'BBCode::fixHtmlChars', $content);
		$content = preg_replace('/(@\s*-\s*B\s*B\s*C\s*O\s*D\s*E\s*@)/s', '@-BBCODE@', $content);
		$content = preg_replace('/(#\s*#\s*B\s*B\s*C\s*O\s*D\s*E\s*_\s*E\s*N\s*D\s*#\s*#)/s', '##BBCODE_END##', $content);


		/**
		 * unmask all allowed bbcodes
		 */
		if ( isset($bbcodes[ 1 ]) && is_array($bbcodes[ 1 ]) )
		{

			#  $content = str_replace (array('@-BBCODE@', '@-BBCODE#@'), array('[', ']'), $content);

			foreach ( $bbcodes[ 2 ] as $match )
			{
				$content = preg_replace('/@(\s*)-\s*B\s*B\s*C\s*O\s*D\s*E(\s*)@\/?' . preg_quote($match, '/') . '#\s*#\s*B\s*B\s*C\s*O\s*D\s*E\s*_\s*E\s*N\s*D\s*#\s*#/sU', '$1[' . $match . ']', $content, 1);
			}

			unset( $bbcodes );
		}



		if ( self::$allowSmilies )
		{


			$content = self::parseSmilies($content);
		}

		// $content = self::_unmaskCode( $content );
		// BBCodes
		if ( self::$allowBBCodes )
		{
			$content = self::quote($content);

			$content = self::unorderedList($content);
			$content = self::listItem($content);

			// System Codes
			$content = self::phpcode($content);
			$content = self::code($content);
			$content = self::jscode($content);
			$content = self::htmlcode($content);
			$content = self::csscode($content);
			$content = self::sqlcode($content);
			$content = self::center($content);
			$content = self::bold($content);
			$content = self::italic($content);
			$content = self::underline($content);
			$content = self::strike($content);
			$content = self::sub($content);
			$content = self::sup($content);


			$content = self::video($content);
			$content = self::googlemaps($content);


			// BBCodes
			$content = self::colour($content);
			$content = self::size($content);


			if ( self::$allowImg )
			{
                $content = self::thumb($content);
				$content = self::image($content);

			}

			$content = self::url($content);
			$content = self::email($content);

            // @todo parse custom bbcodes
		}

		// Auto Parse Urls
		if ( self::$parseLinks )
		{
			$content = preg_replace_callback('#(( |^)(((ftp|http|https|)://)|www\.)\S+)#msi', array (
			                                                                                        'self',
			                                                                                        'linkLenght'
			                                                                                  ), $content);
		}


		$content = self::emptyElements($content);


		// $content = preg_replace('/&/', '&amp;', $content);

		$content = self::CopyTmReg($content);


		$content = str_replace('###SCOPE###', '&lt;&lt;&lt;', $content);

		// escape Core-Tags
		return Tools::escapeCoreTags($content);
	}

	/**
	 * @param string $text
	 * @return string
	 */
	protected static function _maskCode ( $text )
	{

		self::loadCustomBBcodes();

		$customs = ( self::$allowedCustomBBCodes ? explode(',', self::$allowedCustomBBCodes) : array () );
		$customs = Library::unempty($customs);
		$all     = array_merge(self::$_coreBBCodes, $customs);


		preg_match_all('#(\[/?(' . implode('|', $all) . ')[^\]]*\].*\[/\2\])#isU', $text, $match);
		self::$maskedCode = $match[ 0 ];
		$text = preg_replace('#(\[/?(' . implode('|', $all) . ')[^\]]*\].*\[/\2\])#isU', '##BBCODE-CODE-MASK##', $text);


		preg_match_all('#(\[(' . implode('|', $all) . ')[^\]]*\])#isU', $text, $match);
		self::$maskedCode = array_merge( $match[ 0 ], self::$maskedCode);


		return preg_replace('#(\[(' . implode('|', $all) . ')[^\]]*\])#isU', '##BBCODE-CODE-MASK##', $text);

	}

	/**
	 * @param string $text
	 * @return string
	 */
	protected static function _unmaskCode ( $text )
	{

		if ( is_array(self::$maskedCode) )
		{
			foreach ( self::$maskedCode as $str )
			{
				$str  = preg_replace('#(<br\s*/?\s*>)#is', "\n", $str);
				$text = preg_replace('/##BBCODE-CODE-MASK##/', $str, $text, 1);
			}

			self::$maskedCode = null;
		}

		return $text;
	}

	/**
	 * @param string $content
	 * @return string
	 */
	public static function removeBBCode ( $content )
	{

		self::loadCustomBBcodes();

		$customs = ( self::$allowedCustomBBCodes ? explode(',', self::$allowedCustomBBCodes) : array () );
		$customs = Library::unempty($customs);

		$allBBcodes = array_merge(self::$_coreBBCodes, $customs);
		foreach ( $allBBcodes as $bbcode )
		{
			$codeMask = preg_quote((string)$bbcode, '#');

			$content = preg_replace('#\['.$codeMask .'\](.+?)\[/'.$codeMask .'\]#is', "\\1 ", $content);
			$content = preg_replace('#\['.$codeMask .'=([^\]]+?)\](.+?)\[/'.$codeMask .'\]#is', "\\2 ", $content);

			// remove single bbcode tags
			$content = preg_replace('#\['.$codeMask .'\]#is', "", $content);
			$content = preg_replace('#\['.$codeMask .'=([^\]]+?)\]#is', "", $content);
		}

		$content = str_replace( '[*]', '', $content );
		// $content = preg_replace('/\[\/?([^\]]*)\]/si', '', $content);

		return $content;
	}

	/**
	 *
	 * @return array
	 */
	public static function getSmilies ()
	{

		if ( self::$smilieLoad )
		{
			return self::$smilies;
		}
		self::$smilieLoad = true;

		$smilies = Cache::get('smilies');
		if ( $smilies === null )
		{
			$db      = Database::getInstance();
			$smilies = $db->query('SELECT * FROM %tp%smilies ORDER BY smilieorder ASC')->fetchAll();
			Cache::write('smilies', $smilies);
		}

		self::$smilies = $smilies;

		return self::$smilies;
	}

	/**
	 *
	 */
	public static function markitup_smilies ()
	{

		// don't bother setting up smilies if they are disabled
		self::getSmilies();

		if ( !count(self::$smilies) )
		{
			Library::sendJson(false, 'no smilies found');
		}

		$ajax = '<div id="hiddensmilielist" style="display:none;"><ul id="smilielist">';
		foreach ( self::$smilies as $r )
		{

			$r[ 'smiliepath_formated' ] = HTML_URL . 'img/smilies/' . $r[ 'smiliepath' ];


			if ( !is_file(ROOT_PATH . $r[ 'smiliepath_formated' ]) )
			{
				Library::log('File not found', 'warn', ROOT_PATH . $r[ 'smiliepath_formated' ]);
				continue;
			}


			$ajax .= '<li class="smiliebit"><a href="#smilielist" rel="' . $r[ 'smiliecode' ] . '"><img src="' . $r[ 'smiliepath_formated' ] . '" alt="" title="' . $r[ 'smilietitle' ] . '" /></a></li>';
		}

		$ajax .= '</ul></div>';
		self::$smilies = null;

		echo Library::json(array (
		                         'success'    => true,
		                         'smilielist' => $ajax
		                   ));
		exit;
	}

	/**
	 *
	 * @param string $text
	 * @return string
	 */
	public static function removeSmilies ( $text )
	{

		self::getSmilies();
		self::loadCustomBBcodes();
/*
		$customs = ( self::$allowedCustomBBCodes ? explode(',', self::$allowedCustomBBCodes) : array () );
		$customs = Library::unempty($customs);
		$all     = array_merge(self::$_coreBBCodes, $customs);

*/
		$text = self::_maskCode($text);

/*
		// mask bbcodes
		preg_match_all('#(\[(' . implode('|', $all) . '|\*)\].*\[/\2\])#isU', $text, $match);
		$masked = $match[ 0 ];
		$text   = preg_replace('#(\[(' . implode('|', $all) . '|\*)\].*\[/\2\])#isU', '##BBCODE-CODE-MASK##', $text);
*/


		foreach ( self::$smilies as $r )
		{
			if ( $r[ 'smiliecode' ] != '' ) {
				$text = str_ireplace( $r[ 'smiliecode' ], '', $text);
			}
		}


		$text = self::_unmaskCode($text);
/*
		// unmask bbcodes
		foreach ( $masked as $str )
		{
			$text = preg_replace('/##BBCODE-CODE-MASK##/', $str, $text, 1);
		}
*/
		return $text;
	}

	/**
	 * Parse all Smilie Codes
	 *
	 * @param string $text
	 * @return string
	 */
	private static function parseSmilies ( $text )
	{

		self::getSmilies();
		self::loadCustomBBcodes();

		$text = self::_maskCode($text);


/*
		$customs = ( self::$allowedCustomBBCodes ? explode(',', self::$allowedCustomBBCodes) : array () );
		$customs = Library::unempty($customs);
		$all     = array_merge(self::$_coreBBCodes, $customs);

		preg_match_all('#(\[(' . implode('|', $all) . '|\*)[^\]]*\].*\[/\2\])#isU', $text, $match);
		$masked = $match[ 0 ];
		$text   = preg_replace('#(\[(' . implode('|', $all) . '|\*)[^\]]*\].*\[/\2\])#isU', '##BBCODE-CODE-MASK##', $text);
*/

		$limitCount = 0;
        die($text);
		foreach ( self::$smilies as $r )
		{
			if ( $r[ 'smiliecode' ] )
			{
				$r[ 'smiliepath' ] = HTML_URL . 'img/smilies/' . $r[ 'smiliepath' ];

				$image = Html::createTag(array (
				                               'tagname'    => 'img',
				                               'attributes' => array (
					                               'src'   => $r[ 'smiliepath' ],
					                               'title' => $r[ 'smilietitle' ],
					                               'alt'   => ''
				                               )
				                         ));

				preg_match_all('/(\s{1,}|>)' . preg_quote($r[ 'smiliecode' ], '/') . '\s/uis', $text, $matches);

                if ( is_array($matches[ 0 ])) {

                    $matches[ 0 ] = array_unique($matches[ 0 ]);

                    foreach ( $matches[ 0 ] as $_str )
                    {
                        if ( $_str ) {
                            if ( $limitCount >= self::$smilieLimit )
                            {
                                break;
                            }

                            $text = preg_replace('/' . preg_quote($_str, '/') . '/is', ' '.$image.' ', $text, 1);
                            $limitCount++;
                        }
                    }
                }
			}
		}
		$text = self::_unmaskCode($text);

		/*

		foreach ( $masked as $str )
		{
			$text = preg_replace('/##BBCODE-CODE-MASK##/', $str, $text, 1);
		}
*/

		return $text;
	}

	/**
	 * Wrap long words
	 *
	 * @param string $text
	 * @return string
	 */
	private static function longWords ( $text )
	{

		$max_word_lenght = 30;
		$max_link_lenght = 200;
		$splitter        = ' ';
		$lines           = explode("\n", $text);
		foreach ( $lines as $key_line => $line )
		{
			$words = explode(' ', $line);
			foreach ( $words as $key_word => $word )
			{
				$word       = preg_replace('/\[(.*)\]/Usi', '', trim($word));
				$max_lenght = ( substr(strtolower($word), 0, 7) === 'http://' || substr(strtolower($word), 0, 8) === 'https://' || substr(strtolower($word), 0, 4) === 'www.' ) ? $max_link_lenght : $max_word_lenght;
				if ( strlen($word) > $max_lenght )
				{
					$words[ $key_word ] = chunk_split($words[ $key_word ], $max_lenght, $splitter);
					$length             = strlen($words[ $key_word ]) - strlen($splitter);
					$words[ $key_word ] = substr($words[ $key_word ], 0, $length);
				}
			}
			$lines[ $key_line ] = implode(' ', $words);
		}

		return implode("\n", $lines);
	}


    /**
     * @param $content
     * @return mixed
     */
    private static function parseCustomBBCodes ( $content )
	{

		$allowed = expplode(',', strtolower(self::$allowedCustomBBCodes));


		foreach ( self::$_customBBcodes_Data as $r )
		{
			if ( !in_array(strtolower($r[ 'bbcodetag' ]), $allowed) )
			{
				$content = preg_replace('#\[/?' . preg_quote($r[ 'bbcodetag' ], '#') . '[^\]]*\]#', '', $content);
			}
			else
			{

			}
		}

		return $content;
	}


	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function video ( $content )
	{

		return preg_replace_callback('#\[video([^\]]*)\](.*)\[\/video\]#isU', array ( 'self', 'prepareVideo' ), $content);
	}

	/**
	 * currently only youtube videos
	 *
	 * @param array $match
	 * @return string
	 * @todo add more tube sites support
	 */
	private static function prepareVideo ( $match )
	{

		$url = $match[ 2 ];

		if ( !preg_match('#(youtube\.com)#', $url) )
		{
			return '';
		}

		$attr = array ();
		if ( trim($match[ 1 ]) )
		{
			$attributes = trim($match[ 1 ]);

			preg_match_all('#([a-z]+?)\s*=\s*([\'"])([^\2]*)\2#isU', $attributes, $matches, PREG_SET_ORDER);
			foreach ( $matches as $idx => $m )
			{
				$a = strtolower(trim($matches[ $idx ][ 1 ]));

				if ( ( $a === 'height' || $a === 'width' ) && (int)$matches[ $idx ][ 3 ] )
				{
					$attr[ $a ] = $matches[ $idx ][ 3 ];
				}
			}
		}

		$attribut_str = '';

		if ( isset( $attr[ 'width' ] ) )
		{
			$attribut_str .= ' width="' . (int)$attr[ 'width' ] . '"';
		}

		if ( isset( $attr[ 'height' ] ) )
		{
			$attribut_str .= ' height="' . (int)$attr[ 'height' ] . '"';
		}

		return <<<E
<div class="bbcode-video"><iframe src="{$url}" frameborder="0"{$attribut_str}></iframe></div>
E;

	}


	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function googlemaps ( $content )
	{

		return preg_replace_callback('#\[map([^\]]*)\](.*)\[\/map\]#isU', array (
		                                                                        'self',
		                                                                        'mapCallback'
		                                                                  ), $content);
	}

	/**
	 * currently only youtube videos
	 *
	 * @param array $match
	 * @return string
	 * @todo add more tube sites support
	 */
	private static function mapCallback ( $match )
	{


		$latlon = $match[ 2 ];
		$latlon = trim(str_replace(array ( '"', "'", ' ', '(', ')' ), '', $latlon));

		if ( !$latlon )
		{
			return '';
		}

		$ln  = explode(',', $latlon);
		$lat = trim($ln[ 0 ]);
		$lon = trim($ln[ 1 ]);

		$attr = array ();
		if ( trim($match[ 1 ]) )
		{
			$attributes = trim($match[ 1 ]);

			preg_match_all('#([a-z]+?)\s*=\s*([\'"])([^\2]*)\2#isU', $attributes, $matches, PREG_SET_ORDER);

			foreach ( $matches as $idx => $m )
			{
				$a = strtolower(trim($matches[ $idx ][ 1 ]));

				if ( ( $a === 'height' || $a === 'width' || $a === 'zoom' ) && (int)$matches[ $idx ][ 3 ] )
				{
					$attr[ $a ] = $matches[ $idx ][ 3 ];
				}
			}
		}

		$attribut_str = '';
		if ( $attr[ 'width' ] )
		{
			$attribut_str .= 'width:' . (int)$attr[ 'width' ] . 'px;';
		}
		else
		{
			$attribut_str .= 'width: 100%;';
		}


		if ( $attr[ 'height' ] )
		{
			$attribut_str .= 'height:' . (int)$attr[ 'height' ] . 'px;';
		}
		else
		{
			$attribut_str .= 'height: 300px;';
		}

		$zoom = 7;
		if ( isset( $attr[ 'zoom' ] ) && (int)$attr[ 'zoom' ] )
		{
			$zoom = (int)$attr[ 'zoom' ];
		}

		self::$mapindex++;
		$mapindex = self::$mapindex;

		return <<<E
<div class="bbcode-map"><div id="bbcode-map-{$mapindex}" style="position:relative;{$attribut_str}"></div></div>
<script type="text/javascript">
$(document).ready(function(){
	if (typeof Maps != 'undefined' ) {
		new Maps('#bbcode-map-{$mapindex}', {
		    showScale: false,   // make static
			lat:{$lat},
			lon:{$lon},
			zoom:{$zoom}
		});
	}
});
</script>
E;

	}


	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function center ( $content )
	{

		return preg_replace('#\[center\](.*)\[\/center\]#isU', '<div style="text-align:center">\\1</div>', $content);
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function bold ( $content )
	{

		return preg_replace('/\[b\](.*)\[\/b\]/isU', '<strong>\\1</strong>', $content);
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function sub ( $content )
	{

		return preg_replace('/\[sub\](.*)\[\/sub\]/isU', '<sub>\\1</sub>', $content);
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function sup ( $content )
	{

		return preg_replace('/\[sup\](.*)\[\/sup\]/isU', '<sup>\\1</sup>', $content);
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function strike ( $content )
	{

		return preg_replace('/\[s\](.*)\[\/s\]/isU', '<s>\\1</s>', $content);
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function italic ( $content )
	{

		return preg_replace('/\[i\](.*)\[\/i\]/isU', '<em>\\1</em>', $content);
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function underline ( $content )
	{

		return preg_replace('/\[u\](.*)\[\/u\]/isU', '<span class="underline">\\1</span>', $content);
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function code ( $content )
	{

		return preg_replace('/\[code\](.*)\[\/code\]/isU', '<code>\\1</code>', $content);
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function htmlcode ( $content )
	{

		return preg_replace('/\[html\](.*)\[\/html\]/isU', '<pre class="brush:html">\\1</pre>', $content);
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function csscode ( $content )
	{

		return preg_replace('/\[css\](.*)\[\/css\]/isU', '<pre class="brush:css">\\1</pre>', $content);
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function sqlcode ( $content )
	{

		return preg_replace('/\[sql\](.*)\[\/sql\]/isU', '<pre class="brush:sql">\\1</pre>', $content);
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function phpcode ( $content )
	{

		return preg_replace_callback('/\[php\](.*)\[\/php\]/isU', array (
		                                                                'self',
		                                                                'phpFormatCallback'
		                                                          ), $content);
	}

    /**
     * @param $matches
     * @return string
     */
    private static function phpFormatCallback ( $matches )
	{

		$text = preg_replace('#<\?(php)#i', '', $matches[ 1 ]);
		$text = preg_replace('#\?>#', '', $text);

		return '<pre class="brush:php;">' . "&lt;?php\n" . $text . "\n?&gt;" . '</pre>';
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function jscode ( $content )
	{

		return preg_replace('/\[js\](.*)\[\/js\]/isU', '<pre class="brush:javascript">\\1</pre>', $content);
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function unorderedList ( $content )
	{

		$content = preg_replace('/\[list\=1\]\s*\n*(<br\s*\/?>)?\s*\n*(.*)\[\/list\]/isU', '<ol>\\2</ol>', $content);
		$content = preg_replace('/\[list\]\s*\n*(<br\s*\/?>)?\s*\n*(.*)\[\/list\]/isU', '<ul>\\2</ul>', $content);

		return str_replace("<ul>\n", '<ul>', $content);
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function listItem ( $content )
	{

		$content = preg_replace("/\s*\t*\n*(<br\s*\/>)?\s*\t*\n*\[li\]\s*\t*\n*([^(\[li\]]*)\[\/li\]\s*\t*\n*/isU", '<li>\\2</li>', $content);
		$content = preg_replace("/\n*\[\*\]\s*\t*([^\[*\]\n]*?)(<br\s*\/>)?\n/isU", '<li>\\1</li>', $content);

		return str_replace("</li>\n", '</li>', $content);
	}

	/**
	 * parse font colors
	 *
	 * @todo fix sub colors to the first color tag
	 * @param string $content
	 * @return string
	 */
	private static function colour ( $content )
	{

		return preg_replace_callback('/\[color=([^\]]*)\](.*)\[\/color\]/isU', array (
		                                                                             'self',
		                                                                             'colourCallback'
		                                                                       ), $content);
	}

	/**
	 * @param array $matches
	 * @return string
	 */
	private static function colourCallback ( $matches )
	{

		$colour  = strtolower(str_replace('#', '', $matches[ 1 ]));
		$text    = $matches[ 2 ];
		$colours = array (
			'aliceblue'    => 'f0f8ff',
			'antiquewhite' => 'faebd7'
			// ...
		);

		if (isset($colours[$colour]) )
		{
			$class = $colour;
		}
		elseif ( in_array($colour, $colours) )
		{
			$class = array_search($colour, $colours);
		}
		else
		{
			return '<span style="color:' . $colour . '!important">' . $text . '</span>';
		}

		return '<span class="' . $class . '">' . $text . '</span>';
	}

	/**
	 * parse sizes
	 *
	 * @todo fix sub sizes to the first size tag
	 * @param string $content
	 * @return string
	 */
	private static function size ( $content )
	{

		return preg_replace_callback('/(\[size=(["\']?)(xs|s|m|l|xl|\d{1,2})\2\](.*)\[\/size\])/mUsi', array (
		                                                                                                     'self',
		                                                                                                     'sizeCallback'
		                                                                                               ), $content);
	}

	/**
	 * @param array $matches
	 * @return string
	 */
	private static function sizeCallback ( $matches )
	{

		$size  = strtolower($matches[ 3 ]);
		$text  = $matches[ 4 ];
		$sizes = array (
			'xs' => 8,
			's'  => 10,
			'm'  => 12,
			'l'  => 18,
			'xl' => 24
			// ...
		);
		if ( array_key_exists($size, $sizes) )
		{
			$class = $size;
		}
		elseif ( in_array($size, $sizes) )
		{
			$class = array_search($size, $sizes);
		}
		else
		{
			return $text;
		}

		return '<span class="' . $class . '">' . $text . '</span>';
	}

	/**
	 * parse quotes
	 *
	 * @param string $content
	 * @return string
	 */
	private static function quote ( $content )
	{

		while ( preg_match('/(\[quote\](.*)\[\/quote\])/isU', $content) )
		{
			$content = preg_replace_callback('/\[quote\](.*)\[\/quote\]/isU', array (
			                                                                        'self',
			                                                                        'quoteCallback'
			                                                                  ), $content);
		}
		while ( preg_match('/\[quote\s*=\s*([\'"])([^\1]*)\1\](.*)\[\/quote\]/isU', $content) )
		{
			$content = preg_replace_callback('/\[quote\s*=\s*([\'"])([^\1]*)\1\](.*)\[\/quote\]/isU', array (
			                                                                                         'self',
			                                                                                         'quoteCallback'
			                                                                                   ), $content);
		}

		return $content;
	}

	/**
	 * @param array $match
	 * @return string
	 */
	private static function quoteCallback ( $match )
	{

		if ( $match[ 2 ] != '' && $match[ 3 ] != '' )
		{
			return '<div class="bbcode-qoute"><span class="top">&nbsp;</span><div class="qoute-header">' . $match[ 2 ] . '</div><div class="qoute-body">' . $match[ 3 ] . '</div><span class="bot">&nbsp;</span></div>';
		}
		else
		{
			return '<div class="bbcode-qoute"><span class="top">&nbsp;</span><div class="qoute-body">' . $match[ 1 ] . '</div><span class="bot">&nbsp;</span></div>';
		}
	}

    /**
     * @param array $matches
     * @return string
     */
    private static function makeThumb($matches) {

        if ( is_file(ROOT_PATH . $matches[1]) )
        {
            $img = ImageTools::create(PAGE_CACHE_PATH . 'thumbnails');
            $chain = Library::getImageChain();

            $_data = $img->process(
                array(
                    'source' => Library::formatPath(ROOT_PATH . $matches[1]),
                    'output' => 'png',
                    'chain'  => $chain
                )
            );

            if ( $_data['path'] )
            {
                $thumb_link = str_replace(ROOT_PATH, '', $_data['path']);
                return '<img src="'.$thumb_link.'" width="'.$_data['width'].'" height="'.$_data['height'].'" alt="thumb"/>';
            }
        }

        return '';
    }


    /**
     * parse images
     *
     * @param string $content
     * @return string
     */
    private static function thumb ( $content )
    {

        $content = preg_replace_callback('/\[thumb=([^\]]*)\]/Usi', array (
            'self',
            'makeThumb'
        ), $content);

        return preg_replace_callback('/\[thumb\](.*)\[\/img\]/Usi', array (
            'self',
            'makeThumb'
        ), $content);

    }
	/**
	 * parse images
	 *
	 * @param string $content
	 * @return string
	 */
	private static function image ( $content )
	{

        $content = preg_replace_callback('/\[img\](.*)\[\/img\]/Usi', create_function('$e', 'if ($e[1]) { return \'<img src="\'.$e[1].\'" alt="\'.basename($e[1]).\'" />\'; } else { return $e[0]; }'), $content);
        $content = preg_replace_callback('/\[img=([^\]]*)\]/Usi', create_function('$e', 'if ($e[1]) { return \'<img src="\'.$e[1].\'" alt="\'.basename($e[1]).\'" />\'; } else { return $e[0]; }'), $content);
        return preg_replace_callback('/\[img \s*width="([^"]*)"\s*height="([^"]*)"\s*\](.*)\[\/img\]/Usi', create_function(
            '$e',
            'if ($e[3]) { return \'<img height="\'.$e[2].\'" width="\'.$e[1].\'" src="\'.$e[3].\'" alt="\'.basename($e[3]).\'" />\'; } else { return $e[0]; }'), $content);
	}

	/**
	 * parse ulrs
	 *
	 * @param string $content
	 * @return string
	 */
	private static function url ( $content )
	{

		$content = preg_replace_callback('/\[url\](.*)\[\/url\]/Usi', array (
		                                                                    'self',
		                                                                    'linkLenght'
		                                                              ), $content);
		$content = preg_replace_callback('/\[url=(.*)\](.*)\[\/url\]/Usi', array (
		                                                                         'self',
		                                                                         'linkLenght'
		                                                                   ), $content);


		return $content;
	}

	/**
	 * @param array $matches
	 * @return string
	 */
	private static function linkLenght ( $matches )
	{

		$url = trim($matches[ 1 ]);
		$url = str_replace(array ( '"', "'" ), '', $url);

		$linktext = isset( $matches[ 2 ] ) && strlen(trim($matches[ 2 ])) > 0 ? $matches[ 2 ] : trim($matches[ 1 ]);

		if ( strlen($linktext) > 50 && !substr_count(strtolower($linktext), '<img') && !substr_count(strtolower($linktext), '[img') )
		{
			$linktext = substr($linktext, 0, 45 - 3) . ' … ' . substr($linktext, -5);
		}

		$url = substr(strtolower($url), 0, 3) == 'www' ? 'http://' . $url : $url;

		return ' <a href="' . $url . '" target="_blank">' . $linktext . '</a> ';
	}


	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function email ( $content )
	{

		$content = preg_replace('/\[email\](.*)\[\/email\]/Usi', '<a href="mailto:\\1">\\1</a>', $content);
		$content = preg_replace('/\[email=(\'")?([^\1]*)\1\](.*)\[\/email\]/Usi', '<a href="mailto:\\1">\\2</a>', $content);

		return $content;
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function censor ( $content )
	{

		$content = preg_replace('/\[email\](.*)\[\/email\]/Usi', '<a href="mailto:\\1">\\1</a>', $content);
		$content = preg_replace('/\[email=(\'")?([^\1]*)\1\](.*)\[\/email\]/Usi', '<a href="mailto:\\1">\\2</a>', $content);

		return $content;
	}

	/**
	 *
	 * @param string $content
	 * @return string
	 */
	private static function CopyTmReg ( $content )
	{

		$content = preg_replace("#\(c\)#i", "&copy;", $content);
		$content = preg_replace("#\(tm\)#i", "&#153;", $content);

		return preg_replace("#\(r\)#i", "&reg;", $content);
	}

	/**
	 * Remove all empty html tags after parse text
	 *
	 * @param string $content
	 * @return string
	 */
	private static function emptyElements ( $content )
	{

		return preg_replace('/<([a-z0-9_:%;\-\"=]*)>\s*\r*\n*\t*<\/([^>]+)>/mis', '', $content);
	}

}

?>