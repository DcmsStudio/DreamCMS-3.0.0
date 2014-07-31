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
 * @file        Sql.php

 */
class Compiler_Tag_Sql extends Compiler_Tag_Abstract
{


    /**
     *
     */
    public function configure()
    {
        $this->tag->setAttributeConfig(
                array(
                    'assign' => array(
                        Compiler_Attribute::OPTIONAL,
                        Compiler_Attribute::HARD_STRING ),
                    'return' => array(
	                    Compiler_Attribute::OPTIONAL,
	                    Compiler_Attribute::HARD_STRING )
                )
        );
    }

    public function process()
    {

        $assign = $this->getAttributeValue( 'assign' );
        $return = $this->getAttributeValue( 'return' );

        if ( !$this->tag->isEndTag() && (!$assign && !$return) )
        {
            throw new Compiler_Exception( 'The tag "' . $this->tag->getXmlName() . '" must have a attribute named `assign` or `return`. Tag giving: ' . htmlspecialchars( $this->tag->getTagSource() ) );
        }

        $_assign = '';
        if ( $assign !== null && $assign != '' )
        {
            $_assign = '$this->dat[\'' . $assign . '\'] = ';
        }
        if ( $return !== null && $return != '' )
        {
            $_assign = '$this->dat[\'' . $return . '\'] = ';
        }


        $this->set( 'nophp', true );
        $this->setStartTag( Compiler_Abstract::PHP_OPEN . '$sql = <<<SQL' . "\n" );
        $this->setEndTag( "\nSQL;\nif (trim(\$sql)) { " . $_assign . 'Database::getInstance()->query($sql)->fetchAll(); } else { ' . $_assign . ' array(); } $sql = null;' . Compiler_Abstract::PHP_CLOSE );
    }

}
