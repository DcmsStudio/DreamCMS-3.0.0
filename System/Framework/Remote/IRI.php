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
 * @file        IRI.php
 *
 */
class Remote_IRI
{

    /**
     * Scheme
     *
     * @access private
     * @var string
     */
    private $scheme;

    /**
     * User Information
     *
     * @access private
     * @var string
     */
    private $userinfo;

    /**
     * Host
     *
     * @access private
     * @var string
     */
    private $host;

    /**
     * Port
     *
     * @access private
     * @var string
     */
    private $port;

    /**
     * Path
     *
     * @access private
     * @var string
     */
    private $path;

    /**
     * Query
     *
     * @access private
     * @var string
     */
    private $query;

    /**
     * Fragment
     *
     * @access private
     * @var string
     */
    private $fragment;

    /**
     * Whether the object represents a valid IRI
     *
     * @access private
     * @var array
     */
    private $valid = array();

    /**
     * Return the entire IRI when you try and read the object as a string
     *
     * @access public
     * @return string
     */
    public function __toString()
    {
        return $this->get_iri();
    }

    /**
     * Create a new IRI object, from a specified string
     *
     * @access public
     * @param string $iri
     * @return \Remote_IRI
     */
    public function __construct( $iri )
    {
        $iri = (string) $iri;
        if ( $iri !== '' )
        {
            $parsed = $this->parse_iri( $iri );
            $this->set_scheme( $parsed[ 'scheme' ] );
            $this->set_authority( $parsed[ 'authority' ] );
            $this->set_path( $parsed[ 'path' ] );
            $this->set_query( $parsed[ 'query' ] );
            $this->set_fragment( $parsed[ 'fragment' ] );
        }
    }

    /**
     * Create a new IRI object by resolving a relative IRI
     *
     * @static
     * @access public
     * @param SimplePie_IRI $base Base IRI
     * @param string $relative Relative IRI
     * @return SimplePie_IRI
     */
    public static function absolutize( $base, $relative )
    {
        $relative = (string) $relative;
        if ( $relative !== '' )
        {
            $relative = new SimplePie_IRI( $relative );
            if ( $relative->get_scheme() !== null )
            {
                $target = $relative;
            }
            elseif ( $base->get_iri() !== null )
            {
                if ( $relative->get_authority() !== null )
                {
                    $target = $relative;
                    $target->set_scheme( $base->get_scheme() );
                }
                else
                {
                    $target = new SimplePie_IRI( '' );
                    $target->set_scheme( $base->get_scheme() );
                    $target->set_userinfo( $base->get_userinfo() );
                    $target->set_host( $base->get_host() );
                    $target->set_port( $base->get_port() );
                    if ( $relative->get_path() !== null )
                    {
                        if ( strpos( $relative->get_path(), '/' ) === 0 )
                        {
                            $target->set_path( $relative->get_path() );
                        }
                        elseif ( ($base->get_userinfo() !== null || $base->get_host() !== null || $base->get_port() !== null) && $base->get_path() === null )
                        {
                            $target->set_path( '/' . $relative->get_path() );
                        }
                        elseif ( ($last_segment = strrpos( $base->get_path(), '/' )) !== false )
                        {
                            $target->set_path( substr( $base->get_path(), 0, $last_segment + 1 ) . $relative->get_path() );
                        }
                        else
                        {
                            $target->set_path( $relative->get_path() );
                        }
                        $target->set_query( $relative->get_query() );
                    }
                    else
                    {
                        $target->set_path( $base->get_path() );
                        if ( $relative->get_query() !== null )
                        {
                            $target->set_query( $relative->get_query() );
                        }
                        elseif ( $base->get_query() !== null )
                        {
                            $target->set_query( $base->get_query() );
                        }
                    }
                }
                $target->set_fragment( $relative->get_fragment() );
            }
            else
            {
                // No base URL, just return the relative URL
                $target = $relative;
            }
        }
        else
        {
            $target = $base;
        }
        return $target;
    }

    /**
     * Parse an IRI into scheme/authority/path/query/fragment segments
     *
     * @access private
     * @param string $iri
     * @return array
     */
    public function parse_iri( $iri )
    {
        preg_match( '/^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$/', $iri, $match );
        for ( $i = count( $match ); $i <= 9; $i++ )
        {
            $match[ $i ] = '';
        }
        return array(
            'scheme'    => $match[ 2 ],
            'authority' => $match[ 4 ],
            'path'      => $match[ 5 ],
            'query'     => $match[ 7 ],
            'fragment'  => $match[ 9 ] );
    }

    /**
     * Remove dot segments from a path
     *
     * @access private
     * @param string $input
     * @return string
     */
    public function remove_dot_segments( $input )
    {
        $output = '';
        while ( strpos( $input, './' ) !== false || strpos( $input, '/.' ) !== false || $input === '.' || $input === '..' )
        {
            // A: If the input buffer begins with a prefix of "../" or "./", then remove that prefix from the input buffer; otherwise,
            if ( strpos( $input, '../' ) === 0 )
            {
                $input = substr( $input, 3 );
            }
            elseif ( strpos( $input, './' ) === 0 )
            {
                $input = substr( $input, 2 );
            } // B: if the input buffer begins with a prefix of "/./" or "/.", where "." is a complete path segment, then replace that prefix with "/" in the input buffer; otherwise,
            elseif ( strpos( $input, '/./' ) === 0 )
            {
                $input = substr_replace( $input, '/', 0, 3 );
            }
            elseif ( $input === '/.' )
            {
                $input = '/';
            } // C: if the input buffer begins with a prefix of "/../" or "/..", where ".." is a complete path segment, then replace that prefix with "/" in the input buffer and remove the last segment and its preceding "/" (if any) from the output buffer; otherwise,
            elseif ( strpos( $input, '/../' ) === 0 )
            {
                $input = substr_replace( $input, '/', 0, 4 );
                $output = substr_replace( $output, '', strrpos( $output, '/' ) );
            }
            elseif ( $input === '/..' )
            {
                $input = '/';
                $output = substr_replace( $output, '', strrpos( $output, '/' ) );
            } // D: if the input buffer consists only of "." or "..", then remove that from the input buffer; otherwise,
            elseif ( $input === '.' || $input === '..' )
            {
                $input = '';
            } // E: move the first path segment in the input buffer to the end of the output buffer, including the initial "/" character (if any) and any subsequent characters up to, but not including, the next "/" character or the end of the input buffer
            elseif ( ($pos = strpos( $input, '/', 1 )) !== false )
            {
                $output .= substr( $input, 0, $pos );
                $input = substr_replace( $input, '', 0, $pos );
            }
            else
            {
                $output .= $input;
                $input = '';
            }
        }
        return $output . $input;
    }

    /**
     * Replace invalid character with percent encoding
     *
     * @param string $string Input string
     * @param string $valid_chars Valid characters not in iunreserved or iprivate (this is ASCII-only)
     * @param int $case Normalise case
     * @param bool $iprivate Allow iprivate
     * @return string
     */
    protected function replace_invalid_with_pct_encoding( $string, $valid_chars, $case = SIMPLEPIE_SAME_CASE, $iprivate = false )
    {
        // Normalize as many pct-encoded sections as possible
        $string = preg_replace_callback( '/(?:%[A-Fa-f0-9]{2})+/', array(
            &$this,
            'remove_iunreserved_percent_encoded' ), $string );

        // Replace invalid percent characters
        $string = preg_replace( '/%(?![A-Fa-f0-9]{2})/', '%25', $string );

        // Add unreserved and % to $valid_chars (the latter is safe because all
        // pct-encoded sections are now valid).
        $valid_chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~%';

        // Now replace any bytes that aren't allowed with their pct-encoded versions
        $position = 0;
        $strlen = strlen( $string );
        while ( ($position += strspn( $string, $valid_chars, $position )) < $strlen )
        {
            $value = ord( $string[ $position ] );

            // Start position
            $start = $position;

            // By default we are valid
            $valid = true;

            // No one byte sequences are valid due to the while.
            // Two byte sequence:
            if ( ($value & 0xE0) === 0xC0 )
            {
                $character = ($value & 0x1F) << 6;
                $length = 2;
                $remaining = 1;
            } // Three byte sequence:
            elseif ( ($value & 0xF0) === 0xE0 )
            {
                $character = ($value & 0x0F) << 12;
                $length = 3;
                $remaining = 2;
            } // Four byte sequence:
            elseif ( ($value & 0xF8) === 0xF0 )
            {
                $character = ($value & 0x07) << 18;
                $length = 4;
                $remaining = 3;
            } // Invalid byte:
            else
            {
                $valid = false;
                $length = 1;
                $remaining = 0;
            }

            if ( $remaining )
            {
                if ( $position + $length <= $strlen )
                {
                    for ( $position++; $remaining; $position++ )
                    {
                        $value = ord( $string[ $position ] );

                        // Check that the byte is valid, then add it to the character:
                        if ( ($value & 0xC0) === 0x80 )
                        {
                            $character |= ($value & 0x3F) << ( --$remaining * 6);
                        } // If it is invalid, count the sequence as invalid and reprocess the current byte:
                        else
                        {
                            $valid = false;
                            $position--;
                            break;
                        }
                    }
                }
                else
                {
                    $position = $strlen - 1;
                    $valid = false;
                }
            }

            // Percent encode anything invalid or not in ucschar
            if (
            // Invalid sequences
                    !$valid
                    // Non-shortest form sequences are invalid
                    || $length > 1 && $character <= 0x7F || $length > 2 && $character <= 0x7FF || $length > 3 && $character <= 0xFFFF
                    // Outside of range of ucschar codepoints
                    // Noncharacters
                    || ($character & 0xFFFE) === 0xFFFE || $character >= 0xFDD0 && $character <= 0xFDEF || (
                    // Everything else not in ucschar
                    $character > 0xD7FF && $character < 0xF900 || $character < 0xA0 || $character > 0xEFFFD
                    ) && (
                    // Everything not in iprivate, if it applies
                    !$iprivate || $character < 0xE000 || $character > 0x10FFFD
                    )
            )
            {
                // If we were a character, pretend we weren't, but rather an error.
                if ( $valid )
                    $position--;

                for ( $j = $start; $j <= $position; $j++ )
                {
                    $string = substr_replace( $string, sprintf( '%%%02X', ord( $string[ $j ] ) ), $j, 1 );
                    $j += 2;
                    $position += 2;
                    $strlen += 2;
                }
            }
        }

        // Normalise case
        if ( $case & SIMPLEPIE_LOWERCASE )
        {
            $string = strtolower( $string );
        }
        elseif ( $case & SIMPLEPIE_UPPERCASE )
        {
            $string = strtoupper( $string );
        }

        return $string;
    }

    /**
     * Callback function for preg_replace_callback.
     * Removes sequences of percent encoded bytes that represent UTF-8
     * encoded characters in iunreserved
     *
     * @param array $match PCRE match
     * @return string Replacement
     */
    protected function remove_iunreserved_percent_encoded( $match )
    {
        // As we just have valid percent encoded sequences we can just explode
        // and ignore the first member of the returned array (an empty string).
        $bytes = explode( '%', $match[ 0 ] );

        // Initialize the new string (this is what will be returned) and that
        // there are no bytes remaining in the current sequence (unsurprising
        // at the first byte!).
        $string = '';
        $remaining = 0;

        // Loop over each and every byte, and set $value to its value
        for ( $i = 1, $len = count( $bytes ); $i < $len; $i++ )
        {
            $value = hexdec( $bytes[ $i ] );

            // If we're the first byte of sequence:
            if ( !$remaining )
            {
                // Start position
                $start = $i;

                // By default we are valid
                $valid = true;

                // One byte sequence:
                if ( $value <= 0x7F )
                {
                    $character = $value;
                    $length = 1;
                } // Two byte sequence:
                elseif ( ($value & 0xE0) === 0xC0 )
                {
                    $character = ($value & 0x1F) << 6;
                    $length = 2;
                    $remaining = 1;
                } // Three byte sequence:
                elseif ( ($value & 0xF0) === 0xE0 )
                {
                    $character = ($value & 0x0F) << 12;
                    $length = 3;
                    $remaining = 2;
                } // Four byte sequence:
                elseif ( ($value & 0xF8) === 0xF0 )
                {
                    $character = ($value & 0x07) << 18;
                    $length = 4;
                    $remaining = 3;
                } // Invalid byte:
                else
                {
                    $valid = false;
                    $remaining = 0;
                }
            } // Continuation byte:
            else
            {
                // Check that the byte is valid, then add it to the character:
                if ( ($value & 0xC0) === 0x80 )
                {
                    $remaining--;
                    $character |= ($value & 0x3F) << ($remaining * 6);
                } // If it is invalid, count the sequence as invalid and reprocess the current byte as the start of a sequence:
                else
                {
                    $valid = false;
                    $remaining = 0;
                    $i--;
                }
            }

            // If we've reached the end of the current byte sequence, append it to Unicode::$data
            if ( !$remaining )
            {
                // Percent encode anything invalid or not in iunreserved
                if (
                // Invalid sequences
                        !$valid
                        // Non-shortest form sequences are invalid
                        || $length > 1 && $character <= 0x7F || $length > 2 && $character <= 0x7FF || $length > 3 && $character <= 0xFFFF
                        // Outside of range of iunreserved codepoints
                        || $character < 0x2D || $character > 0xEFFFD
                        // Noncharacters
                        || ($character & 0xFFFE) === 0xFFFE || $character >= 0xFDD0 && $character <= 0xFDEF
                        // Everything else not in iunreserved (this is all BMP)
                        || $character === 0x2F || $character > 0x39 && $character < 0x41 || $character > 0x5A && $character < 0x61 || $character > 0x7A && $character < 0x7E || $character > 0x7E && $character < 0xA0 || $character > 0xD7FF && $character < 0xF900
                )
                {
                    for ( $j = $start; $j <= $i; $j++ )
                    {
                        $string .= '%' . strtoupper( $bytes[ $j ] );
                    }
                }
                else
                {
                    for ( $j = $start; $j <= $i; $j++ )
                    {
                        $string .= chr( hexdec( $bytes[ $j ] ) );
                    }
                }
            }
        }

        // If we have any bytes left over they are invalid (i.e., we are
        // mid-way through a multi-byte sequence)
        if ( $remaining )
        {
            for ( $j = $start; $j < $len; $j++ )
            {
                $string .= '%' . strtoupper( $bytes[ $j ] );
            }
        }

        return $string;
    }

    /**
     * Check if the object represents a valid IRI
     *
     * @access public
     * @return bool
     */
    public function is_valid()
    {
        return array_sum( $this->valid ) === count( $this->valid );
    }

    /**
     * Set the scheme. Returns true on success, false on failure (if there are
     * any invalid characters).
     *
     * @access public
     * @param string $scheme
     * @return bool
     */
    public function set_scheme( $scheme )
    {
        if ( $scheme === null || $scheme === '' )
        {
            $this->scheme = null;
        }
        else
        {
            $len = strlen( $scheme );
            switch ( true )
            {
                case $len > 1:
                    if ( !strspn( $scheme, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+-.', 1 ) )
                    {
                        $this->scheme = null;
                        $this->valid[ __FUNCTION__ ] = false;
                        return false;
                    }

                case $len > 0:
                    if ( !strspn( $scheme, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz', 0, 1 ) )
                    {
                        $this->scheme = null;
                        $this->valid[ __FUNCTION__ ] = false;
                        return false;
                    }
            }
            $this->scheme = strtolower( $scheme );
        }
        $this->valid[ __FUNCTION__ ] = true;
        return true;
    }

    /**
     * Set the authority. Returns true on success, false on failure (if there are
     * any invalid characters).
     *
     * @access public
     * @param string $authority
     * @return bool
     */
    public function set_authority( $authority )
    {
        if ( ($userinfo_end = strrpos( $authority, '@' )) !== false )
        {
            $userinfo = substr( $authority, 0, $userinfo_end );
            $authority = substr( $authority, $userinfo_end + 1 );
        }
        else
        {
            $userinfo = null;
        }

        if ( ($port_start = strpos( $authority, ':' )) !== false )
        {
            $port = substr( $authority, $port_start + 1 );
            if ( $port === false )
            {
                $port = null;
            }
            $authority = substr( $authority, 0, $port_start );
        }
        else
        {
            $port = null;
        }

        return $this->set_userinfo( $userinfo ) && $this->set_host( $authority ) && $this->set_port( $port );
    }

    /**
     * Set the userinfo.
     *
     * @access public
     * @param string $userinfo
     * @return bool
     */
    public function set_userinfo( $userinfo )
    {
        if ( $userinfo === null || $userinfo === '' )
        {
            $this->userinfo = null;
        }
        else
        {
            $this->userinfo = $this->replace_invalid_with_pct_encoding( $userinfo, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~!$&\'()*+,;=:' );
        }
        $this->valid[ __FUNCTION__ ] = true;
        return true;
    }

    /**
     * Set the host. Returns true on success, false on failure (if there are
     * any invalid characters).
     *
     * @access public
     * @param string $host
     * @return bool
     */
    public function set_host( $host )
    {
        if ( $host === null || $host === '' )
        {
            $this->host = null;
            $this->valid[ __FUNCTION__ ] = true;
            return true;
        }
        elseif ( $host[ 0 ] === '[' && substr( $host, -1 ) === ']' )
        {
            if ( SimplePie_Net_IPv6::checkIPv6( substr( $host, 1, -1 ) ) )
            {
                $this->host = $host;
                $this->valid[ __FUNCTION__ ] = true;
                return true;
            }
            else
            {
                $this->host = null;
                $this->valid[ __FUNCTION__ ] = false;
                return false;
            }
        }
        else
        {
            $this->host = $this->replace_invalid_with_pct_encoding( $host, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~!$&\'()*+,;=', SIMPLEPIE_LOWERCASE );
            $this->valid[ __FUNCTION__ ] = true;
            return true;
        }
    }

    /**
     * Set the port. Returns true on success, false on failure (if there are
     * any invalid characters).
     *
     * @access public
     * @param string $port
     * @return bool
     */
    public function set_port( $port )
    {
        if ( $port === null || $port === '' )
        {
            $this->port = null;
            $this->valid[ __FUNCTION__ ] = true;
            return true;
        }
        elseif ( strspn( $port, '0123456789' ) === strlen( $port ) )
        {
            $this->port = (int) $port;
            $this->valid[ __FUNCTION__ ] = true;
            return true;
        }
        else
        {
            $this->port = null;
            $this->valid[ __FUNCTION__ ] = false;
            return false;
        }
    }

    /**
     * Set the path.
     *
     * @access public
     * @param string $path
     * @return bool
     */
    public function set_path( $path )
    {
        if ( $path === null || $path === '' )
        {
            $this->path = null;
            $this->valid[ __FUNCTION__ ] = true;
            return true;
        }
        elseif ( substr( $path, 0, 2 ) === '//' && $this->userinfo === null && $this->host === null && $this->port === null )
        {
            $this->path = null;
            $this->valid[ __FUNCTION__ ] = false;
            return false;
        }
        else
        {
            $this->path = $this->replace_invalid_with_pct_encoding( $path, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~!$&\'()*+,;=@/' );
            if ( $this->scheme !== null )
            {
                $this->path = $this->remove_dot_segments( $this->path );
            }
            $this->valid[ __FUNCTION__ ] = true;
            return true;
        }
    }

    /**
     * Set the query.
     *
     * @access public
     * @param string $query
     * @return bool
     */
    public function set_query( $query )
    {
        if ( $query === null || $query === '' )
        {
            $this->query = null;
        }
        else
        {
            $this->query = $this->replace_invalid_with_pct_encoding( $query, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~!$\'()*+,;:@/?&=' );
        }
        $this->valid[ __FUNCTION__ ] = true;
        return true;
    }

    /**
     * Set the fragment.
     *
     * @access public
     * @param string $fragment
     * @return bool
     */
    public function set_fragment( $fragment )
    {
        if ( $fragment === null || $fragment === '' )
        {
            $this->fragment = null;
        }
        else
        {
            $this->fragment = $this->replace_invalid_with_pct_encoding( $fragment, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~!$&\'()*+,;=:@/?' );
        }
        $this->valid[ __FUNCTION__ ] = true;
        return true;
    }

    /**
     * Get the complete IRI
     *
     * @access public
     * @return string
     */
    public function get_iri()
    {
        $iri = '';
        if ( $this->scheme !== null )
        {
            $iri .= $this->scheme . ':';
        }
        if ( ($authority = $this->get_authority()) !== null )
        {
            $iri .= '//' . $authority;
        }
        if ( $this->path !== null )
        {
            $iri .= $this->path;
        }
        if ( $this->query !== null )
        {
            $iri .= '?' . $this->query;
        }
        if ( $this->fragment !== null )
        {
            $iri .= '#' . $this->fragment;
        }

        if ( $iri !== '' )
        {
            return $iri;
        }
        else
        {
            return null;
        }
    }

    /**
     * Get the scheme
     *
     * @access public
     * @return string
     */
    public function get_scheme()
    {
        return $this->scheme;
    }

    /**
     * Get the complete authority
     *
     * @access public
     * @return string
     */
    public function get_authority()
    {
        $authority = '';
        if ( $this->userinfo !== null )
        {
            $authority .= $this->userinfo . '@';
        }
        if ( $this->host !== null )
        {
            $authority .= $this->host;
        }
        if ( $this->port !== null )
        {
            $authority .= ':' . $this->port;
        }

        if ( $authority !== '' )
        {
            return $authority;
        }
        else
        {
            return null;
        }
    }

    /**
     * Get the user information
     *
     * @access public
     * @return string
     */
    public function get_userinfo()
    {
        return $this->userinfo;
    }

    /**
     * Get the host
     *
     * @access public
     * @return string
     */
    public function get_host()
    {
        return $this->host;
    }

    /**
     * Get the port
     *
     * @access public
     * @return string
     */
    public function get_port()
    {
        return $this->port;
    }

    /**
     * Get the path
     *
     * @access public
     * @return string
     */
    public function get_path()
    {
        return $this->path;
    }

    /**
     * Get the query
     *
     * @access public
     * @return string
     */
    public function get_query()
    {
        return $this->query;
    }

    /**
     * Get the fragment
     *
     * @access public
     * @return string
     */
    public function get_fragment()
    {
        return $this->fragment;
    }

}
