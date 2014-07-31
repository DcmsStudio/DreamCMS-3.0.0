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
 * @file         Lazyload.php
 */


class Compiler_Tag_Custom_Lazyload extends Compiler_Tag_Abstract
{
    /**
     *
     */
    public function configure()
    {
        $this->tag->setAttributeConfig(
            array(
                'enable'     => array(
                    Compiler_Attribute::REQUIRED,
                    Compiler_Attribute::BOOL )
            )
        );
    }

    public function process()
    {
        if ( $this->tag->isEndTag() )
        {
            return;
        }

        $enable    = $this->getAttributeValue('enable');

        if ($enable)
        {
            $this->tag->getCompiler()->setOption('lazy', true);
        }
        else
        {
            $this->tag->getCompiler()->setOption('lazy', null); // reset
        }

    }
}