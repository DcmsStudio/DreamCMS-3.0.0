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
 * @file        Browser.php
 *
 */
class Tracking_Browser extends Tracking_Abstract
{

    /**
     * @var array
     */
    private static $BrowsersSearchIDOrder = array(
# Most frequent standard web browsers are first in this list (except msie, netscape and firefox)
        'firebird',
        'firefox',
        'go\!zilla',
        'icab',
        'konqueror',
        'links',
        'lynx',
        'omniweb',
        'opera',
        'msie',
# Other standard web browsers
        'crome',
        '22acidownload',
        'aol\-iweng',
        'amaya',
        'amigavoyager',
        'aweb',
        'bonecho',
        'bpftp',
        'camino',
        'chimera',
        'cyberdog',
        'dillo',
        'doris',
        'dreamcast',
        'xbox',
        'downloadagent',
        'ecatch',
        'emailsiphon',
        'encompass',
        'epiphany',
        'friendlyspider',
        'fresco',
        'galeon',
        'flashget',
        'freshdownload',
        'getright',
        'leechget',
        'netants',
        'headdump',
        'hotjava',
        'ibrowse',
        'intergo',
        'k\-meleon',
        'linemodebrowser',
        'lotus\-notes',
        'macweb',
        'multizilla',
        'ncsa_mosaic',
        'netcaptor',
        'netpositive',
        'nutscrape',
        'msfrontpageexpress',
        'phoenix',
        'shiira', # Must be before safari
        'safari',
        'tzgeturl',
        'viking',
        'webfetcher',
        'webexplorer',
        'webmirror',
        'webvcr',
# Site grabbers
        'teleport',
        'webcapture',
        'webcopier',
# Media only browsers
        'real',
        'winamp', # Works for winampmpeg and winamp3httprdr
        'windows\-media\-player',
        'audion',
        'freeamp',
        'itunes',
        'jetaudio',
        'mint_audio',
        'mpg123',
        'mplayer',
        'nsplayer',
        'qts',
        'sonique',
        'uplayer',
        'xaudio',
        'xine',
        'xmms',
# RSS Readers
        'abilon',
        'aggrevator',
        'akregator',
        'applesyndication',
        'betanews_reader',
        'blogbridge',
        'cyndicate',
        'feeddemon',
        'feedreader',
        'feedtools',
        'greatnews',
        'gregarius',
        'hatena_rss',
        'jetbrains_omea',
        'liferea',
        'netnewswire',
        'newsfire',
        'newsgator',
        'newzcrawler',
        'plagger',
        'pluck',
        'potu',
        'pubsub\-rss\-reader',
        'pulpfiction',
        'rssbandit',
        'rssreader',
        'rssowl',
        'rss\sxpress',
        'rssxpress',
        'sage',
        'sharpreader',
        'shrook',
        'straw',
        'syndirella',
        'vienna',
        'wizz\srss\snews\sreader',
# PDA/Phonecell browsers
        'alcatel', # Alcatel
        'lg\-', # LG
        'mot\-', # Motorola
        'nokia', # Nokia
        'panasonic', # Panasonic
        'philips', # Philips
        'sagem', # Sagem
        'samsung', # Samsung
        'sie\-', # SIE
        'sec\-', # SonyEricsson
        'sonyericsson', # SonyEricsson
        'ericsson', # Ericsson (must be after sonyericsson)
        'mmef',
        'mspie',
        'wapalizer',
        'wapsilon',
        'webcollage',
        'up\.', # Works for UP.Browser and UP.Link
# PDA/Phonecell I-Mode browsers
        'docomo',
        'portalmmm',
# Others (TV)
        'webtv',
# Anonymous Proxy Browsers (can be used as grabbers as well...)
        'cjb\.net',
        'ossproxy',
# Other kind of browsers
        'apt',
        'analogx_proxy',
        'gnome\-vfs',
        'neon',
        'curl',
        'csscheck',
        'httrack',
        'fdm',
        'javaws',
        'wget',
        'chilkat',
        'webdownloader\sfor\sx',
        'w3m',
        'wdg_validator',
        'webreaper',
        'webzip',
        'staroffice',
        'gnus',
        'nikto',
        'microsoft\-webdav\-miniredir',
        'microsoft\sdata\saccess\sinternet\spublishing\sprovider\scache\smanager',
        'microsoft\sdata\saccess\sinternet\spublishing\sprovider\sdav',
        'microsoft\sdata\saccess\sinternet\spublishing\sprovider\sprotocol\sdiscovery',
        'POE\-Component\-Client\-HTTP',
        'mozilla', # Must be at end because a lot of browsers contains mozilla in string
        'libwww', # Must be at end because some browser have both 'browser id' and 'libwww'
        'lwp'
    );

    /**
     * @var array
     */
    private static $BrowsersHashIDLib = array(
# Common web browsers text
        'AppleWebKit'                                                                  => 'Apple Web Kit',
        'msie'                                                                         => 'MS Internet Explorer',
        'ie'                                                                           => 'MS Internet Explorer',
        'crome'                                                                        => 'Google Crome',
        'GranParadiso'                                                                 => 'GranParadiso',
        'netscape'                                                                     => 'Netscape',
        'firefox'                                                                      => 'Firefox',
        'svn'                                                                          => 'Subversion client',
        'khtml'                                                                        => 'KHTML',
        'firebird'                                                                     => 'Firebird (Old Firefox)',
        'go!zilla'                                                                     => 'Go!Zilla',
        'icab'                                                                         => 'iCab',
        'konqueror'                                                                    => 'Konqueror',
        'links'                                                                        => 'Links',
        'lynx'                                                                         => 'Lynx',
        'omniweb'                                                                      => 'OmniWeb',
        'opera'                                                                        => 'Opera',
# Other standard web browsers
        '22acidownload'                                                                => '22AciDownload',
        'aol\-iweng'                                                                   => 'AOL-Iweng',
        'amaya'                                                                        => 'Amaya',
        'amigavoyager'                                                                 => 'AmigaVoyager',
        'aweb'                                                                         => 'AWeb',
        'bonecho'                                                                      => '<a href="http://www.mozilla.org/projects/bonecho/" title="Browser home page [new window]" target="_blank">BonEcho (Firefox 2.0 development)</a>',
        'bpftp'                                                                        => 'BPFTP',
        'camino'                                                                       => 'Camino',
        'chimera'                                                                      => 'Chimera (Old Camino)',
        'cyberdog'                                                                     => 'Cyberdog',
        'dillo'                                                                        => 'Dillo',
        'doris'                                                                        => 'Doris (for Symbian)',
        'dreamcast'                                                                    => 'Dreamcast',
        'xbox'                                                                         => 'XBoX',
        'downloadagent'                                                                => 'DownloadAgent',
        'ecatch'                                                                       => 'eCatch',
        'emailsiphon'                                                                  => 'EmailSiphon',
        'encompass'                                                                    => 'Encompass',
        'epiphany'                                                                     => 'Epiphany',
        'friendlyspider'                                                               => 'FriendlySpider',
        'fresco'                                                                       => 'ANT Fresco',
        'galeon'                                                                       => 'Galeon',
        'flashget'                                                                     => 'FlashGet',
        'freshdownload'                                                                => 'FreshDownload',
        'getright'                                                                     => 'GetRight',
        'leechget'                                                                     => 'LeechGet',
        'netants'                                                                      => 'NetAnts',
        'headdump'                                                                     => 'HeadDump',
        'hotjava'                                                                      => 'Sun HotJava',
        'ibrowse'                                                                      => 'iBrowse',
        'intergo'                                                                      => 'InterGO',
        'k\-meleon'                                                                    => 'K-Meleon',
        'linemodebrowser'                                                              => 'W3C Line Mode Browser',
        'lotus\-notes'                                                                 => 'Lotus Notes web client',
        'macweb'                                                                       => 'MacWeb',
        'multizilla'                                                                   => 'MultiZilla',
        'ncsa_mosaic'                                                                  => 'NCSA Mosaic',
        'netcaptor'                                                                    => 'NetCaptor',
        'netpositive'                                                                  => 'NetPositive',
        'nutscrape'                                                                    => 'Nutscrape',
        'msfrontpageexpress'                                                           => 'MS FrontPage Express',
        'phoenix'                                                                      => 'Phoenix',
        'shiira'                                                                       => 'Shiira',
        'safari'                                                                       => 'Safari',
        'tzgeturl'                                                                     => 'TzGetURL',
        'viking'                                                                       => 'Viking',
        'webfetcher'                                                                   => 'WebFetcher',
        'webexplorer'                                                                  => 'IBM-WebExplorer',
        'webmirror'                                                                    => 'WebMirror',
        'webvcr'                                                                       => 'WebVCR',
# Site grabbers
        'teleport'                                                                     => 'TelePort Pro',
        'webcapture'                                                                   => 'Acrobat Webcapture',
        'webcopier'                                                                    => 'WebCopier',
# Media only browsers
        'real'                                                                         => 'Real player or compatible (media player)',
        'winamp'                                                                       => 'WinAmp (media player)', # Works for winampmpeg and winamp3httprdr
        'windows\-media\-player'                                                       => 'Windows Media Player (media player)',
        'audion'                                                                       => 'Audion (media player)',
        'freeamp'                                                                      => 'FreeAmp (media player)',
        'itunes'                                                                       => 'Apple iTunes (media player)',
        'jetaudio'                                                                     => 'JetAudio (media player)',
        'mint_audio'                                                                   => 'Mint Audio (media player)',
        'mpg123'                                                                       => 'mpg123 (media player)',
        'mplayer'                                                                      => 'The Movie Player (media player)',
        'nsplayer'                                                                     => 'NetShow Player (media player)',
        'qts'                                                                          => 'Quicktime',
        'sonique'                                                                      => 'Sonique (media player)',
        'uplayer'                                                                      => 'Ultra Player (media player)',
        'xaudio'                                                                       => 'Some XAudio Engine based MPEG player (media player)',
        'xine'                                                                         => 'Xine, a free multimedia player (media player)',
        'xmms'                                                                         => 'XMMS (media player)',
# RSS Readers
        'abilon'                                                                       => 'Abilon (RSS Reader)',
        'aggrevator'                                                                   => 'Aggrevator (RSS Reader)',
        'akregator'                                                                    => '<a href="http://akregator.sourceforge.net/" title="Browser home page [new window]" target="_blank">Akregator (RSS Reader)</a>',
        'applesyndication'                                                             => '<a href="http://www.apple.com/macosx/features/safari/" title="Browser home page [new window]" target="_blank">AppleSyndication (RSS Reader)</a>',
        'betanews_reader'                                                              => 'Betanews Reader (RSS Reader)',
        'blogbridge'                                                                   => '<a href="http://www.blogbridge.com/" title="Browser home page [new window]" target="_blank">BlogBridge (RSS Reader)</a>',
        'cyndicate'                                                                    => 'Cyndicate (RSS Reader)',
        'feeddemon'                                                                    => 'FeedDemon (RSS Reader)',
        'feedreader'                                                                   => 'FeedReader (RSS Reader)',
        'feedtools'                                                                    => '<a href="http://sporkmonger.com/projects/feedtools/" title="Browser home page [new window]" target="_blank">FeedTools (RSS Reader)</a>',
        'greatnews'                                                                    => '<a href="http://www.curiostudio.com/" title="Browser home page [new window]" target="_blank">GreatNews (RSS Reader)</a>',
        'gregarius'                                                                    => '<a href="http://devlog.gregarius.net/docs/ua" title="Browser home page [new window]" target="_blank">Gregarius (RSS Reader)</a>',
        'hatena_rss'                                                                   => '<a href="http://r.hatena.ne.jp/" title="Browser home page [new window]" target="_blank">Hatena (RSS Reader)</a>',
        'jetbrains_omea'                                                               => 'Omea (RSS Reader)',
        'liferea'                                                                      => '<a href="http://liferea.sourceforge.net/" title="Browser home page [new window]" target="_blank">Liferea (RSS Reader)</a>',
        'netnewswire'                                                                  => 'NetNewsWire (RSS Reader)',
        'newsfire'                                                                     => 'NewsFire (RSS Reader)',
        'newsgator'                                                                    => 'NewsGator (RSS Reader)',
        'newzcrawler'                                                                  => 'NewzCrawler (RSS Reader)',
        'plagger'                                                                      => 'Plagger (RSS Reader)',
        'pluck'                                                                        => 'Pluck (RSS Reader)',
        'potu'                                                                         => '<a href="http://www.potu.com/" title="Potu Rss-Reader home page [new window]" target="_blank">Potu (RSS Reader)</a>',
        'pubsub\-rss\-reader'                                                          => '<a href="http://www.pubsub.com/" title="Browser home page [new window]" target="_blank">PubSub (RSS Reader)</a>',
        'pulpfiction'                                                                  => 'PulpFiction (RSS Reader)',
        'rssbandit'                                                                    => 'RSS Bandit (RSS Reader)',
        'rssreader'                                                                    => 'RssReader (RSS Reader)',
        'rssowl'                                                                       => 'RSSOwl (RSS Reader)',
        'rss\sxpress'                                                                  => 'RSS Xpress (RSS Reader)',
        'rssxpress'                                                                    => 'RSSXpress (RSS Reader)',
        'sage'                                                                         => 'Sage (RSS Reader)',
        'sharpreader'                                                                  => 'SharpReader (RSS Reader)',
        'shrook'                                                                       => 'Shrook (RSS Reader)',
        'straw'                                                                        => 'Straw (RSS Reader)',
        'syndirella'                                                                   => 'Syndirella (RSS Reader)',
        'vienna'                                                                       => '<a href="http://www.opencommunity.co.uk/vienna2.php" title="Vienna RSS-Reader [new window]" target="_blank">Vienna (RSS Reader)</a>',
        'wizz\srss\snews\sreader'                                                      => 'Wizz RSS News Reader (RSS Reader)',
# PDA/Phonecell browsers
        'alcatel'                                                                      => 'Alcatel Browser (PDA/Phone browser)',
        'lg\-'                                                                         => 'LG (PDA/Phone browser)',
        'mot\-'                                                                        => 'Motorola Browser (PDA/Phone browser)',
        'nokia'                                                                        => 'Nokia Browser (PDA/Phone browser)',
        'panasonic'                                                                    => 'Panasonic Browser (PDA/Phone browser)',
        'philips'                                                                      => 'Philips Browser (PDA/Phone browser)',
        'sagem'                                                                        => 'Sagem (PDA/Phone browser)',
        'samsung'                                                                      => 'Samsung (PDA/Phone browser)',
        'sie\-'                                                                        => 'SIE (PDA/Phone browser)',
        'sec\-'                                                                        => 'Sony/Ericsson (PDA/Phone browser)',
        'sonyericsson'                                                                 => 'Sony/Ericsson Browser (PDA/Phone browser)',
        'ericsson'                                                                     => 'Ericsson Browser (PDA/Phone browser)', # Must be after SonyEricsson
        'mmef'                                                                         => 'Microsoft Mobile Explorer (PDA/Phone browser)',
        'mspie'                                                                        => 'MS Pocket Internet Explorer (PDA/Phone browser)',
        'wapalizer'                                                                    => 'WAPalizer (PDA/Phone browser)',
        'wapsilon'                                                                     => 'WAPsilon (PDA/Phone browser)',
        'webcollage'                                                                   => 'WebCollage (PDA/Phone browser)',
        'up\.'                                                                         => 'UP.Browser (PDA/Phone browser)', # Works for UP.Browser and UP.Link
# PDA/Phonecell I-Mode browsers
        'docomo'                                                                       => 'I-Mode phone (PDA/Phone browser)',
        'portalmmm'                                                                    => 'I-Mode phone (PDA/Phone browser)',
# Others (TV)
        'webtv'                                                                        => 'WebTV browser',
# Anonymous Proxy Browsers (can be used as grabbers as well...)
        'cjb\.net'                                                                     => '<a href="http://proxy.cjb.net/" title="Browser home page [new window]" target="_blank">CJB.NET Proxy</a>',
        'ossproxy'                                                                     => '<a href="http://www.marketscore.com/FAQ.Aspx" title="OSSProxy home page [new window]" target="_blank">OSSProxy</a>',
# Other kind of browsers
        'apt'                                                                          => 'Debian APT',
        'analogx_proxy'                                                                => 'AnalogX Proxy',
        'gnome\-vfs'                                                                   => 'Gnome FileSystem Abstraction library',
        'neon'                                                                         => 'Neon HTTP and WebDAV client library',
        'curl'                                                                         => 'Curl',
        'csscheck'                                                                     => 'WDG CSS Validator',
        'httrack'                                                                      => 'HTTrack',
        'fdm'                                                                          => '<a href="http://www.freedownloadmanager.org/" title="Browser home page [new window]" target="_blank">FDM Free Download Manager</a>',
        'javaws'                                                                       => 'Java Web Start',
        'wget'                                                                         => 'Wget',
        'chilkat'                                                                      => 'Chilkat',
        'webdownloader\sfor\sx'                                                        => 'Downloader for X',
        'w3m'                                                                          => 'w3m',
        'wdg_validator'                                                                => 'WDG HTML Validator',
        'webreaper'                                                                    => 'WebReaper',
        'webzip'                                                                       => 'WebZIP',
        'staroffice'                                                                   => 'StarOffice',
        'gnus'                                                                         => 'Gnus Network User Services',
        'nikto'                                                                        => 'Nikto Web Scanner',
        'microsoft\-webdav\-miniredir'                                                 => 'Microsoft Data Access Component Internet Publishing Provider',
        'microsoft\sdata\saccess\sinternet\spublishing\sprovider\scache\smanager'      => 'Microsoft Data Access Component Internet Publishing Provider Cache Manager',
        'microsoft\sdata\saccess\sinternet\spublishing\sprovider\sdav'                 => 'Microsoft Data Access Component Internet Publishing Provider DAV',
        'microsoft\sdata\saccess\sinternet\spublishing\sprovider\sprotocol\sdiscovery' => 'Microsoft Data Access Component Internet Publishing Provider Protocol Discovery',
        'POE\-Component\-Client\-HTTP'                                                 => 'HTTP user-agent for POE (portable networking framework for Perl)',
        'mozilla'                                                                      => 'Mozilla',
        'libwww'                                                                       => 'LibWWW',
        'lwp'                                                                          => 'LibWWW-perl'
    );

    /**
     * @var array
     */
    private static $BrowsersHashIcon = array(
# Standard web browsers
        'msie'                                                                         => 'msie',
        'crome'                                                                        => 'googlecrome',
        'ie'                                                                           => 'msie',
        'curl'                                                                         => 'curl',
        'AppleWebKit'                                                                  => 'AppleWebKit',
        'GranParadiso'                                                                 => 'GranParadiso',
        'netscape'                                                                     => 'netscape',
        'firefox'                                                                      => 'firefox',
        'svn'                                                                          => 'subversion',
        'firebird'                                                                     => 'phoenix',
        'go!zilla'                                                                     => 'gozilla',
        'icab'                                                                         => 'icab',
        'khtml'                                                                        => 'konqueror',
        'konqueror'                                                                    => 'konqueror',
        'lynx'                                                                         => 'lynx',
        'omniweb'                                                                      => 'omniweb',
        'opera'                                                                        => 'opera',
# Other standard web browsers
        'amaya'                                                                        => 'amaya',
        'amigavoyager'                                                                 => 'amigavoyager',
        'avantbrowser'                                                                 => 'avant',
        'aweb'                                                                         => 'aweb',
        'bonecho'                                                                      => 'firefox',
        'bpftp'                                                                        => 'bpftp',
        'camino'                                                                       => 'chimera',
        'chimera'                                                                      => 'chimera',
        'cyberdog'                                                                     => 'cyberdog',
        'dillo'                                                                        => 'dillo',
        'doris'                                                                        => 'doris',
        'dreamcast'                                                                    => 'dreamcast',
        'xbox'                                                                         => 'winxbox',
        'ecatch'                                                                       => 'ecatch',
        'encompass'                                                                    => 'encompass',
        'epiphany'                                                                     => 'epiphany',
        'fresco'                                                                       => 'fresco',
        'galeon'                                                                       => 'galeon',
        'flashget'                                                                     => 'flashget',
        'freshdownload'                                                                => 'freshdownload',
        'getright'                                                                     => 'getright',
        'leechget'                                                                     => 'leechget',
        'hotjava'                                                                      => 'hotjava',
        'ibrowse'                                                                      => 'ibrowse',
        'k-meleon'                                                                     => 'kmeleon',
        'lotus-notes'                                                                  => 'lotusnotes',
        'macweb'                                                                       => 'macweb',
        'multizilla'                                                                   => 'multizilla',
        'msfrontpageexpress'                                                           => 'fpexpress',
        'ncsa_mosaic'                                                                  => 'ncsa_mosaic',
        'netpositive'                                                                  => 'netpositive',
        'phoenix'                                                                      => 'phoenix',
        'safari'                                                                       => 'safari',
# Site grabbers
        'teleport'                                                                     => 'teleport',
        'webcapture'                                                                   => 'adobe',
        'webcopier'                                                                    => 'webcopier',
# Media only browsers
        'real'                                                                         => 'real',
        'winamp'                                                                       => 'mediaplayer', # Works for winampmpeg and winamp3httprdr
        'windows-media-player'                                                         => 'mplayer',
        'audion'                                                                       => 'mediaplayer',
        'freeamp'                                                                      => 'mediaplayer',
        'itunes'                                                                       => 'mediaplayer',
        'jetaudio'                                                                     => 'mediaplayer',
        'mint_audio'                                                                   => 'mediaplayer',
        'mpg123'                                                                       => 'mediaplayer',
        'mplayer'                                                                      => 'mediaplayer',
        'nsplayer'                                                                     => 'netshow',
        'qts'                                                                          => 'mediaplayer',
        'sonique'                                                                      => 'mediaplayer',
        'uplayer'                                                                      => 'mediaplayer',
        'xaudio'                                                                       => 'mediaplayer',
        'xine'                                                                         => 'mediaplayer',
        'xmms'                                                                         => 'mediaplayer',
# PDA/Phonecell browsers
        'alcatel'                                                                      => 'pdaphone', # Alcatel
        'lg-'                                                                          => 'pdaphone', # LG
        'ericsson'                                                                     => 'pdaphone', # Ericsson
        'mot-'                                                                         => 'pdaphone', # Motorola
        'nokia'                                                                        => 'pdaphone', # Nokia
        'panasonic'                                                                    => 'pdaphone', # Panasonic
        'philips'                                                                      => 'pdaphone', # Philips
        'sagem'                                                                        => 'pdaphone', # Sagem
        'samsung'                                                                      => 'pdaphone', # Samsung
        'sie-'                                                                         => 'pdaphone', # SIE
        'sec-'                                                                         => 'pdaphone', # Sony/Ericsson
        'sonyericsson'                                                                 => 'pdaphone', # Sony/Ericsson
        'mmef'                                                                         => 'pdaphone',
        'mspie'                                                                        => 'pdaphone',
        'wapalizer'                                                                    => 'pdaphone',
        'wapsilon'                                                                     => 'pdaphone',
        'webcollage'                                                                   => 'pdaphone',
        'up.'                                                                          => 'pdaphone', # Works for UP.Browser and UP.Link
# PDA/Phonecell I-Mode browsers
        'docomo'                                                                       => 'pdaphone',
        'portalmmm'                                                                    => 'pdaphone',
# Others (TV)
        'webtv'                                                                        => 'webtv',
# Anonymous Proxy Browsers (can be used as grabbers as well...)
        'cjb.net'                                                                      => 'cjbnet',
# RSS Readers
        'abilon'                                                                       => 'abilon',
        'aggrevator'                                                                   => 'rss',
        'akregator'                                                                    => 'rss',
        'applesyndication'                                                             => 'rss',
        'betanews_reader'                                                              => 'rss',
        'blogbridge'                                                                   => 'rss',
        'feeddemon'                                                                    => 'rss',
        'feedreader'                                                                   => 'rss',
        'feedtools'                                                                    => 'rss',
        'greatnews'                                                                    => 'rss',
        'gregarius'                                                                    => 'rss',
        'hatena_rss'                                                                   => 'rss',
        'jetbrains_omea'                                                               => 'rss',
        'liferea'                                                                      => 'rss',
        'netnewswire'                                                                  => 'rss',
        'newsfire'                                                                     => 'rss',
        'newsgator'                                                                    => 'rss',
        'newzcrawler'                                                                  => 'rss',
        'plagger'                                                                      => 'rss',
        'pluck'                                                                        => 'rss',
        'potu'                                                                         => 'rss',
        'pubsub-rss-reader'                                                            => 'rss',
        'pulpfiction'                                                                  => 'rss',
        'rssbandit'                                                                    => 'rss',
        'rssreader'                                                                    => 'rss',
        'rssowl'                                                                       => 'rss',
        'rss\sxpress'                                                                  => 'rss',
        'rssxpress'                                                                    => 'rss',
        'sage'                                                                         => 'rss',
        'sharpreader'                                                                  => 'rss',
        'shrook'                                                                       => 'rss',
        'straw'                                                                        => 'rss',
        'syndirella'                                                                   => 'rss',
        'vienna'                                                                       => 'rss',
        'wizz\srss\snews\sreader'                                                      => 'wizz',
# Other kind of browsers
        'apt'                                                                          => 'apt',
        'analogx_proxy'                                                                => 'analogx',
        'microsoft-webdav-miniredir'                                                   => 'frontpage',
        'microsoft\sdata\saccess\sinternet\spublishing\sprovider\scache\smanager'      => 'frontpage',
        'microsoft\sdata\saccess\sinternet\spublishing\sprovider\sdav'                 => 'frontpage',
        'microsoft\sdata\saccess\sinternet\spublishing\sprovider\sprotocol\sdiscovery' => 'frontpage',
        'gnome-vfs'                                                                    => 'gnome',
        'neon'                                                                         => 'neon',
        'javaws'                                                                       => 'java',
        'webzip'                                                                       => 'webzip',
        'webreaper'                                                                    => 'webreaper',
        'httrack'                                                                      => 'httrack',
        'staroffice'                                                                   => 'staroffice',
        'gnus'                                                                         => 'gnus',
        'mozilla'                                                                      => 'mozilla'
    );

    /**
     * @var array
     */
    private static $SearchEnginesHashID = array(
# Major international search engines
        'base\.google\.'                                                                                                                                                         => 'google_base',
        'froogle\.google\.'                                                                                                                                                      => 'google_froogle',
        'groups\.google\.'                                                                                                                                                       => 'google_groups',
        'images\.google\.'                                                                                                                                                       => 'google_image',
        'google\.'                                                                                                                                                               => 'google',
        'googlee\.'                                                                                                                                                              => 'google',
        'googlecom\.com'                                                                                                                                                         => 'google',
        'goggle\.co\.hu'                                                                                                                                                         => 'google',
        '216\.239\.(35|37|39|51)\.100'                                                                                                                                           => 'google_cache',
        '216\.239\.(35|37|39|51)\.101'                                                                                                                                           => 'google_cache',
        '216\.239\.5[0-9]\.104'                                                                                                                                                  => 'google_cache',
        '64\.233\.1[0-9]{2}\.104'                                                                                                                                                => 'google_cache',
        '66\.102\.[1-9]\.104'                                                                                                                                                    => 'google_cache',
        '66\.249\.93\.104'                                                                                                                                                       => 'google_cache',
        '72\.14\.2[0-9]{2}\.104'                                                                                                                                                 => 'google_cache',
        'msn\.'                                                                                                                                                                  => 'msn',
        'live\.com'                                                                                                                                                              => 'live',
        'voila\.'                                                                                                                                                                => 'voila',
        'mindset\.research\.yahoo'                                                                                                                                               => 'yahoo_mindset',
        'yahoo\.'                                                                                                                                                                => 'yahoo',
        '(66\.218\.71\.225|216\.109\.117\.135|216\.109\.125\.130|66\.218\.69\.11)'                                                                                               => 'yahoo',
        'lycos\.'                                                                                                                                                                => 'lycos',
        'alexa\.com'                                                                                                                                                             => 'alexa',
        'alltheweb\.com'                                                                                                                                                         => 'alltheweb',
        'altavista\.'                                                                                                                                                            => 'altavista',
        'a9\.com'                                                                                                                                                                => 'a9',
        'dmoz\.org'                                                                                                                                                              => 'dmoz',
        'netscape\.'                                                                                                                                                             => 'netscape',
        'search\.terra\.'                                                                                                                                                        => 'terra',
        'www\.search\.com'                                                                                                                                                       => 'search.com',
        'tiscali\.'                                                                                                                                                              => 'tiscali',
        'search\.aol\.co'                                                                                                                                                        => 'aol',
        'search\.sli\.sympatico\.ca'                                                                                                                                             => 'sympatico',
        'excite\.'                                                                                                                                                               => 'excite',
# Minor international search engines
        '4\-counter\.com'                                                                                                                                                        => 'google4counter',
        'att\.net'                                                                                                                                                               => 'att',
        'bungeebonesdotcom'                                                                                                                                                      => 'bungeebonesdotcom',
        'northernlight\.'                                                                                                                                                        => 'northernlight',
        'hotbot\.'                                                                                                                                                               => 'hotbot',
        'kvasir\.'                                                                                                                                                               => 'kvasir',
        'webcrawler\.'                                                                                                                                                           => 'webcrawler',
        'metacrawler\.'                                                                                                                                                          => 'metacrawler',
        'go2net\.com'                                                                                                                                                            => 'go2net',
        '(^|\.)go\.com'                                                                                                                                                          => 'go',
        'euroseek\.'                                                                                                                                                             => 'euroseek',
        'looksmart\.'                                                                                                                                                            => 'looksmart',
        'spray\.'                                                                                                                                                                => 'spray',
        'nbci\.com\/search'                                                                                                                                                      => 'nbci',
        'de\.ask.\com'                                                                                                                                                           => 'askde', # break out Ask country specific engines.
        'es\.ask.\com'                                                                                                                                                           => 'askes',
        'fr\.ask.\com'                                                                                                                                                           => 'askfr',
        'it\.ask.\com'                                                                                                                                                           => 'askit',
        'nl\.ask.\com'                                                                                                                                                           => 'asknl',
        'uk\.ask.\com'                                                                                                                                                           => 'askuk',
        '(^|\.)ask\.co\.uk'                                                                                                                                                      => 'askuk',
        '(^|\.)ask\.com'                                                                                                                                                         => 'ask',
        'atomz\.'                                                                                                                                                                => 'atomz',
        'overture\.com'                                                                                                                                                          => 'overture', # Replace 'goto\.com' => 'Goto.com',
        'teoma\.'                                                                                                                                                                => 'teoma',
        'findarticles\.com'                                                                                                                                                      => 'findarticles',
        'infospace\.com'                                                                                                                                                         => 'infospace',
        'mamma\.'                                                                                                                                                                => 'mamma',
        'dejanews\.'                                                                                                                                                             => 'dejanews',
        'dogpile\.com'                                                                                                                                                           => 'dogpile',
        'wisenut\.com'                                                                                                                                                           => 'wisenut',
        'ixquick\.com'                                                                                                                                                           => 'ixquick',
        'search\.earthlink\.net'                                                                                                                                                 => 'earthlink',
        'i-une\.com'                                                                                                                                                             => 'iune',
        'blingo\.com'                                                                                                                                                            => 'blingo',
        'centraldatabase\.org'                                                                                                                                                   => 'centraldatabase',
        'clusty\.com'                                                                                                                                                            => 'clusty',
        'mysearch\.'                                                                                                                                                             => 'mysearch',
        'vivisimo\.com'                                                                                                                                                          => 'vivisimo',
        'kartoo\.com'                                                                                                                                                            => 'kartoo',
        'icerocket\.com'                                                                                                                                                         => 'icerocket',
        'sphere\.com'                                                                                                                                                            => 'sphere',
        'ledix\.net'                                                                                                                                                             => 'ledix',
        'start\.shaw\.ca'                                                                                                                                                        => 'shawca',
        'searchalot\.com'                                                                                                                                                        => 'searchalot',
        'copernic\.com'                                                                                                                                                          => 'copernic',
        'avantfind\.com'                                                                                                                                                         => 'avantfind',
        'steadysearch\.com'                                                                                                                                                      => 'steadysearch',
        'steady-search\.com'                                                                                                                                                     => 'steadysearch',
# Chello Portals
        'chello\.at'                                                                                                                                                             => 'chelloat',
        'chello\.be'                                                                                                                                                             => 'chellobe',
        'chello\.cz'                                                                                                                                                             => 'chellocz',
        'chello\.fr'                                                                                                                                                             => 'chellofr',
        'chello\.hu'                                                                                                                                                             => 'chellohu',
        'chello\.nl'                                                                                                                                                             => 'chellonl',
        'chello\.no'                                                                                                                                                             => 'chellono',
        'chello\.pl'                                                                                                                                                             => 'chellbt',
        'chello\.se'                                                                                                                                                             => 'chellose',
        'chello\.sk'                                                                                                                                                             => 'chellosk',
        'chello'                                                                                                                                                                 => 'chellocom',
# Mirago
        'mirago\.be'                                                                                                                                                             => 'miragobe',
        'mirago\.ch'                                                                                                                                                             => 'miragoch',
        'mirago\.de'                                                                                                                                                             => 'miragode',
        'mirago\.dk'                                                                                                                                                             => 'miragodk',
        'es\.mirago\.com'                                                                                                                                                        => 'miragoes',
        'mirago\.fr'                                                                                                                                                             => 'miragofr',
        'mirago\.it'                                                                                                                                                             => 'miragoit',
        'mirago\.nl'                                                                                                                                                             => 'miragonl',
        'no\.mirago\.com'                                                                                                                                                        => 'miragono',
        'mirago\.se'                                                                                                                                                             => 'miragose',
        'mirago\.co\.uk'                                                                                                                                                         => 'miragocouk',
        'mirago'                                                                                                                                                                 => 'mirago', # required as catchall for new countries not yet known
        'answerbus\.com'                                                                                                                                                         => 'answerbus',
        'icq\.com\/search'                                                                                                                                                       => 'icq',
        'nusearch\.com'                                                                                                                                                          => 'nusearch',
        'goodsearch\.com'                                                                                                                                                        => 'goodsearch',
        'scroogle\.org'                                                                                                                                                          => 'scroogle',
        'questionanswering\.com'                                                                                                                                                 => 'questionanswering',
        'mywebsearch\.com'                                                                                                                                                       => 'mywebsearch',
        'as\.starware\.com'                                                                                                                                                      => 'comettoolbar',
# Social Bookmarking Services
        'del\.icio\.us'                                                                                                                                                          => 'delicious',
        'digg\.com'                                                                                                                                                              => 'digg',
        'stumbleupon\.com'                                                                                                                                                       => 'stumbleupon',
        'swik\.net'                                                                                                                                                              => 'swik',
        'segnalo\.alice\.it'                                                                                                                                                     => 'segnalo',
        'ineffabile\.it'                                                                                                                                                         => 'ineffabile',
# Minor Australian search engines
        'anzwers\.com\.au'                                                                                                                                                       => 'anzwers',
# Minor brazilian search engines
        'engine\.exe'                                                                                                                                                            => 'engine',
        'miner\.bol\.com\.br'                                                                                                                                                    => 'miner',
# Minor chinese search engines
        '\.baidu\.com'                                                                                                                                                           => 'baidu',
        'iask\.com'                                                                                                                                                              => 'iask',
        '\.accoona\.com'                                                                                                                                                         => 'accoona',
        '\.3721\.com'                                                                                                                                                            => '3721',
        '\.163\.com'                                                                                                                                                             => 'netease',
        '\.soso\.com'                                                                                                                                                            => 'soso',
        '\.zhongsou\.com'                                                                                                                                                        => 'zhongsou',
        '\.vnet\.cn'                                                                                                                                                             => 'vnet',
        '\.sogou\.com'                                                                                                                                                           => 'sogou',
# Minor czech search engines
        'atlas\.cz'                                                                                                                                                              => 'atlas',
        'seznam\.cz'                                                                                                                                                             => 'seznam',
        'quick\.cz'                                                                                                                                                              => 'quick',
        'centrum\.cz'                                                                                                                                                            => 'centrum',
        'jyxo\.(cz|com)'                                                                                                                                                         => 'jyxo',
        'najdi\.to'                                                                                                                                                              => 'najdi',
        'redbox\.cz'                                                                                                                                                             => 'redbox',
# Minor danish search-engines
        'opasia\.dk'                                                                                                                                                             => 'opasia',
        'danielsen\.com'                                                                                                                                                         => 'danielsen',
        'sol\.dk'                                                                                                                                                                => 'sol',
        'jubii\.dk'                                                                                                                                                              => 'jubii',
        'find\.dk'                                                                                                                                                               => 'finddk',
        'edderkoppen\.dk'                                                                                                                                                        => 'edderkoppen',
        'netstjernen\.dk'                                                                                                                                                        => 'netstjernen',
        'orbis\.dk'                                                                                                                                                              => 'orbis',
        'tyfon\.dk'                                                                                                                                                              => 'tyfon',
        '1klik\.dk'                                                                                                                                                              => '1klik',
        'ofir\.dk'                                                                                                                                                               => 'ofir',
# Minor dutch search engines
        'ilse\.'                                                                                                                                                                 => 'ilse',
        'vindex\.'                                                                                                                                                               => 'vindex',
# Minor english search engines
        'bbc\.co\.uk/cgi-bin/search'                                                                                                                                             => 'bbc',
        'ifind\.freeserve'                                                                                                                                                       => 'freeserve',
        'looksmart\.co\.uk'                                                                                                                                                      => 'looksmartuk',
        'splut\.'                                                                                                                                                                => 'splut',
        'spotjockey\.'                                                                                                                                                           => 'spotjockey',
        'ukdirectory\.'                                                                                                                                                          => 'ukdirectory',
        'ukindex\.co\.uk'                                                                                                                                                        => 'ukindex',
        'ukplus\.'                                                                                                                                                               => 'ukplus',
        'searchy\.co\.uk'                                                                                                                                                        => 'searchy',
# Minor finnish search engines
        'haku\.www\.fi'                                                                                                                                                          => 'haku',
# Minor french search engines
        'recherche\.aol\.fr'                                                                                                                                                     => 'aolfr',
        'ctrouve\.'                                                                                                                                                              => 'ctrouve',
        'francite\.'                                                                                                                                                             => 'francite',
        '\.lbb\.org'                                                                                                                                                             => 'lbb',
        'rechercher\.libertysurf\.fr'                                                                                                                                            => 'libertysurf',
        'search[\w\-]+\.free\.fr'                                                                                                                                                => 'free',
        'recherche\.club-internet\.fr'                                                                                                                                           => 'clubinternet',
        'toile\.com'                                                                                                                                                             => 'toile',
        'biglotron\.com',
        'biglotron',
        'mozbot\.fr',
        'mozbot',
# Minor german search engines
        'sucheaol\.aol\.de'                                                                                                                                                      => 'aolde',
        'fireball\.de'                                                                                                                                                           => 'fireball',
        'infoseek\.de'                                                                                                                                                           => 'infoseek',
        'suche\d?\.web\.de'                                                                                                                                                      => 'webde',
        '[a-z]serv\.rrzn\.uni-hannover\.de'                                                                                                                                      => 'meta',
        'suchen\.abacho\.de'                                                                                                                                                     => 'abacho',
        'brisbane\.t-online\.de'                                                                                                                                                 => 't-online',
        'allesklar\.de'                                                                                                                                                          => 'allesklar',
        'meinestadt\.de'                                                                                                                                                         => 'meinestadt',
        '212\.227\.33\.241'                                                                                                                                                      => 'metaspinner',
        '(161\.58\.227\.204|161\.58\.247\.101|212\.40\.165\.90|213\.133\.108\.202|217\.160\.108\.151|217\.160\.111\.99|217\.160\.131\.108|217\.160\.142\.227|217\.160\.176\.42)' => 'metacrawler_de',
        'wwweasel\.de'                                                                                                                                                           => 'wwweasel',
        'netluchs\.de'                                                                                                                                                           => 'netluchs',
        'schoenerbrausen\.de'                                                                                                                                                    => 'schoenerbrausen',
# Minor Hungarian search engines
        'heureka\.hu'                                                                                                                                                            => 'heureka',
        'vizsla\.origo\.hu'                                                                                                                                                      => 'origo',
        'lapkereso\.hu'                                                                                                                                                          => 'lapkereso',
        'goliat\.hu'                                                                                                                                                             => 'goliat',
        'index\.hu'                                                                                                                                                              => 'indexhu',
        'wahoo\.hu'                                                                                                                                                              => 'wahoo',
        'webmania\.hu'                                                                                                                                                           => 'webmania',
        'search\.internetto\.hu'                                                                                                                                                 => 'internetto',
        'tango\.hu'                                                                                                                                                              => 'tango_hu',
        'keresolap\.hu'                                                                                                                                                          => 'keresolap_hu',
        'polymeta\.hu'                                                                                                                                                           => 'polymeta_hu',
# Minor Indian search engines
        'sify\.com'                                                                                                                                                              => 'sify',
# Minor Italian search engines
        'virgilio\.it'                                                                                                                                                           => 'virgilio',
        'arianna\.libero\.it'                                                                                                                                                    => 'arianna',
        'supereva\.com'                                                                                                                                                          => 'supereva',
        'kataweb\.it'                                                                                                                                                            => 'kataweb',
        'search\.alice\.it\.master'                                                                                                                                              => 'aliceitmaster',
        'search\.alice\.it'                                                                                                                                                      => 'aliceit',
        'gotuneed\.com'                                                                                                                                                          => 'gotuneed',
        'godado'                                                                                                                                                                 => 'godado',
        'jumpy\.it'                                                                                                                                                              => 'jumpy\.it',
        'shinyseek\.it'                                                                                                                                                          => 'shinyseek\.it',
        'teecno\.it'                                                                                                                                                             => 'teecnoit',
# Minor Japanese search engines
        'ask\.jp'                                                                                                                                                                => 'askjp',
        'sagool\.jp'                                                                                                                                                             => 'sagool',
# Minor Norwegian search engines
        'sok\.start\.no'                                                                                                                                                         => 'start',
        'eniro\.no'                                                                                                                                                              => 'eniro',
# Minor Polish search engines
        'szukaj\.wp\.pl'                                                                                                                                                         => 'wp',
        'szukaj\.onet\.pl'                                                                                                                                                       => 'onetpl',
        'dodaj\.pl'                                                                                                                                                              => 'dodajpl',
        'gazeta\.pl'                                                                                                                                                             => 'gazetapl',
        'gery\.pl'                                                                                                                                                               => 'gerypl',
        'netsprint\.pl\/hoga\-search'                                                                                                                                            => 'hogapl',
        'netsprint\.pl'                                                                                                                                                          => 'netsprintpl',
        'interia\.pl'                                                                                                                                                            => 'interiapl',
        'katalog\.onet\.pl'                                                                                                                                                      => 'katalogonetpl',
        'o2\.pl'                                                                                                                                                                 => 'o2pl',
        'polska\.pl'                                                                                                                                                             => 'polskapl',
        'szukacz\.pl'                                                                                                                                                            => 'szukaczpl',
        'wow\.pl'                                                                                                                                                                => 'wowpl',
# Minor russian search engines
        'ya(ndex)?\.ru'                                                                                                                                                          => 'yandex',
        'aport\.ru'                                                                                                                                                              => 'aport',
        'rambler\.ru'                                                                                                                                                            => 'rambler',
        'turtle\.ru'                                                                                                                                                             => 'turtle',
        'metabot\.ru'                                                                                                                                                            => 'metabot',
# Minor Swedish search engines
        'evreka\.passagen\.se'                                                                                                                                                   => 'passagen',
        'eniro\.se'                                                                                                                                                              => 'enirose',
# Minor Slovak search engines
        'zoznam\.sk'                                                                                                                                                             => 'zoznam',
# Minor Portuguese search engines
        'sapo\.pt'                                                                                                                                                               => 'sapo',
# Minor swiss search engines
        'search\.ch'                                                                                                                                                             => 'searchch',
        'search\.bluewin\.ch'                                                                                                                                                    => 'bluewin',
# Generic search engines
        'search\..*\.\w+'                                                                                                                                                        => 'search'
    );

    /**
     * @var array
     */
    private static $SearchEnginesKnownUrl = array(
# Most common search engines
        'alexa'             => 'q=',
        'alltheweb'         => 'q(|uery)=',
        'altavista'         => 'q=',
        'a9'                => 'a9\.com\/',
        'dmoz'              => 'search=',
        'google_base'       => '(p|q|as_p|as_q)=',
        'google_froogle'    => '(p|q|as_p|as_q)=',
        'google_groups'     => 'group\/', # does not work
        'google_image'      => '(p|q|as_p|as_q)=',
        'google_cache'      => '(p|q|as_p|as_q)=cache:[0-9A-Za-z]{12}:',
        'google'            => '(p|q|as_p|as_q)=',
        'lycos'             => 'query=',
        'msn'               => 'q=',
        'live'              => 'q=',
        'netscape'          => 'search=',
        'tiscali'           => 'key=',
        'aol'               => 'query=',
        'terra'             => 'query=',
        'voila'             => '(kw|rdata)=',
        'search.com'        => 'q=',
        'yahoo_mindset'     => 'p=',
        'yahoo'             => 'p=',
        'sympatico'         => 'query=',
        'excite'            => 'search=',
# Minor international search engines
        'google4counter'    => '(p|q|as_p|as_q)=',
        'att'               => 'qry=',
        'bungeebonesdotcom' => 'query=',
        'go'                => 'qt=',
        'askde'             => '(ask|q)=', # break out Ask country specific engines.
        'askes'             => '(ask|q)=',
        'askfr'             => '(ask|q)=',
        'askit'             => '(ask|q)=',
        'asknl'             => '(ask|q)=',
        'ask'               => '(ask|q)=',
        'atomz'             => 'sp-q=',
        'euroseek'          => 'query=',
        'findarticles'      => 'key=',
        'go2net'            => 'general=',
        'hotbot'            => 'mt=',
        'infospace'         => 'qkw=',
        'kvasir'            => 'q=',
        'looksmart'         => 'key=',
        'mamma'             => 'query=',
        'metacrawler'       => 'general=',
        'nbci'              => 'keyword=',
        'northernlight'     => 'qr=',
        'overture'          => 'keywords=',
        'dogpile'           => 'q(|kw)=',
        'spray'             => 'string=',
        'teoma'             => 'q=',
        'webcrawler'        => 'searchText=',
        'wisenut'           => 'query=',
        'ixquick'           => 'query=',
        'earthlink'         => 'q=',
        'iune'              => '(keywords|q)=',
        'blingo'            => 'q=',
        'centraldatabase'   => 'query=',
        'clusty'            => 'query=',
        'mysearch'          => 'searchfor=',
        'vivisimo'          => 'query=',
# kartoo: No keywords passed in referring URL.
        'kartoo'            => '',
        'icerocket'         => 'q=',
        'sphere'            => 'q=',
        'ledix'             => 'q=',
        'shawca'            => 'q=',
        'searchalot'        => 'q=',
        'copernic'          => 'web\/',
        'avantfind'         => 'keywords=',
        'steadysearch'      => 'w=',
# Chello Portals
        'chelloat'          => 'q1=',
        'chellobe'          => 'q1=',
        'chellocz'          => 'q1=',
        'chellofr'          => 'q1=',
        'chellohu'          => 'q1=',
        'chellonl'          => 'q1=',
        'chellono'          => 'q1=',
        'chellbt'           => 'q1=',
        'chellose'          => 'q1=',
        'chellosk'          => 'q1=',
        'chellocom'         => 'q1=',
# Mirago
        'miragobe'          => '(txtsearch|qry)=',
        'miragoch'          => '(txtsearch|qry)=',
        'miragode'          => '(txtsearch|qry)=',
        'miragodk'          => '(txtsearch|qry)=',
        'miragoes'          => '(txtsearch|qry)=',
        'miragofr'          => '(txtsearch|qry)=',
        'miragoit'          => '(txtsearch|qry)=',
        'miragonl'          => '(txtsearch|qry)=',
        'miragono'          => '(txtsearch|qry)=',
        'miragose'          => '(txtsearch|qry)=',
        'miragocouk'        => '(txtsearch|qry)=',
        'mirago'            => '(txtsearch|qry)=',
        'answerbus'         => '', # Does not provide query parameters
        'icq'               => 'q=',
        'nusearch'          => 'nusearch_terms=',
        'goodsearch'        => 'Keywords=',
        'scroogle'          => 'Gw=', # Does not always provide query parameters
        'questionanswering' => '',
        'mywebsearch'       => 'searchfor=',
        'comettoolbar'      => 'qry=',
# Social Bookmarking Services
        'delicious'         => 'all=',
        'digg'              => 's=',
        'stumbleupon'       => '',
        'swik'              => 'swik\.net/', # does not work. Keywords follow domain, e.g. http://swik.net/awstats+analytics
        'segnalo'           => '',
        'ineffabile'        => '',
# Minor Australian search engines
        'anzwers'           => 'search=',
# Minor brazilian search engines
        'engine'            => 'p1=',
        'miner'             => 'q=',
# Minor chinese search engines
        'baidu'             => '(wd|word)=',
        'iask'              => '(w|k)=',
        'accoona'           => 'qt=',
        '3721'              => '(p|name)=',
        'netease'           => 'q=',
        'soso'              => 'q=',
        'zhongsou'          => '(word|w)=',
        'sogou'             => 'query=',
        'vnet'              => 'kw=',
# Minor czech search engines
        'atlas'             => 'searchtext=',
        'seznam'            => 'w=',
        'quick'             => 'query=',
        'centrum'           => 'q=',
        'jyxo'              => 's=',
        'najdi'             => 'dotaz=',
        'redbox'            => 'srch=',
# Minor danish search engines
        'opasia'            => 'q=',
        'danielsen'         => 'q=',
        'sol'               => 'q=',
        'jubii'             => 'soegeord=',
        'finddk'            => 'words=',
        'edderkoppen'       => 'query=',
        'orbis'             => 'search_field=',
        '1klik'             => 'query=',
        'ofir'              => 'querytext=',
# Minor dutch search engines
        'ilse'              => 'search_for=',
        'vindex'            => 'in=',
# Minor english search engines
        'askuk'             => '(ask|q)=',
        'bbc'               => 'q=',
        'freeserve'         => 'q=',
        'looksmartuk'       => 'key=',
        'splut'             => 'pattern=',
        'spotjockey'        => 'Search_Keyword=',
        'ukindex'           => 'stext=',
        'ukdirectory'       => 'k=',
        'ukplus'            => 'search=',
        'searchy'           => 'search_term=',
# Minor finnish search engines
        'haku'              => 'w=',
# Minor french search engines
        'francite'          => 'name=',
        'clubinternet'      => 'q=',
        'toile'             => 'q=',
        'biglotron'         => 'question=',
        'mozbot'            => 'q=',
# Minor german search engines
        'aolde'             => 'q=',
        'fireball'          => 'q=',
        'infoseek'          => 'qt=',
        'webde'             => 'su=',
        'abacho'            => 'q=',
        't-online'          => 'q=',
        'metaspinner'       => 'qry=',
        'metacrawler_de'    => 'qry=',
        'wwweasel'          => 'q=',
        'netluchs'          => 'query=',
        'schoenerbrausen'   => 'q=',
# Minor Hungarian search engines
        'heureka'           => 'heureka=',
        'origo'             => '(q|search)=',
        'goliat'            => 'KERESES=',
        'wahoo'             => 'q=',
        'internetto'        => 'searchstr=',
        'keresolap_hu'      => 'q=',
        'tango_hu'          => 'q=',
        'polymeta_hu'       => '',
# Minor Indian search engines
        'sify'              => 'keyword=',
# Minor Italian search engines
        'virgilio'          => 'qs=',
        'arianna'           => 'query=',
        'supereva'          => 'q=',
        'kataweb'           => 'q=',
        'aliceitmaster'     => 'qs=',
        'aliceit'           => 'qs=',
        'gotuneed'          => '', # Not yet known
        'godado'            => 'Keywords=',
        'jumpy\.it'         => 'searchWord=',
        'shinyseek\.it'     => 'KEY=',
        'teecnoit'          => 'q=',
# Minor Japanese search engines
        'askjp'             => '(ask|q)=',
        'sagool'            => 'q=',
# Minor Norwegian search engines
        'start'             => 'q=',
        'eniro'             => 'q=',
# Minor Polish search engines
        'wp'                => 'szukaj=',
        'onetpl'            => 'qt=',
        'dodajpl'           => 'keyword=',
        'gazetapl'          => 'slowo=',
        'gerypl'            => 'q=',
        'hogapl'            => 'qt=',
        'netsprintpl'       => 'q=',
        'interiapl'         => 'q=',
        'katalogonetpl'     => 'qt=',
        'o2pl'              => 'qt=',
        'polskapl'          => 'qt=',
        'szukaczpl'         => 'q=',
        'wowpl'             => 'q=',
# Minor russian search engines
        'yandex'            => 'text=',
        'rambler'           => 'words=',
        'aport'             => 'r=',
        'metabot'           => 'st=',
# Minor swedish search engines
        'passagen'          => 'q=',
        'enirose'           => 'q=',
# Minor swiss search engines
        'searchch'          => 'q=',
        'bluewin'           => 'qry='
    );

    /**
     * @var array
     */
    private static $SearchEnginesHashLib = array(
# Major international search engines
        'alexa'             => '<a href="http://www.alexa.com/" title="Search Engine Home Page [new window]" target="_blank">Alexa</a>',
        'alltheweb'         => '<a href="http://www.alltheweb.com/" title="Search Engine Home Page [new window]" target="_blank">AllTheWeb</a>',
        'altavista'         => '<a href="http://www.altavista.com/" title="Search Engine Home Page [new window]" target="_blank">AltaVista</a>',
        'a9'                => '<a href="http://www.a9.com/" title="Search Engine Home Page [new window]" target="_blank">A9</a>',
        'AppleWebKit'       => 'Apple Web Kit',
        'dmoz'              => '<a href="http://dmoz.org/" title="Search Engine Home Page [new window]" target="_blank">DMOZ</a>',
        'google_base'       => '<a href="http://base.google.com/" title="Search Engine Home Page [new window]" target="_blank">Google (Base)</a>',
        'google_froogle'    => '<a href="http://froogle.google.com/" title="Search Engine Home Page [new window]" target="_blank">Froogle (Google)</a>',
        'google_groups'     => '<a href="http://groups.google.com/" title="Search Engine Home Page [new window]" target="_blank">Google (Groups)</a>',
        'google_image'      => '<a href="http://images.google.com/" title="Search Engine Home Page [new window]" target="_blank">Google (Images)</a>',
        'google_cache'      => '<a href="http://www.google.com/help/features.html#cached" title="Search Engine Home Page [new window]" target="_blank">Google (cache)</a>',
        'google'            => '<a href="http://www.google.com/" title="Search Engine Home Page [new window]" target="_blank">Google</a>',
        'lycos'             => '<a href="http://www.lycos.com/" title="Search Engine Home Page [new window]" target="_blank">Lycos</a>',
        'msn'               => '<a href="http://search.msn.com/" title="Search Engine Home Page [new window]" target="_blank">MSN Search</a>',
        'live'              => '<a href="http://www.live.com/" title="Search Engine Home Page [new window]" target="_blank">Windows Live</a>',
        'netscape'          => '<a href="http://www.netscape.com/" title="Search Engine Home Page [new window]" target="_blank">Netscape</a>',
        'aol'               => '<a href="http://www.aol.com/" title="Search Engine Home Page [new window]" target="_blank">AOL</a>',
        'terra'             => '<a href="http://www.terra.es/" title="Search Engine Home Page [new window]" target="_blank">Terra</a>',
        'tiscali'           => '<a href="http://search.tiscali.com/" title="Search Engine Home Page [new window]" target="_blank">Tiscali</a>',
        'voila'             => '<a href="http://www.voila.fr/" title="Search Engine Home Page [new window]" target="_blank">Voila</a>',
        'search.com'        => '<a href="http://www.search.com/" title="Search Engine Home Page [new window]" target="_blank">Search.com</a>',
        'yahoo_mindset'     => '<a href="http://mindset.research.yahoo.com/" title="Search Engine Home Page [new window]" target="_blank">Yahoo! Mindset</a>',
        'yahoo'             => '<a href="http://www.yahoo.com/" title="Search Engine Home Page [new window]" target="_blank">Yahoo!</a>',
        'sympatico'         => '<a href="http://sympatico.msn.ca/" title="Search Engine Home Page [new window]" target="_blank">Sympatico</a>',
        'excite'            => '<a href="http://www.excite.com/" title="Search Engine Home Page [new window]" target="_blank">Excite</a>',
# Minor international search engines
        'google4counter'    => '<a href="http://www.4-counter.com/" title="Search Engine Home Page [new window]" target="_blank">4-counter (Google)</a>',
        'att'               => '<a href="http://www.att.net/" title="Search Engine Home Page [new window]" target="_blank">AT&T search (powered by Google)</a>',
        'bungeebonesdotcom' => '<a href="http://BungeeBones.com/search.php/" title="Search Engine Home Page [new window]" target="_blank">BungeeBones</a>',
        'go'                => 'Go.com',
        'askde'             => '<a href="http://de.ask.com/" title="Search Engine Home Page [new window]" target="_blank">Ask Deutschland</a>',
        'askes'             => '<a href="http://es.ask.com/" title="Search Engine Home Page [new window]" target="_blank">Ask Espa&ntilde;a</a>', # break out Ask country specific engines.
        'askfr'             => '<a href="http://fr.ask.com/" title="Search Engine Home Page [new window]" target="_blank">Ask France</a>',
        'askit'             => '<a href="http://it.ask.com/" title="Search Engine Home Page [new window]" target="_blank">Ask Italia</a>',
        'asknl'             => '<a href="http://nl.ask.com/" title="Search Engine Home Page [new window]" target="_blank">Ask Nederland</a>',
        'ask'               => '<a href="http://www.ask.com/" title="Search Engine Home Page [new window]" target="_blank">Ask</a>',
        'atomz'             => 'Atomz',
        'dejanews'          => 'DejaNews',
        'euroseek'          => 'Euroseek',
        'findarticles'      => 'Find Articles',
        'go2net'            => 'Go2Net (Metamoteur)',
        'hotbot'            => 'Hotbot',
        'infospace'         => 'InfoSpace',
        'kvasir'            => 'Kvasir',
        'looksmart'         => 'Looksmart',
        'mamma'             => 'Mamma',
        'metacrawler'       => 'MetaCrawler (Metamoteur)',
        'nbci'              => 'NBCI',
        'northernlight'     => 'NorthernLight',
        'overture'          => 'Overture', # Replace 'goto\.com' => 'Goto.com',
        'dogpile'           => '<a href="http://www.dogpile.com/" title="Search Engine Home Page [new window]" target="_blank">Dogpile</a>',
        'spray'             => 'Spray',
        'teoma'             => '<a href="http://search.ask.com/" title="Search Engine Home Page [new window]" target="_blank">Teoma</a>', # Replace 'directhit\.com' => 'DirectHit',
        'webcrawler'        => '<a href="http://www.webcrawler.com/" title="Search Engine Home Page [new window]" target="_blank">WebCrawler</a>',
        'wisenut'           => 'WISENut',
        'ixquick'           => '<a href="http://www.ixquick.com/" title="Search Engine Home Page [new window]" target="_blank">ix quick</a>',
        'earthlink',
        'Earth Link',
        'iune'              => '<a href="http://www.i-une.com/" title="Search Engine Home Page [new window]" target="_blank">i-une</a>',
        'blingo'            => '<a href="http://www.blingo.com/" title="Search Engine Home Page [new window]" target="_blank">Blingo</a>',
        'centraldatabase'   => '<a href="http://search.centraldatabase.org/" title="Search Engine Home Page [new window]" target="_blank">GPU p2p search</a>',
        'clusty'            => '<a href="http://www.clusty.com/" title="Search Engine Home Page [new window]" target="_blank">Clusty</a>',
        'mysearch'          => '<a href="http://www.mysearch.com" title="Search Engine Home Page [new window]" target="_blank">My Search</a>',
        'vivisimo'          => '<a href="http://www.vivisimo.com/" title="Search Engine Home Page [new window]" target="_blank">Vivisimo</a>',
        'kartoo'            => '<a href="http://www.kartoo.com/" title="Search Engine Home Page [new window]" target="_blank">Kartoo</a>',
        'icerocket'         => '<a href="http://www.icerocket.com/" title="Search Engine Home Page [new window]" target="_blank">Icerocket (Blog)</a>',
        'sphere'            => '<a href="http://www.sphere.com/" title="Search Engine Home Page [new window]" target="_blank">Sphere (Blog)</a>',
        'ledix'             => '<a href="http://www.ledix.net/" title="Search Engine Home Page [new window]" target="_blank">Ledix</a>',
        'shawca'            => '<a href="http://start.shaw.ca/" title="Search Engine Home Page [new window]" target="_blank">Shaw.ca</a>',
        'searchalot'        => '<a href="http://www.searchalot.com/" title="Search Engine Home Page [new window]" target="_blank">Searchalot</a>',
        'copernic'          => '<a href="http://www.copernic.com/" title="Search Engine Home Page [new window]" target="_blank">Copernic</a>',
        'avantfind'         => '<a href="http://www.avantfind.com/" title="Search Engine Home Page [new window]" target="_blank">Avantfind</a>',
        'steadysearch'      => '<a href="http://www.avantfind.com/" title="Search Engine Home Page [new window]" target="_blank">Avantfind</a>',
# Chello Portals
        'chelloat'          => '<a href="http://www.chello.at/" title="Search Engine Home Page [new window]" target="_blank">Chello Austria</a>',
        'chellobe'          => '<a href="http://www.chello.be/" title="Search Engine Home Page [new window]" target="_blank">Chello Belgium</a>',
        'chellocz'          => '<a href="http://www.chello.cz/" title="Search Engine Home Page [new window]" target="_blank">Chello Czech Republic</a>',
        'chellofr'          => '<a href="http://www.chello.fr/" title="Search Engine Home Page [new window]" target="_blank">Chello France</a>',
        'chellohu'          => '<a href="http://www.chello.hu/" title="Search Engine Home Page [new window]" target="_blank">Chello Hungary</a>',
        'chellonl'          => '<a href="http://www.chello.nl/" title="Search Engine Home Page [new window]" target="_blank">Chello Netherlands</a>',
        'chellono'          => '<a href="http://www.chello.no/" title="Search Engine Home Page [new window]" target="_blank">Chello Norway</a>',
        'chellbt'           => '<a href="http://www.chello.pl/" title="Search Engine Home Page [new window]" target="_blank">Chello Poland</a>',
        'chellose'          => '<a href="http://www.chello.se/" title="Search Engine Home Page [new window]" target="_blank">Chello Sweden</a>',
        'chellosk'          => '<a href="http://www.chello.sk/" title="Search Engine Home Page [new window]" target="_blank">Chello Slovakia</a>',
        'chellocom'         => '<a href="http://www.chello.com/" title="Search Engine Home Page [new window]" target="_blank">Chello (Country not recognized)</a>',
# Mirago
        'miragobe'          => '<a href="http://www.mirago.be/" title="Search Engine Home Page [new window]" target="_blank">Mirago Belgium</a>',
        'miragoch'          => '<a href="http://www.mirago.ch/" title="Search Engine Home Page [new window]" target="_blank">Mirago Switzerland</a>',
        'miragode'          => '<a href="http://www.mirago.de/" title="Search Engine Home Page [new window]" target="_blank">Mirago Germany</a>',
        'miragodk'          => '<a href="http://www.mirago.dk/" title="Search Engine Home Page [new window]" target="_blank">Mirago Denmark</a>',
        'miragoes'          => '<a href="http://es.mirago.com/" title="Search Engine Home Page [new window]" target="_blank">Mirago Spain</a>',
        'miragofr'          => '<a href="http://www.mirago.fr/" title="Search Engine Home Page [new window]" target="_blank">Mirago France</a>',
        'miragoit'          => '<a href="http://www.mirago.it/" title="Search Engine Home Page [new window]" target="_blank">Mirago Italy</a>',
        'miragonl'          => '<a href="http://www.mirago.nl/" title="Search Engine Home Page [new window]" target="_blank">Mirago Netherlands</a>',
        'miragono'          => '<a href="http://no.mirago.com/" title="Search Engine Home Page [new window]" target="_blank">Mirago Norway</a>',
        'miragose'          => '<a href="http://www.mirago.se/" title="Search Engine Home Page [new window]" target="_blank">Mirago Sweden</a>',
        'miragocouk'        => '<a href="http://zone.mirago.co.uk/" title="Search Engine Home Page [new window]" target="_blank">Mirago UK</a>',
        'mirago'            => '<a href="http://www.mirago.com/" title="Search Engine Home Page [new window]" target="_blank">Mirago (country unknown)</a>',
        'answerbus'         => '<a href="http://www.answerbus.com/" title="Search Engine Home Page [new window]" target="_blank">Answerbus</a>',
        'icq'               => '<a href="http://www.icq.com/" title="Search Engine Home Page [new window]" target="_blank">icq</a>',
        'nusearch'          => '<a href="http://www.nusearch.com/" title="Search Engine Home Page [new window]" target="_blank">Nusearch</a>',
        'goodsearch'        => '<a href="http://www.goodsearch.com/" title="Search Engine Home Page [new window]" target="_blank">GoodSearch</a>',
        'scroogle'          => '<a href="http://www.scroogle.org/" title="Search Engine Home Page [new window]" target="_blank">Scroogle</a>',
        'questionanswering' => '<a href="http://www.questionanswering.com/" title="Questionanswering home page [new window]" target="_blank">Questionanswering</a>',
        'mywebsearch'       => '<a href="http://search.mywebsearch.com/" title="MyWebSearch home page [new window]" target="_blank">MyWebSearch</a>',
        'comettoolbar'      => '<a href="http://as.starware.com/dp/search" title="Comet toolbar search home page [new window]" target="_blank">Comet toolbar search</a>',
# Social Bookmarking Services
        'delicious'         => '<a href="http://del.icio.us/" title="del.icio.us home page [new window]" target="_blank">del.icio.us</a> (Social Bookmark)',
        'digg'              => '<a href="http://www.digg.com/" title="Digg home page [new window]" target="_blank">Digg</a> (Social Bookmark)',
        'stumbleupon'       => '<a href="http://www.stumbleupon.com/" title="Stumbleupon home page [new window]" target="_blank">Stumbleupon</a> (Social Bookmark)',
        'swik'              => '<a href="http://swik.net/" title="Swik home page [new window]" target="_blank">Swik</a> (Social Bookmark)',
        'segnalo'           => '<a href="http://segnalo.alice.it/" title="Segnalo home page [new window]" target="_blank">Segnalo</a> (Social Bookmark)',
        'ineffabile'        => '<a href="http://www.ineffabile.it/" title="Ineffabile.it home page [new window]" target="_blank">Ineffabile.it</a> (Social Bookmark)',
# Minor Australian search engines
        'anzwers'           => '<a href="http://anzwers.com.au/" title="anzwers.com.au home page [new window]" target="_blank">anzwers.com.au</a>',
# Minor brazilian search engines
        'engine'            => 'Cade',
        'miner'             => 'Meta Miner',
# Minor chinese search engines
        'baidu'             => '<a href="http://www.baidu.com/" target="_blank">Baidu</a>',
        'iask'              => '<a href="http://www.iask.com/" target="_blank">Iask</a>',
        'accoona'           => '<a href="http://cn.accoona.com">Accoona</a>',
        '3721'              => '<a href="http://www.3721.com/" target="_blank">3721</a>',
        'netease',
        '<a href="http://www.163.com/" target="_blank">NetEase</a>',
        'soso'              => '<a href="http://www.soso.com/" target="_blank">SoSo</a>',
        'zhongsou'          => '<a href="http://www.zhongsou.com/" target="_blank">ZhongSou</a>',
        'sogou',
        '<a href="http://www.sogou.com/" target="_blank">SoGou</a>',
        'vnet'              => '<a href="http://114.vnet.cn/" target="_blank">VNet</a>',
# Minor czech search engines
        'atlas'             => 'Atlas.cz',
        'seznam'            => 'Seznam',
        'quick'             => 'Quick.cz',
        'centrum'           => 'Centrum.cz',
        'jyxo'              => 'Jyxo.cz',
        'najdi'             => 'Najdi.to',
        'redbox'            => 'RedBox.cz',
# Minor danish search-engines
        'opasia'            => 'Opasia',
        'danielsen'         => 'Thor (danielsen.com)',
        'sol'               => 'SOL',
        'jubii'             => 'Jubii',
        'finddk'            => 'Find',
        'edderkoppen'       => 'Edderkoppen',
        'netstjernen'       => 'Netstjernen',
        'orbis'             => 'Orbis',
        'tyfon'             => 'Tyfon',
        '1klik'             => '1Klik',
        'ofir'              => 'Ofir',
# Minor dutch search engines
        'ilse'              => 'Ilse',
        'vindex'            => 'Vindex\.nl',
# Minor english search engines
        'askuk'             => '<a href="http://uk.ask.com/" title="Search Engine Home Page [new window]" target="_blank">Ask UK</a>',
        'bbc'               => 'BBC',
        'freeserve'         => 'Freeserve',
        'looksmartuk'       => 'Looksmart UK',
        'splut'             => 'Splut',
        'spotjockey'        => 'Spotjockey',
        'ukdirectory'       => 'UK Directory',
        'ukindex'           => 'UKIndex',
        'ukplus'            => 'UK Plus',
        'searchy'           => 'searchy.co.uk',
# Minor finnish search engines
        'haku'              => 'Ihmemaa',
# Minor french search engines
        'aolfr'             => 'AOL (fr)',
        'ctrouve'           => 'C\'est trouvÈ',
        'francite'          => 'FrancitÈ',
        'lbb'               => 'LBB',
        'libertysurf'       => 'Libertysurf',
        'free'              => 'Free.fr',
        'clubinternet'      => 'Club-internet',
        'toile',
        'Toile du QuÈbec',
        'biglotron'         => '<a href="http://www.biglotron.com/" title="Search Engine Home Page [new window]" target="_blank">Biglotron</a>',
        'mozbot'            => '<a href="http://www.mozbot.fr/" title="Search Engine Home Page [new window]" target="_blank">Mozbot</a>',
# Minor German search engines
        'aolde'             => 'AOL (de)',
        'fireball'          => 'Fireball',
        'infoseek'          => 'Infoseek',
        'webde'             => 'Web.de',
        'abacho'            => 'Abacho',
        't-online'          => 'T-Online',
        'allesklar'         => 'allesklar.de',
        'meinestadt'        => 'meinestadt.de',
        'metaspinner'       => 'metaspinner',
        'metacrawler_de'    => 'metacrawler.de',
        'wwweasel'          => '<a href="http://wwweasel.de/" title="Search Engine Home Page [new window]" target="_blank">WWWeasel</a>',
        'netluchs'          => '<a href="http://www.netluchs.de/" title="Search Engine Home Page [new window]" target="_blank">Netluchs</a>',
        'schoenerbrausen'   => '<a href="http://www.schoenerbrausen.de/" title="Search Engine Home Page [new window]" target="_blank">Schoenerbrausen/</a>',
# Minor hungarian search engines
        'heureka'           => 'Heureka',
        'origo'             => 'Origo-Vizsla',
        'lapkereso'         => 'Startlapkeresı',
        'goliat'            => 'GÛli·t',
        'indexhu'           => 'Index',
        'wahoo'             => 'Wahoo',
        'webmania'          => 'webmania.hu',
        'internetto'        => 'Internetto Keresı',
        'tango_hu'          => '<a href="http://tango.hu/" title="Search Engine Home Page [new window]" target="_blank">Tango</a>',
        'keresolap_hu'      => '<a href="http://keresolap.hu/" title="Search Engine Home Page [new window]" target="_blank">Tango keresolap</a>',
        'polymeta_hu'       => '<a href="http://www.polymeta.hu/" title="Search Engine Home Page [new window]" target="_blank">Polymeta</a>',
# Minor Indian search engines
        'sify'              => '<a href="http://search.sify.com/" title="Search Engine Home Page [new window]" target="_blank">Sify</a>',
# Minor Italian search engines
        'virgilio'          => '<a href="http://www.virgilio.it/" title="Search Engine Home Page [new window]" target="_blank">Virgilio</a>',
        'arianna'           => '<a href="http://arianna.libero.it/" title="Search Engine Home Page [new window]" target="_blank">Arianna</a>',
        'supereva'          => '<a href="http://search.supereva.com/" title="Search Engine Home Page [new window]" target="_blank">Supereva</a>',
        'kataweb'           => '<a href="http://www.kataweb.it/ricerca/" title="Search Engine Home Page [new window]" target="_blank">Kataweb</a>',
        'aliceitmaster'     => '<a href="http://www.alice.it/" title="Search Engine Home Page [new window]" target="_blank">search.alice.it.master</a>',
        'aliceit'           => '<a href="http://www.alice.it/" title="Search Engine Home Page [new window]" target="_blank">alice.it</a>',
        'gotuneed'          => '<a href="http://www.gotuneed.com/" title="Search Engine Home Page [new window]" target="_blank">got u need</a>',
        'godado'            => 'Godado.it',
        'jumpy\.it'         => 'Jumpy.it',
        'shinyseek\.it'     => 'Shinyseek.it',
        'teecnoit'          => '<a href="http://www.teecno.it/" title="Teecno home page [new window]" target="_blank">Teecno</a>',
# Minor Japanese search engines
        'askjp'             => '<a href="http://www.ask.jp/" title="Search E@SearchEngngine Home Page [new window]" target="_blank">Ask Japan</a>',
        'sagool'            => '<a href="http://sagool.jp/" title="Sagool home page [new window]" target="_blank">Sagool</a>',
# Minor Norwegian search engines
        'start'             => 'start.no',
        'eniro'             => '<a href="http://www.eniro.no/" title="Search Engine Home Page [new window]" target="_blank">Eniro</a>',
# Minor polish search engines
        'wp'                => '<a href="http://szukaj.wp.pl/" title="Wirtualna Polska home page [new window]" target="_blank">Wirtualna Polska</a>',
        'onetpl'            => '<a href="http://szukaj.onet.pl/" title="Onet.pl home page [new window]" target="_blank">Onet.pl</a>',
        'dodajpl'           => '<a href="http://www.dodaj.pl/" title="Dodaj.pl home page [new window]" target="_blank">Dodaj.pl</a>',
        'gazetapl'          => '<a href="http://szukaj.gazeta.pl/" title="Gazeta.pl home page [new window]" target="_blank">Gazeta.pl</a>',
        'gerypl'            => '<a href="http://szukaj.gery.pl/" title="Gery.pl home page [new window]" target="_blank">Gery.pl</a>',
        'hogapl'            => '<a href="http://www.hoga.pl/" title="Hoga.pl home page [new window]" target="_blank">Hoga.pl</a>',
        'netsprintpl'       => '<a href="http://www.netsprint.pl/" title="NetSprint.pl home page [new window]" target="_blank">NetSprint.pl</a>',
        'interiapl'         => '<a href="http://www.google.interia.pl/" title="Interia.pl home page [new window]" target="_blank">Interia.pl</a>',
        'katalogonetpl'     => '<a href="http://katalog.onet.pl/" ti@SearchEngtle="Katalog.Onet.pl home page [new window]" target="_blank">Katalog.Onet.pl</a>',
        'o2pl'              => '<a href="http://szukaj2.o2.pl/" title="o2.pl home page [new window]" target="_blank">o2.pl</a>',
        'polskapl'          => '<a href="http://szukaj.polska.pl/" title="Polska home page [new window]" target="_blank">Polska</a>',
        'szukaczpl'         => '<a href="http://www.szukacz.pl/" title="Szukacz home page [new window]" target="_blank">Szukacz</a>',
        'wowpl'             => '<a href="http://szukaj.wow.pl/" title="Wow.pl home page [new window]" target="_blank">Wow.pl</a>',
# Minor russian search engines
        'yandex'            => 'Yandex',
        'aport'             => 'Aport',
        'rambler'           => 'Rambler',
        'turtle'            => 'Turtle',
        'metabot'           => 'MetaBot',
# Minor Swedish search engines
        'passagen'          => 'Evreka',
        'enirose'           => '<a href="http://www.eniro.se/" title="Eniro Sverige home page [new window]" target="_blank">Eniro Sverige</a>',
# Minor Slovak search engines
        'zoznam'            => '<a href="http://www.zoznam.sk/" title="Zoznam search engine home page [new window]" target="_blank">Zoznam</a>',
# Minor Portuguese search engines
        'sapo'              => '<a href="http://www.sapo.pt/" title="Sapo search engine home page [new window]" target="_blank">Sapo</a>',
# Minor Swiss search engines
        'searchch',
        'search.ch',
        'bluewin',
        'search.bluewin.ch',
# Generic search engines
        'search'            => 'Unknown search engines'
    );

    /**
     * Browser Informations
     */
    public static function getVersion( $browser = '\s' )
    {
        $version_string = $browser . "*[\/\sa-z\(]\s*([0-9]+)([\.0-9a-z]*)";

        $version = '';
        $ver = null;

        if ( preg_match( "/" . $version_string . "/i", self::$ua, $ver ) )
        {

            if ( trim( (string) $ver[ 1 ] ) )
            {
                $maj_ver = trim( (string) $ver[ 1 ] );
                $match = null;

                // parse the minor version string and look for alpha chars
                preg_match( '/([.\0-9]*)([\.a-z0-9]*)/i', $ver[ 2 ], $match );


                $min_ver = '';
                if ( isset( $match[ 1 ] ) && trim( (string) $match[ 1 ] ) )
                {
                    $min_ver = trim( (string) $match[ 1 ] );
                }
                else
                {
                    $min_ver = '.0';
                }

                $version = $maj_ver . $min_ver;
            }
        }

        return $version;
    }

    /**
     * @return array
     */
    public static function getBrowser()
    {
        $data = Tracking_AgentParser::getBrowser( self::$ua );


        self::$browser = $data[ 'id' ];
        self::$browser_version = $data[ 'version' ];

        return array(
            self::$browser,
            self::$browser_version );

        /*
         * 
          while ( list($key, $match) = each( self::$BrowsersSearchIDOrder ) )
          {
          if ( preg_match( '#' . $match . '#isU', self::$ua ) )
          {
          self::$browser = $match;
          self::$browser_version = self::getVersion( $match );

          return array( self::$browser, self::$browser_version );
          }
          } */
    }

}
