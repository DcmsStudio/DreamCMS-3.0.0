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
 * @file        Literal.php
 */
class Compiler_Tag_Literal extends Compiler_Tag_Abstract
{

    /**
     * @var string
     */
    private $_defaultType = 'cdata';

    /**
     *
     */
    public function configure()
    {
        $this->tag->setAttributeConfig(
                array(
                    'type' => array(
                        Compiler_Attribute::OPTIONAL,
                        Compiler_Attribute::HARD_STRING
                    )
                )
        );
    }

    public function process()
    {

        // First, disable displaying CDATA around all CDATA text parts found
        $this->disableCDATA( $this->tag, false );

        $type = $this->getAttributeValue( 'type' );
        $type = ($type ? strtolower($type) : $this->_defaultType);
        $this->set( 'nophp', true );

        switch ( $type )
        {
            case 'comment':
                $this->set( 'comment', true );
                $this->setStartTag( '<!--' );
                $this->setEndTag( '-->' );
                break;

            case 'cdata_comment':
                $this->set( 'cdata', true );
                $this->setStartTag( "\n" . '/*<![CDATA[*/' . "\n" );
                $this->setEndTag( "\n" . '/*]]>*/' . "\n" );

                break;

            case 'cdata':
            default:
                $this->set( 'cdata', true );
                $this->setStartTag( '<![CDATA[' );
                $this->setEndTag( ']]>' );

                break;
        }
    }

    public function postProcess()
    {
        $this->set( 'comment', false );
        $this->set( 'cdata', false );
	    $this->set( 'nophp', false );
    }

    /**
     * Used on a node, it looks for the CDATA elements and disables the
     * CDATA flag on them. Moreover, it allows to disable the text entitizing.
     *
     * @see Compiler_Template::removeCdata()
     * @param Compiler_Tag $node The scanned node
     * @param boolean $noEntitize optional The entitizing flag.
     */
    private function disableCDATA( Compiler_Tag $node, $noEntitize = false )
    {
        $this->tag->getTemplateInstance()->removeCdata( $node, !$noEntitize );
    }

}
