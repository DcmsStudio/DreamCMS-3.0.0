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


abstract class Compiler_Tag_Abstract
{
    /**
     * @var Compiler_Tag
     */
    protected $tag = null;

    /**
     * @var Compiler
     */
    protected $compiler = null;

    /**
     * @var Compiler_Template
     */
    protected $templateInstance = null;

    /**
     *
     * @var array
     * @deprecated no longer used???
     */
    protected $_compilerTags = null;

    /**
     * internal options
     *
     * @var array
     */
    protected $_options = null;

    /**
     * @var array
     */
    protected $_parseTags = array(
        'img'    => true,
        'select' => true,
        'input'  => true,
        'option' => true,
    );


    /**
     * @param Compiler $compiler
     */
    public function setCompiler(Compiler $compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * @param Compiler_Tag $tag
     */
    public function setTag(Compiler_Tag &$tag)
    {
        $this->tag =& $tag;
    }

    /**
     * the default post process
     */
    public function postProcess()
    {
        if ( $this->tag->isEndTag() )
        {
            $this->set( 'nophp', true );
            $this->setEndTag( '</' . $this->tag->getXmlName() . '>' );
        }

        $this->set( 'nophp', false );
    }

    /**
     * the default configure method
     */
    public function configure()
    {
    }


    /**
     * @param $key
     * @throws Compiler_Exception
     */
    public function get($key)
    {
        if ( !$this->tag instanceof Compiler_Tag )
        {
            throw new Compiler_Exception( 'Invalid Tag instance' );
        }

        $this->tag->get( $key );
    }


    /**
     * @param $key
     * @param null $value
     * @throws Compiler_Exception
     */
    public function set($key, $value = null)
    {
        if ( !$this->tag instanceof Compiler_Tag )
        {
            throw new Compiler_Exception( 'Invalid Tag instance' );
        }
        $this->tag->set( $key, $value );
    }


    /**
     * @param array $arr
     * @param string $name
     * @return null|string
     */
    protected function getAttr(array $arr, $name)
    {
        foreach ($arr as $r) {
            if ($r['name'] == $name) {
                return $r['value'];
            }
        }

        return null;

    }




    /**
     *
     * @param string $name
     * @param bool|string $namespace
     * @param boolean $returnOnly default is false.<br/>
     *                                Will test the attribut type if is set "false" and return the parsed value. If the<br/>
     *                                attribut type not set then will return the unparsed value.<br/>
     *                                If this value "true", then ignore the attribut type and <br/>
     *                                return the unparsed value.
     * @param integer $type default is null.<br/>
     *                                If set the type-integer then will use this type to parse the value.
     * @return mixed
     */
    protected function getAttributeValue($name, $namespace = false, $returnOnly = false, $type = null)
    {

        $attr = $this->tag->getAttribute( $name, $namespace );

        return ( ( $attr instanceof Compiler_Attribute ) ? $attr->getValue( $returnOnly, $type ) : $this->get( '_attributeDefault' ) );
    }

    /**
     *
     * @param string $name
     * @param bool|string $namespace
     * @return TemplateCompiler_Attribute
     */
    protected function getAttribute($name, $namespace = false)
    {
        $attr = $this->tag->getAttribute( $name, $namespace );

        return ( ( $attr instanceof Compiler_Attribute ) ? $attr : null );
    }

    /**
     *
     * @param string $name
     * @throws Compiler_Exception
     * @return string/null
     */
    protected function isNamespacedAttribute($name)
    {

        $attr = & $this->tag->getAttributes();

        foreach ( $attr as $data )
        {
            if ( $data instanceof Compiler_Attribute )
            {
                if ( $data->getName() == $name )
                {
                    if ( $data->getNamespace() )
                    {
                        return $data->getNamespace();
                    }
                    else
                    {
                        return null;
                    }
                }
            }
            else
            {
                throw new Compiler_Exception( 'Empty attribut instance!' );
            }
        }

        return null;
    }

    /**
     *
     * @param string $name
     * @param bool|string $namespace default is false
     * @return $this
     * @throws Compiler_Exception
     */
    public function removeAttribute($name, $namespace = false)
    {

        $attributes = & $this->tag->getAttributes();

        if ( !is_array( $attributes ) )
        {
            return;
        }


        foreach ( $attributes as $index => &$attr )
        {
            if ( $attr instanceof Compiler_Attribute )
            {
                if ( $attr->getName() == $name )
                {
                    if ( $namespace && $namespace == $attr->getNamespace() )
                    {
                        unset( $attributes[ $index ] );
                    }
                    else
                    {
                        unset( $attributes[ $index ] );
                    }
                }
            }
            else
            {
                throw new Compiler_Exception( 'Empty attribut instance!' );
            }
        }

        return $this;
    }

    /**
     * will replace an existing tag attribute
     *
     * @param string $name
     * @param             $search
     * @param null $replace
     * @param bool|string $namespace default is false
     * @param bool $returnValueOnly
     * @throws Compiler_Exception
     * @internal param mixed $value
     */
    public function replaceAttributValue($name, $search, $replace = null, $namespace = false, $returnValueOnly = false)
    {

        $attributes = & $this->tag->getAttributes();
        if ( !is_array( $attributes ) )
        {
            return;
        }

        foreach ( $attributes as &$attr )
        {
            if ( $attr instanceof Compiler_Attribute )
            {
                if ( $attr->getName() == $name )
                {
                    if ( $namespace && $namespace == $attr->getNamespace() )
                    {
                        $value = $attr->getValue( $returnValueOnly );
                        $value = str_replace( $search, $replace, $value );




                        $attr->setValue( $value );
                    }
                    else
                    {
                        $value = $attr->getValue( $returnValueOnly );
                        $value = str_replace( $search, $replace, $value );
                        $attr->setValue( $value );
                    }
                }
            }
            else
            {
                throw new Compiler_Exception( 'Empty attribut instance!' );
            }
        }
    }

    /**
     *
     * @param mixed $var
     * @param boolean $return
     * @return string
     */
    public function var_export_min($var, $return = false)
    {

        if ( is_array( $var ) )
        {
            $toImplode = array();
            foreach ( $var as $key => $value )
            {

                if ( ( is_numeric( $value ) && substr( $value, 0, 1 ) !== 0 ) || is_bool( $value ) )
                {
                    $toImplode[ ] = var_export( $key, true ) . '=>' . ( is_bool( $value ) ? ( $value ? 'true' : 'false' ) : $value );
                }
                else
                {
                    $toImplode[ ] = var_export( $key, true ) . '=>' . self::var_export_min( $value, true );
                }
            }

            $code = 'array(' . implode( ',', $toImplode ) . ')';

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
     * @return array
     * @throws Compiler_Exception
     */
    public function getAttributeArray()
    {

        $attributes = & $this->tag->getAttributes();
        if ( !is_array( $attributes ) )
        {
            return array();
        }


        $ret = array();
        foreach ( $attributes as &$attr )
        {
            if ( $attr instanceof Compiler_Attribute )
            {
                if ( $attr->getName() )
                {
                    if ( $attr->getNamespace() )
                    {
                        $value                                                 = $attr->getValue();
                        $value                                                 = str_replace( $search, $replace, $value );
                        $ret[ $attr->getNamespace() . ':' . $attr->getName() ] = $value;
                    }
                    else
                    {
                        $value                   = $attr->getValue();
                        $value                   = str_replace( $search, $replace, $value );
                        $ret[ $attr->getName() ] = $value;
                    }
                }
            }
            else
            {
                throw new Compiler_Exception( 'Empty attribut instance!' );
            }
        }

        return $ret;
    }

    /**
     *
     * @param array $ignoreNamespaces
     * @param bool $isFrontend
     * @return string
     */
    public function getCompiledHtmlAttributes($ignoreNamespaces = array(), $isFrontend = false)
    {

        $str = array();
        $tagname = $this->tag->getTagName();

        if ( $tagname == 'img' )
        {
            // add "alt" attribute if not in "img" tag
            if ( !$this->tag->hasAttribute( 'alt' ) )
            {
                $comp = $this->tag->getCompiler();
                $attribute = new Compiler_Attribute( $this->tag, $comp, array(), array('name' => 'alt', 'value' => 'Image') );
                $this->tag->addAttribute( $attribute );
            }

            // "border" attribute not allowed in "img" tags
            if ( $this->tag->hasAttribute( 'border' ) )
            {
                $this->removeAttribute( 'border' );
            }

            // lazyload ?
            if ( $isFrontend && $this->tag->getCompiler()->getOption( 'lazy' ) )
            {
                if ( $this->tag->hasAttribute( 'src' ) )
                {
                    $comp = $this->tag->getCompiler();
                    $attr = new Compiler_Attribute( $this->tag, $comp, array(), array(
                        'name'  => 'data-src',
                        'value' => $this->tag->getAttribute( 'src' )->getValue( true )
                    ) );
                    $this->tag->addAttribute( $attr );
                    $this->tag->getAttribute( 'src' )->setValue( DUMMY_IMAGE );
                }
            }
        }

        if ( is_array( $this->tag->getAttributes() ) )
        {
            foreach ( $this->tag->getAttributes() as $data )
            {
                if ( $data instanceof Compiler_Attribute )
                {
                    if ( !$data->getNamespace() || ( $data->getNamespace() && !in_array( $data->getNamespace(), $ignoreNamespaces ) ) )
                    {
                        $_value = $data->getValue( true );
                        $_value = $this->tag->getTemplateInstance()->postCompiler( $_value, $tagname, $data->getName() );


                        $s = explode('?>', $_value);
                        $teststring = '';
                        $count = sizeof($s);

                        for ($i = 0; $i <$count; ++$i )
                        {
                            $ss = explode('<?', $s[$i]);
                            if (isset($ss[1]) || ($ss[0] && !isset($ss[1])) ) { $teststring .= $ss[0]; }
                        }

                        if (strpos($teststring, '{') !== false && strpos($teststring, '}') !== false && strpos($teststring, ':') !== false)
                        {
                            // attribut contains a json string

                            if (strpos( $teststring, '"' ) === false || (strpos( $teststring, '\"' ) !== false && strpos( $teststring, '"' ) !== false)) {
                                $_value = $this->tag->prepareValue($data->getName(), $_value);
                                $str[ ] = $data->getXmlName() . '="' . $_value . '"';
                            }
                            else {
                                $_value = $this->tag->prepareValue($data->getName(), $_value);
                                $str[ ] = $data->getXmlName() . '=\'' . $_value . '\'';
                            }
                        }
                        else {
                            if (strpos( $teststring, '"' ) === false || (strpos( $teststring, '\"' ) !== false && strpos( $teststring, '"' ) !== false)) {
                                $_value = $this->tag->prepareValue($data->getName(), $_value);
                                $str[ ] = $data->getXmlName() . '="' . $_value . '"';
                            }
                            else {
                                $_value = $this->tag->prepareValue($data->getName(), $_value);
                                $str[ ] = $data->getXmlName() . '=\'' . $_value . '\'';
                            }
                        }

/*
                        print_r($str);

                        die($_value.' test:'.$teststring);
                        // json patch
                        if ( preg_match( '#\{\s*"[a-zA-Z0-9_\-]*"\s*:[^\}]*\}#', $_value ) )
                        {
                            $str[ ] = $data->getXmlName() . '=\'' . $_value . '\'';
                        }
                        else
                        {

                            $test = preg_replace( '/<\\?.*(\\?>|$)/Us', '', $_value );

                            if ( strpos( $test, '"' ) === false )
                            {
                                $str[ ] = $data->getXmlName() . '="' . $_value . '"';
                            }
                            else
                            {
                                $str[ ] = $data->getXmlName() . '=\'' . $_value . '\'';
                            }
                        }


                        // $str[ ] = $data->getXmlName() . '="' . $_value . '"';

*/
                    }
                }
                else
                {
                    if ( empty( $data[ 'ns' ] ) || ( !empty( $data[ 'ns' ] ) && !in_array( $data[ 'ns' ], $ignoreNamespaces ) ) )
                    {

                        /*

                        $hasPhp = false;
                        $_value = $data[ 'value' ];
                        if (strpos($_value, Compiler_Abstract::PHP_OPEN) === false)
                        {
                            $hasPhp = true;
                        }

                        if (!$hasPhp)
                        {
                            if (strpos($_value, '"') === false)
                            {
                                $str[ ] = ( $data[ 'ns' ] ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ].'="' . $_value . '"';
                            }
                            else
                            {
                                $str[ ] = ( $data[ 'ns' ] ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ].'=\'' . $_value . '\'';
                            }
                        }
                        else
                        {
                            $qqoute = substr_count($_value, '"');
                            $sqoute = substr_count($_value, "'");

                            if ( $qqoute > $sqoute && strpos($_value, "'".Compiler_Abstract::PHP_OPEN) === false ) {
                                $str[ ] = ( $data[ 'ns' ] ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ]. '=\'' . $_value . '\'';
                            }
                            else {
                                $str[ ] = ( $data[ 'ns' ] ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ]. '="' . $_value . '"';
                            }
                        }
                        */


                        $data[ 'value' ] = $this->tag->getTemplateInstance()->postCompiler( $data[ 'value' ] );
                        $s = explode('?>', $data[ 'value' ]);
                        $teststring = '';
                        $count = sizeof($s);

                        for ($i = 0; $i <$count; ++$i )
                        {
                            $ss = explode('<?', $s[$i]);
                            if (isset($ss[1]) || ($ss[0] && !isset($ss[1])) ) { $teststring .= $ss[0]; }
                        }

                        if (strpos($teststring, '{') !== false && strpos($teststring, '}') !== false && strpos($teststring, ':') !== false)
                        {
                            // attribut contains a json string

                            if ( strpos( $teststring, '"' ) === false || (strpos( $teststring, '\"' ) !== false && strpos( $teststring, '"' ) !== false) )
                            {

                                $data[ 'value' ] = $this->tag->prepareValue($data[ 'name' ], $data[ 'value' ]);


                                $str[ ] = ( $data[ 'ns' ] ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ] . '="' . $data[ 'value' ] . '"';
                            }
                            else
                            {
                                $data[ 'value' ] = $this->tag->prepareValue($data[ 'name' ], $data[ 'value' ]);
                                $str[ ] = ( $data[ 'ns' ] ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ] . '=\'' . $data[ 'value' ] . '\'';
                            }
                        }
                        else {
                            if ( strpos( $teststring, '"' ) === false || (strpos( $teststring, '\"' ) !== false && strpos( $teststring, '"' ) !== false))
                            {
                                $data[ 'value' ] = $this->tag->prepareValue($data[ 'name' ], $data[ 'value' ]);
                                $str[ ] = ( $data[ 'ns' ] ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ] . '="' . $data[ 'value' ] . '"';
                            }
                            else
                            {
                                $data[ 'value' ] = $this->tag->prepareValue($data[ 'name' ], $data[ 'value' ]);
                                $str[ ] = ( $data[ 'ns' ] ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ] . '=\'' . $data[ 'value' ] . '\'';
                            }
                        }



/*







                        $_value = $data[ 'value' ];


                        // json patch
                        if ( preg_match( '#\{\s*"[a-zA-Z0-9_\-]*"\s*:[^\}]*\}#', $_value ) )
                        {
                            $str[ ] = ( $data[ 'ns' ] ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ] . '=\'' . $_value . '\'';
                        }
                        else
                        {
                            $test = $_value;
                            $spos = strpos('<\?', $_value );

                            $s = explode('<\?', $_value);
                            $teststring = '';
                            foreach ($s as $_str) {
                                $teststring .= $_str[0];
                                $s1 = explode('\?>', $_str[1] );

                                if ($s1[1]) {
                                    $teststring .= $s1[1];
                                }
                            }

                            die(''.$teststring);

                            $containPhp = false;

                            if ($spos !== false)
                            {
                                $epos = strpos('\?>', $_value );
                                if ($epos !== false) {
                                    $containPhp = true;
                                }
                                else {
                                    $epos = strpos('$this-', $_value );
                                    if (strpos('$this-', $_value ) !== false || strpos('$GLOBALS', $_value )) {
                                        $containPhp = true;
                                    }
                                }
                            }



                            $test = preg_replace( '/<\\?.*(\\?>|$)/Us', '', $_value );
                            if ( strpos( $test, '"' ) === false )
                            {
                                $str[ ] = ( $data[ 'ns' ] ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ] . '="' . $_value . '"';
                            }
                            else
                            {
                                $str[ ] = ( $data[ 'ns' ] ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ] . '=\'' . $_value . '\'';
                            }
                        }
*/

                    }
                }
            }
        }

        return implode( ' ', $str );
    }








    /**
     *
     * @return array
     */
    public function getNamespacedAttributes()
    {

        $str = array();
        if ( is_array( $this->tag->getAttributes() ) )
        {
            foreach ( $this->tag->getAttributes() as $data )
            {
                if ( $this->tag->getTemplateInstance()->isCompilerNamespace( $data->getNamespace() ) )
                {
                    $str[ ] = $data;
                }
            }
        }

        return $str;
    }

    /**
     *
     * @param array $tags
     */
    public function setCompilerTags(array $tags)
    {

        $this->compilerTags = $tags;
        $this->tag->setCompilerTags( $tags );
    }

    /**
     *
     * @return array
     */
    public function getCompilerTags()
    {

        return $this->compilerTags;
    }

    /**
     *
     * @param array $rootNodes
     * @param string $tagName /boolean $tagNs namespace string (default false)
     * @param boolean $isEmptyTag (default true and will find all empty tags)
     * @param bool $tagNs
     * @return array
     */
    public function &searchTag(&$rootNodes, $tagName, $isEmptyTag = true, $tagNs = false)
    {

        $result = array();
        $this->__processNodes( $result, $rootNodes, $tagName, $isEmptyTag, $tagNs );

        return $result;
    }

    /**
     *
     * @param         $result
     * @param         $nodes
     * @param string $tagName /boolean $tagNs namespace string (default false)
     * @param boolean $isEmptyTag (default true and will find all empty tags)
     * @param bool $tagNs
     * @internal param array $rootNodes
     */
    private function __processNodes(&$result, &$nodes, $tagName, $isEmptyTag = true, $tagNs = false)
    {

        $tagNames = explode( ',', $tagName );

        foreach ( $nodes as &$node )
        {
            switch ( $node[ 'type' ] )
            {
                case Compiler::TAG:

                    if ( in_array( $node[ 'tagname' ], $tagNames ) )
                    {
                        if ( !$tagNs && $isEmptyTag == $node[ 'singletag' ] )
                        {
                            $result[ ] = $node;
                        }
                        elseif ( $tagNs == $node[ 'namespace' ] && $isEmptyTag == $node[ 'singletag' ] )
                        {
                            $result[ ] = $node;
                        }
                    }

                    if ( isset( $node[ 'children' ] ) && is_array( $node[ 'children' ] ) )
                    {
                        $this->__processNodes( $result, $node[ 'children' ], $tagName, $isEmptyTag, $tagNs );
                    }
                    break;
            }
        }
    }

    /**
     *
     * @param string $str
     */
    public function appendStartTag($str)
    {

        if ( $this->get( 'nophp' ) || $this->tag->get( 'nophp' ) )
        {
            $this->tag->appendOpen( $str );
        }
        else
        {
            $this->tag->appendOpen( Compiler_Abstract::PHP_OPEN . ' ' . $str . ' ' . Compiler_Abstract::PHP_CLOSE );
        }
    }

    /**
     *
     * @param string $str
     */
    public function appendEndTag($str)
    {

        if ( $this->get( 'nophp' ) || $this->tag->get( 'nophp' ) )
        {
            $this->tag->appendClose( $str );
        }
        else
        {
            $this->tag->appendClose( Compiler_Abstract::PHP_OPEN . ' ' . $str . ' ' . Compiler_Abstract::PHP_CLOSE );
        }
    }

    /**
     *
     * @param string $str
     */
    public function setStartTag($str)
    {

        if ( $this->get( 'nophp' ) || $this->tag->get( 'nophp' ) )
        {
            $this->tag->setOpen( $str );
        }
        else
        {
            $this->tag->setOpen( Compiler_Abstract::PHP_OPEN . ' ' . $str . ' ' . Compiler_Abstract::PHP_CLOSE );
        }
    }

    /**
     *
     * @param string $str
     */
    public function setEndTag($str)
    {

        if ( $this->get( 'nophp' ) || $this->tag->get( 'nophp' ) )
        {
            $this->tag->setClose( $str );
        }
        else
        {
            $this->tag->setClose( Compiler_Abstract::PHP_OPEN . ' ' . $str . ' ' . Compiler_Abstract::PHP_CLOSE );
        }
    }


}