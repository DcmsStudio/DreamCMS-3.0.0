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
 * @file         Thumb.php
 */


class Compiler_Tag_Custom_Thumb extends Compiler_Tag_Abstract
{

	/**
	 * @var
	 */
	private static $_id;

	/**
	 * @var array
	 */
	private static $_a = array (
		'name',
		'width',
		'height',
		'audio',
		'reload',
		'quality',
		'bgcolor'
	);

	/**
	 *
	 */
	public function configure ()
	{

		$this->tag->setAttributeConfig(
		                                     array (
			                                     'src'    => array (
				                                     Compiler_Attribute::REQUIRED,
				                                     Compiler_Attribute::STRING
			                                     ),
			                                     'width'  => array (
				                                     Compiler_Attribute::OPTIONAL,
				                                     Compiler_Attribute::NUMBER
			                                     ),
			                                     'height' => array (
				                                     Compiler_Attribute::OPTIONAL,
				                                     Compiler_Attribute::NUMBER
			                                     ),
			                                     'title'  => array (
				                                     Compiler_Attribute::OPTIONAL,
				                                     Compiler_Attribute::STRING
			                                     ),
                                                 'alt'  => array (
                                                     Compiler_Attribute::OPTIONAL,
                                                     Compiler_Attribute::STRING
                                                 ),
			                                     'aspect' => array (
				                                     Compiler_Attribute::OPTIONAL,
				                                     Compiler_Attribute::BOOL
			                                     ),
			                                     'shrink' => array (
				                                     Compiler_Attribute::OPTIONAL,
				                                     Compiler_Attribute::BOOL
			                                     ),
			                                     'crop'   => array (
				                                     Compiler_Attribute::OPTIONAL,
				                                     Compiler_Attribute::BOOL
			                                     ),
			                                     'cache'  => array (
				                                     Compiler_Attribute::OPTIONAL,
				                                     Compiler_Attribute::HARD_STRING
			                                     ),
			                                     'chain'  => array (
				                                     Compiler_Attribute::OPTIONAL,
				                                     Compiler_Attribute::STRING
			                                     ),
                                                 'quality'  => array (
                                                     Compiler_Attribute::OPTIONAL,
                                                     Compiler_Attribute::NUMBER
                                                 ),
		                                     )
		                               );
	}




    /**
     *
     * @throws BaseException
     * @return void
     */
    public function process ()
    {

        $src    = $this->getAttributeValue('src');
        $cache  = $this->getAttributeValue('cache');
        $width  = $this->getAttributeValue('width');
        $height = $this->getAttributeValue('height');
        $title  = $this->getAttributeValue('title');
        $alt  = $this->getAttributeValue('alt');
        $aspect = $this->getAttributeValue('aspect');
        $shrink = $this->getAttributeValue('shrink');
        //   $crop = $this->getAttributeValue( 'crop' );
        $chain = $this->getAttributeValue('chain');
        $quality = $this->getAttributeValue('quality');

        if ( $quality >= 0 && $quality <= 100)
        {

        }
        else if ( $quality > 100 ) {

            Compiler_Library::log('Invalid cp:thumb quality '. $quality, 'error');
            $quality = false;
        }

        if ( ( !$width[ 0 ] || !$height[ 0 ] ) && !$chain[ 0 ] )
        {
            trigger_error('Please set width and height in your Template before use the &lt;cp:thumb&gt; Tag or add the attribute chain!', E_USER_ERROR);
        }



        $code = '
		$srcImg = ' . $src[0] . ';
		$srci = preg_replace( "#^". preg_quote( Settings::get(\'portalurl\'), "#" ) ."#", \'\', $srcImg);
        $srci = preg_replace("#^". preg_quote(PAGE_URL_PATH, "#" ) ."#", "", $srci);
        $srci = preg_replace("#^". preg_quote(PAGE_PATH, "#" ) ."#", "", $srci);
        $srci = preg_replace("#^/?pages/".PAGEID."/pages/".PAGEID."/#", "", $srci);
        $srci = preg_replace("#^/?pages/".PAGEID."/#", "", $srci);
        $srci0 = $srci;
		$srci = Compiler_Library::formatPath( PAGE_PATH . $srci );

		if ( file_exists($srci) ){';
        $code .= '  $cache_path = PAGE_PATH . \'.cache/thumbnails/img/\';';

        if ( !empty( $cache ) )
        {

            if ( substr($cache, 0, 1) === '/' )
            {
                $cache = substr($cache, 1);
            }

            $code .= '  $cache_path .= \'' . $cache . '\';if (substr($cache_path,-1) !== "/"){ $cache_path .= "/"; }';
        }

        $code .= '  if ( !is_dir( $cache_path ) ){';
        $code .= '      Compiler_Library::makeDirectory( $cache_path );';
        $code .= '  }';
        $code .= '  $img = ImageTools::create( $cache_path );';
        $code .= '  $ext = strtolower( Compiler_Library::getExtension($srci) );';
        $code .= '  if ($ext === \'jpg\' || $ext === \'jpeg\' || $ext === \'png\' || $ext === \'gif\') { ';
        $code .= '      $saveType = ($ext === \'jpg\' || $ext === \'gif\' ? \'jpeg\' : $ext);';

        $code .= '
		$quality = '.($quality ? $quality : 'false').';
        $chain = array(  0 => array(
                                    0 => \'resize\',
                                    1 => array( \'width\' => intval(' . $width . '),
                                                \'height\'  => intval(' . $height . '),' . ( $this->getAttribute('aspect') ? ' \'keep_aspect\' => (bool)' . ( (bool)$aspect ? 'true' : 'false' ) . ', ' : '' ) . ( $this->getAttribute('shrink') ?
                ' \'shrink_only\' => (bool)' . ( (bool)$shrink ? 'true' : 'false' ) . ', ' : '' ) . '
                                         )
                                ));
';

        if ( $chain[ 0 ] && substr($chain[ 0 ], 1, -1) )
        {
            $code .= '
        $_chain = Compiler_Library::getImageChain( ' . $chain[ 0 ] . ' ); if ( is_array($_chain) ){ $chain = $_chain; }';
        }


        $code .= '

		if ( $quality ) {
		    $img->setQuality($quality);
		}

        $_data = $img->process(
                                array(
                                        \'source\' => Compiler_Library::formatPath($srci),
                                        \'output\' => $saveType,
                                        \'chain\'  => $chain
                                )
                        );

        if ( $_data[\'path\'] ) {
           $_title = (' . ($title[ 0 ] ? $title[ 0 ] : 'false') . ' ? htmlspecialchars(' . $title[ 0 ] . ') : \'\');
           $_alt = (' . ($alt[ 0 ] ? $alt[ 0 ] : 'false') . ' ? htmlspecialchars(' . $alt[ 0 ] . ') : \'\');
           $useAlt = ($_alt ? $_alt : $_title);
           $useTitle = ($_title ? $_title : $_alt);
        ';

        if ( $this->tag->getCompiler()->getOption( 'lazy' ) ) {
            $code .= ' echo \'<img src="\'.DUMMY_IMAGE.\'" data-src="\'. $_data[\'path\'] .\'" width="\'. intval( $_data[\'width\'] ) .\'" height="\'. intval($_data[\'height\']) .\'" alt="\'.$useAlt.\'" title="\'.$useTitle.\'"/>\';';
        }
        else {
            $code .= ' echo \'<img src="\'. $_data[\'path\'] .\'" width="\'. intval( $_data[\'width\'] ) .\'" height="\'. intval($_data[\'height\']) .\'" alt="\'.$useAlt.\'" title="\'.$useTitle.\'"/>\';';
        }
        $code .= '
        }
        else {

            echo \'<img src="public/html/img/placeholder.png" height="100" alt="placeholder" />\';

                //echo "<!-- Invalid image! @1 (". str_replace(PUBLIC_PATH, "", $srci) .") -->";
        }
';
        $code .= '  unset($chain); } $img = null; unset($ext); unset($cache_path); ';
        $code .= '} else {
		        if (substr($srcImg, 0, 10) == "data:image") {
                    echo \'<img src="\'.$srcImg.\'" alt="\'.$useAlt.\'" title="\'.$useTitle.\'" />\';
		        }
		        else {
		        echo \'<img src="public/html/img/placeholder.png" height="100" alt="placeholder" />\';
                    //echo "<!-- Invalid image! @2 (". $srci0 . " -- ".$srci /*str_replace(PUBLIC_PATH, "", $srci)*/ .") -->";
                }
            }';

        $this->setStartTag($code);
    }

}