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
 * @package     DreamCMS
 * @version     3.0.0 Beta
 * @category    Framework
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Regex.php
 *
 */
class Router_Regex extends Router_Abstract
{

    /**
     * @var null|string
     */
    protected $_regex = null;

    /**
     * @var array
     */
    protected $_defaults = array();

    /**
     * @var null|string
     */
    protected $_reverse = null;

    /**
     * @var array
     */
    protected $_map = array();

    /**
     * @var array
     */
    protected $_values = array();

    /**
     * @var null
     */
    protected $_indexes = null;

    /**
     *
     * @param string $route
     * @param array  $defaults
     * @param array  $mapKeys
     * @param string $reverse
     * @param null   $mapIndex
     * @internal param array $map
     */
    public function __construct( $route, $defaults = array(), $mapKeys = array(), $reverse = null, $mapIndex = null )
    {
        $this->_regex = $route;
        $this->_defaults = (array) $defaults;
        $this->_map = (array) $mapKeys;
        $this->_reverse = $reverse;
        $this->_indexes = $mapIndex;
    }

    /**
     *
     * @param string $route
     * @param array  $defaults
     * @param array  $mapKeys
     * @param string $reverse
     * @param null   $mapIndex
     * @internal param array $map
     */
    public function setRule( $route, $defaults = array(), $mapKeys = array(), $reverse = null, $mapIndex = null )
    {
        $this->_regex = $route;
        $this->_defaults = (array) $defaults;
        $this->_map = (array) $mapKeys;
        $this->_reverse = $reverse;
        $this->_indexes = $mapIndex;
    }

    /**
     * Matches a user submitted path with a previously defined route.
     * Assigns and returns an array of defaults on a successful match.
     *
     * @param  string $path Path used to match against this routing map
     * @param  boolean $partial
     * @return array|false  An array of assigned values or a false on a mismatch
     */
    public function match( $path, $partial = false )
    {
        #$this->_regex = str_replace('\#', '', $this->_regex);
        //  if ( !$partial )
        //   {
        //      $path  = trim( $path, '/' );
        //       $regex = '#^' . $this->_regex . '$#ixU';
        //   }
        //   else
        //   {
        $path = trim( $path, '/' );
        $regex = '#^' . $this->_regex . '$#ixU';
        //  }


        $res = preg_match( $regex, $path, $values );
        if ( $res === 0 )
        {
            return false;
        }


        if ( $partial )
        {
            //    $this->setMatchedPath( $values[ 0 ] );
        }

        // array_filter_key()? Why isn't this in a standard PHP function set yet? :)
        foreach ( $values as $i => $value )
        {
            if ( !is_int( $i ) || $i === 0 )
            {
                unset( $values[ $i ] );
            }
        }

        $this->_values = $values;


        return $this->_getMappedValues( $values );
    }

    /**
     * Maps numerically indexed array values to it's associative mapped counterpart.
     * Or vice versa. Uses user provided map array which consists of index => name
     * parameter mapping. If map is not found, it returns original array.
     *
     * Method strips destination type of keys form source array. Ie. if source array is
     * indexed numerically then every associative key will be stripped. Vice versa if reversed
     * is set to true.
     *
     * @param  array $values Indexed or associative array of values to map
     * @param  boolean $reversed False means translation of index to association. True means reverse.
     * @param  boolean $preserve Should wrong type of keys be preserved or stripped.
     * @return array   An array of mapped values
     */
    protected function _getMappedValues( $values, $reversed = false, $preserve = false )
    {
        if ( count( $this->_map ) === 0 )
        {
            return $values;
        }

        $return = array();

        foreach ( $values as $key => $value )
        {
            if ( is_int( $key ) && !$reversed )
            {

                if ( $this->_indexes !== null )
                {
                    $index = $this->_indexes[ $key ];
                }
                else
                {
                    if ( isset($this->_map[$key])  )
                    {
                        $index = $this->_map[ $key ];
                    }
                    elseif ( false === ($index = array_search( $key, $this->_map )) )
                    {
                        $index = $key;
                    }
                }

                $return[ $index ] = $values[ $key ];
            }
            elseif ( $reversed )
            {
                $index = $key;
                if ( !is_int( $key ) )
                {
                    if ( isset($this->_map[$key]) )
                    {
                        $index = $this->_map[ $key ];
                    }
                    else
                    {
                        $index = array_search( $key, $this->_map, true );
                    }
                }
                if ( false !== $index )
                {
                    $return[ $index ] = $values[ $key ];
                }
            }
            elseif ( $preserve )
            {
                $return[ $key ] = $value;
            }
        }

        return $return;
    }

    /**
     * Return a single parameter of route's defaults
     *
     * @param string $name Array key of the parameter
     * @return string Previously set default
     */
    public function getDefault( $name )
    {
        if ( isset( $this->_defaults[ $name ] ) )
        {
            return $this->_defaults[ $name ];
        }
    }

    /**
     * Return an array of defaults
     *
     * @return array Route defaults
     */
    public function getDefaults()
    {
        return $this->_defaults;
    }

    /**
     * Get all variables which are used by the route
     *
     * @return array
     */
    public function getVariables()
    {
        $variables = array();

        foreach ( $this->_map as $key => $value )
        {
            if ( is_numeric( $key ) )
            {
                $variables[] = $value;
            }
            else
            {
                $variables[] = $key;
            }
        }

        return $variables;
    }

    /**
     * _arrayMergeNumericKeys() - allows for a strict key (numeric's included) array_merge.
     * php's array_merge() lacks the ability to merge with numeric keys.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    protected function _arrayMergeNumericKeys( Array $array1, Array $array2 )
    {
        $returnArray = $array1;
        foreach ( $array2 as $array2Index => $array2Value )
        {
            $returnArray[ $array2Index ] = $array2Value;
        }
        return $returnArray;
    }

    /**
     * Assembles a URL path defined by this route
     *
     * @param  array $data An array of name (or index) and value pairs used as parameters
     * @param bool   $reset
     * @param bool   $encode
     * @param bool   $partial
     * @throws BaseException
     * @return string Route path with user submitted parameters
     */
    public function assemble( $data = array(), $reset = false, $encode = false, $partial = false )
    {
        if ( $this->_reverse === null )
        {
            throw new BaseException( 'Cannot assemble. Reversed route is not specified.' );
        }

        $defaultValuesMapped = $this->_getMappedValues( $this->_defaults, true, false );
        $matchedValuesMapped = $this->_getMappedValues( $this->_values, true, false );
        $dataValuesMapped = $this->_getMappedValues( $data, true, false );

        // handle resets, if so requested (By null value) to do so
        if ( ($resetKeys = array_search( null, $dataValuesMapped, true )) !== false )
        {
            foreach ( (array) $resetKeys as $resetKey )
            {
                if ( isset( $matchedValuesMapped[ $resetKey ] ) )
                {
                    unset( $matchedValuesMapped[ $resetKey ] );
                    unset( $dataValuesMapped[ $resetKey ] );
                }
            }
        }

        // merge all the data together, first defaults, then values matched, then supplied
        $mergedData = $defaultValuesMapped;
        $mergedData = $this->_arrayMergeNumericKeys( $mergedData, $matchedValuesMapped );
        $mergedData = $this->_arrayMergeNumericKeys( $mergedData, $dataValuesMapped );

        if ( $encode )
        {
            foreach ( $mergedData as $key => &$value )
            {
                $value = urlencode( $value );
            }
        }

        ksort( $mergedData );

        $return = @vsprintf( $this->_reverse, $mergedData );

        if ( $return === false )
        {
            throw new BaseException( 'Cannot assemble. Too few arguments?' );
        }

        return $return;
    }

}

?>