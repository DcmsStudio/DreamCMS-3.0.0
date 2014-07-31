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
 * @file        AgentParser.php
 *
 */
class Tracking_AgentParser
{

    // browser regex => browser ID
    // if there are aliases, the common name should be last
    /**
     * @var array
     */
    static protected $browsers = array(
        'abrowse'                     => 'AB',
        'amaya'                       => 'AM',
        'amigavoyager'                => 'AV',
        'amiga-aweb'                  => 'AW',
        'arora'                       => 'AR',
        'beonex'                      => 'BE',
        // BlackBerry smartphones and tablets
        'blackberry'                  => 'BB', // BlackBerry 6 and PlayBook adopted webkit
        'bb10'                        => 'B2', // BlackBerry 10
        'playbook'                    => 'BP',
        'browsex'                     => 'BX',
        // Camino (and earlier incarnation)
        'chimera'                     => 'CA',
        'camino'                      => 'CA',
        'cheshire'                    => 'CS',
        // Chrome, Chromium, and ChromePlus
        'crmo'                        => 'CH',
        'chrome'                      => 'CH',
        // Chrome Frame
        'chromeframe'                 => 'CF',
        'cometbird'                   => 'CO',
        'dillo'                       => 'DI',
        'elinks'                      => 'EL',
        'epiphany'                    => 'EP',
        'fennec'                      => 'FE',
        // Dolfin (or Dolphin)
        'dolfin'                      => 'DF',
        // Firefox (in its many incarnations and rebranded versions)
        'phoenix'                     => 'PX',
        'mozilla firebird'            => 'FB',
        'firebird'                    => 'FB',
        'bonecho'                     => 'FF',
        'minefield'                   => 'FF',
        'namoroka'                    => 'FF',
        'shiretoko'                   => 'FF',
        'granparadiso'                => 'FF',
        'iceweasel'                   => 'FF',
        'icecat'                      => 'FF',
        'firefox'                     => 'FF',
        'thunderbird'                 => 'TB',
        'flock'                       => 'FL',
        'fluid'                       => 'FD',
        'galeon'                      => 'GA',
        'google earth'                => 'GE',
        'hana'                        => 'HA',
        'hotjava'                     => 'HJ',
        'ibrowse'                     => 'IB',
        'icab'                        => 'IC',
        // IE (including shells: Acoo, AOL, Avant, Crazy Browser, Green Browser, KKMAN, Maxathon)
        'msie'                        => 'IE',
        'microsoft internet explorer' => 'IE',
        'internet explorer'           => 'IE',
        'iron'                        => 'IR',
        'kapiko'                      => 'KP',
        'kazehakase'                  => 'KZ',
        'k-meleon'                    => 'KM',
        'konqueror'                   => 'KO',
        'links'                       => 'LI',
        'lynx'                        => 'LX',
        'midori'                      => 'MI',
        // SeaMonkey (formerly Mozilla Suite) (and rebranded versions)
        'mozilla'                     => 'MO',
        'gnuzilla'                    => 'SM',
        'iceape'                      => 'SM',
        'seamonkey'                   => 'SM',
        // NCSA Mosaic (and incarnations)
        'mosaic'                      => 'MC',
        'ncsa mosaic'                 => 'MC',
        // Netscape Navigator
        'navigator'                   => 'NS',
        'netscape6'                   => 'NS',
        'netscape'                    => 'NS',
        'nx'                          => 'NF',
        'netfront'                    => 'NF',
        'omniweb'                     => 'OW',
        // Opera
        'nitro) opera'                => 'OP',
        'opera'                       => 'OP',
        'rekonq'                      => 'RK',
        // Safari
        'safari'                      => 'SF',
        'applewebkit'                 => 'SF',
        'titanium'                    => 'TI',
        'webos'                       => 'WO',
        'webpro'                      => 'WP',
        // Nokia Phone
        'nokia'                       => 'nokia',
        'curl'                        => 'curl',
        'libwww'                      => 'libwww',
    );

    // browser family (by layout engine)
    /**
     * @var array
     */
    static protected $browserType = array(
        'ie'     => array(
            'IE' ),
        'gecko'  => array(
            'NS',
            'PX',
            'FF',
            'FB',
            'CA',
            'GA',
            'KM',
            'MO',
            'SM',
            'CO',
            'FE',
            'KP',
            'KZ',
            'TB' ),
        'khtml'  => array(
            'KO' ),
        'webkit' => array(
            'SF',
            'CH',
            'OW',
            'AR',
            'EP',
            'FL',
            'WO',
            'AB',
            'IR',
            'CS',
            'FD',
            'HA',
            'MI',
            'GE',
            'DF',
            'BB',
            'BP',
            'TI',
            'CF',
            'RK',
            'B2',
            'NF' ),
        'opera'  => array(
            'OP' ),
    );

    // WebKit version numbers to Apple Safari version numbers (if Version/X.Y.Z not present)
    /**
     * @var array
     */
    static protected $safariVersions = array(
        '536.25'   => array(
            '6',
            '0' ),
        '534.48'   => array(
            '5',
            '1' ),
        '533.16'   => array(
            '5',
            '0' ),
        '533.4'    => array(
            '4',
            '1' ),
        '526.11.2' => array(
            '4',
            '0' ),
        '525.26'   => array(
            '3',
            '2' ),
        '525.13'   => array(
            '3',
            '1' ),
        '522.11'   => array(
            '3',
            '0' ),
        '412'      => array(
            '2',
            '0' ),
        '312'      => array(
            '1',
            '3' ),
        '125'      => array(
            '1',
            '2' ),
        '100'      => array(
            '1',
            '1' ),
        '85'       => array(
            '1',
            '0' ),
        '73'       => array(
            '0',
            '9' ),
        '48'       => array(
            '0',
            '8' ),
    );

    // OmniWeb build numbers to OmniWeb version numbers (if Version/X.Y.Z not present)
    /**
     * @var array
     */
    static protected $omniWebVersions = array(
        '622.15' => array(
            '5',
            '11' ),
        '622.10' => array(
            '5',
            '10' ),
        '622.8'  => array(
            '5',
            '9' ),
        '622.3'  => array(
            '5',
            '8' ),
        '621'    => array(
            '5',
            '7' ),
        '613'    => array(
            '5',
            '6' ),
        '607'    => array(
            '5',
            '5' ),
        '563.34' => array(
            '5',
            '1' ),
        '558.36' => array(
            '5',
            '0' ),
        '496'    => array(
            '4',
            '5' ),
    );

    // OS regex => OS ID
    /**
     * @var array
     */
    static protected $operatingSystems = array(
        'Android'                      => 'AND',
        'Maemo'                        => 'MAE',
        'kubuntu'                      => 'LUB',
        'Ubuntu'                       => 'LUB',
        'OpenSuse'                     => 'LSU',
        'SuSe'                         => 'LSU',
        'Fedora'                       => 'LFE',
        'mandra'                       => 'LMA',
        'CentOs'                       => 'LCE',
        'Debian'                       => 'LDE',
        'Redhat'                       => 'LRH',
        'Red Hat'                      => 'LRH',
        'CrOS '                        => 'LIN',
        'Linux'                        => 'LIN',
        'Xbox'                         => 'XBX',
        // workaround for vendors who changed the WinPhone 7 user agent
        'WP7'                          => 'WPH',
        'CYGWIN_NT-6.2'                => 'WI8',
        'Windows NT 6.2'               => 'WI8',
        'Windows 8'                    => 'WI8',
        'CYGWIN_NT-6.1'                => 'WI7',
        'Windows NT 6.1'               => 'WI7',
        'Windows 7'                    => 'WI7',
        'CYGWIN_NT-6.0'                => 'WVI',
        'Windows NT 6.0'               => 'WVI',
        'Windows Vista'                => 'WVI',
        'CYGWIN_NT-5.2'                => 'WS3',
        'Windows NT 5.2'               => 'WS3',
        'Windows Server 2003 / XP x64' => 'WS3',
        'CYGWIN_NT-5.1'                => 'WXP',
        'Windows NT 5.1'               => 'WXP',
        'Windows XP'                   => 'WXP',
        'CYGWIN_NT-5.0'                => 'W2K',
        'Windows NT 5.0'               => 'W2K',
        'Windows 2000'                 => 'W2K',
        'CYGWIN_NT-4.0'                => 'WNT',
        'Windows NT 4.0'               => 'WNT',
        'WinNT'                        => 'WNT',
        'Windows NT'                   => 'WNT',
        'CYGWIN_ME-4.90'               => 'WME',
        'Win 9x 4.90'                  => 'WME',
        'Windows ME'                   => 'WME',
        'CYGWIN_98-4.10'               => 'W98',
        'Win98'                        => 'W98',
        'Windows 98'                   => 'W98',
        'CYGWIN_95-4.0'                => 'W95',
        'Win32'                        => 'W95',
        'Win95'                        => 'W95',
        'Windows 95'                   => 'W95',
        'Windows 3'                    => 'W31',
        // Windows Phone OS 7 and above
        'Windows Phone OS'             => 'WPH',
        // Windows Mobile 6.x and some later versions of Windows Mobile 5
        'IEMobile'                     => 'WMO', // fallback
        'Windows Mobile'               => 'WMO',
        // Windows CE, Pocket PC, and Windows Mobile 5 are indistinguishable without vendor/device specific detection
        'Windows CE'                   => 'WCE',
        'iPod'                         => 'IPD',
        'iPad'                         => 'IPA',
        'iPhone'                       => 'IPH',
        // 'iOS'                          => 'IOS',
        'Darwin'                       => 'MAC',
        'Macintosh'                    => 'MAC',
        'Power Macintosh'              => 'MAC',
        'Mac_PowerPC'                  => 'MAC',
        'Mac PPC'                      => 'MAC',
        'PPC'                          => 'MAC',
        'Mac PowerPC'                  => 'MAC',
        'Mac OS'                       => 'MAC',
        'webOS'                        => 'WOS',
        'Palm webOS'                   => 'WOS',
        'PalmOS'                       => 'POS',
        'Palm OS'                      => 'POS',
        'BB10'                         => 'BBX',
        'BlackBerry'                   => 'BLB',
        'RIM Tablet OS'                => 'QNX',
        'QNX'                          => 'QNX',
        'SymbOS'                       => 'SYM',
        'Symbian OS'                   => 'SYM',
        'SymbianOS'                    => 'SYM',
        'bada'                         => 'SBA',
        'SunOS'                        => 'SOS',
        'AIX'                          => 'AIX',
        'HP-UX'                        => 'HPX',
        'OpenVMS'                      => 'VMS',
        'FreeBSD'                      => 'BSD',
        'NetBSD'                       => 'NBS',
        'OpenBSD'                      => 'OBS',
        'DragonFly'                    => 'DFB',
        'Syllable'                     => 'SYL',
        'Nintendo WiiU'                => 'WIU',
        'Nintendo Wii'                 => 'WII',
        'Nitro'                        => 'NDS',
        'Nintendo DSi'                 => 'DSI',
        'Nintendo DS'                  => 'NDS',
        'Nintendo 3DS'                 => '3DS',
        'PlayStation Vita'             => 'PSV',
        'PlayStation Portable'         => 'PSP',
        'PlayStation 3'                => 'PS3',
        'IRIX'                         => 'IRI',
        'OSF1'                         => 'T64',
        'OS/2'                         => 'OS2',
        'BEOS'                         => 'BEO',
        'Amiga'                        => 'AMI',
        'AmigaOS'                      => 'AMI',
    );

    // os family
    // NOTE: The keys in this array are used by plugins/UserSettings/functions.php . Any  changes
    // made here should also be made in that file.
    /**
     * @var array
     */
    static protected $osType = array(
        'Windows'               => array(
            'WI8',
            'WI7',
            'WVI',
            'WS3',
            'WXP',
            'W2K',
            'WNT',
            'WME',
            'W98',
            'W95',
            'W31' ),
        'Linux'                 => array(
            'LIN',
            'LUB',
            'LSU',
            'LFE',
            'LMA',
            'LCE',
            'LDE',
            'LRH' ),
        'Mac'                   => array(
            'MAC' ),
        'iOS'                   => array(
            'IPD',
            'IPA',
            'IPH' ),
        'Android'               => array(
            'AND' ),
        'Windows Mobile'        => array(
            'WPH',
            'WMO',
            'WCE' ),
        'Gaming Console'        => array(
            'WII',
            'WIU',
            'PS3',
            'XBX' ),
        'Mobile Gaming Console' => array(
            'PSP',
            'PSV',
            'NDS',
            'DSI',
            '3DS' ),
        'Unix'                  => array(
            'SOS',
            'AIX',
            'HP-UX',
            'BSD',
            'NBS',
            'OBS',
            'DFB',
            'SYL',
            'IRI',
            'T64' ),
        'Other Mobile'          => array(
            'MAE',
            'WOS',
            'POS',
            'BLB',
            'QNX',
            'SYM',
            'SBA' ),
        'Other'                 => array(
            'VMS',
            'OS2',
            'BEOS',
            'AMI' )
    );

    /**
     * @var
     */
    static protected $browserIdToName;

    /**
     * @var
     */
    static protected $browserIdToShortName;

    /**
     * @var
     */
    static protected $operatingSystemsIdToName;

    /**
     * @var
     */
    static protected $operatingSystemsIdToShortName;

    /**
     * @var bool
     */
    static private $init = false;

    /**
     * Returns an array of the OS for the submitted user agent
     *        'id' => '',
     *        'name' => '',
     *        'short_name' => '',
     *
     * @param string $userAgent
     * @return string false if OS couldn't be identified, or 3 letters ID (eg. WXP)
     * @see UserAgentParser/OperatingSystems.php for the list of OS (also available in self::$operatingSystems)
     */
    static public function getOperatingSystem( $userAgent )
    {
        $userAgent = self::cleanupUserAgent( $userAgent );
        self::init();
        $info = array(
            'id'         => '',
            'name'       => '',
            'short_name' => '',
        );
        foreach ( self::$operatingSystems as $key => $value )
        {
            if ( stristr( $userAgent, $key ) !== false )
            {
                $info[ 'id' ] = $value;
                break;
            }
        }
        if ( empty( $info[ 'id' ] ) )
        {
            return false;
        }
        $info[ 'name' ] = self::getOperatingSystemNameFromId( $info[ 'id' ] );
        $info[ 'short_name' ] = self::getOperatingSystemShortNameFromId( $info[ 'id' ] );
        return $info;
    }

    /**
     * @param $userAgent
     * @return string
     */
    static protected function cleanupUserAgent( $userAgent )
    {
        // in case value is URL encoded
        return urldecode( $userAgent );
    }

    /**
     * Returns the browser information array, given a user agent string.
     *
     * @param string $userAgent
     * @return array false if the browser is "unknown", or
     *                array(        'id'            => '', // 2 letters ID, eg. FF
     *                            'name'            => '', // 2 letters ID, eg. FF
     *                            'short_name'    => '', // 2 letters ID, eg. FF
     *                            'major_number'    => '', // 2 in firefox 2.0.12
     *                            'minor_number'    => '', // 0 in firefox 2.0.12
     *                            'version'        => '', // major_number.minor_number
     *                );
     * @see self::$browsers for the list of OS
     */
    static public function getBrowser( $userAgent )
    {
        $userAgent = self::cleanupUserAgent( $userAgent );

        self::init();

        $info = array(
            'id'           => '',
            'name'         => '',
            'short_name'   => '',
            'major_number' => '',
            'minor_number' => '',
            'version'      => '',
        );

        $browsers = self::$browsers;

        // derivative browsers often clone the base browser's useragent
        unset( $browsers[ 'firefox' ] );
        unset( $browsers[ 'mozilla' ] );
        unset( $browsers[ 'safari' ] );
        unset( $browsers[ 'applewebkit' ] );

        $browsersPattern = str_replace( ')', '\)', implode( '|', array_keys( $browsers ) ) );

        $results = array();

        // Misbehaving IE add-ons
        $userAgent = preg_replace( '/[; ]Mozilla\/[0-9.]+ \([^)]+\)/', '', $userAgent );

        // Clean-up BlackBerry device UAs
        $userAgent = preg_replace( '~^BlackBerry\d+/~', 'BlackBerry/', $userAgent );

        if ( preg_match_all( "/($browsersPattern)[\/\sa-z(]*([0-9]+)([\.0-9a-z]+)?/i", $userAgent, $results ) || (strpos( $userAgent, 'Shiira' ) === false && preg_match_all( "/(firefox|thunderbird|safari)[\/\sa-z(]*([0-9]+)([\.0-9a-z]+)?/i", $userAgent, $results )) || preg_match_all( "/(applewebkit)[\/\sa-z(]*([0-9]+)([\.0-9a-z]+)?/i", $userAgent, $results ) || preg_match_all( "/^(mozilla)\/([0-9]+)([\.0-9a-z-]+)?(?: \[[a-z]{2}\])? (?:\([^)]*\))$/i", $userAgent, $results ) || preg_match_all( "/^(mozilla)\/[0-9]+(?:[\.0-9a-z-]+)?\s\(.* rv:([0-9]+)([.0-9a-z]+)\) gecko(\/[0-9]{8}|$)(?:.*)/i", $userAgent, $results ) || (strpos( $userAgent, 'Nintendo 3DS' ) !== false && preg_match_all( "/^(mozilla).*version\/([0-9]+)([.0-9a-z]+)?/i", $userAgent, $results ))
        )
        {
            // browser code (usually the first match)
            $count = 0;
            $info[ 'id' ] = self::$browsers[ strtolower( $results[ 1 ][ 0 ] ) ];

            // sometimes there's a better match at the end
            if ( strpos( $userAgent, 'chromeframe' ) !== false )
            {
                $count = count( $results[ 0 ] ) - 1;
                $info[ 'id' ] = 'CF';
            }
            elseif ( ($info[ 'id' ] == 'IE' || $info[ 'id' ] == 'LX') && (count( $results[ 0 ] ) > 1) )
            {
                $count = count( $results[ 0 ] ) - 1;
                $info[ 'id' ] = self::$browsers[ strtolower( $results[ 1 ][ $count ] ) ];
            }

            // Netscape fix
            if ( $info[ 'id' ] == 'MO' && $count == 0 )
            {
                if ( stripos( $userAgent, 'PlayStation' ) !== false )
                {
                    return false;
                }
                if ( strpos( $userAgent, 'Nintendo 3DS' ) !== false )
                {
                    $info[ 'id' ] = 'NF';
                }
                elseif ( count( $results ) == 4 )
                {
                    $info[ 'id' ] = 'NS';
                }
            } // BlackBerry devices
            elseif ( strpos( $userAgent, 'BlackBerry' ) !== false )
            {
                $info[ 'id' ] = 'BB';
            }
            elseif ( strpos( $userAgent, 'RIM Tablet OS' ) !== false )
            {
                $info[ 'id' ] = 'BP';
            }
            elseif ( strpos( $userAgent, 'BB10' ) !== false )
            {
                $info[ 'id' ] = 'B2';
            }
            elseif ( strpos( $userAgent, 'Playstation Vita' ) !== false )
            {
                $info[ 'id' ] = 'NF';

                if ( preg_match_all( "/(silk)[\/\sa-z(]*([0-9]+)([\.0-9a-z]+)?/i", $userAgent, $newResults ) )
                {
                    $results = $newResults;
                    $count = count( $results[ 0 ] ) - 1;
                }
            }

            // Version/X.Y.Z override
            if ( preg_match_all( "/(version)[\/\sa-z(]*([0-9]+)([\.0-9a-z]+)?/i", $userAgent, $newResults ) )
            {
                $results = $newResults;
                $count = count( $results[ 0 ] ) - 1;
            }

            // major version number (1 in mozilla 1.7)
            $info[ 'major_number' ] = $results[ 2 ][ $count ];

            // is an minor version number ? If not, 0
            $match = array();

            preg_match( '/([.\0-9]+)?([\.a-z0-9]+)?/i', $results[ 3 ][ $count ], $match );

            if ( isset( $match[ 1 ] ) )
            {
                // find minor version number (7 in mozilla 1.7, 9 in firefox 0.9.3)
                $dot = strpos( substr( $match[ 1 ], 1 ), '.' );
                if ( $dot !== false )
                {
                    $info[ 'minor_number' ] = substr( $match[ 1 ], 1, $dot );
                }
                else
                {
                    $info[ 'minor_number' ] = substr( $match[ 1 ], 1 );
                }
            }
            else
            {
                $info[ 'minor_number' ] = '0';
            }
            $info[ 'version' ] = $info[ 'major_number' ] . '.' . $info[ 'minor_number' ];

            // IE compatibility mode
            if ( $info[ 'id' ] == 'IE' && strncmp( $userAgent, 'Mozilla/4.0', 11 ) == 0 && preg_match( '~ Trident/([0-9]+)\.[0-9]+~', $userAgent, $tridentVersion ) )
            {
                $info[ 'major_number' ] = $tridentVersion[ 1 ] + 4;
                $info[ 'minor_number' ] = '0';
                $info[ 'version' ] = $info[ 'major_number' ] . '.' . $info[ 'minor_number' ];
            }

            // Safari fix
            if ( $info[ 'id' ] == 'SF' )
            {
                foreach ( self::$safariVersions as $buildVersion => $productVersion )
                {
                    if ( version_compare( $info[ 'version' ], $buildVersion ) >= 0 )
                    {
                        $info[ 'major_number' ] = $productVersion[ 0 ];
                        $info[ 'minor_number' ] = $productVersion[ 1 ];
                        $info[ 'version' ] = $info[ 'major_number' ] . '.' . $info[ 'minor_number' ];
                        break;
                    }
                }
            }

            // OmniWeb fix
            if ( $info[ 'id' ] == 'OW' )
            {
                foreach ( self::$omniWebVersions as $buildVersion => $productVersion )
                {
                    if ( version_compare( $info[ 'version' ], $buildVersion ) >= 0 )
                    {
                        $info[ 'major_number' ] = $productVersion[ 0 ];
                        $info[ 'minor_number' ] = $productVersion[ 1 ];
                        $info[ 'version' ] = $info[ 'major_number' ] . '.' . $info[ 'minor_number' ];
                        break;
                    }
                }
            }

            // SeaMonkey fix
            if ( $info[ 'id' ] == 'MO' && $info[ 'version' ] == '1.9' )
            {
                $info[ 'id' ] = 'SM';
            }

            $info[ 'name' ] = self::getBrowserNameFromId( $info[ 'id' ] );
            $info[ 'short_name' ] = self::getBrowserShortNameFromId( $info[ 'id' ] );

            return $info;
        }

        return false;
    }

    static protected function init()
    {
        if ( self::$init )
        {
            return;
        }
        self::$init = true;

        // init browser names and short names
        self::$browserIdToName = array_map( 'ucwords', array_flip( self::$browsers ) );
        self::$browserIdToName[ 'AB' ] = 'ABrowse';
        self::$browserIdToName[ 'AV' ] = 'AmigaVoyager';
        self::$browserIdToName[ 'AW' ] = 'Amiga AWeb';
        self::$browserIdToName[ 'BB' ] = 'BlackBerry';
        self::$browserIdToName[ 'BP' ] = 'PlayBook';
        self::$browserIdToName[ 'B2' ] = 'BlackBerry';
        self::$browserIdToName[ 'BX' ] = 'BrowseX';
        self::$browserIdToName[ 'CF' ] = 'Chrome Frame';
        self::$browserIdToName[ 'CO' ] = 'CometBird';
        self::$browserIdToName[ 'EL' ] = 'ELinks';
        self::$browserIdToName[ 'FF' ] = 'Firefox';
        self::$browserIdToName[ 'HJ' ] = 'HotJava';
        self::$browserIdToName[ 'IB' ] = 'IBrowse';
        self::$browserIdToName[ 'IC' ] = 'iCab';
        self::$browserIdToName[ 'KM' ] = 'K-Meleon';
        self::$browserIdToName[ 'MC' ] = 'NCSA Mosaic';
        self::$browserIdToName[ 'NF' ] = 'NetFront';
        self::$browserIdToName[ 'OW' ] = 'OmniWeb';
        self::$browserIdToName[ 'SF' ] = 'Safari';
        self::$browserIdToName[ 'SM' ] = 'SeaMonkey';
        self::$browserIdToName[ 'WO' ] = 'Palm webOS';
        self::$browserIdToName[ 'WP' ] = 'WebPro';


        self::$browserIdToName[ 'nokia' ] = 'Nokia Phone';
        self::$browserIdToName[ 'libwww' ] = 'Lib-WWW';
        self::$browserIdToName[ 'curl' ] = 'CURL';


        self::$browserIdToShortName = self::$browserIdToName;
        self::$browserIdToShortName[ 'AW' ] = 'AWeb';
        self::$browserIdToShortName[ 'FB' ] = 'Firebird';
        self::$browserIdToShortName[ 'IE' ] = 'IE';
        self::$browserIdToShortName[ 'MC' ] = 'Mosaic';
        self::$browserIdToShortName[ 'BP' ] = 'PlayBook';
        self::$browserIdToShortName[ 'WO' ] = 'webOS';

        // init OS names and short names
        self::$operatingSystemsIdToName = array_merge( array_flip( self::$operatingSystems ), array(
            'IPD' => 'iPod',
            'IPA' => 'iPad',
            'WME' => 'Windows Me',
            'BEO' => 'BeOS',
            'T64' => 'Tru64',
            'NDS' => 'Nintendo DS',
            'WIU' => 'Nintendo Wii U',
            '3DS' => 'Nintendo 3DS',
            // These are for BC purposes only
            'W75' => 'WinPhone 7.5',
            'WP7' => 'WinPhone 7',
            'W65' => 'WinMo 6.5',
            'W61' => 'WinMo 6.1',
                ) );
        self::$operatingSystemsIdToShortName = array_merge( self::$operatingSystemsIdToName, array(
            'PS3' => 'PS3',
            'PSP' => 'PSP',
            'WII' => 'Wii',
            'WIU' => 'Wii U',
            'NDS' => 'DS',
            'DSI' => 'DSi',
            '3DS' => '3DS',
            'PSV' => 'PS Vita',
            'WI8' => 'Win 8',
            'WI7' => 'Win 7',
            'WVI' => 'Win Vista',
            'WS3' => 'Win S2003',
            'WXP' => 'Win XP',
            'W98' => 'Win 98',
            'W2K' => 'Win 2000',
            'WNT' => 'Win NT',
            'WME' => 'Win Me',
            'W95' => 'Win 95',
            'WPH' => 'WinPhone',
            'WMO' => 'WinMo',
            'WCE' => 'Win CE',
            'WOS' => 'webOS',
            'UNK' => 'Unknown',
                ) );
    }

    /**
     * @param $browserId
     * @return bool
     */
    static public function getBrowserNameFromId( $browserId )
    {
        self::init();
        if ( isset( self::$browserIdToName[ $browserId ] ) )
        {
            return self::$browserIdToName[ $browserId ];
        }
        return false;
    }

    /**
     * @param $browserId
     * @return bool
     */
    static public function getBrowserShortNameFromId( $browserId )
    {
        self::init();
        if ( isset( self::$browserIdToShortName[ $browserId ] ) )
        {
            return self::$browserIdToShortName[ $browserId ];
        }
        return false;
    }

    /**
     * @param $browserId
     * @return int|string
     */
    static public function getBrowserFamilyFromId( $browserId )
    {
        self::init();
        $familyNameToUse = 'unknown';
        foreach ( self::$browserType as $familyName => $aBrowsers )
        {
            if ( in_array( $browserId, $aBrowsers ) )
            {
                $familyNameToUse = $familyName;
                break;
            }
        }
        return $familyNameToUse;
    }

    /**
     * @param $osId
     * @return bool
     */
    static public function getOperatingSystemNameFromId( $osId )
    {
        self::init();
        if ( isset( self::$operatingSystemsIdToName[ $osId ] ) )
        {
            return self::$operatingSystemsIdToName[ $osId ];
        }
        return false;
    }

    /**
     * @param $osId
     * @return bool
     */
    static public function getOperatingSystemShortNameFromId( $osId )
    {
        self::init();
        if ( isset( self::$operatingSystemsIdToShortName[ $osId ] ) )
        {
            return self::$operatingSystemsIdToShortName[ $osId ];
        }
        return false;
    }

    /**
     * @param $osName
     * @return bool
     */
    static public function getOperatingSystemIdFromName( $osName )
    {
        return isset( self::$operatingSystems[ $osName ] ) ? self::$operatingSystems[ $osName ] : false;
    }

    /**
     * @param $osId
     * @return int|string
     */
    static public function getOperatingSystemFamilyFromId( $osId )
    {
        self::init();
        foreach ( self::$osType as $familyName => $aSystems )
        {
            if ( in_array( $osId, $aSystems ) )
            {
                return $familyName;
            }
        }
        return 'unknown';
    }

}
