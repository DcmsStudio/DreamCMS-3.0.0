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
 * @package     Importer
 * @version     3.0.0 Beta
 * @category    Config
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Base.php
 */
class CloudTag
{

    /**
     * To create a tag cloud based on the tags provided
     * @param array $tags - array($tagName=>$tagCount)
     * @param string $link - Link to connect to the tags
     * @param int $cloud_spread - max tags to show
     * @param string $sort - the sorting (count|tag)
     * @param string $title - the title to enter in the link. Can be formatted with %tag% | %count% to add the tag and count respectively in the a href title
     * @param bool $sizeTags - to allow multiple size of fonts
     * @return string LI
     */
    public static function create( array $tags, $link = "", $cloud_spread = 0, $sort = "count", $title = "%tag%", $sizeTags = false )
    {
        // Count tags
        $totalTags = count( $tags );

        // The base size of the font-size
        $fontSize_base = 13;

        // the font size ratio, the higher the bigger the font-size will be
        $fontSize_ratio = 1.5;

        // Sorting the tags
        if ( $sort == "tag" )
        {
            ksort( $tags );
        }
        else
        {
            arsort( $tags );
        }

        $count = 0;

        // Creating the list
        foreach ( $tags as $tagName => $tagCount )
        {

            $fontSize = (round( ($tagCount * 100) / $totalTags ) * $fontSize_ratio) + $fontSize_base;

            $urlKey = urlencode( $tagName );

            $urlTitle = str_replace( array(
                "%tag%",
                "%count%" ), array(
                $tagName,
                $tagCount ), $title );

            $styleTag = ($sizeTags) ? (" style=\"font-size:{$fontSize}px;\"") : "";

            $cloud .= "<li><a href=\"{$link}{$urlKey}\" title=\"{$urlTitle}\"><span{$styleTag}>{$tagName}</span></a></li>\n";

            $count++; // To count for cloud spread

            if ( $cloud_spread && $count >= $cloud_spread )
            {
                break;
            }
        }

        return $cloud;
    }

}
