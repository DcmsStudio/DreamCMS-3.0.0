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
 * @file        Feedlink.php
 *
 */
class Compiler_Tag_Feedlink extends Compiler_Tag_Abstract
{

    /**
     *
     */
    public function configure()
    {
        $this->tag->setAttributeConfig(
                array(
                    'url'       => array(
                        Compiler_Attribute::REQUIRED,
                        Compiler_Attribute::HARD_STRING ),
                    'mode'      => array(
	                    Compiler_Attribute::REQUIRED,
                        Compiler_Attribute::HARD_STRING ),
                    'cacheable' => array(
	                    Compiler_Attribute::OPTIONAL,
	                    Compiler_Attribute::BOOL ),
                    'cachetime' => array(
	                    Compiler_Attribute::OPTIONAL,
                        Compiler_Attribute::NUMBER ),
                    'icon'      => array(
	                    Compiler_Attribute::OPTIONAL,
                        Compiler_Attribute::BOOL ),
                    'label'     => array(
	                    Compiler_Attribute::OPTIONAL,
                        Compiler_Attribute::STRING ),
                )
        );
    }

    /**
     *
     * @return void
     */
    public function process()
    {
        $sourceTag = $this->tag->getTagSource();

        $url = $this->getAttributeValue( 'url' );
        $mode = $this->getAttributeValue( 'mode' );

        $cacheable = $this->getAttributeValue( 'cacheable' );
        $cachetime = $this->getAttributeValue( 'cachetime' );

        $icon = $this->getAttributeValue( 'icon' );
        $label = $this->getAttributeValue( 'label' );

        $extension = 'rss';
        if ( $mode )
        {
            switch ( strtolower( $mode ) )
            {
                case 'rss':
                default:
                    $defaultLabel = "trans('RSS Feed')";
                    break;
                case 'atom':
                    $defaultLabel = "trans('Atom Feed')";
                    $extension = 'atom';
                    break;
            }
        }

        $useIcon = '';
        if ( $icon )
        {
            $useIcon = '<span class="feedicon fa fa-rss '.$extension.'"></span> ';
        }

        $str = Compiler_Abstract::PHP_OPEN . ' $_loc = Api::feedLink(\'' . addcslashes( $url, "'" ) . '\', \'' . $extension . '\'); ' . Compiler_Abstract::PHP_CLOSE;
        $str .= '<a href="' . Compiler_Abstract::PHP_OPEN . ' echo $_loc; ' . Compiler_Abstract::PHP_CLOSE . '"';
        $str .= ' class="feed-btn">'.$useIcon;

        if ( isset($label[0]) )
        {
            $str .= Compiler_Abstract::PHP_OPEN . ' echo '.$label[0].';' . Compiler_Abstract::PHP_CLOSE;
        }
        else
        {
            $str .= Compiler_Abstract::PHP_OPEN . ' echo ' . $defaultLabel . '; ' . Compiler_Abstract::PHP_CLOSE;
        }
        $str .= '</a>';


        $this->set( 'nophp', true );
        $this->setStartTag( $str );
    }

}

?>