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
 * @category    Framework
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Tools.php
 *
 */
class Tools
{

    /**
     * Execute shell command
     *
     * @param string $command command line
     * @param array $output stdout strings
     * @param array $return_var process exit code
     * @param array $error_output stderr strings
     * @return int exit code
     * */
    public static function processExec( $command, array &$output = null, &$return_var = -1, array &$error_output = null )
    {

        $descriptorspec = array(
            0 => array(
                "pipe",
                "r" ), // stdin
            1 => array(
                "pipe",
                "w" ), // stdout
            2 => array(
                "pipe",
                "w" ) // stderr
        );

        $process = proc_open( $command, $descriptorspec, $pipes, null, null );

        if ( is_resource( $process ) )
        {

            fclose( $pipes[ 0 ] );

            $tmpout = '';
            $tmperr = '';

            $output = stream_get_contents( $pipes[ 1 ] );
            $error_output = stream_get_contents( $pipes[ 2 ] );

            fclose( $pipes[ 1 ] );
            fclose( $pipes[ 2 ] );
            $return_var = proc_close( $process );
        }

        return $return_var;
    }

    /**
     *
     */
    public static function escapeCoreTags( &$source )
    {
        $source = preg_replace( '/\{([a-z]+)+:([^\}]*)\}/i', '&#123;$1:$2&#125;', $source );
        $source = preg_replace( '/\{([a-z]+)+\}/i', '&#123;$1&#125;', $source );

        // find core tags for unescape
        preg_match_all( '#<!--\s*unescape-coretag\s*-->(.+?)<!--\s*/\s*unescape-coretag\s*-->#', $source, $match );
        if ( isset( $match[ 1 ] ) )
        {
            foreach ( $match[ 1 ] as $idx => $str )
            {
                $unesc = self::unescapeCoreTags( $str );
                $source = preg_replace( '/' . preg_quote( $match[ 0 ][ $idx ], '/' ) . '/U', ' ' . $unesc . ' ', $source, 1 );
            }
            $match = null;
        }
        return $source;
    }

    /**
     * @param $source
     * @return mixed
     */
    public static function unescapeCoreTags( &$source )
    {
        $source = preg_replace( '/&#123;([a-z]+)+:([^\}]*)&#125;/i', '{$1:$2}', $source );
        return preg_replace( '/&#123;([a-z]+)+&#125;/i', '{$1}', $source );
    }

    /**
     * @param      $size
     * @param null $unit
     * @param null $retstring
     * @param bool $si
     * @return array
     */
    private static function sizeReadable( $size, $unit = NULL, $retstring = NULL, $si = true )
    {
        $sizes = array(
            'B',
            'KB',
            'MB',
            'GB',
            'TB',
            'PB' );
        $mod = 1024;
        $ii = count( $sizes ) - 1;
        $unit = array_search( (string) $unit, $sizes );
        if ( $unit === NULL || $unit === false )
        {
            $unit = $ii;
        }
        if ( $retstring === NULL )
        {
            $retstring = '%2.2f';
        }
        $i = 0;
        while ( $unit != $i && $size >= 1024 && $i < $ii )
        {
            $size /= $mod;
            $i++;
        }
        return array(
            sprintf( $retstring, $size ),
            $sizes[ $i ] );
    }

    /**
     *
     * @param        $sizein
     * @param string $format
     * @internal param int $size
     * @return string
     */
    public static function formatSize( $sizein, $format = NULL )
    {
        $size = self::sizeReadable( $sizein, NULL, $format, NULL );
        return $size[ 0 ] . ' ' . $size[ 1 ];
    }

    /**
     * extract the domain from a string
     * @param string $uri
     * @return string eg. dreamcms.de
     */
    public static function getDomainnameFromUri( $uri )
    {
        $exp1 = '/^(https|http|ftp)?:\/\/(www\.|ftp\.)?([^\/]+)/i';
        $matches = array();
        preg_match( $exp1, $uri, $matches );

        if ( isset( $matches[ 3 ] ) )
        {
            return $matches[ 3 ];
        }
        else
        {
            return '';
        }
    }

    /**
     * @param        $str
     * @param int    $length
     * @param string $break
     * @param bool   $cut
     * @return string
     */
    public static function wordwrap( $str, $length = 80, $break = '', $cut = true )
    {
        // breaking down each word in the $comment into arrays using explode().
        $array = preg_split( '/([\s\r\n\t_])/', $str );

        // here i used the for loop to enable me to run each word one by one through wordwrap() 
        // and add a space into the word if it is longer than 15 characters.

        $word_split = '';
        for ( $i = 0, $array_num = count( $array ); $i < $array_num; $i++ )
        {
            $word_split .= wordwrap( $array[ $i ], $length, $break, true );
        }

        return $word_split;
    }

    /**
     *
     * @param string $linkref
     * @param string $cmsurl
     * @return boolean
     */
    public static function isExternalUrl( $linkref, $cmsurl )
    {
        //$linkref="http://ps3.macvillage.de/wp-content/uploads/2008/08/bioshock_packshot.jpg";
        //$linkref="index.html";
        //$linkref="https://localhost/dreamcms/1/core/thumb.php";

        $internal_domainname = self::getDomainnameFromUri( trim( (string) $cmsurl ) );
        $linkref_domainname = self::getDomainnameFromUri( trim( (string) $linkref ) );


        $linktype = 'internal'; // default is internal, e.g. <a href="index.php?...

        if ( substr( $linkref, 0, 5 ) === 'http:' )
        {
            $linktype = 'external-http';
        }

        if ( substr( $linkref, 0, 6 ) === 'https:' )
        {
            $linktype = 'external-http';
        }

        if ( substr( $linkref, 0, 4 ) === 'ftp:' )
        {
            $linktype = 'external-ftp';
        }

        if ( substr( $linkref, 0, 7 ) === 'mailto:' )
        {
            $linktype = 'mailto';
        }

        if ( $linktype === 'internal' || (($linktype === 'external-http') && ($linkref_domainname == $internal_domainname)) )
        {
            // -- the link is internal but with leading http...
            return false;
        }
        else
        {
            return true;
        }
    }

}

?>