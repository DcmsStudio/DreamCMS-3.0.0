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
 * @file         Tag.php
 */

class Compiler_Tag extends Compiler_Node
{

    /**
     * @var array
     */
    private $_node = array();

    /**
     * store the full TAG
     *
     * @var string
     */
    protected $_currentTag;

    /**
     * store the full TAG
     *
     * @var array
     */
    protected $_currentTagCompiled;

    /**
     * @var bool
     */
    protected $_OpenTagCompiled = false;

    /**
     * @var bool
     */
    protected $_CloseTagCompiled = false;

    /**
     * @var null
     */
    protected $_tagContent = null;

    /**
     *
     * @var integer
     */
    protected $_type = null;

    /**
     *
     * @var integer
     */
    protected $_pos = 0;

    /**
     * @var bool
     */
    protected $_configured = false;

    protected $_attributConfig = null;

    /**
     * @param array $node
     * @param \Compiler_Compile|\Compiler_Template $obj
     */
    public function __construct(array &$node, Compiler_Compile &$obj)
    {
        $this->_node =& $node;
        $this->templateInstance =& $obj;


        $comp = $obj->getCompiler();


        $this->setCompiler( $comp );


        if ( isset( $node[ 'singletag' ] ) && $node[ 'singletag' ] )
        {
            $this->_isEmptyTag = true;
        }

        if ( $node[ 'isEndTag' ] )
        {
            $this->_isEndTag = true;
        }

        $this->_currentTag = isset( $node[ 'tag' ] ) ? $node[ 'tag' ] : false;
        $this->_tagname    = $node[ 'tagname' ];
        $this->_NS         = isset( $node[ 'namespace' ] ) ? $node[ 'namespace' ] : '';
        $this->_type       = $node[ 'type' ];

        if ( isset( $node[ 'attributes' ] ) )
        {
            $this->_attributes = $node[ 'attributes' ];
        }



    }

    /**
     * @return array
     */
    public function &getNode()
    {
        return $this->_node;
    }


    /**
     * @return bool|string
     */
    public function getCurrentTag()
    {

        return $this->_currentTag;
    }

    /**
     * @param object $processor
     * @return Compiler_Node
     */
    public function registerProcessor($processor)
    {

        $this->processor = $processor;

        return $this;
    }


    /**
     *
     * @return object the processor instance
     */
    public function &getProcessor()
    {

        return $this->processor;
    }

    /**
     *
     * @return array
     */
    public function getTagData()
    {

        return $this->_node;
    }

    public function configure()
    {

        if ( $this->_configured )
        {
            return;
        }

        if ( !is_object( $this->processor ) )
        {
            throw new Compiler_Exception( sprintf( 'Processor is not set for the tag `%s`!', (string)$this->getXmlName() ) );
        }

        if ( !method_exists( $this->processor, 'configure' ) )
        {
            throw new Compiler_Exception( sprintf( 'The current Processor `%s` has no method `configure`', (string)$this->processor ) );
        }

        $this->processor->configure();

        if ( !$this->_node[ 'isEndTag' ] )
        {
            $this->_prepareAttributInstance();
            $this->_validateAttributes();
        }
        $this->_configured = true;

    }

    /**
     *
     * @param array $options
     */
    public function setAttributeConfig($options = null)
    {

        $this->_attributConfig = $options;
    }

    /**
     *
     */
    private function _prepareAttributInstance()
    {

        if ( is_array( $this->_attributes ) )
        {
            if ( !is_array( $this->_attributConfig ) )
            {
                $this->_attributConfig = array();
            }


            $tmp = array();

            foreach ( $this->_attributes as &$data )
            {
                $tmp[ ] = new Compiler_Attribute( $this, $this->_compiler, $this->_attributConfig, $data );
            }

            $this->_attributes = $tmp;
        }
    }


    /**
     *
     * @throws Compiler_Exception
     * @return void
     */
    private function _validateAttributes()
    {

        if ( !is_array( $this->_attributConfig ) )
        {
            return;
        }

        $required = array();
        $optional = array();
        $unknown  = array();

        foreach ( $this->_attributConfig as $name => $settings )
        {
            if ( $this->_attributConfig[ $name ][ 0 ] === Compiler_Attribute::REQUIRED )
            {
                $required[ $name ] = $settings;
            }
            elseif ( $this->_attributConfig[ $name ][ 0 ] === Compiler_Attribute::OPTIONAL )
            {
                $optional[ $name ] = $settings;
            }
            else
            {
                $unknown[ $name ] = true;
            }
        }

        $attributes = $this->getAttributes();
        foreach ( $required as $name => $settings )
        {
            $ok = false;

            foreach ( $attributes as $data )
            {
                if ( !( $data instanceof Compiler_Attribute ) )
                {
                    throw new Compiler_Exception( 'Invalid Attribut instance' );
                }

                if ( $name === $data->getName() )
                {
                    $ok = true;
                    break;
                }
            }

            if ( !$ok )
            {
                throw new Compiler_Exception( 'The attribute `' . $name . '` is required in the tag: ' . htmlspecialchars( $this->getTagSource() ) . '' );
            }
        }
    }


    /**
     * @param \Compiler_Tag|\TemplateCompiler_Tag $tag
     */
    public function setEndTagInstance(Compiler_Tag $tag)
    {

        $this->_EndTagInstance = $tag;
    }

    /**
     * @param $tags
     */
    public function setCompilerTags($tags)
    {

        $this->_compilerTags = $tags;
    }

    /**
     * @return array
     */
    public function getCompilerTags()
    {

        return $this->_compilerTags;
    }

    /**
     *
     * @return string
     */
    public function getTagName()
    {

        return $this->_tagname;
    }

    /**
     * Returns the tag name (with the namespace, if possible)
     *
     * @return String
     */
    public function getXmlName()
    {

        if ( empty( $this->_NS ) )
        {
            return $this->_tagname;
        }

        return $this->_NS . ':' . $this->_tagname;
    }

    /**
     *
     * @return string
     */
    public function getTagNamespace()
    {

        return $this->_NS;
    }

    /**
     *
     * @return type
     */
    public function hasNamespace()
    {

        return $this->_NS ? true : false;
    }

    /**
     *
     * @return string
     */
    public function getTagSource()
    {

        return '<' . ( $this->isEndTag() ? '/' : '' ) . $this->getXmlName() . $this->__getAttributes( $this->getAttributes() ) . ( $this->isEmptyTag() ? '/' : '' ) . '>';
    }

    /**
     *
     * @return boolean
     */
    public function isEmptyTag()
    {

        return $this->_isEmptyTag;
    }

    /**
     *
     * @return boolean
     */
    public function isEndTag()
    {

        return $this->_isEndTag;
    }

    /**
     *
     * @return integer
     */
    public function getPos()
    {

        return $this->_pos;
    }

    /**
     *
     * @return integer
     */
    public function getBlockStartPos()
    {

        return $this->_blockstartpos;
    }

    /**
     *
     * @return integer
     */
    public function getBlockEndPos()
    {

        return $this->_blockendpos;
    }

    /**
     *
     * @param integer $index
     */
    public function setStartTagIndexPos($index = null)
    {

        $this->_startTagIndexPos = $index;
    }

    /**
     *
     * @return integer
     */
    public function getStartTagIndexPos()
    {

        return $this->_startTagIndexPos;
    }

    /**
     *
     * @param integer $index
     */
    public function setEndTagIndexPos($index = null)
    {

        $this->_endTagIndexPos = $index;
    }

    /**
     *
     * @return integer
     */
    public function getEndTagIndexPos()
    {

        return $this->_endTagIndexPos;
    }

    /**
     *
     * @param string $content
     */
    public function setTagContent($content)
    {

        $this->_tagContent = $content;
    }

    /**
     *
     * @param $node
     * @return string
     */
    public function getHtmlTagSource($node)
    {

        if ( empty( $node[ 'namespace' ] ) || $node[ 'namespace' ] === ':' )
        {
            $tag = $node[ 'tagname' ];
        }
        else
        {
            $tag = $node[ 'namespace' ] . ':' . $node[ 'tagname' ];
        }


        return '<' . ( $node[ 'isEndTag' ] ? '/' : '' ) . $tag . ( isset( $node[ 'attributes' ] ) ? $this->__getAttributes( $node[ 'attributes' ] ) : '' ) . ( $node[ 'singletag' ] ? '/' : '' ) . '>';
    }

    /**
     *
     * @param array $nodes
     */
    private function processNodes(&$nodes)
    {

        static $_cdata;

        if ( is_bool( $_cdata ) )
        {
            $_cdata = false;
        }

        foreach ( $nodes as &$node )
        {
            switch ( $node[ 'type' ] )
            {
                case Compiler::TAG:
                    $this->_tagContent .= $this->getHtmlTagSource( $node );
                    break;

                case Compiler::CDATA_OPEN :
                    $this->_tagContent .= '<![CDATA[';

                    $_cdata = true;

                    break;
                case Compiler::CDATA_CLOSE :
                    $this->_tagContent .= ']]>';
                    $_cdata = false;
                    break;
                case Compiler::CDATA:





                    if ( !$_cdata )
                    {
                        #$this->_tagContent .= '<![CDATA[' . $this->_compiler->getTemplate()->parseEntities($node[ 'value' ]) . ']]>';
                    }
                    else
                    {
                        #$this->_tagContent .= $node[ 'value' ];
                    }

                    $this->_tagContent .= $node[ 'value' ];
                    break;

                case Compiler::COMMENT:

                    if ( $this->_compiler->printComments )
                    {
                        $this->_tagContent .= '<!-- ' . $this->templateInstance->parseSpecialChars( (string)$node[ 'value' ] ) . ' -->';
                    }

                    break;
                case Compiler::TEXT:
                    $this->_tagContent .= $node[ 'value' ];
                    break;
            }


            if ( isset( $node[ 'children' ] ) && is_array( $node[ 'children' ] ) )
            {
                $this->processNodes( $node[ 'children' ] );
            }
        }
    }

    /**
     *
     * @return string
     */
    public function getTagContent()
    {

        if ( $this->_isEmptyTag || $this->_isEndTag || !$this->hasChildren() )
        {
            return '';
        }


        if ( $this->_tagContent === null )
        {
            $arr = $this->getChildren();
            $this->processNodes( $arr );
        }

        return $this->_tagContent;
    }

    /**
     * @param string $attributname
     * @param string $value
     * @return string
     */
    public function prepareValue( $attributname, $value )
    {
        $tagname = $this->getTagName();

        if ( $tagname && $this->getCompiler()->hasFilter($tagname, $attributname) && $value )
        {
            $s = explode(Compiler::PHP_CLOSE, $value);

            $count = sizeof($s);
            $gencode = '';
            for ($i = 0; $i < $count; ++$i) {
                if (!empty($s[$i]))
                {
                    $r = explode(Compiler::PHP_OPEN, $s[$i]);
                    $gencode .= ($gencode ? ' .' : '');
                    $gencode .= $this->getCompiler()->getTemplate()->getCompilerProcess()->compileString( $r[0] );

                    if (isset($r[1]) && !empty($r[1]) )
                    {
                        $gencode .= '. (' . preg_replace('/;$/', '', preg_replace('/^echo\s{1,}/', '', trim($r[1])) ) .') ';
                    }
                }
            }

            $gencode = Compiler::PHP_OPEN .'echo Compiler::filterContent(\''.$tagname.'\', '.( $attributname ? '\''. $attributname .'\'' : 'null' ).', '. trim($gencode) .')'. Compiler::PHP_CLOSE;

            return $gencode;
        }

        return $value;
    }

    /**
     *
     * @param array $attributes
     * @return string
     */
    private function __getAttributes($attributes)
    {
        $str = array();
        if ( is_array( $attributes ) )
        {
            foreach ( $attributes as $data )
            {
                if ( $data instanceof Compiler_Attribute )
                {
                    $_value = $data->getValue( true );
                    $_value = $this->templateInstance->postCompiler( $_value );


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
                            $_value = $this->prepareValue($data->getName(), $_value);
                            $str[ ] = $data->getXmlName() . '="' . $_value . '"';
                        }
                        else {
                            $_value = $this->prepareValue($data->getName(), $_value);
                            $str[ ] = $data->getXmlName() . '=\'' . $_value . '\'';
                        }
                    }
                    else {
                        if (strpos( $teststring, '"' ) === false || (strpos( $teststring, '\"' ) !== false && strpos( $teststring, '"' ) !== false)) {
                            $_value = $this->prepareValue($data->getName(), $_value);
                            $str[ ] = $data->getXmlName() . '="' . $_value . '"';
                        }
                        else {
                            $_value = $this->prepareValue($data->getName(), $_value);
                            $str[ ] = $data->getXmlName() . '=\'' . $_value . '\'';
                        }
                    }

/*
                    // json patch
                    if ( preg_match( '#\{\s*"[a-zA-Z0-9_\-]*"\s*:[^\}]*\}#', $v ) )
                    {
                        $str[ ] = ( $data[ 'ns' ] ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ] . '=\'' . $v . '\'';
                    }
                    else
                    {
                        $test = preg_replace('/<\\?.*(\\?>|$)/Us', '',$v);

                        if ( strpos( $test, '"' ) === false )
                        {
                            $str[ ] = $data->getXmlName() . '="' . $v . '"';
                        }
                        else
                        {
                            $str[ ] = $data->getXmlName() . '=\'' . $v . '\'';
                        }
                    }
*/
                    // $str[ ] = $data->getXmlName() . '="' . $data->getValue(true) . '"';
                }
                else
                {
                    $data[ 'value' ] = $this->templateInstance->postCompiler( $data[ 'value' ] );

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
                            $data[ 'value' ] = $this->prepareValue($data[ 'name' ], $data[ 'value' ]);
                            $str[ ] = ( $data[ 'ns' ] ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ] . '="' . $data[ 'value' ] . '"';
                        }
                        else
                        {
                            $data[ 'value' ] = $this->prepareValue($data[ 'name' ], $data[ 'value' ]);
                            $str[ ] = ( $data[ 'ns' ] ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ] . '=\'' . $data[ 'value' ] . '\'';
                        }
                    }
                    else {
                        if ( strpos( $teststring, '"' ) === false || (strpos( $teststring, '\"' ) !== false && strpos( $teststring, '"' ) !== false))
                        {
                            $data[ 'value' ] = $this->prepareValue($data[ 'name' ], $data[ 'value' ]);
                            $str[ ] = ( $data[ 'ns' ] ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ] . '="' . $data[ 'value' ] . '"';
                        }
                        else
                        {
                            $data[ 'value' ] = $this->prepareValue($data[ 'name' ], $data[ 'value' ]);
                            $str[ ] = ( $data[ 'ns' ] ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ] . '=\'' . $data[ 'value' ] . '\'';
                        }
                    }


                    /*


                    $v = $data[ 'value' ];


                    if ( preg_match( '#\{\s*"[a-zA-Z0-9_\-]*"\s*:[^\}]*\}#', $v ) )
                    {
                        $str[ ] = ( !empty( $data[ 'ns' ] ) && $data[ 'ns' ] != ':' ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ] . '=\'' . $v . '\'';
                    }
                    else
                    {
                        $test = preg_replace('/<\\?.*(\\?>|$)/Us', '',$v);

                        if ( strpos( $test, '"' ) === false )
                        {
                            $str[ ] = ( !empty( $data[ 'ns' ] ) && $data[ 'ns' ] != ':' ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ] . '="' . $v . '"';
                        }
                        else
                        {
                            $str[ ] = ( !empty( $data[ 'ns' ] ) && $data[ 'ns' ] != ':' ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ] . '=\'' . $v . '\'';
                        }
                    }

                    //$str[ ] = ( !empty( $data[ 'ns' ] ) && $data[ 'ns' ] != ':' ? $data[ 'ns' ] . ':' : '' ) . $data[ 'name' ] . '="' . str_replace( '"', '&quote;', $data[ 'value' ] ) . '"';
                */
                }
            }
        }

        return ( count( $str ) ? ' ' : '' ) . implode( ' ', $str );
    }
    /**
     *
     * @return array
     */
    public function &getAttributesArray()
    {
        $out = array();

        if ( is_array( $this->_attributes ) )
        {
            foreach ( $this->_attributes as $data )
            {
                if ( $data instanceof Compiler_Attribute )
                {
                    $out[$data->getXmlName()] = $data->getValue( true );
                }
                else {
                    $out = array_merge($out, $data);
                }
            }
        }

        return $out;
    }
    /**
     *
     * @return array
     */
    public function &getAttributes()
    {
        return $this->_attributes;
    }




    /**
     * returns the value if exists the attribute
     *
     * @param string $name
     * @param bool $namespace
     * @throws Compiler_Exception
     * @return Compiler_Attribute|bool
     */
    public function &getAttribute($name, $namespace = false)
    {

        if ( is_array( $this->_attributes ) )
        {
            foreach ( $this->_attributes as &$data )
            {
                if ( $data instanceof Compiler_Attribute )
                {
                    if ( $data->getName() == $name )
                    {
                        if ( $namespace && $namespace == $data->getNamespace() )
                        {
                            return $data;
                        }
                        elseif ( !$namespace )
                        {
                            return $data;
                        }
                    }
                }
                else
                {
                    throw new Compiler_Exception( sprintf( 'The attribut "%s" for the tag &lt;%s&gt; not exists in the Template "%s"!', ( $namespace ? $namespace . ':' : '' ) . $name, $this->_currentTag, $this->templateInstance->getCurrentTemplateFilename() ) );
                }
            }
        }

        $ref = false;

        return $ref;
    }

    /**
     * Adds a new attribute to the tag.
     *
     * @param Compiler_Attribute $attribute The new attribute.
     * @throws Compiler_Exception
     */
    public function addAttribute(Compiler_Attribute $attribute)
    {

        if ( !is_array( $this->_attributes ) )
        {
            $this->_attributes = array();
        }

        if (!$attribute->getXmlName())
        {
            throw new Compiler_Exception( sprintf('Invalid attribut name for tag %s', $this->getXmlName() ));
        }

        $this->_attributes[ $attribute->getXmlName() ] = $attribute;
    }

    /**
     * check if exists a attribute in tag
     *
     * @param string $name
     * @return boolean
     */
    public function hasAttribute($name)
    {

        if ( isset( $this->_attributes ) && is_array( $this->_attributes ) )
        {
            foreach ( $this->_attributes as $data )
            {
                if ( $data instanceof Compiler_Attribute )
                {
                    if ( $data->getName() === $name )
                    {
                        return true;
                    }
                }
                else
                {
                    if ( $data[ 'name' ] === $name )
                    {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Hase the tag a namespaced attributes
     *
     * @throws Compiler_Exception
     * @return string/boolean
     */
    public function hasNamespacedAttributes()
    {
        if ( isset( $this->_attributes ) && is_array( $this->_attributes ) )
        {
            foreach ( $this->_attributes as $attr )
            {
                if ( $attr instanceof Compiler_Attribute )
                {
                    if ( $this->templateInstance->isCompilerNamespace( $attr->getNamespace() ) )
                    {
                        return $attr->getNamespace();
                    }
                }
                else
                {
                    throw new Compiler_Exception( 'Empty attribut instance!' );
                }
            }
        }

        return false;
    }

    /**
     * check if exists a attribute namespace
     * if exists a namespace will return the namespace of the attribute
     *
     * @param string $name
     * @throws Compiler_Exception
     * @return string/boolean
     */
    public function attributeHasNamespace($name)
    {

        if ( isset( $this->_attributes ) && is_array( $this->_attributes ) )
        {
            foreach ( $this->_attributes as $attr )
            {
                if ( $attr instanceof Compiler_Attribute )
                {
                    if ( $attr->getName() == $name )
                    {
                        return $attr->getNamespace();
                    }
                }
                else
                {
                    throw new Compiler_Exception( 'Empty attribut instance!' );
                }
            }
        }

        return false;
    }

    /**
     *
     * @param string $name
     * @param bool|string $namespace default is false
     * @throws Compiler_Exception
     * @return array/null
     */
    public function getAttributesByName($name, $namespace = false)
    {

        if ( !$this->hasAttribute( $name ) )
        {
            return null;
        }

        if ( $namespace !== false )
        {
            if ( !$this->hasNamespacedAttributes() )
            {
                return null;
            }
        }

        $result     = array();
        $attributes = $this->getAttributes();
        foreach ( $attributes as $attr )
        {
            if ( $attr instanceof Compiler_Attribute )
            {
                if ( $attr->getName() == $name )
                {
                    if ( $namespace && $namespace == $attr->getNamespace() )
                    {
                        $result[ ] = $attr;
                    }
                    else
                    {
                        $result[ ] = $attr;
                    }
                }
            }
            else
            {
                throw new Compiler_Exception( 'Empty attribut instance!' );
            }
        }

        return $result;
    }

    /**
     *
     * @param string $name
     * @param bool|string $namespace default is false
     * @throws Compiler_Exception
     */
    public function removeAttribute($name, $namespace = false)
    {

        $attributes = & $this->getAttributes();
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
    }

    /**
     *
     * @param string $str
     */
    public function appendOpen($str = '')
    {

        if ( !is_array( $this->_OpenTagCompiled ) )
        {
            $this->_OpenTagCompiled = array();
        }
        $this->_OpenTagCompiled[ ] = $str;
    }

    /**
     *
     * @param string $str
     */
    public function appendClose($str = '')
    {

        if ( !is_array( $this->_CloseTagCompiled ) )
        {
            $this->_CloseTagCompiled = array();
        }
        $this->_CloseTagCompiled[ ] = $str;
    }

    /**
     *
     * @param string $str
     */
    public function setOpen($str = '')
    {

        $this->_OpenTagCompiled = array(0 => $str);
    }

    /**
     *
     * @param string $str
     */
    public function setClose($str = '')
    {

        $this->_CloseTagCompiled = array(0 => $str);
    }

    /**
     * returns the compiled open tag
     *
     * @return string
     */
    public function getCompiledOpenTag()
    {

        return is_array( $this->_OpenTagCompiled ) ? implode( '', $this->_OpenTagCompiled ) : '';
    }

    /**
     * returns the compiled close tag
     *
     * @return string
     */
    public function getCompiledCloseTag()
    {

        return is_array( $this->_CloseTagCompiled ) ? implode( '', $this->_CloseTagCompiled ) : '';
    }


}