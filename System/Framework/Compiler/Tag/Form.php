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
 * @file        Form.php
 */
class Compiler_Tag_Form extends Compiler_Tag_Abstract
{

    /**
     * @var int
     */
    protected static $cnt = 0;


    public function process()
    {
        $floodcheck = $this->getAttributeValue( 'floodcheck', false, false, Compiler_Attribute::BOOL );
        if ( $floodcheck )
        {
            $this->removeAttribute( 'floodcheck' );
        }

        $name = $this->getAttributeValue( 'name', false, false, Compiler_Attribute::HARD_STRING );
        $attr = $this->getCompiledHtmlAttributes();
        if ( !empty( $attr ) )
        {
            $attr = ' ' . $attr;
        }

        $this->set( 'nophp', true );


        $str = '<form' . $attr . ($this->tag->isEmptyTag() ? ' /' : '') . '>';

        $str .= Compiler_Abstract::PHP_OPEN . '
                Compiler_Library::enableFloodcheck();
                $uuid = Compiler_Library::UUIDv3( Compiler_Library::makeUUIDv3( md5("' . $name . '" . time()) ), "' . $attr[ 'name' ] . '" );
                if (class_exists(\'Session\', false)) { Session::save("floodCheck-' . $name . '", $uuid); }
                ' . Compiler_Abstract::PHP_CLOSE . '<input type="hidden" name="floodCheck" value="' . Compiler_Abstract::PHP_OPEN . ' echo $uuid;' . Compiler_Abstract::PHP_CLOSE . '" />';
        $str .= '<input type="hidden" name="floodCheckName" value="' . $name . '" />';


        $this->appendStartTag( $str );
        $this->appendEndTag( '</form>' );
    }

}
