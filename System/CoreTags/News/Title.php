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

class CoreTag_News_Title extends DCMS_ContentProvider
{

	/**
	 * @param $tag
	 * @return string
	 */
	public static function render($tag)
    {
        $dcms = self::$dcms;
        $_alias = '';
        $_id = 0;
        if ( is_numeric( $tag[0] ) )
        {
            $_id = intval($tag[0]);
        }
        elseif ( is_string( $tag[0] ))
        {
            $_alias = trim($tag[0]);
        }

        $__dat = self::getCacheData('news');

        ob_start();

        if ($_id && !$_alias)
        {
            if (isset($__dat[$_id]))
            {
                $page = $__dat[$_id];
            }

            if (!isset($page['title']))
            {
                $page = $GLOBALS['FRONTEND']->db->query('
                    SELECT n.*, nt.alias, nt.suffix, nt.title
                    FROM %tp%news AS n
                    LEFT JOIN %tp%news_trans AS nt ON(nt.id=n.id AND IF(nt.lang != ?, nt.lang = n.corelang, nt.lang = ?))
                    WHERE n.id = ?', CONTENT_TRANS, CONTENT_TRANS, $_id)->fetch();

                $r[(string)$page['id']]['title'] = $page['title'];
                self::setCacheData('news', $r );
            }

            if ( isset($page['title']))
                echo $page['title'];
            else
                echo trans('News Titel kann nicht gefunden werden!');
        }
        elseif ($_alias)
        {
            if (isset($__dat[$_alias]))
            {
                $page = $__dat[$_alias];
            }

            if (!isset($page['title']))
            {
                $page = $GLOBALS['FRONTEND']->db->query('
                    SELECT n.*, nt.alias, nt.suffix, nt.title
                    FROM %tp%news AS n
                    LEFT JOIN %tp%news_trans AS nt ON(nt.id=n.id AND IF(nt.lang != ?, nt.lang = n.corelang, nt.lang = ?))
                    WHERE nt.alias = ?', CONTENT_TRANS, CONTENT_TRANS, $_alias)->fetch();

                $r[$page['alias']]['title'] =  $page['title'] ;
                $r[(string)$page['id']]['title'] = $page['title'];
                self::setCacheData('news', $r );
            }

            if ( isset($page['title']))
            {
                echo $page['title'];
            }
            else
            {
                echo trans('News Titel kann nicht gefunden werden!');
            }
        }
        else
        {
            echo trans('News Titel kann nicht gefunden werden, da kein Alias und auch keine ID übergeben wurde!');
        }

        $output = ob_get_contents();
        $buf = ob_end_clean();
        $buf = @ob_end_clean();

        return $output;
    }

}

?>