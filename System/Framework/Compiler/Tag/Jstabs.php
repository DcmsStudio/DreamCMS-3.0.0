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
 * @category    Template Engine
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Jstabs.php
 */
class Compiler_Tag_Jstabs extends Compiler_Tag_Abstract
{

    /**
     * @var string
     */
    private $_defaultType = 'cdata';

    /**
     * @var int
     */
    private static $index = 0;

    /**
     *
     */
    public function configure()
    {
        $this->tag->setAttributeConfig(
                array(
                    'tabs'     => array(
                        Compiler_Attribute::REQUIRED,
                        Compiler_Attribute::HARD_STRING ),
                    'usetrans' => array(
	                    Compiler_Attribute::OPTIONAL,
	                    Compiler_Attribute::BOOL ),
                    'default'  => array(
	                    Compiler_Attribute::OPTIONAL,
	                    Compiler_Attribute::HARD_STRING ),
                )
        );
    }

    public function process()
    {
        $usetrans = $this->getAttributeValue( 'usetrans' );
        if ( !$usetrans )
        {
            
        }

        $default = $this->getAttributeValue( 'default' );
        if ( !$default )
        {
            
        }

        $tabs = $this->getAttributeValue( 'tabs' );

	    $templateInstance = $this->tag->getTemplateInstance();

        self::$index++;
        $newTabIndex = self::$index;

        $_parsed = array();
        foreach ( explode( ',', $tabs ) as $tab )
        {


            if ( preg_match( '/\{?' . $templateInstance->_rVariable . '\}?/xS', $tab ) || preg_match( $templateInstance->_rExpressionTag, $tab ) )
            {

                if ( substr( $tab, 0, 1 ) == '{' )
                {
                    $tab = substr( $tab, 1 );
                }


                if ( substr( $tab, -1 ) == '}' )
                {
                    $tab = substr( $tab, 0, -1 );
                }


                $_result = $templateInstance->compileExpression( $tab );
                $result[ 0 ] = (is_array( $_result ) ? $_result[ 0 ] : $_result);
            }
            elseif ( preg_match( '/\{?' . $templateInstance->_rFunctions . '\}?/xS', $tab ) )
            {
                if ( substr( $tab, 0, 1 ) == '{' )
                {
                    $tab = substr( $tab, 1 );
                }
                if ( substr( $tab, -1 ) == '}' )
                {
                    $tab = substr( $tab, 0, -1 );
                }


                $_result = $templateInstance->postCompileFunctions( $tab );
                $result[ 0 ] = (is_array( $_result ) ? $_result[ 0 ] : $_result);
            }
            else
            {

                $result[ 0 ] = $templateInstance->compileString( $tab );
            }

            $_parsed[] = '$_tabClsInstance' . $newTabIndex . '->addTab(' . $result[ 0 ] . ', false);';
        }


        $this->setStartTag( '$_tabCls' . $newTabIndex . ' = new Javascript(Javascript::TAB);$_tabClsInstance' . $newTabIndex . '=$_tabCls' . $newTabIndex . '->getInstance(); ' . implode( '', $_parsed ) . '
echo $_tabClsInstance' . $newTabIndex . '->create()->getHtml();
if ( !isset($GLOBALS[\'tabs_js_files\']) ) { echo $_tabClsInstance' . $newTabIndex . '->getScript(); $GLOBALS[\'tabs_js_files\'] = true; }
' );
    }

}
