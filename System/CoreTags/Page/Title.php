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
 * @package     CoreTags
 * @version     3.0.0 Beta
 * @category    Core Tag
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Title.php
 *
 */

class Tag_Page_Title extends Provider_Abstract
{

	/**
	 * @param $tag
	 * @return string
	 */
	public static function render($tag)
    {
        $dcms = self::$dcms;

        if ( is_numeric( $tag[0] ) )
        {
            $_id = intval($tag[0]);
        }
        elseif ( is_string( $tag[0] ))
        {
            $_alias = trim($tag[0]);
        }


        $__dat = self::getCacheData('pages');
        $Applicationitems = Applicationitems::getInstance();

        if ($_id && !$_alias)
        {
            if (isset($__dat[$_id]))
            {
                $item = $__dat[$_id];
                $p['title'] = $item['title'];
            }
            else
            {
                $item = $Applicationitems->getItem(null, $_id);
                $r[$item['alias']] = $item;
                $r[(string)$item['itemid']] = $item ;
                self::setCacheData('pages', $r );
            }
        }
        else
        {
            if (isset($__dat[$_alias]))
            {
                $item = $__dat[$_alias];
                $p['title'] = $item['title'];
            }
            else
            {
                $item = $Applicationitems->getItemByAlias(null, $_alias);
                $r[$item['alias']] = $item;
                $r[(string)$item['itemid']] = $item ;
                self::setCacheData('pages', $r );
            }
        }

        if ($item !== null)
            $output = $item['title'];
        else
            $output = '';
        return $output;
    }
}
?>