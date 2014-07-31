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
 * @file        Date.php
 *
 */

class Tag_Date extends Provider_Abstract
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @param $tag
     */
    public function render($tag)
    {
        if (empty($tag[0]))
        {
            $time = time();
        }
        elseif (is_numeric($tag[0]) || is_string($tag[0]))
        {
            $time = intval($tag[0]);
        }

        if (!$time)
        {
            $time = time();
        }




        $out = '';
        $tmp = strtolower(trim($tag[1]));
        if (is_string($tag[1]))
        {
            $key = strtolower(trim($tag[1]));
        }
        else
        {
            $key = strtolower(trim($tag[1]));
            $tmp = Locales::getTranslatedDate($key, $time);
        }


        if ($tmp == $key)
        {
            switch ($key)
            {

                case 'fulldate':
                    $out = Locales::formatFullDate($time);
                    break;
                case 'fulldatetime':
                    $out = Locales::formatFullDateTime($time);
                    break;
                case 'datetime':
                    $out = Locales::formatDateTime($time);
                    break;

                case 'd':
                    $out = date('d', $time);
                    break;


                case 'm':
                    $out = date('m', $time);
                    break;

                case 'Y':
                    $out = date('Y', $time);
                    break;





                case 'short':
                default:
                    $out = Locales::formatDate($time);
                    break;
            }
        }
        else
        {
            $out = $tmp;
        }
    }

}

