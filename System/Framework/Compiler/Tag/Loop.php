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
 * @file         Loop.php
 */

class Compiler_Tag_Loop extends Compiler_Tag_Abstract
{
	/**
	 * The current nesting level of "cp:loop"
	 * @var Integer
	 */
	protected static $_nesting = 1;
	protected $__index = 0;

    protected static $_scopecache = array();

	/**
	 *
	 */
	public function configure()
	{
		$this->tag->setAttributeConfig(
			array(
			     'name'  => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::ID ),
			     'key'   => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::ID ),
			     'index' => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::ID ),
			     // 'separator' => array(TemplateCompiler_Attribute::OPTIONAL, TemplateCompiler_Attribute::EXPRESSION)
			)
		);
	}

    /**
     * @return mixed
     */
    public function process()
	{
		$this->set( 'nophp', false );
		$template = $this->compiler->getTemplate();

		$name = $this->getAttributeValue( 'name' );
		$key = $this->getAttributeValue( 'key' );



		self::$_nesting++;
		#Compiler::$cnt++;
		#self::$_nesting = Compiler::$cnt;
		$this->__index = self::$_nesting;

		$_realKey = $key;
		$_realName = $name;

		if ( substr( $name, 0, 1 ) !== '$' )
		{

			$parts = explode( '.', ltrim( $name, '.' ) );

			$_key = '';
			$_useScope = '$this->dat';
			$scopeFound = false;

			foreach ( $parts as $idx => $part )
			{
				if ( !$scopeFound && ($scope = $this->compiler->getScope( $part )) !== null )
				{
					$_useScope = '$' . $scope;


				#	 $key = $part;
					$scopeFound = true;
				}
				else
				{
					$_key .= '[' . str_replace( '"', "'", var_export( $part, true ) ) . ']';
				}
			}

			$name = $_useScope . $_key;
		}
		else
		{
			$_realName = substr( $name, 1 );
		}


	#	echo $_realName . ' ' . $key."\n";

		$this->set( 'scopeName', $_realKey );
		$this->set( 'nophp', false );


		$index = $this->getAttributeValue( 'index' );
		if ( !is_string( $index ) )
		{
			$index = null;
		}

        if ($this->compiler->getScope($key) !== null) {

            $v = $this->compiler->getScope($key);
#die($v);
            do {
                self::$_nesting++;
                $this->__index = self::$_nesting;
                $v = $this->compiler->getScope($key);
            }
            while ($v == '__lo' . self::$_nesting . '_val');
        }

		$codeBegin = '
                if (is_array(' . $name . ') && ($__lototal' . self::$_nesting . ' = sizeof(' . $name . ')) > 0 ){
                    $yy' . self::$_nesting . ' = 0;
                    ' . (!is_string( $index ) ? '$__lo' . self::$_nesting . '_x = 1;' : '') . '
                    foreach(' . $name . ' as ' . (is_string( $index ) ? '$__lo' . self::$_nesting . '_idx => ' : '') . '&$__lo' . self::$_nesting . '_val){
                    //for ($_yy' . self::$_nesting . '=0; $_yy' . self::$_nesting . '<$__lototal' . self::$_nesting . ';++$_yy' . self::$_nesting . ') {
                    //while($spl' . self::$_nesting . '->valid()) {
                      // $__lo' . self::$_nesting . '_val = $spl' . self::$_nesting . '->dequeue();//' . $name . '[$_yy' . self::$_nesting . '];
                        $__lo' . self::$_nesting . '_val[\'next_' . $key . '\'] = (isset(' . $name . '[$yy' . self::$_nesting . ' + 1]) ? ' . $name . '[$yy' . self::$_nesting . ' + 1] : array());
                        $__lo' . self::$_nesting . '_val[\'' . $key . '_index\'] = $yy' . self::$_nesting . ' + 1;
                        $__lo' . self::$_nesting . '_val[\'' . $key . '_total\'] = $__lototal' . self::$_nesting . ';
                        $__lo' . self::$_nesting . '_val[\'__index\'] = $__lo' . self::$_nesting . '_val[\'' . $key . '_index\'];
                        ++$yy' . self::$_nesting . ';
';








		$this->compiler->addScope( $key, '__lo' . self::$_nesting . '_val', $this->tag->getTagSource() );

        self::$_scopecache[self::$_nesting] = array('__lo' . self::$_nesting . '_val', $key);


		if ( is_string( $index ) && $index != '' )
		{
			$this->compiler->addScope( $index, '__lo' . self::$_nesting . '_idx', $this->tag->getTagSource() );
		#	$this->compiler->addScope( 'Row'.$this->__index, '__lo' . self::$_nesting . '_idx', $this->__index );
		}

		$this->setStartTag( $codeBegin );
		$this->setEndTag( ' $__lo' . self::$_nesting . '_x++;} $__lo' . self::$_nesting . '_val = null; }' );
		return $_realKey;
	}


	/**
	 * remove the used scope
	 */
	public function postProcess($level = 0)
	{
        $key = $this->getAttributeValue( 'key' );
		#self::$_nesting++;
        /*
                $template = $this->compiler->getTemplate();
                $name = $this->getAttributeVal( 'name' );
                /*
                $parts = explode( '.', ltrim( $name, '.' ) );
                $key = $this->getAttributeValue( 'key' );
                $index = $this->getAttributeValue( 'index' );

                if (count($parts)>1) {
                    $key = $parts[0];
                }
        */



		$index = $this->getAttributeValue( 'index' );

		if ( !is_string( $index ) )
		{
			$index = null;
		}
        /*

		$_realName = $name;
		if ( substr( $name, 0, 1 ) == '$' )
		{
			$_realName = substr( $name, 1 );
		}
*/
	//	$scopename = $this->get( 'scopeName' );
		//self::$_nesting++;
	#	$this->tag->getTemplateInstance()->removeScope($key);
/*
		if ( $level > 2) {
			if ( is_string( $index ) )
			{
				$this->tag->getTemplateInstance()->removeScope( $index, $this->__index );
				$this->tag->getTemplateInstance()->removeScope( 'Row', $this->__index );
			}
			else {
				$this->tag->getTemplateInstance()->removeScope( 'Row', $this->__index );
			}

			$this->tag->getTemplateInstance()->removeLastScope($key, $this->__index);
		}
*/

			if ( is_string( $index ) && $index != '' )
			{
				$this->compiler->removeScope( $index );
			}

        $v = $this->compiler->getScope($key);
        if (sizeof(self::$_scopecache))
        {
        #    $c = array_shift(self::$_scopecache);
        #    $this->compiler->removeScope($c[1], $c[0]);
        }

			#$this->compiler->removeScope('Row'.$this->__index, $this->__index);
	#		$this->compiler->removeScope($key);

		$this->set( 'nophp', false );
		#$this->_compiler->removeScope($index);
	}

}