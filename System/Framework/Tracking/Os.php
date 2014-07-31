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
 * @file        Os.php
 *
 */
class Tracking_Os extends Tracking_Abstract
{

    /**
     * @var array
     */
    private static $OSGroupHasch = array(
// Windows
        'winlong'       => 'win',
        'win2003'       => 'win',
        'winseven'      => 'win',
        'winxp'         => 'win',
        'winme'         => 'win',
        'win2000'       => 'win',
        'winnt'         => 'win',
        'win98'         => 'win',
        'win95'         => 'win',
        'win16'         => 'win',
        'wince'         => 'win',
        'winunknown'    => 'win',
        'winxbox'       => 'win',
// Mac
        'macosx'        => 'mac',
        'macintosh'     => 'mac',
// Linux
        'linuxcentos'   => 'linuxcentos',
        'linuxdebian'   => 'linuxdebian',
        'linuxmandr'    => 'linuxmandr',
        'linuxredhat'   => 'linuxredhat',
        'linuxopensuse' => 'linuxopensuse',
        'linuxsuse'     => 'linuxsuse',
        'linuxubuntu'   => 'linuxubuntu',
        'linux'         => 'linux',
// BSD
        'bsdi'          => 'bsd',
        'bsdkfreebsd'   => 'bsd',
        'bsdopenbsd'    => 'bsd',
        'bsdnetbsd'     => 'bsd',
// Other
        'aix'           => 'other',
        'sunos'         => 'other',
        'irix'          => 'other',
        'osf'           => 'other',
        'hp-ux'         => 'other',
        'unix'          => 'other',
        'beos'          => 'other',
        'os/2'          => 'other',
        'amigaos'       => 'other',
        'atari'         => 'other',
        'vms'           => 'other',
        'commodore'     => 'other',
        'cp/m'          => 'other',
        'crayos'        => 'other',
        'dreamcast'     => 'other',
        'riscos'        => 'other',
        'symbian'       => 'other',
        'webtv'         => 'other',
        'psp'           => 'other',
    );

# OSHashID
# Each OS Search ID is associated to a string that is the AWStats id and
# also the name of icon file for this OS.
#--------------------------------------------------------------------------
    /**
     * @var array
     */

    private static $OSHaschID = array(
        # Windows OS family
        'windows\s*2005'            => 'winlong',
        'windows\s*nt\s*6\.0'       => 'winlong',
        'windows\s*nt\s*6\.1'       => 'winseven',
        'windows\s*2003'            => 'win2003',
        'windows\s*nt\s*5\.2'       => 'win2003',
        'windows\s*xp'              => 'winxp',
        'windows(\s*)?nt(\s*)?5\.1' => 'winxp',
        'syndirella'                => 'winxp',
        'windows\s*me'              => 'winme',
        'win\s*9x'                  => 'winme',
        'windows\s*2000'            => 'win2000',
        'windows\s*nt\s*5'          => 'win2000',
        'winnt'                     => 'winnt',
        'windows\s*-?nt'            => 'winnt',
        'win32'                     => 'winnt',
        'win(.*)98'                 => 'win98',
        'win(.*)95'                 => 'win95',
        'win(.*)16'                 => 'win16',
        'windows\s*3'               => 'win16',
        'win(.*)ce'                 => 'wince',
        'microsoft'                 => 'winunknown',
        'msie\s*'                   => 'winunknown',
        'ms\s*frontpage'            => 'winunknown',
        # Macintosh OS family
        'mac\s*os\s*x'              => 'macosx',
        'vienna'                    => 'macosx',
        'newsfire'                  => 'macosx',
        'applesyndication'          => 'macosx',
        'mac\s*p'                   => 'macintosh',
        'mac\s*68'                  => 'macintosh',
        'macweb'                    => 'macintosh',
        'macintosh'                 => 'macintosh',
        # Linux family (linuxyyy)
        'centos'                    => 'linuxcentos',
        'debian'                    => 'linuxdebian',
        'fedora'                    => 'linuxfedora',
        'mandr'                     => 'linuxmandr',
        'red\s*hat'                 => 'linuxredhat',
        'opensuse'                  => 'linuxopensuse',
        'suse'                      => 'linuxsuse',
        'kubuntu'                   => 'linuxubuntu',
        'ubuntu'                    => 'linuxubuntu',
        'linux'                     => 'linux',
        'akregator'                 => 'linux',
        # Hurd family
        'gnu\.hurd'                 => 'gnu',
        # BSDs family (bsdyyy)
        'bsdi'                      => 'bsdi',
        'gnu\.kfreebsd'             => 'bsdkfreebsd', # Must be before freebsd
        'freebsd'                   => 'bsdfreebsd',
        'openbsd'                   => 'bsdopenbsd',
        'netbsd'                    => 'bsdnetbsd',
        # Other Unix, Unix-like
        'aix'                       => 'aix',
        'sunos'                     => 'sunos',
        'irix'                      => 'irix',
        'osf'                       => 'osf',
        'hp-ux'                     => 'hp-ux',
        'unix'                      => 'unix',
        'x11'                       => 'unix',
        'gnome-vfs'                 => 'unix',
        'plagger'                   => 'unix',
        # Other famous OS
        'beos'                      => 'beos',
        'os\/2'                     => 'os/2',
        'amiga'                     => 'amigaos',
        'atari'                     => 'atari',
        'vms'                       => 'vms',
        'commodore'                 => 'commodore',
        # Miscellanous OS
        'cp\/m'                     => 'cp/m',
        'crayos'                    => 'crayos',
        'dreamcast'                 => 'dreamcast',
        'risc\s*os'                 => 'riscos',
        'symbian'                   => 'symbian',
        'webtv'                     => 'webtv',
        'playstation\s*portable'    => 'psp',
        'xbox'                      => 'winxbox',
        'Nintendo\s*Wii'            => 'wii',
    );

# OS name list ('os unique id in lower case','os clear text')
# Each unique ID string is associated to a label
#-----------------------------------------------------------
    /**
     * @var array
     */

    private static $OSHashLib = array(
        # Windows family OS
        'winlong'     => '<a href="http://www.microsoft.com/windows/" title="Windows Vista home page" target="_blank">Windows Vista</a>',
        'winseven'    => '<a href="http://www.microsoft.com/windows/Windows-7/" title="Windows Seven home page" target="_blank">Windows 7</a>',
        'win2003'     => '<a href="http://www.microsoft.com/windowsserver2003/" title="Windows 2003 home page" target="_blank">Windows 2003</a>',
        'winxp'       => '<a href="http://www.microsoft.com/windowsxp/" title="Windows XP home page" target="_blank">Windows XP</a>',
        'winme'       => '<a href="http://www.microsoft.com/windowsme/" title="Windows Me home page" target="_blank">Windows Me</a>',
        'win2000'     => '<a href="http://www.microsoft.com/windows2000/" title="Windows 2000 home page" target="_blank">Windows 2000</a>',
        'winnt'       => '<a href="http://www.microsoft.com/ntworkstation/" title="Windows NT home page" target="_blank">Windows NT</a>',
        'win98'       => '<a href="http://www.microsoft.com/windows98/" title="Windows 98 home page" target="_blank">Windows 98</a>',
        'win95'       => '<a href="http://www.microsoft.com/windows95/" title="Windows 95 home page" target="_blank">Windows 95</a>',
        'win16'       => '<a href="http://www.microsoft.com/" title="Windows 3.xx home page" target="_blank">Windows 3.xx</a>',
        'wince'       => '<a href="http://www.microsoft.com/windowsmobile/" title="Windows CE home page" target="_blank">Windows CE</a>',
        'winunknown'  => 'Windows (unknown version)',
        'winxbox'     => '<a href="http://www.xbox.com/en-US/hardware/xbox/" title="Microsoft XBOX home page" target="_blank">Microsoft XBOX</a>',
        # Macintosh OS
        'macosx'      => '<a href="http://www.apple.com/macosx/" title="Mac OS X home page" target="_blank">Mac OS X</a>',
        'macintosh'   => '<a href="http://www.apple.com/" title="Mac OS home page" target="_blank">Mac OS</a>',
        # Linux
        'linuxcentos' => '<a href="http://www.centos.org/" title="Centos home page" target="_blank">Centos</a>',
        'linuxdebian' => '<a href="http://www.debian.org/" title="Debian home page" target="_blank">Debian</a>',
        'linuxfedora' => '<a href="http://fedora.redhat.com/" title="Fedora home page" target="_blank">Fedora</a>',
        'linuxmandr'  => '<a href="http://www.mandriva.com/" title="Mandriva (former Mandrake) home page" target="_blank">Mandriva (or Mandrake)</a>',
        'linuxredhat' => '<a href="http://www.redhat.com/" title="Red Hat home page" target="_blank">Red Hat</a>',
        'linuxsuse'   => '<a href="http://www.novell.com/linux/suse/" title="Suse home page" target="_blank">Suse</a>',
        'linuxubuntu' => '<a href="http://www.ubuntulinux.org/" title="Ubuntu home page" target="_blank">Ubuntu</a>',
        'linux'       => '<a href="http://www.distrowatch.com/" title="Linux DistroWatch home page. Useful if you find the associated user agent string in your logs." target="_blank">Linux (Unknown/unspecified)</a>',
        #'linux' => 'GNU Linux <small>(Unknown or unspecified distribution)</small>',
        # Hurd
        'gnu'         => '<a href="www.gnu.org/software/hurd/hurd.html" title="GNU Hurd home page" target="_blank">GNU Hurd</a>',
        # BSDs
        'bsdi'        => '<a href="http://en.wikipedia.org/wiki/BSDi" title="BSDi home page" target="_blank">BSDi</a>',
        'bsdkfreebsd' => 'GNU/kFreeBSD',
        'freebsd'     => '<a href="http://www.freebsd.org/" title="FreeBSD home page" target="_blank">FreeBSD</a>', # For backard compatibility
        'bsdfreebsd'  => '<a href="http://www.freebsd.org/" title="FreeBSD home page" target="_blank">FreeBSD</a>',
        'openbsd'     => '<a href="http://www.openbsd.org/" title="OpenBSD home page" target="_blank">OpenBSD</a>', # For backard compatibility
        'bsdopenbsd'  => '<a href="http://www.openbsd.org/" title="OpenBSD home page" target="_blank">OpenBSD</a>',
        'netbsd'      => '<a href="http://www.netbsd.org/" title="NetBSD home page" target="_blank">NetBSD</a>', # For backard compatibility
        'bsdnetbsd'   => '<a href="http://www.netbsd.org/" title="NetBSD home page" target="_blank">NetBSD</a>',
        # Other Unix, Unix-like
        'aix'         => '<a href="http://www-1.ibm.com/servers/aix/" title="Aix home page" target="_blank">Aix</a>',
        'sunos'       => '<a href="http://www.sun.com/software/solaris/" title="Sun Solaris home page" target="_blank">Sun Solaris</a>',
        'irix'        => '<a href="http://www.sgi.com/products/software/irix/" title="Irix home page" target="_blank">Irix</a>',
        'osf'         => '<a href="http://www.tru64.org/" title="OSF Unix home page" target="_blank">OSF Unix</a>',
        'hp-ux'       => '<a href="http://www.hp.com/products1/unix/operating/" title="HP UX home page" target="_blank">HP UX</a>',
        'unix'        => 'Unknown Unix system',
        # Other famous OS
        'beos'        => '<a href="http://www.beincorporated.com/" title="BeOS home page" target="_blank">BeOS</a>',
        'os/2'        => '<a href="http://www.ibm.com/software/os/warp/" title="OS/2 home page" target="_blank">OS/2</a>',
        'amigaos'     => '<a href="http://www.amiga.com/amigaos/" title="AmigaOS home page" target="_blank">AmigaOS</a>',
        'atari'       => '<a href="http://www.atarimuseum.com/computers/computers.html" title="Atari home page" target="_blank">Atari</a>',
        'vms'         => '<a href="http://h71000.www7.hp.com/" title="VMS home page" target="_blank">VMS</a>',
        'commodore'   => '<a href="http://en.wikipedia.org/wiki/Commodore_64" title="Commodore 64 wikipedia page" target="_blank">Commodore 64</a>',
        # Miscellanous OS
        'cp/m'        => '<a href="http://www.digitalresearch.biz/CPM.HTM" title="CPM home page" target="_blank">CPM</a>',
        'crayos'      => '<a href="http://www.cray.com/" title="CrayOS home page" target="_blank">CrayOS</a>',
        'dreamcast'   => '<a href="http://www.sega.com/" title="Dreamcast home page" target="_blank">Dreamcast</a>',
        'riscos'      => '<a href="http://www.riscos.com/" title="RISC OS home page" target="_blank">RISC OS</a>',
        'symbian'     => '<a href="http://www.symbian.com/" title="Symbian OS home page" target="_blank">Symbian OS</a>',
        'webtv'       => '<a href="http://www.webtv.com/" title="WebTV home page" target="_blank">WebTV</a>',
        'psp'         => '<a href="http://www.playstation.jp/psp/" title="Sony PlayStation Portable home page" target="_blank">Sony PlayStation Portable</a>',
        'wii'         => '<a href="http://www.nintendo.de/NOE/de_DE/wii_54.html" title="Nintendo Wii" target="_blank">Nintendo Wii</a>'
    );

    /**
     * Get Operation System
     *
     *
     * @staticvar array $OSHaschID
     * @param <type> $user_agent
     * @return <type>
     */
    public static function getOS()
    {

        if ( self::$ua != null && strcmp( self::$ua, 'undefined' ) )
        {
            $data = Tracking_AgentParser::getOperatingSystem( self::$ua );

            if ( $data[ 'id' ] )
            {
                return $data[ 'id' ];
            }


            /*
              foreach (self::$OSHaschID as $regex => $key)
              {
              if (preg_match('#' . $regex . '#isU', self::$ua))
              {
              return $key;
              }
              } */
        }

        return null;
    }

}
