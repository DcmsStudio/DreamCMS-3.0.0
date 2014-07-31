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
 * @file         Else.php
 */
class Compiler_Tag_Else extends Compiler_Tag_Abstract
{


    public function process()
    {

        $prev = $this->tag->getPrev();


        if ( $prev )
        {

            if ( $prev instanceof Compiler_Tag )
            {
                $prevName = $prev->getTagName();
                $allowed  = Compiler_Helper::getAllowedPrevTags();

                if ( !isset( $allowed[ $prevName ] ) )
                {

                    $keys = array_keys( $allowed );
                    $keys = '&quot;' . Compiler::TAGNAMESPACE . ':' . implode( '&quot;, &quot;' . Compiler::TAGNAMESPACE . ':', $keys ) . '&quot;';
                    throw new Compiler_Exception( 'The &quot;' . Compiler::TAGNAMESPACE . ':else&quot; is not allowed. you can use only after tag ' . $keys . '. Current parent tag is: ' . $prev->getXmlName() );
                }
            }
            else
            {

                $allowed = Compiler_Helper::getAllowedPrevTags();


                $keys = array_keys( $allowed );
                $keys = '&quot;' . Compiler::TAGNAMESPACE . ':' . implode( '&quot;, &quot;' . Compiler::TAGNAMESPACE . ':', $keys ) . '&quot;';
                throw new Compiler_Exception( 'The &quot;' . Compiler::TAGNAMESPACE . ':else&quot; is not allowed. you can use only after tag ' . $keys . '. Current parent tag is: ' . $prev->getXmlName() );
            }
        }

        $this->setStartTag( 'else {' );
        $this->setEndTag( ' } ' );
    }
}