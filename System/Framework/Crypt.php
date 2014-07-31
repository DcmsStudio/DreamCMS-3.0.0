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
 * @file        Crypt.php
 *
 */
class Crypt
{

    /**
     * Current object instance (Singleton)
     * @var object
     */
    protected static $objInstance;

    /**
     * Mcrypt resource
     * @var resource
     */
    protected $resTd;

    /**
     * Please provide an encryption key that is at least 8 characters long. Note
     * that encrypted data can only be decrypted with the same key! Therefore
     * note it down and do not change it if your data is encrypted already.
     *
     *   encryptionMode   = defaults to "cfb"
     *   encryptionCipher = defaults to "rijndael 256"
     *
     * See PHP extension "mcrypt" for more information.
     */
    protected $encryptionKey = 'd0ed530a347ef69c13c5f81c238e1c56';

    protected $encryptionMode = 'cfb';

    protected $encryptionCipher = 'rijndael-256';

    protected $_mcrypt_exists = FALSE;

    protected $hash_type = 'sha1';

    /**
     * Initialize the encryption module
     * @param $encryptionKey default is null and will use the internal key
     * @throws Exception
     */
    public function __construct( $encryptionKey = null )
    {

        if ( $encryptionKey !== null && $encryptionKey )
        {
            $this->encryptionKey = $encryptionKey;
        }


        if ( !function_exists( 'mcrypt_module_open' ) )
        {
            if ( !strlen( $this->encryptionKey ) )
            {
                throw new Exception( 'Encryption key not set' );
            }
            return;
        }

        $this->_mcrypt_exists = true;

        if ( ($this->resTd = mcrypt_module_open( $this->encryptionCipher, '', $this->encryptionMode, '' )) == false )
        {
            throw new Exception( 'Error initializing encryption module' );
        }

        if ( !strlen( $this->encryptionKey ) )
        {
            throw new Exception( 'Encryption key not set' );
        }
    }

    /**
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone()
    {
        
    }

    /**
     * Encrypt a value
     * @param  mixed
     * @return string
     */
    public function encrypt( $strValue )
    {
        if ( $strValue == '' )
        {
            return '';
        }
        if ( $this->_mcrypt_exists )
        {
            $iv = mcrypt_create_iv( mcrypt_enc_get_iv_size( $this->resTd ), MCRYPT_RAND );
            mcrypt_generic_init( $this->resTd, md5( $this->encryptionKey ), $iv );

            $strEncrypted = mcrypt_generic( $this->resTd, $strValue );
            $strEncrypted = base64_encode( $iv . $strEncrypted );

            mcrypt_generic_deinit( $this->resTd );
        }
        else
        {
            $strEncrypted = base64_encode( $this->xor_encode( $strValue ) );
        }

        return $strEncrypted;
    }

    /**
     * Decrypt a value
     * @param  mixed
     * @return string
     */
    public function decrypt( $strValue )
    {
        if ( $strValue == '' )
        {
            return '';
        }

        $strValue = base64_decode( $strValue );

        if ( $this->_mcrypt_exists )
        {
            $ivsize = mcrypt_enc_get_iv_size( $this->resTd );
            $iv = substr( $strValue, 0, $ivsize );
            $strValue = substr( $strValue, $ivsize );

            if ( $strValue == '' )
            {
                return '';
            }

            mcrypt_generic_init( $this->resTd, md5( $this->encryptionKey ), $iv );
            $strDecrypted = mdecrypt_generic( $this->resTd, $strValue );

            mcrypt_generic_deinit( $this->resTd );
        }
        else
        {
            $strDecrypted = $this->xor_decode( $strValue );
        }

        return $strDecrypted;
    }

    /**
     * XOR Encode
     *
     * Takes a plain-text string and key as input and generates an
     * encoded bit-string using XOR
     *
     * @access    private
     * @param    string
     * @return    string
     */
    private function xor_encode( $string )
    {
        $rand = '';
        while ( strlen( $rand ) < 32 )
        {
            $rand .= mt_rand( 0, mt_getrandmax() );
        }

        $rand = $this->Hash( $rand );

        $enc = '';
        for ( $i = 0; $i < strlen( $string ); $i++ )
        {
            $enc .= substr( $rand, ($i % strlen( $rand ) ), 1 ) . (substr( $rand, ($i % strlen( $rand ) ), 1 ) ^ substr( $string, $i, 1 ));
        }

        return $this->xor_merge( $enc );
    }

    /**
     * XOR Decode
     *
     * Takes an encoded string and key as input and generates the
     * plain-text original message
     *
     * @access    private
     * @param    string
     * @return    string
     */
    private function xor_decode( $string )
    {
        $string = $this->xor_merge( $string );

        $dec = '';
        for ( $i = 0; $i < strlen( $string ); $i++ )
        {
            $dec .= (substr( $string, $i++, 1 ) ^ substr( $string, $i, 1 ));
        }

        return $dec;
    }

    /**
     * XOR key + string Combiner
     *
     * Takes a string and key as input and computes the difference using XOR
     *
     * @access    private
     * @param    string
     * @return    string
     */
    private function xor_merge( $string )
    {
        $hash = $this->Hash( $this->encryptionKey );

        $str = '';
        for ( $i = 0; $i < strlen( $string ); $i++ )
        {
            $str .= substr( $string, $i, 1 ) ^ substr( $hash, ($i % strlen( $hash ) ), 1 );
        }

        return $str;
    }

    /**
     * Set the Hash type
     *
     * @access    public
     * @param    string
     * @return    string
     */
    public function setHash( $type = 'sha1' )
    {
        $this->hash_type = ($type != 'sha1' AND $type != 'md5') ? 'sha1' : $type;
    }

    // --------------------------------------------------------------------

    /**
     * Hash encode a string
     *
     * @access    public
     * @param    string
     * @return    string
     */
    public function Hash( $str )
    {
        return ($this->hash_type == 'sha1') ? $this->sha1( $str ) : md5( $str );
    }

    /**
     * Generate an SHA1 Hash
     *
     * @access    public
     * @param    string
     * @return    string
     */
    public function sha1( $str )
    {
        if ( !function_exists( 'sha1' ) )
        {
            if ( !function_exists( 'mhash' ) )
            {
                $SH = new Crypt_Sha1();
                return $SH->generate( $str );
            }
            else
            {
                return bin2hex( mhash( MHASH_SHA1, $str ) );
            }
        }
        else
        {
            return sha1( $str );
        }
    }

}
