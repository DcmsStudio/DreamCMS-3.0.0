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
 * @file         Abstract.php
 */


abstract class Compiler_Abstract /* extends Loader*/
{


    /**
     * @var string
     */
    const PHP_OPEN = '<?php ';

    /**
     * @var string
     */
    const PHP_CLOSE = ' ?>';

    /**
     * character set of the template, used by string manipulation plugins
     *
     * it must be lowercase, but setCharset() will take care of that
     *
     * @see setCharset
     * @see getCharset
     * @var string
     */
    public $charset = 'utf-8';



	protected $_checkheaders = array();

	protected $_useBlockHeaders = array();

	protected $_registredBlocks = array();


	/**
	 * store the template output
	 *
	 * @var bool|string
	 */
	protected $buffer  = false;

	/**
	 *
	 * @var bool
	 */
	public $forceCompilation = false;

	/**
	 * @var Compiler_Helper
	 */
	public $helper;

    /**
     * @var null|array
     */
    protected $dat = null;

	/**
	 * @var null|array
	 */
	public static $_staticData = array();

	/**
	 * stores the custom plugins registered with this compiler
	 *
	 * @var array
	 */
	protected $customPlugins = array ();

	/**
	 * stores a list of plugins that are used in the currently compiled
	 * template, and that are not compilable. these plugins will be loaded
	 * during the template's runtime if required.
	 *
	 * it is a 1D array formatted as key:pluginName value:pluginType
	 *
	 * @var array
	 */
	protected $_usedPlugins = array ();

	/**
	 * stores all block names
	 *
	 * @var array
	 */
	protected $_usedBlocks = array ();

    /**
     *
     * @var array
     */
    public $_functions = null;

    /**
     *
     * @var array
     */
    protected $_sections = array();


    /**
     *
     * @var array
     */
    protected $scope = array();
    /**
     *
     * @var array
     */
    protected $scopeSourceTag = array();
    /**
     *
     * @var array
     */
    protected $scopeIndex = array();

    protected $scopeIndexCounter = 0;

    protected $_options = array();

    public $fromprovidertag = false;

    /**
     * @param null $name
     * @return array|null
     */
    public function getData($name = null)
    {
        return ($name === null ? $this->dat : (isset($this->dat[$name]) ? $this->dat[$name] : null ) );
    }

    /**
     * @param string $name
     * @param string $code
     */
    public function addSection($name, $code)
    {
        $this->_sections[$name] = $code;
    }

    /**
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getSection($name, $default = '')
    {
        return (isset($this->_sections[$name]) ? $this->_sections[$name] : $default);
    }

    /**
     * @param string $name
     */
    public function removeSection($name)
    {
        if (isset($this->_sections[$name])) {
            unset($this->_sections[$name]);
        }
    }

    public function setFunctions (  )
    {
        if ($this->_functions === null )
        {
            $this->_functions = $this->helper->getFunctions();
        }
    }

    /**
     * @return array
     */
    public function getFunctions ()
    {
        return $this->_functions;
    }


    /**
     *
     * @param string $name
     * @param string $includePath
     */
    public function addUsedBlock( $name, $includePath )
    {
        $this->_usedBlocks[ $name ] = $includePath;
        $this->_usedBlocks = array_unique( $this->_usedBlocks );
    }

    /**
     *
     * @return array
     */
    public function getUsedBlocks()
    {
        return $this->_usedBlocks;
    }

    /**
     * @param string $key
     * @param null $val
     */
    public function setOption($key, $val = null) {
        $this->_options[$key] = $val;
    }

    /**
     * @param string $key
     * @param mixed $default default is null
     * @return mixed default is null
     */
    public function getOption($key, $default = null) {
        return (isset($this->_options[$key]) ? $this->_options[$key] : $default );
    }


    // ----------- Scopes


    /**
     *
     */
    public function clearScopes()
    {
		$this->scopeIndex = array();
		$this->scope = array();
		$this->scopeSourceTag = array();
        $this->scopeIndexCounter = 0;
	}

    /**
     * @param string $originalname
     * @param string $scopename
     * @param null|string $sourceTag
     * @throws BaseException
     */
    public function addScope ( $originalname, $scopename, $sourceTag = null )
	{
		if (is_string($sourceTag))
		{
			$this->scopeSourceTag[$originalname] = htmlspecialchars($sourceTag);
		}

        if (isset($this->scope[ $originalname ]) && $this->scope[ $originalname ] === $scopename )
        {
            throw new BaseException('Scope is defined! Name: '.$originalname .' Replace to: '.$scopename );
        }

        $this->scopeIndexCounter++;
		$this->scopeIndex[] = $originalname;
		$this->scope[ $originalname ] = $scopename;
	}

    /**
     * @param $originalname
     * @param string $replace
     */
    public function removeScope ( $originalname, $replace = '' )
    {

		foreach ($this->scopeIndex as $idx => $n )
        {
			if ($n === $originalname)
            {
                $re = $this->scope[$originalname];
                if ($replace && $re === $re) {
                    unset($this->scopeIndex[$idx]);
                    $this->scopeIndexCounter--;
                    unset( $this->scope[ $originalname ] );
                    unset( $this->scopeSourceTag[ $originalname ] );
                    break;
                }
			}
		}
	}

    /**
     * @param $name
     * @return null|string
     */
    public function getScope ( $name )
	{
		return isset( $this->scope[ $name ] ) ? $this->scope[ $name ] : null;
	}

    /**
     * @return array
     */
    public function getScopes ()
	{

		return array($this->scope, $this->scopeIndex, $this->scopeSourceTag);
	}

    /**
     * @return bool
     */
    public function getLastScope()
    {

        #array_pop();


        #$name = (isset($this->scopeIndex[$this->scopeIndexCounter-1]) ? $this->scopeIndex[$this->scopeIndexCounter-1] : -1);
        $tmp = $this->scope;
        $name = array_pop($tmp);

        return isset($this->scope[ $name ]) ? $this->scope[ $name ] : false;
    }

    /**
     * @param null $scopes
     */
    public function setScopes ( $scopes = null )
	{
		if (is_array($scopes)) {
			$this->scope = $scopes[0];
			$this->scopeIndex = $scopes[1];
			$this->scopeSourceTag = $scopes[2];
            $this->scopeIndexCounter = count($this->scope);
		}
	}


    // ----------- END Scopes


    /**
     * adds an used plugin, this is reserved for use by the {template} plugin
     *
     * @param string $name function name
     * @param        $includePath
     */
    public function addUsedPlugin($name, $includePath)
    {
        $this->_usedPlugins[ $name ] = $includePath;
    }

    /**
     * remove a plugin
     */
    public function removeUsedPlugin($name)
    {
        if ( isset( $this->_usedPlugins[ 'dcmsFunc_' . $name ] ) )
        {
            unset( $this->_usedPlugins[ 'dcmsFunc_' . $name ] );
        }
    }

    /**
     *
     * @return array
     */
    public function getUsedPlugins()
    {
        return $this->_usedPlugins;
    }

	public function registerTags() {

	}


	/**
	 *
	 */
	public function forceCompilation ()
	{
		$this->forceCompilation = true;
	}



    /**
     * @param string $name
     * @param string $filepath
     * @param array $options
     * @param string $compiledScope
     */
    public function registerBlocks( $name, $filepath, $options, $compiledScope )
	{
        if ($this->fromprovidertag)
        {
            return;
        }



		$this->_registredBlocks[ $name ][ 'path' ] = $filepath;
		$this->_registredBlocks[ $name ][ 'compiledScope' ] = $compiledScope; // call from this file

        if (is_array($options)) {
            if ( isset( $options[ 'append' ] ) && $options[ 'append' ] != '' )
            {
                if ( isset( $this->_registredBlocks[ $options[ 'append' ] ] ) )
                {
                    $this->_registredBlocks[ $options[ 'append' ] ][ 'append' ] = $name;
                }
            }

            if ( isset( $options[ 'prepend' ] ) && $options[ 'prepend' ] != '' )
            {
                if ( isset( $this->_registredBlocks[ $options[ 'prepend' ] ] ) )
                {
                    $this->_registredBlocks[ $options[ 'prepend' ] ][ 'prepend' ] = $name;
                }
            }
        }
	}

	/**
	 *
	 * @param string $name
	 * @return string
	 */
	public function useBlock( $name )
	{
        if (isset($this->_registredBlocks[ $name ]['output']))
        {
            return $this->_registredBlocks[ $name ]['output'];
        }

		if ( isset( $this->_registredBlocks[ $name ][ 'path' ] ) && $this->_registredBlocks[ $name ][ 'path' ] /* && file_exists( $this->getCompileDir() . $this->registredBlocks[ $name ][ 'path' ] ) */ )
		{
			Compiler_Library::disableErrorHandling();
			$out = include($this->getCompileDir() . $this->_registredBlocks[ $name ][ 'path' ]);
            Compiler_Library::enableErrorHandling();

            if (is_string($out)) {

                if ( isset( $this->_registredBlocks[ $name ][ 'append' ] ) && $this->_registredBlocks[ $name ][ 'append' ] !== '' )
                {
                    $append = $this->useBlock( $this->_registredBlocks[ $name ][ 'append' ] );
                    $out = $out . $append;
                }

                if ( isset( $this->_registredBlocks[ $name ][ 'prepend' ] ) && $this->_registredBlocks[ $name ][ 'prepend' ] !== '' )
                {
                    $prepend = $this->useBlock( $this->_registredBlocks[ $name ][ 'prepend' ] );
                    $out = $prepend . $out;
                }

                $this->_registredBlocks[ $name ]['output'] = $out;

                return $out;
            }

            return '<!-- Undefined Block - Compiled Template: '. $name .'. Used in File: '. $this->_registredBlocks[ $name ][ 'compiledScope' ] .' -->';

		}
		else
		{
			return ''; //
			//sprintf('Block `%s` not exists!', $name);
		}
	}

}