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
 * @file        Sha1.php
 *
 */
class Crypt_Sha1
{

    /**
     *
     */
    public function __construct()
    {
        
    }

    /**
     * Generate the Hash
     *
     * @access    public
     * @param    string
     * @return    string
     */
    public function generate( $str )
    {
        $n = ((strlen( $str ) + 8) >> 6) + 1;

        for ( $i = 0; $i < $n * 16; $i++ )
        {
            $x[ $i ] = 0;
        }

        for ( $i = 0; $i < strlen( $str ); $i++ )
        {
            $x[ $i >> 2 ] |= ord( substr( $str, $i, 1 ) ) << (24 - ($i % 4) * 8);
        }

        $x[ $i >> 2 ] |= 0x80 << (24 - ($i % 4) * 8);

        $x[ $n * 16 - 1 ] = strlen( $str ) * 8;

        $a = 1732584193;
        $b = -271733879;
        $c = -1732584194;
        $d = 271733878;
        $e = -1009589776;

        for ( $i = 0; $i < count( $x ); $i += 16 )
        {
            $olda = $a;
            $oldb = $b;
            $oldc = $c;
            $oldd = $d;
            $olde = $e;

            for ( $j = 0; $j < 80; $j++ )
            {
                if ( $j < 16 )
                {
                    $w[ $j ] = $x[ $i + $j ];
                }
                else
                {
                    $w[ $j ] = $this->_rol( $w[ $j - 3 ] ^ $w[ $j - 8 ] ^ $w[ $j - 14 ] ^ $w[ $j - 16 ], 1 );
                }

                $t = $this->_safe_add( $this->_safe_add( $this->_rol( $a, 5 ), $this->_ft( $j, $b, $c, $d ) ), $this->_safe_add( $this->_safe_add( $e, $w[ $j ] ), $this->_kt( $j ) ) );

                $e = $d;
                $d = $c;
                $c = $this->_rol( $b, 30 );
                $b = $a;
                $a = $t;
            }

            $a = $this->_safe_add( $a, $olda );
            $b = $this->_safe_add( $b, $oldb );
            $c = $this->_safe_add( $c, $oldc );
            $d = $this->_safe_add( $d, $oldd );
            $e = $this->_safe_add( $e, $olde );
        }

        return $this->_hex( $a ) . $this->_hex( $b ) . $this->_hex( $c ) . $this->_hex( $d ) . $this->_hex( $e );
    }

    // --------------------------------------------------------------------

    /**
     * Convert a decimal to hex
     *
     * @access    private
     * @param    string
     * @return    string
     */
    private function _hex( $str )
    {
        $str = dechex( $str );

        if ( strlen( $str ) == 7 )
        {
            $str = '0' . $str;
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     *  Return result based on iteration
     *
     * @access    private
     * @param $t
     * @param $b
     * @param $c
     * @param $d
     * @return    string
     */
    private function _ft( $t, $b, $c, $d )
    {
        if ( $t < 20 )
            return ($b & $c) | ((~$b) & $d);
        if ( $t < 40 )
            return $b ^ $c ^ $d;
        if ( $t < 60 )
            return ($b & $c) | ($b & $d) | ($c & $d);

        return $b ^ $c ^ $d;
    }

    // --------------------------------------------------------------------

    /**
     * Determine the additive constant
     *
     * @access    private
     * @param $t
     * @return    string
     */
    private function _kt( $t )
    {
        if ( $t < 20 )
        {
            return 1518500249;
        }
        else if ( $t < 40 )
        {
            return 1859775393;
        }
        else if ( $t < 60 )
        {
            return -1894007588;
        }
        else
        {
            return -899497514;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Add integers, wrapping at 2^32
     *
     * @access    private
     * @param $x
     * @param $y
     * @return    string
     */
    private function _safe_add( $x, $y )
    {
        $lsw = ($x & 0xFFFF) + ($y & 0xFFFF);
        $msw = ($x >> 16) + ($y >> 16) + ($lsw >> 16);

        return ($msw << 16) | ($lsw & 0xFFFF);
    }

    // --------------------------------------------------------------------

    /**
     * Bitwise rotate a 32-bit number
     *
     * @access    private
     * @param $num
     * @param $cnt
     * @return    integer
     */
    private function _rol( $num, $cnt )
    {
        return ($num << $cnt) | $this->_zero_fill( $num, 32 - $cnt );
    }

    // --------------------------------------------------------------------

    /**
     * Pad string with zero
     *
     * @access    private
     * @param $a
     * @param $b
     * @return    string
     */
    private function _zero_fill( $a, $b )
    {
        $bin = decbin( $a );

        if ( strlen( $bin ) < $b )
        {
            $bin = 0;
        }
        else
        {
            $bin = substr( $bin, 0, strlen( $bin ) - $b );
        }

        for ( $i = 0; $i < $b; $i++ )
        {
            $bin = "0" . $bin;
        }

        return bindec( $bin );
    }

}
