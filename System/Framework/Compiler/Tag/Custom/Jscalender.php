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
 * @file        Jscalender.php
 */
class Compiler_Tag_Custom_Jscalender extends Compiler_Tag_Abstract
{

    /**
     * @var
     */
    static $_id;

    /**
     *
     */
    public function configure()
    {
        $this->tag->setAttributeConfig(
                array(
                    'name'       => array(
                        Compiler_Attribute::REQUIRED,
                        Compiler_Attribute::HARD_STRING ),
                    'showtime'   => array(
	                    Compiler_Attribute::OPTIONAL,
	                    Compiler_Attribute::BOOL ),
                    'firstday'   => array(
	                    Compiler_Attribute::OPTIONAL,
	                    Compiler_Attribute::NUMBER ),
                    'timeformat' => array(
	                    Compiler_Attribute::OPTIONAL,
	                    Compiler_Attribute::HARD_STRING ),
                    'value'      => array(
	                    Compiler_Attribute::OPTIONAL,
                        Compiler_Attribute::STRING )
                )
        );
    }

    public function process()
    {
        self::$_id++;

        $name = $this->getAttributeValue( 'name' );
        $_value = $this->getAttributeValue( 'value' );
        $showtime = $this->getAttributeValue( 'showtime' );

        $_timeformat = '%d.%m.%Y';
        if ( !empty( $showtime ) )
        {
            $_timeformat = $timeformat;
        }

        $_timeformat = '%d.%m.%Y';

        $id_name = self::$_id;

        $start = '<input type="text" name="' . $name . '" id="cal_' . $id_name . '" value="';
        $end = '" class="cal_input"/>';

        $value = Compiler_Abstract::PHP_OPEN . '$_value=' . $_value[ 0 ] . ';if($_value){echo strftime("' . $_timeformat . '", $_value);}'
                . Compiler_Abstract::PHP_CLOSE;

        $this->set( 'nophp', true );
        $this->setStartTag( $start . $value . $end );
    }

    public function postProcess()
    {
        $this->set( 'nophp', false );
    }

}

?>