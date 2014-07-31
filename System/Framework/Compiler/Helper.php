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
 * @file         Helper.php
 */

class Compiler_Helper {

	/**
	 * @var null
	 */
	static $parserCustomTags = null;

	/**
	 * @var null
	 */
	static $parserSystemTags = null;

	/**
	 * @var array
	 */
	static $_allowedElsePrevTags = array(
		'loop'    => 1,
		'tree'    => 1,
		'if'      => 1,
		'elseif'  => 1,
		'for'     => 1,
		'hasperm' => 1 );

	/**
	 *
	 * @var array
	 */
	static protected $_functions = null;
    static protected $_charset = null;

    private static $_inited = false;

	/**
	 *
	 */
	public function __construct( $charset = 'utf-8')
	{
        self::$_charset = $charset;

	}

    public function initCache()
    {
        if (!self::$_inited) {
            $this->scanParserTags();

            if (self::$_functions === null)
            {
                self::$_functions = Compiler_Functions::getSystemFunctions(self::$_charset);
            }

            self::$_inited = true;
        }
    }

	/**
	 * @return array
	 */
	public static function getAllowedPrevTags()
	{
		return self::$_allowedElsePrevTags;
	}

	/**
	 *
	 * @param string $templateAlias
	 * @param string $call
	 */
	public function registerFunction( $templateAlias, $call )
	{
		self::$_functions[ $templateAlias ] = $call;
	}

    /**
     * @param $name
     * @return bool
     */
    public static function isFunction($name)
    {
        return (isset(self::$_functions[$name]) ? self::$_functions[$name] : false);
    }

	/**
	 *
	 * @return array
	 */
	public function getFunctions()
	{
		return self::$_functions;
	}

	/**
	 * @param $tagname
	 * @return bool
	 */
	public static function isCustomTag( $tagname )
	{
		return (isset( self::$parserCustomTags[ ucfirst( strtolower( $tagname ) ) ] ) ? true : false);
	}

	/**
	 * @param $tagname
	 * @return bool
	 */
	public static function isSystemTag( $tagname )
	{
		return (isset( self::$parserSystemTags[ ucfirst( strtolower( $tagname ) ) ] ) ? true : false);
	}

	/**
	 *
	 * @return type
	 */
	public function getParserSystemTags()
	{
		return self::$parserSystemTags;
	}
    /**
     *
     * @param mixed $var
     * @param boolean $return
     * @return string
     */
    public static function var_export_min( $var, $return = false )
    {
        if ( is_array( $var ) )
        {
            $toImplode = array();
            foreach ( $var as $key => $value )
            {

                if ( (is_numeric( $value ) && substr( $value, 0, 1 ) !== 0) || is_bool( $value ) )
                {
                    $toImplode[] = var_export( $key, true ) . '=>' . (is_bool( $value ) ? ($value ? 'true' : 'false') : $value);
                }
                else
                {
                    $toImplode[] = var_export( $key, true ) . '=>' . self::var_export_min( $value, true );
                }
            }

            $code = 'array(' . implode( ',', $toImplode ) . ')';
            unset( $toImplode, $var );

            if ( $return )
            {
                return $code;
            }
            else
            {
                echo $code;
            }
        }
        else
        {
            return var_export( $var, $return );
        }
    }
	/**
	 * Tag Registry
	 */
	public function scanParserTags()
	{
		if ( self::$parserSystemTags === null )
		{
			$path = dirname( __FILE__ ) . '/';

            if (file_exists($path . 'tc.php'))
            {
                global $_CUSTOMTAGS, $_SYSTAGS;

                include_once $path . 'tc.php';
                self::$parserCustomTags = $_CUSTOMTAGS;
                self::$parserSystemTags = $_SYSTAGS;

                return;
            }




			$dir_iterator = new DirectoryIterator( $path . 'Tag/Custom/' );
			foreach ( $dir_iterator as $file )
			{
				$filename = $file->getFilename();
				if ( $file->isFile() && Compiler_Library::getExtension( $filename ) === 'php' )
				{
					$name = explode( '.', $filename );
					self::$parserCustomTags[ $name[ 0 ] ] = true;
				}
			}

			self::$parserSystemTags = array();
			$dir_iterator = new DirectoryIterator( $path . 'Tag/' );
			foreach ( $dir_iterator as $file )
			{
				$filename = $file->getFilename();
				if ( $file->isFile() && Compiler_Library::getExtension( $filename ) === 'php' )
				{
					$name = explode( '.', $filename );
					self::$parserSystemTags[ $name[ 0 ] ] = true;
				}
			}


            file_put_contents($path . 'tc.php', '<?php $_SYSTAGS = '.self::var_export_min(self::$parserSystemTags, true) .';$_CUSTOMTAGS = '.self::var_export_min(self::$parserCustomTags, true).'; ?>' );

		}
	}

}