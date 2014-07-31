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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Framework
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Abstract.php
 */
class Firewall_Abstract
{

	/**
	 * @var array
	 */
	public $cookie_rules = array (
		'applet',
		'base',
		'bgsound',
		'blink',
		'embed',
		'expression',
		'frame',
		'javascript',
		'layer',
		'link',
		'meta',
		'object',
		'onabort',
		'onactivate',
		'onafterprint',
		'onafterupdate',
		'onbeforeactivate',
		'onbeforecopy',
		'onbeforecut',
		'onbeforedeactivate',
		'onbeforeeditfocus',
		'onbeforepaste',
		'onbeforeprint',
		'onbeforeunload',
		'onbeforeupdate',
		'onblur',
		'onbounce',
		'oncellchange',
		'onchange',
		'onclick',
		'oncontextmenu',
		'oncontrolselect',
		'oncopy',
		'oncut',
		'ondataavailable',
		'ondatasetchanged',
		'ondatasetcomplete',
		'ondblclick',
		'ondeactivate',
		'ondrag',
		'ondragend',
		'ondragenter',
		'ondragleave',
		'ondragover',
		'ondragstart',
		'ondrop',
		'onerror',
		'onerrorupdate',
		'onfilterchange',
		'onfinish',
		'onfocus',
		'onfocusin',
		'onfocusout',
		'onhelp',
		'onkeydown',
		'onkeypress',
		'onkeyup',
		'onlayoutcomplete',
		'onload',
		'onlosecapture',
		'onmousedown',
		'onmouseenter',
		'onmouseleave',
		'onmousemove',
		'onmouseout',
		'onmouseover',
		'onmouseup',
		'onmousewheel',
		'onmove',
		'onmoveend',
		'onmovestart',
		'onpaste',
		'onpropertychange',
		'onreadystatechange',
		'onreset',
		'onresize',
		'onresizeend',
		'onresizestart',
		'onrowenter',
		'onrowexit',
		'onrowsdelete',
		'onrowsinserted',
		'onscroll',
		'onselect',
		'onselectionchange',
		'onselectstart',
		'onstart',
		'onstop',
		'onsubmit',
		'onunload',
		'script',
		'style',
		'title',
		'vbscript',
		'xml',
		'cp:'
	);

	/**
	 *
	 * @var type
	 */
	public $url_rules = array (
		'absolute_path',
		'ad_click',
		'alert(',
		'alert%20',
		' and ',
		'basepath',
		'bash_history',
		'.bash_history',
		'cgi-',
		'chmod(',
		'chmod%20',
		'%20chmod',
		'chmod=',
		'chown%20',
		'chgrp%20',
		'chown(',
		'/chown',
		'chgrp(',
		'chr(',
		'chr=',
		'chr%20',
		'%20chr',
		'chunked',
		'cookie=',
		'cmd',
		'cmd=',
		'%20cmd',
		'cmd%20',
		'.conf',
		'configdir',
		'config.php',
		'.config.php',
		'cp%20',
		'%20cp',
		'cp(',
		'diff%20',
		'dat?',
		'db_mysql.inc',
		'document.location',
		'document.cookie',
		'drop%20',
		'echr(',
		'%20echr',
		'echr%20',
		'echr=',
		'}else{',
		'.eml',
		'esystem(',
		'esystem%20',
		'.exe',
		'exploit',
		'file\://',
		'fopen',
		'fwrite',
		'~ftp',
		'ftp:',
		'ftp.exe',
		'getenv',
		'%20getenv',
		'getenv%20',
		'getenv(',
		'grep%20',
		'_global',
		'global_',
		'global[',
		'http:',
		'_globals',
		'globals_',
		'globals[',
		'grep(',
		'g\+\+',
		'halt%20',
		'.history',
		'?hl=',
		'.htpasswd',
		'http_',
		'http-equiv',
		'http/1.',
		'http_php',
		'http_user_agent',
		'http_host',
		'&icq',
		'if{',
		'if%20{',
		'img src',
		'img%20src',
		'.inc.php',
		'.inc',
		'insert%20into',
		'ISO-8859-1',
		'ISO-',
		'javascript\://',
		'.jsp',
		'.js',
		'kill%20',
		'kill(',
		'killall',
		'%20like',
		'like%20',
		'locate%20',
		'locate(',
		'lsof%20',
		'mdir%20',
		'%20mdir',
		'mdir(',
		'mcd%20',
		'motd%20',
		'mrd%20',
		'rm%20',
		'%20mcd',
		'%20mrd',
		'mcd(',
		'mrd(',
		'mcd=',
		'mod_gzip_status',
		'modules/',
		'mrd=',
		'mv%20',
		'nc.exe',
		'new_password',
		'nigga(',
		'%20nigga',
		'nigga%20',
		'~nobody',
		'org.apache',
		'+outfile+',
		'%20outfile%20',
		'*/outfile/*',
		' outfile ',
		'outfile',
		'password=',
		'passwd%20',
		'%20passwd',
		'passwd(',
		'phpadmin',
		'perl%20',
		'/perl',
		'phpbb_root_path',
		'*/phpbb_root_path/*',
		'p0hh',
		'ping%20',
		'.pl',
		'powerdown%20',
		'rm(',
		'%20rm',
		'rmdir%20',
		'mv(',
		'rmdir(',
		'phpinfo()',
		'<?php',
		'reboot%20',
		'/robot.txt',
		'~root',
		'root_path',
		'rush=',
		'%20and%20',
		'%20xorg%20',
		'%20rush',
		'rush%20',
		'secure_site, ok',
		'select%20',
		'select from',
		'select%20from',
		'_server',
		'server_',
		'server[',
		'server-info',
		'server-status',
		'servlet',
		'sql=',
		'<script',
		'<script>',
		'</script',
		'script>',
		'/script',
		'switch{',
		'switch%20{',
		'.system',
		'system(',
		'telnet%20',
		'traceroute%20',
		'union%20',
		'%20union',
		'union(',
		'union=',
		'vi(',
		'vi%20',
		'wget',
		'wget%20',
		'%20wget',
		'wget(',
		'window.open',
		'wwwacl',
		' xor ',
		'xp_enumdsn',
		'xp_availablemedia',
		'xp_filelist',
		'xp_cmdshell',
		'$_request',
		'$_get',
		'$request',
		'$get',
		'&aim',
		'/etc/password',
		'/etc/shadow',
		'/etc/groups',
		'/etc/gshadow',
		'/bin/ps',
		'uname\x20-a',
		'/usr/bin/id',
		'/bin/echo',
		'/bin/kill',
		'/bin/',
		'/chgrp',
		'/usr/bin',
		'bin/python',
		'bin/tclsh',
		'bin/nasm',
		'/usr/x11r6/bin/xterm',
		'/bin/mail',
		'/etc/passwd',
		'/home/ftp',
		'/home/www',
		'/servlet/con',
		'?>',
		'.txt',
		'cp:',
		'/.cache/',
		'<',
		'>'
	);

	/**
	 *
	 * @var type
	 */
	public $bot_rules = array (
		'@nonymouse',
		'addresses.com',
		'ideography.co.uk',
		'adsarobot',
		'ah-ha',
		'aktuelles',
		'alexibot',
		'almaden',
		'amzn_assoc',
		'anarchie',
		'art-online',
		'aspseek',
		'assort',
		'asterias',
		'attach',
		'atomz',
		'atspider',
		'autoemailspider',
		'backweb',
		'backdoorbot',
		'bandit',
		'batchftp',
		'bdfetch',
		'big.brother',
		'black.hole',
		'blackwidow',
		'blowfish',
		'bmclient',
		'boston project',
		'botalot',
		'bravobrian',
		'buddy',
		'bullseye',
		'bumblebee ',
		'builtbottough',
		'bunnyslippers',
		'capture',
		'cegbfeieh',
		'cherrypicker',
		'cheesebot',
		'chinaclaw',
		'cicc',
		'civa',
		'clipping',
		'collage',
		'collector',
		'copyrightcheck',
		'cosmos',
		'crescent',
		'custo',
		'cyberalert',
		'deweb',
		'diagem',
		'digger',
		'digimarc',
		'diibot',
		'directupdate',
		'disco',
		'dittospyder',
		'download accelerator',
		'download demon',
		'download wonder',
		'downloader',
		'drip',
		'dsurf',
		'dts agent',
		'dts.agent',
		'easydl',
		'ecatch',
		'echo extense',
		'efp@gmx.net',
		'eirgrabber',
		'elitesys',
		'emailsiphon',
		'emailwolf',
		'envidiosos',
		'erocrawler',
		'esirover',
		'express webpictures',
		'extrac',
		'eyenetie',
		'fastlwspider',
		'favorg',
		'favorites sweeper',
		'fezhead',
		'filehound',
		'filepack.superbr.org',
		'flashget',
		'flickbot',
		'fluffy',
		'frontpage',
		'foobot',
		'galaxyBot',
		'generic',
		'getbot ',
		'getleft',
		'getright',
		'getsmart',
		'geturl',
		'getweb',
		'gigabaz',
		'girafabot',
		'go-ahead-got-it',
		'go!zilla',
		'gornker',
		'grabber',
		'grabnet',
		'grafula',
		'green research',
		'harvest',
		'havindex',
		'hhjhj@yahoo',
		'hloader',
		'hmview',
		'homepagesearch',
		'htmlparser',
		'hulud',
		'http agent',
		'httpconnect',
		'httpdown',
		'http generic',
		'httplib',
		'httrack',
		'humanlinks',
		'ia_archiver',
		'iaea',
		'ibm_planetwide',
		'image stripper',
		'image sucker',
		'imagefetch',
		'incywincy',
		'indy',
		'infonavirobot',
		'informant',
		'interget',
		'internet explore',
		'infospiders',
		'internet ninja',
		'internetlinkagent',
		'interneteseer.com',
		'ipiumbot',
		'iria',
		'irvine',
		'jbh',
		'jeeves',
		'jennybot',
		'jetcar',
		'joc web spider',
		'jpeg hunt',
		'justview',
		'kapere',
		'kdd explorer',
		'kenjin.spider',
		'keyword.density',
		'kwebget',
		'lachesis',
		'larbin',
		'laurion(dot)com',
		'leechftp',
		'lexibot',
		'lftp',
		'libweb',
		'links aromatized',
		'linkscan',
		'link*sleuth',
		'linkwalker',
		'libwww',
		'lightningdownload',
		'likse',
		'lwp',
		'mac finder',
		'mag-net',
		'magnet',
		'marcopolo',
		'mass',
		'mata.hari',
		'mcspider',
		'memoweb',
		'microsoft url control',
		'microsoft.url',
		'midown',
		'miixpc',
		'minibot',
		'mirror',
		'missigua',
		'mister.pix',
		'mmmtocrawl',
		'moget',
		'mozilla/2',
		'mozilla/3.mozilla/2.01',
		'mozilla.*newt',
		'multithreaddb',
		'munky',
		'msproxy',
		'nationaldirectory',
		'naverrobot',
		'navroad',
		'nearsite',
		'netants',
		'netcarta',
		'netcraft',
		'netfactual',
		'netmechanic',
		'netprospector',
		'netresearchserver',
		'netspider',
		'net vampire',
		'newt',
		'netzip',
		'nicerspro',
		'npbot',
		'octopus',
		'offline.explorer',
		'offline explorer',
		'offline navigator',
		'opaL',
		'openfind',
		'opentextsitecrawler',
		'orangebot',
		'packrat',
		'papa foto',
		'pagegrabber',
		'pavuk',
		'pbwf',
		'pcbrowser',
		'personapilot',
		'pingalink',
		'pockey',
		'program shareware',
		'propowerbot/2.14',
		'prowebwalker',
		'proxy',
		'psbot',
		'psurf',
		'puf',
		'pushsite',
		'pump',
		'qrva',
		'quepasacreep',
		'queryn.metasearch',
		'realdownload',
		'reaper',
		'recorder',
		'reget',
		'replacer',
		'repomonkey',
		'rma',
		'robozilla',
		'rover',
		'rpt-httpclient',
		'rsync',
		'rush=',
		'searchexpress',
		'searchhippo',
		'searchterms.it',
		'second street research',
		'seeker',
		'shai',
		'sitecheck',
		'sitemapper',
		'sitesnagger',
		'slysearch',
		'smartdownload',
		'snagger',
		'spacebison',
		'spankbot',
		'spanner',
		'spegla',
		'spiderbot',
		'spiderengine',
		'sqworm',
		'ssearcher100',
		'star downloader',
		'stripper',
		'sucker',
		'superbot',
		'surfwalker',
		'superhttp',
		'surfbot',
		'surveybot',
		'suzuran',
		'sweeper',
		'szukacz/1.4',
		'tarspider',
		'takeout',
		'teleport',
		'telesoft',
		'templeton',
		'the.intraformant',
		'thenomad',
		'tighttwatbot',
		'titan',
		'tocrawl/urldispatcher',
		'toolpak',
		'traffixer',
		'true_robot',
		'turingos',
		'turnitinbot',
		'tv33_mercator',
		'uiowacrawler',
		'urldispatcherlll',
		'url_spider_pro',
		'urly.warning ',
		'utilmind',
		'vacuum',
		'vagabondo',
		'vayala',
		'vci',
		'visualcoders',
		'visibilitygap',
		'vobsub',
		'voideye',
		'vspider',
		'w3mir',
		'webauto',
		'webbandit',
		'web.by.mail',
		'webcapture',
		'webcatcher',
		'webclipping',
		'webcollage',
		'webcopier',
		'webcopy',
		'webcraft@bea',
		'web data extractor',
		'webdav',
		'webdevil',
		'webdownloader',
		'webdup',
		'webenhancer',
		'webfetch',
		'webgo',
		'webhook',
		'web.image.collector',
		'web image collector',
		'webinator',
		'webleacher',
		'webmasters',
		'webmasterworldforumbot',
		'webminer',
		'webmirror',
		'webmole',
		'webreaper',
		'websauger',
		'websaver',
		'website.quester',
		'website quester',
		'websnake',
		'websucker',
		'web sucker',
		'webster',
		'webreaper',
		'webstripper',
		'webvac',
		'webwalk',
		'webweasel',
		'webzip',
		'wget',
		'widow',
		'wisebot',
		'whizbang',
		'whostalking',
		'wonder',
		'wumpus',
		'wweb',
		'www-collector-e',
		'wwwoffle',
		'wysigot',
		'xaldon',
		'xenu',
		'xget',
		'x-tractor',
		'zeus',
		'zmeu'
	);

	/** protection contre le vers santy */

	/**
	 *
	 * @var type
	 */
	public $santy_rules = array (
		'highlight=%',
		'chr(',
		'pillar',
		'visualcoder',
		'sess_'
	);

	/**
	 * @var array
	 */
	public $click_rules = array (
		'/*',
		'c2nyaxb0',
		'/*'
	);

	/**
	 *
	 * @var type
	 */
	public $xxs_rules = array (
		//       'http://',
		//       'https://',
		'cmd=',
		'&cmd',
		'?cmd=',
		'exec',
		'concat', /* './', '../', */
		//       'http:',
		'h%20ttp:',
		'ht%20tp:',
		'htt%20p:',
		'http%20:',
		//       'https:',
		'h%20ttps:',
		'ht%20tps:',
		'htt%20ps:',
		'http%20s:',
		'https%20:',
		//        'ftp:',
		'f%20tp:',
		'ft%20p:',
		'ftp%20:',
		//        'ftps:',
		'f%20tps:',
		'ft%20ps:',
		'ftp%20s:',
		'ftps%20:',
		'.php?url=',
		'.php?path='
	);

	/**
	 *
	 * @var type
	 */
	public $sql_rules = array (
		'*/from/*',
		'*/insert/*',
		'+into+',
		'%20into%20',
		'*/into/*',
		' into ',
		'into',
		'*/limit/*',
		'not123exists*',
		'*/radminsuper/*',
		'*/select/*',
		'+select+',
		'%20select%20',
		' select ',
		'+union+',
		'%20union%20',
		'*/union/*',
		' union ',
		'*/update/*',
		'*/where/*'
	);

	/**
	 * not longer used :)
	 * we use the spammer database
	 *
	 * @var type
	 */
	/**
	 *
	 * @deprecated
	 * @see protection_bad_ips this is the new protection
	 */
	public $range_ip_spam = array (
		'24',
		'186',
		'189',
		'190',
		'200',
		'201',
		'202',
		'209',
		'212',
		'213',
		'217',
		'222'
	);

	/**
	 *
	 * @var type
	 */
	public $range_ip_deny = array (
		'0',
		'1',
		'2',
		'5',
		'10',
		'14',
		'23',
		'27',
		'31',
		'36',
		'37',
		'39',
		'42',
		'46',
		'49',
		'50',
		'100',
		'101',
		'102',
		'103',
		'104',
		'105',
		'106',
		'107',
		'114',
		'172',
		'176',
		'177',
		'179',
		'181',
		'185',
		'223',
		'224'
	);

	/**
	 *
	 * @var type
	 */
	public $layout = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="EXPIRES" content="Fri, 24 Dec 2020 11:12:01 GMT"/>
        <meta name="keywords" content="" />
        <meta name="description" content="" />
        <title>DreamCMS Firewall</title>
    </head>
    <body style="font-family: Verdana, Geneva, sans-serif; background-color:silver; padding:100px">
        <center>
            <form name="firewall" method="post" action="">
                <input type="hidden" name="firewall_redirect" value="true"/>
                <input type="submit" value="Click to continue"/>
            </form>
        </center>
    </body>
</html>';

	/**
	 * @var string
	 */
	private $layoutWait = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="EXPIRES" content="Fri, 24 Dec 2020 11:12:01 GMT"/>
        <meta name="keywords" content="" />
        <meta name="description" content="" />
        <title>DreamCMS Firewall</title>
    </head>
    <body style="font-family: Verdana, Geneva, sans-serif; background-color:silver; padding:100px">
        <center>
            %s
        </center>
    </body>
</html>';

	/**
	 * List of Search Engine Agents
	 */
	protected $SearchEngineUserAgent = array (
		'Googlebot',
		'msnbot',
		'slurp',
		'fast-webcrawler',
		'Googlebot-Image',
		'teomaagent1',
		'directhit',
		'lycos',
		'ia_archiver',
		'gigabot',
		'whatuseek',
		'Teoma',
		'scooter',
		'Ask Jeeves',
		'slurp@inktomi',
		'gzip(gfe) (via translate.google.com)',
		'Mediapartners-Google',
		'crawler@alexa.com'
	);

	/*
	 * Config
	 */

	/**
	 * @var array
	 */
	protected $config = array (
		//Amount of time in second to show restrict message if a Flooding attack is determined
		'firewall_wait_time'               => 10, // 10 sekunden warten
		//Amount of penalty to be considered a Flooding attack.
		//Every time multiple requests sent to the CMS in less than few a second, penalty count increased by 1.
		'firewall_penalty_allow'           => 10, // wenn 10 anfragen kamen dann firewall_wait_time
		'unsetglobals'                     => true,
		// 'logfile' => 'firewall-log.txt',
		'admin_mail'                       => '',
		'push_mail'                        => false,
		'use_floodcheck'                   => false,
		'protection_bad_ips'               => true,
		'protection_unset_globals'         => true,
		'protection_range_ip_deny'         => false,
		/**
		 *
		 * @deprecated
		 * @see protection_bad_ips this is the new protection
		 */
		//	'protection_ip_spam'               => true,
		//	'protection_range_ip_spam'         => false,
		'protection_url'                   => false,
		'protection_request_server'        => true,
		'protection_santy'                 => false,
		'protection_bots'                  => true,
		'protection_request_method'        => true,
		'protection_dos'                   => true,
		'protection_union_sql'             => true,
		'protection_click_attack'          => true,
		'protection_xss_attack'            => true,
		//
		'protection_cookies'               => false,
		'protection_post'                  => false,
		'protection_get'                   => false,
		//
		'protection_server_ovh'            => false,
		'protection_server_kimsufi'        => false,
		'protection_server_dedibox'        => false,
		'protection_server_digicube'       => false,
		'protection_server_ovh_by_ip'      => true,
		'protection_server_kimsufi_by_ip'  => true,
		'protection_server_dedibox_by_ip'  => true,
		'protection_server_digicube_by_ip' => true,
	);

	/**
	 * @var string
	 */
	protected $firewallVersion = '0.1';

	/**
	 * @var
	 */
	protected $destination_ready;

	/**
	 * @var null store the Client Hostname
	 */
	protected static $host = null;

	//I don't think search engines use cookie
	/**
	 * @return bool
	 */
	public function check_cookie ()
	{

		if ( setcookie("test", "test", time() + 360) )
		{
			if ( isset($_COOKIE[ 'test' ]) )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 *
	 * @param string $subject
	 * @param string $msg
	 */
	protected function push_email ( $subject, $msg )
	{

		if ( $this->config[ 'admin_mail' ] != '' )
		{


			$headers = "From: DreamCMS Firewall: " . $this->config[ 'admin_mail' ] . " <" . $this->config[ 'admin_mail' ] . ">\r\n" . "Reply-To: " . $this->config[ 'admin_mail' ] . "\r\n" . "Priority: urgent\r\n" . "Importance: High\r\n" . "Precedence: special-delivery\r\n" . "Organization: DreamCMS Firewall\r\n" . "MIME-Version: 1.0\r\n" . "Content-Type: text/plain\r\n" . "Content-Transfer-Encoding: 8bit\r\n" . "X-Priority: 1\r\n" . "X-MSMail-Priority: High\r\n" . "X-Mailer: PHP/" . phpversion() . "\r\n" . "X-PHPFirewall: DreamCMS Firewall v" . $this->firewallVersion . "\r\n" . "Date:" . date("D, d M Y H:s:i") . " +0100\n";

			mail($this->config[ 'admin_mail' ], $subject, $msg, $headers);
		}
	}

	/**
	 *
	 * @param $type
	 * @return bool
	 */
	protected function log ( $type )
	{

		$db = Database::getInstance();
		$host = $this->gethostbyaddr();
		$db->query('INSERT INTO %tp%firewall_log (timestamp,requesturi,postparams,getparams,errortype,dns,ip,useragent,refferer,blocked) VALUES(?,?,?,?,?,?,?,?,?,0)', time(), FIREWALL_REQUEST_URI, (is_array($_POST) ?
			serialize($_POST) : ''), (is_array($_GET) ? serialize($_GET) :
			''), $type, ($host ? $host : ''), FIREWALL_GET_IP, FIREWALL_USER_AGENT, FIREWALL_GET_REFERER);

		// file_put_contents( $this->config[ 'logfile' ], $msg, FILE_APPEND );

		if ( $this->config[ 'push_mail' ] === true )
		{
			$msg = date('j-m-Y H:i:s') . " | $type | IP: " . FIREWALL_GET_IP . " | DNS: " . $this->gethostbyaddr() . " | Agent: " . FIREWALL_USER_AGENT . " | URL: " . FIREWALL_REQUEST_URI . " | Referer: " . FIREWALL_GET_REFERER . "\n\n";
			$this->push_email('DreamCMS - Firewall ' . strip_tags($_SERVER[ 'SERVER_NAME' ]), "DreamCMS Firewall logs of " . strip_tags($_SERVER[ 'SERVER_NAME' ]) . "\n" . str_replace('|', "\n", $msg));
		}

		return true;
	}

	/**
	 *
	 *
	 */
	protected function unsetGlobals ()
	{

		if ( ini_get('register_globals') )
		{
			$allow = array (
				'_ENV'     => 1,
				'_GET'     => 1,
				'_POST'    => 1,
				'_COOKIE'  => 1,
				'_FILES'   => 1,
				'_SERVER'  => 1,
				'_REQUEST' => 1,
				'GLOBALS'  => 1
			);

			foreach ( $GLOBALS as $key => $value )
			{
				if ( !empty($allow[ $key ]) )
				{
					unset($GLOBALS[ $key ]);
				}
			}
		}
	}

	/**
	 *
	 * @param $st_var
	 * @return string
	 */
	protected function get_env ( $st_var )
	{

		global $HTTP_SERVER_VARS;

		if ( isset($_SERVER[ $st_var ]) )
		{
			return strip_tags($_SERVER[ $st_var ]);
		}
		elseif ( isset($_ENV[ $st_var ]) )
		{
			return strip_tags($_ENV[ $st_var ]);
		}
		elseif ( isset($HTTP_SERVER_VARS[ $st_var ]) )
		{
			return strip_tags($HTTP_SERVER_VARS[ $st_var ]);
		}
		elseif ( getenv($st_var) )
		{
			return strip_tags(getenv($st_var));
		}
		elseif ( function_exists('apache_getenv') && apache_getenv($st_var, true) )
		{
			return strip_tags(apache_getenv($st_var, true));
		}

		return '';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_ip ()
	{

		if ( $this->get_env('HTTP_X_FORWARDED_FOR') )
		{
			return $this->get_env('HTTP_X_FORWARDED_FOR');
		}
		elseif ( $this->get_env('HTTP_CLIENT_IP') )
		{
			return $this->get_env('HTTP_CLIENT_IP');
		}
		else
		{
			return $this->get_env('REMOTE_ADDR');
		}
	}

	/**
	 *
	 * @return string
	 */
	protected function get_referer ()
	{

		if ( $this->get_env('HTTP_REFERER') )
		{
			return $this->get_env('HTTP_REFERER');
		}

		return 'no referer';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_user_agent ()
	{

		if ( $this->get_env('HTTP_USER_AGENT') )
		{
			return $this->get_env('HTTP_USER_AGENT');
		}

		return 'none';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_query_string ()
	{

		if ( $this->get_env('QUERY_STRING') )
		{
			return str_replace('%09', '%20', $this->get_env('QUERY_STRING'));
		}

		return '';
	}

	/**
	 *
	 * @return string
	 */
	protected function get_request_method ()
	{

		if ( $this->get_env('REQUEST_METHOD') )
		{
			return strtolower($this->get_env('REQUEST_METHOD'));
		}

		return 'none';
	}

	/**
	 *
	 * @return string
	 */
	protected function gethostbyaddr ()
	{

		if ( $this->config[ 'protection_server_ovh' ] === true || $this->config[ 'protection_server_kimsufi' ] === true || $this->config[ 'protection_server_dedibox' ] === true || $this->config[ 'protection_server_digicube' ] === true
		)
		{
			if ( is_null(self::$host) )
			{
				return self::$host = gethostbyaddr($this->get_ip());
			}
			else
			{
				return strip_tags(self::$host);
			}
		}
	}

	/**
	 * --------------------------------------------
	 *              Rest of Firewall Functions
	 *              based on ZB Block http://www.spambotsecurity.com/
	 * --------------------------------------------
	 */

    /**
     *
     * @param $haystack
     * @param $pattern
     * @param $message
     * @internal param $whyblockin
     * @return bool
     */
	protected function inmatch ( $haystack, $pattern, $message )
	{

		if ( substr_count($haystack, $pattern) )
		{
			$this->log('Test inmatch: ' . $pattern . ' Invalid: ' . $message);
			die('Invalid ! Stop it ...');
		}

		return false;
	}

	/**
	 * @param $haystack
	 * @param $pattern
	 * @param $message
	 * @return bool
	 */
	protected function rmatch ( $haystack, $pattern, $message )
	{

		$length = strlen($pattern);
		if ( substr($haystack, -$length) == $pattern )
		{
			$this->log('Test rmatch: ' . $pattern . ' Invalid: ' . $message);
			die('Invalid ! Stop it ...');
		}

		return false;
	}

	/**
	 * @param $haystack
	 * @param $pattern
	 * @param $message
	 * @return bool
	 */
	protected function lmatch ( $haystack, $pattern, $message )
	{
		$length = strlen($pattern);
		if ( substr($haystack, 0, $length) == $pattern )
		{
			$this->log('Test lmatch: ' . $pattern . ' Invalid: ' . $message);
			die('Invalid ! Stop it ...');
		}

		return false;
	}

	/**
	 * @param $haystack
	 * @param $pattern
	 * @param $message
	 * @return bool
	 */
	protected function regexmatch ( $haystack, $pattern, $message )
	{

		//$pattern = str_replace("%","\%",$pattern);
		if ( preg_match('%' . $pattern . '%i', $haystack) )
		{
			$this->log('Test regexmatch: ' . $pattern . ' Invalid: ' . $message);
			die('Invalid ! Stop it ...');
		}

		return false;
	}

	/**
	 * @param $haystack
	 * @param $pattern
	 * @param $allowed
	 * @param $message
	 * @return bool
	 */
	function minmatch ( $haystack, $pattern, $allowed, $message )
	{

		if ( substr_count($haystack, $pattern) > $allowed )
		{
			$this->log('Test minmatch: ' . $pattern . ' Invalid: ' . $message);
			die('Invalid ! Stop it ...');
		}

		return false;
	}

	/**
	 * @param string $hoster
	 */
	protected function testHoster ( $hoster )
	{

		// Normal Hostname Blocks
		$this->inmatch($hoster, 'majestic', 'MJ01 (HN-0001). '); //70e
		$this->inmatch($hoster, 'hosteurope', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0002). '); //68c Fix
		$this->rmatch($hoster, 'seomoz.org', 'Site scrapers (HN-0003). ');
		$this->rmatch($hoster, 'rulinki.ru', 'Bad spider, does not use robots.txt (HN-0004). ');
		$this->rmatch($hoster, 'internetserviceteam.com', 'internetserviceteam, phpbb hackers (HN-0005). ');
		$this->rmatch($hoster, 'kimsufi.com', 'kimsufi, forum spambots (HN-0006). ');
		$this->rmatch($hoster, 'pool.ukrtel.net', 'ukrtel, forum spambots (HN-0007). ');
		$this->rmatch($hoster, 'mbox.kz', "mbox, blog 'sploit bothost (HN-0008). ");
		$this->rmatch($hoster, 'cjh-law.com', 'cjh-law, nasty phishers / scammers (HN-0009). ');
		$this->rmatch($hoster, 'kiyosho.jp', 'kiyosho, infected japanese machine (HN-0010). ');
		$this->rmatch($hoster, 'adsinmedia.co.in', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0011). ');
		$this->rmatch($hoster, 'holhost.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0012). ');
		$this->rmatch($hoster, 'vip-net.pl', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0013). ');
		$this->rmatch($hoster, '23gb.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0014). ');
		$this->rmatch($hoster, 'swifttrim.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0015). ');
		$this->rmatch($hoster, 'dns-safe.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0016). ');
		$this->rmatch($hoster, 'cadinor.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0017). ');
		$this->rmatch($hoster, 'neointeractiva.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0018). ');
		$this->rmatch($hoster, '3fn.net', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0019). ');
		$this->rmatch($hoster, '.fsfreeware.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0020). ');
		$this->rmatch($hoster, 'linkneo.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0021). ');
		$this->rmatch($hoster, 'host.caracastelecom.net', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0022). ');
		$this->rmatch($hoster, 'ig.com.br', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0023). ');
		$this->rmatch($hoster, 'clanmoi.de', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0024). ');
		$this->rmatch($hoster, 'piemontetv.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0025). ');
		$this->rmatch($hoster, 'authenticnetworks.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0026). ');
		$this->rmatch($hoster, 'ogicom.pl', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0027). ');
		$this->rmatch($hoster, 'agava.net', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0028). ');
		$this->rmatch($hoster, 'rivreg.ru', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0029). ');
		$this->rmatch($hoster, 'americanforeclosures.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0030). ');
		$this->rmatch($hoster, 'vampire.pl', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0031). ');
		$this->rmatch($hoster, 'midphase.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0032). ');
		$this->rmatch($hoster, 'brasiltelecom.net.br', 'Bothost and/or Server Farm. Not an access provider ISP (credit: eclecticdjs.com) (HN-0033). ');
		$this->rmatch($hoster, 'starnet.md', 'Bothost and/or Server Farm. Not an access provider ISP (credit: eclecticdjs.com) (HN-0034). ');
		$this->inmatch($hoster, 'steephost', 'Bothost and/or Server Farm. Not an access provider ISP (credit: eclecticdjs.com) (HN-0035). '); //Modified 74b
		$this->rmatch($hoster, 'followmeoffice.com', "Bothost / Better idea, don't follow me (HN-0036). ");
		$this->rmatch($hoster, 'dumpyourbitch.com', 'Rude script attackers (HN-0037). ');
		$this->rmatch($hoster, 'exatt.net', "Bad ISP, allows bots to run loose, hides fact it's in Mumbai, India (HN-0038). ");
		$this->rmatch($hoster, 'dotnetdotcom.org', 'Bad search spider. Ignores robots.txt. Offers an explosive .zip to those who try to use their services (HN-0039). ');
		//$this->rmatch($hoster,'asianet.co.th','Bothost (HN-0040). '); ***PROVISIONAL REMOVAL***
		if ( !($this->rmatch($hoster, '.user.veloxzone.com.br', '')) )
		{
			$this->rmatch($hoster, 'veloxzone.com.br', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0041). ');
		} //modified 73d to see if it user domain should be bypassed.
		$this->rmatch($hoster, 'qvt.net.br', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0042). ');
		$this->rmatch($hoster, 'vtr.net', 'Bothost and/or Server Farm. Not an access provider ISP (weird!) (HN-0043). ');
		$this->rmatch($hoster, 'terra.cl', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0044). ');
		$this->rmatch($hoster, 'iam.net.ma', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0045). ');
		$this->rmatch($hoster, '35up.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0046). ');
		$this->rmatch($hoster, 'isnet.net', 'South African Bothosts (HN-0047). ');
		$this->rmatch($hoster, 'tiscali.it', 'tiscali, constant source of forum spam attempts (HN-0048). ');
		$this->rmatch($hoster, 'dragonara.net', 'Spamjockey ISP... GO AWAY! (HN-0049). ');
		$this->rmatch($hoster, 'accentrainc.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0050). ');
		$this->rmatch($hoster, 'colo.iinet.com', 'Iterasi site scrapers (HN-0051). ');
		$this->rmatch($hoster, 'smartservercontrol.com', "smartservercontrol.com - ya ain't controlling my server bub (HN-0052). ");
		$this->rmatch($hoster, 'hinet.net', 'Taiwanese ISP with a history of uncontrolled attacks (HN-0053). ');
		$this->rmatch($hoster, 'chello.pl', 'Problematic ISP/Host, constant source of attacks (HN-0054). ');
		$this->rmatch($hoster, 'accelovation.com', 'Content scraper for paid service (HN-0055). ');
		$this->rmatch($hoster, 'setooz.com', 'Email harvester from India (HN-0056). ');
		$this->rmatch($hoster, 'altushost.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0057). ');
		$this->rmatch($hoster, 'bestprice.com', 'Spammers (HN-0058). ');
		$this->rmatch($hoster, 'doctore.sk', 'Skiddy (HN-0059). ');
		$this->rmatch($hoster, 'page-store.com', 'SEOMOZ keyword scraper (HN-0061). ');
		$this->rmatch($hoster, 'mantraonline.com', 'Indian spammers / malware spreaders (HN-0062). ');
		$this->rmatch($hoster, 'jkserv.net', 'Game server network that keeps probing the author (HN-0063). ');
		$this->rmatch($hoster, 'webhostserver.biz', 'Server network that keeps probing the author (HN-0064). ');
		$this->rmatch($hoster, 'ctinets.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0065). ');
		$this->rmatch($hoster, 'server4you.de', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0066). ');
		$this->rmatch($hoster, 'propagation.net', 'No propogation allowed (spammers) (HN-0067). ');
		$this->rmatch($hoster, 'hostacy.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0068). ');
		$this->rmatch($hoster, 'onlinehome-server.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0069). ');
		$this->rmatch($hoster, 'onlinehome-server.net', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0070). ');
		$this->rmatch($hoster, 'onlinehome-server.info', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0071). ');
		$this->rmatch($hoster, 'bezeqint.net', 'ISP with a bad reputation, and heavy spam record (HN-0072). ');
		$this->rmatch($hoster, 'prking.com.au', 'Spammer (HN-0073). ');
		$this->rmatch($hoster, '.server.de', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0074). '); //69b adjusted
		$this->rmatch($hoster, 'nettopia.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0075). ');
		$this->rmatch($hoster, 'webfusion.co.uk', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0076). ');
		$this->rmatch($hoster, 'phatservers.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0077). ');
		$this->rmatch($hoster, 'insite.com.br', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0078). ');
		$this->rmatch($hoster, 'justhost.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0079). ');
		$this->rmatch($hoster, 'is74.ru', 'Proxy provider for hackers (HN-0080). ');
		$this->inmatch($hoster, '45ru.net.au', 'Abuse RFC ignorant network, caught abusing (HN-0081). ');
		//$this->rmatch($hoster,'alicedsl.de','Abuse ignorant ISP listed on abuse.rfc-ignorant.org (HN-0082). '); //71a Provisional Removal
		$this->rmatch($hoster, 'reliablehosting.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0083). '); //69c
		$this->rmatch($hoster, 'quadranet.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0084). '); //69c
		$this->rmatch($hoster, 'server.lu', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0085). '); //69c
		$this->rmatch($hoster, 'ertelecom.ru', 'Dangerous network (HN-0086). '); //69c description changed //75a
		$this->rmatch($hoster, '.airtelbroadband.in', 'Please use a different ISP, as Airtel India hides spammers via fast-flux DNS (HN-0087). '); //69c
		$this->rmatch($hoster, '.pldt.net', 'Malicious ISP, fostering spambots (HN-0088). '); //69c
		$this->rmatch($hoster, '.giga-dns.com', 'Scraper host, Not an access provider ISP (HN-0089). '); //70a
		$this->rmatch($hoster, '.rdsnet.ro', 'RDSNET is a constant source of spam and attacks (HN-0090). '); //70c
		$this->rmatch($hoster, '.myforexvps.com', 'Spamhost detection (HN-0091). '); //70c
		$this->rmatch($hoster, '.oroxy.com', 'oroxy.com is an annonmous proxy (HN-0092). '); //70c
		$this->rmatch($hoster, '.duo.carnet.hr', 'Spam host (HN-0093). '); //70c
		$this->rmatch($hoster, '.telecom.net.ar', 'Spam host (HN-0094). '); //70c
		$this->rmatch($hoster, '.pool-xxx.hcm.fpt', 'Spam host (HN-0095). '); //70c
		$this->rmatch($hoster, '.pointandchange.com', 'Scraper host (HN-0096). '); //70d
		$this->inmatch($hoster, 'swebot', 'Scraper host (HN-0097). '); //71c
		$this->inmatch($hoster, 'exabot', 'Scraper host (HN-0098). '); //71d
		$this->inmatch($hoster, 'youdaobot', 'Scraper host (HN-0099). '); //71d
		$this->inmatch($hoster, 'ceptro', 'Scraper host (HN-0100). '); //71d
		$this->inmatch($hoster, 'boardreader', 'Scraper host (HN-0101). '); //71e
		$this->inmatch($hoster, '2dayhost.com', 'Scraper host (HN-0102). '); //71f
		$this->rmatch($hoster, 'retail.telecomitalia.it', 'Spammer tolerant host network (HN-0103). ');
		$this->rmatch($hoster, 'fidelity.com', 'fidelity.c0m, Abusive forum scraping network (HN-0104). '); //69d
		$this->rmatch($hoster, 'technicolor.com', 'technicolor.c0m, Abusive forum scraping network (HN-0105). '); //69d
		$this->inmatch($hoster, 'rumer', 'Caught you! INSTA-BAN (HN-0106). '); //70c
		$this->inmatch($hoster, 'pymep', 'Caught you! INSTA-BAN (HN-0107). '); //70c
		//$this->rmatch($hoster,'tpnet.pl','tpnet.pl, this network turned clean, turned dirty again (HN-0108). '); //70c removed again 73d. Let's see if they play nice now?
		$this->rmatch($hoster, 'hostnoc.net', 'hostnoc.net, Abusive network, SQL hacks (HN-0109). '); //70d
		$this->inmatch($hoster, 'yandex', 'Yandex is banned. INSTA-BAN (HN-0110). '); //71b Yandex has many tlds.
		$this->rmatch($hoster, 'mail.ru', 'Mail.ru is banned. INSTA-BAN (HN-0111). '); //71c
		$this->inmatch($hoster, 'dedibox', 'Dubious French hoster (HN-0112). '); //71d (moved & strengthened)
		$this->inmatch($hoster, 'laycat', 'Dubious French search engine (HN-0113). '); //71d (moved & strengthened)
		$this->inmatch($hoster, 'kyklo', 'Dubious French search engine (HN-0114). '); //71d (moved & strengthened)
		$this->inmatch($hoster, 'aceleo', 'Dubious French search engine (HN-0115). '); //71d (moved & strengthened)
		$this->rmatch($hoster, 'xcelmg.com', 'Host caught trying to hack (HN-0116). '); //71g
		$this->rmatch($hoster, 'dimenoc.com', 'Host caught trying to hack (HN-0117). '); //71g
		$this->inmatch($hoster, 'slicehost', 'Slicehost/Splicehost AS12200 (HN-0118). '); //71g
		$this->rmatch($hoster, 'startdedicated.com', 'Scraper host (HN-0119). '); //71g
		$this->inmatch($hoster, 'triolan', 'triolan.net (HN-0120). '); //71h
		$this->rmatch($hoster, 'vpsnow.ru', 'vpsnow.ru hostile probes (HN-0121). '); //71
		$this->rmatch($hoster, 'ideastack.com', 'ideastack.com, hostile host (HN-0122). '); //72a
		$this->rmatch($hoster, 'esonicspider.com', 'esonicspider.com, hostile host (HN-0123). '); //72a
		$this->rmatch($hoster, 'megacom.biz', 'megacom.biz, hostile host (HN-0124). '); //72a
		$this->rmatch($hoster, 'telsp.net.br', 'telsp.net.br, Bothost and/or Server Farm (HN-0125). '); //72b moved from RBN
		$this->inmatch($hoster, 'kyivstar', 'kyivstar, Supposedly a cell provider, smells like a bothost and/or Server Farm (HN-0126). '); //72b
		$this->inmatch($hoster, 'hotspotsheild', 'esonicspider.com, hostile host (HN-0127). '); //72a
		$this->inmatch($hoster, 'anchorfree', 'esonicspider.com, hostile host (HN-0128). '); //72a
		$this->inmatch($hoster, 'dedipower', 'Depricated tweetmemebot (FAKE) host (HN-0128). '); //73b
		//$this->inmatch($hoster,'.seo','SEO hostname detection (HN-0129.0). '); //73b
		//$this->inmatch($hoster,'seo.','SEO hostname detection (HN-0129.1). '); //73b
		$this->inmatch($hoster, 'seeweb.it', 'Hostile LFI upload attacks from multiple sequential hosts (HN-0130). '); //73b
		$this->inmatch($hoster, 'buyurl.net', 'Strange crawler, probably SEO, Not only RFC-ignorant, but RFC-rejecting (HN-0131). '); //73c
		$this->inmatch($hoster, 'solomono.ru', 'solomono.ru, SEO bothost. Scrapes sites, then checks robots.txt for more URLS, and hits honeypots. (HN-0132). '); //73d
		$this->inmatch($hoster, 'your-server.de', 'your-server.de, Known host of attack scripts. Not an access provider (HN-0133). '); //73d
		$this->inmatch($hoster, 'lightspeedsystems.com', 'LightSpeed systems censor bot, scrapes sites proactively instead of on use (HN-0134). '); //73d
		$this->rmatch($hoster, '.host.ru', 'host.ru, low level access provider, mostly hosting & colo. Constant source of attacks (HN-0135). '); //73d
		$this->inmatch($hoster, 'brandaffinity', 'Possible brandname/client name SLAPP trolling scanner. Does not query, or obey robots.txt (HN-0136). '); //74a
		$this->inmatch($hoster, 'liwio.', 'HTTP_REFERER spamvertising fake medicines (HN-0137). '); //74a
		$this->inmatch($hoster, 'aramenet.com', 'Blind Probes (HN-0138). '); //74a
		$this->inmatch($hoster, 'phishmongers.com', 'Blind Probes (HN-0139). '); //74a
		$this->inmatch($hoster, '2kom.ru', 'Nasty Scraper (HN-0140). '); //74a
		$this->inmatch($hoster, 'awcheck', 'AwcheckBot, Ignores robots.txt (WOT), and is used for SEO keyword analyss (HN-0141). '); //74a
		$this->inmatch($hoster, 'captch', 'Captcha cracker detection (HN-0142). '); //74b
		$this->inmatch($hoster, 'dbcapi.me', 'Captcha cracker detection (HN-0143). '); //74b
		$this->inmatch($hoster, '.oodle.com', 'Keyword scraping bot that does not pull robots.txt (HN-0144). '); //74b
		$this->inmatch($hoster, 'smileweb.com.ua', 'Referer spambots (HN-0145). '); //74c
		$this->inmatch($hoster, 'psychz', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0146). '); //74c
		$this->inmatch($hoster, 'luxuryhandbag', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0147). '); //74c
		$this->inmatch($hoster, 'unassigned', 'Questionable hosthame, access denied (HN-0148). '); //74c
		$this->inmatch($hoster, 'no-data', 'Questionable hosthame, access denied (HN-0149). '); //74c
		$this->inmatch($hoster, 'hostenko.com', 'Host with an attack record, using anonymous whois (HN-0150). '); //74c
		$this->inmatch($hoster, 'moneymattersnow', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0151). '); //74c
		$this->inmatch($hoster, 'seznam.cz', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0152). '); //74c
		$this->inmatch($hoster, 'fulltextrobot', 'Cloaked Dubious spider UA (HN-0153). '); //74c
		$this->inmatch($hoster, 'meanpath', 'Use of meanpathbot not authorized by website admin (HN-0154). '); //74c
		$this->inmatch($hoster, 'no-dns-yet', 'Your Internet Service Provider has a severe problem in rDNS resolution. We are sorry, but until this is repaired, we cannot connect you to the party you are trying to reach. (HN-0155.0). '); //74c
		$this->inmatch($hoster, 'no-reverse-dns-yet', 'Your Internet Service Provider has a severe problem in rDNS resolution. We are sorry, but until this is repaired, we cannot connect you to the party you are trying to reach. (HN-0155.1). '); //74d
		$this->lmatch($hoster, 'hosted-by', 'Questionable hosthame, access denied (HN-0156). '); //74c
		$this->inmatch($hoster, 'sistrix', 'Scraper and/or Harvester (HN-0157). '); //74c
		$this->inmatch($hoster, 'leaseweb', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0158). '); //74c
		$this->lmatch($hoster, 'hosted-in', 'Questionable hosthame, access denied (HN-0159). '); //74c
		$this->inmatch($hoster, 'servepath.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0160). '); //74c
		$this->inmatch($hoster, 'buysellsales', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0161). '); //74c
		$this->inmatch($hoster, 'voxility.net', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0162). '); //74d
		$this->inmatch($hoster, 'vympelstroy.ru', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0163). '); //74d
		$this->inmatch($hoster, 'no-rdns-record', 'Questionable hosthame, access denied (HN-0163). '); //74d
		$this->inmatch($hoster, 'core.youtu.me', 'Questionable hosthame, access denied (HN-0164). '); //74d
		$this->inmatch($hoster, 'anahaqq', 'Questionable hosthame, access denied (HN-0165). '); //74d
		$this->inmatch($hoster, 'spletnahisa', 'Questionable hosthame, access denied (HN-0166). '); //74d
		$this->inmatch($hoster, 'qeas', 'Questionable hosthame, access denied (HN-0167). '); //74d
		$this->inmatch($hoster, 'work.from', 'Questionable hosthame, access denied (HN-0168). '); //74d
		$this->lmatch($hoster, 'test.', 'Questionable hosthame, access denied (HN-0169). '); //74d
		$this->inmatch($hoster, 'therewill.be', 'Questionable hosthame, access denied (HN-0170). '); //74d
		$this->inmatch($hoster, 'bibbly.com', 'Use of Bibbly not authorized by website admin (HN-0171). '); //74d
		$this->inmatch($hoster, 'cyber-uslugi', 'cyber-uslugi.pl and cyber-host.pl, unauthorised SEO service (HN-0172). '); //74d
		$this->inmatch($hoster, 'cheapseovps', 'cheapseovps.com, unauthorised SEO service (HN-0173). '); //74d
		$this->inmatch($hoster, 'dreamhost', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0174). '); //74d
		$this->inmatch($hoster, 'scopehosts', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0175). '); //74d
		$this->inmatch($hoster, 'cyber-host.pl', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0176). '); //74d
		$this->inmatch($hoster, 'yhost.name', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0177). '); //74d
		$this->inmatch($hoster, 'bergdorf-group', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0178). '); //74d
		$this->inmatch($hoster, 'slaskdatacenter.pl', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0179). '); //74d
		$this->inmatch($hoster, 'colocrossing.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0180). '); //74d
		$this->inmatch($hoster, 'instantdedicated.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0181). '); //74d
		$this->inmatch($hoster, 'serverbuddies.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0182). '); //74d
		$this->inmatch($hoster, 'reliablesite.net', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0183). '); //74d
		$this->inmatch($hoster, 'xeex', 'Dangerous host/network (HN-0184). '); //74d
		$this->inmatch($hoster, 'prohibitivestuff', 'Dangerous host/network (HN-0185). '); //74d
		$this->inmatch($hoster, 'drugstore', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0186). '); //74d
		$this->inmatch($hoster, 'productsnetworksx', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0187). '); //74d
		$this->inmatch($hoster, 'jackwellsmusic', 'Compromised server, source of spam (HN-0188). '); //74d
		$this->inmatch($hoster, 'tkvprok.ru', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0189). '); //74d
		$this->inmatch($hoster, 'khavarzamin.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0190). '); //74d
		$this->inmatch($hoster, 'mobilemarketingaid.info', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0191). '); //74d
		$this->inmatch($hoster, 'viral-customers.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0192). '); //74d
		$this->inmatch($hoster, 'moneytech.mg', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0193). '); //74d
		$this->inmatch($hoster, 'inkjetrefillink.com', 'Compromised server, source of spam (HN-0194). '); //74d
		$this->inmatch($hoster, '.as13448.', 'Websense Network. Odd behavior, possible hostile intent (HN-0195.0). '); //74d
		$this->inmatch($hoster, '.websense.', 'Websense Network. Odd behavior, possible hostile intent (HN-0195.1). '); //74d
		$this->inmatch($hoster, 'squider', 'Squider Network. Known spammers hitting spamtraps (WOT) (HN-0196). '); //74d
		$this->inmatch($hoster, 'fibersunucu.com.tr', 'Your ISP is banned. Known collaboration with xrumer (HN-0197). '); //74d
		$this->inmatch($hoster, 'zvelo.com', 'zvelo.com not authorized by site admin (HN-0198). '); //74d
		$this->inmatch($hoster, 'westdc.net', 'Westhost not authorized by site admin (HN-0199). '); //74d
		$this->inmatch($hoster, 'pulsepoint.com', 'Pulsepoint not authorized by site admin (HN-0200). '); //74d
		$this->inmatch($hoster, 'automobilelending4u.info', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0201). '); //74d
		$this->inmatch($hoster, 'loan-modification-fraud4u.info', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0202). '); //74d
		$this->inmatch($hoster, 'artisticgoals.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0203). '); //74d
		$this->inmatch($hoster, 'fast-cash4u.info', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0204). '); //74d
		$this->inmatch($hoster, 'profninja.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0205). '); //74d
		$this->inmatch($hoster, 'replyingst.net', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0206). '); //74d
		$this->rmatch($hoster, 'anonine.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0207). '); //74d
		$this->inmatch($hoster, 'newslettersrus.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0208). '); //74d
		$this->inmatch($hoster, 'cars-loans4u.info', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0209). '); //74d
		$this->inmatch($hoster, 'missiondish.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0210). '); //74d
		$this->rmatch($hoster, 'groupcross.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0211). '); //74d
		$this->lmatch($hoster, 'damage.', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0212). '); //74d
		$this->lmatch($hoster, 'moon.', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0213). '); //74d
		$this->rmatch($hoster, '4u.info', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0214). '); //74d
		$this->inmatch($hoster, 'securityspace.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0215). '); //74d
		$this->rmatch($hoster, 'xlhost.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0216). '); //74d
		$this->rmatch($hoster, 'netcomber.com', 'Reputaion/SEO manager service scrapes sites (HN-0217). '); //74d
		$this->inmatch($hoster, 'vpngate', 'VPNGate, Free VPN network that is anonymous, and untracable for bad behavior (HN-0218). '); //75a
		$this->inmatch($hoster, 'thefreevpn', 'The free VPN, Free VPN network that is anonymous, and untracable for bad behavior (HN-0219). '); //75a
		$this->inmatch($hoster, 'public-net', 'Network that is anonymous, and untracable for bad behavior (HN-0220). '); //75a
		$this->rmatch($hoster, 'dedicatedpanel.com', 'Bothost and/or Server Farm. Not an access provider ISP (HN-0221). '); //75a
		$this->rmatch($hoster, 'mojsite.com', 'quvHa\'ghach qem SoH veQ (HN-0222). '); //75a
		$this->rmatch($hoster, 'barefruit.com', 'Keyword Scraper (HN-0223). '); //75a
		$this->rmatch($hoster, 'balticservers.com', 'Attack host (HN-0224). '); //75b
		$this->inmatch($hoster, 'tag-trek', 'Tag-Trek not authorized by site admin (HN-0225). '); //75d
		$this->rmatch($hoster, 'vpn999.com', 'Possibly dangerous host (HN-0226). '); //76a
	}


	protected function testFromHost ()
	{

		// If coming from own site, skip these spam word detections. 56e
		$fromhost2 = '';
		if ( isset($_SERVER[ 'HTTP_REFERER' ]) )
		{
			$fromhost2 = $_SERVER[ 'HTTP_REFERER' ];
		}


		$fromhost = ($fromhost2 ? strtolower($fromhost2) : false);
		$thishost = strtolower($_SERVER[ 'HTTP_HOST' ]);

		if ( $fromhost )
		{
			$temp  = 'http://' . $thishost;
			$temp2 = 'https://' . $thishost;

			if ( !(substr_count($fromhost, $temp) || substr_count($fromhost, $temp2) || substr_count($fromhost, 'https://www.facebook.com') || substr_count($fromhost, 'http://www.facebook.com') || substr_count($fromhost, 'http://www.google.') || substr_count($fromhost, 'https://www.google.') || substr_count($fromhost, 'http://search.yahoo.com/search')) )
			{
				// Bad Referrers
				$this->inmatch($fromhost, 'filseclab', 'Firewall pollutes serverlogs with spam ads (BADREF-001). ');
				$this->inmatch($fromhost, 'web-ads', 'General HTTP_REFERER spam detection, please paste address in fresh window/tab and try again (BADREF-002). ');
				$this->inmatch($fromhost, 'investblog', "Investment scam, we don't link from there (BADREF-003). ");
				$this->inmatch($fromhost, 'aimtrust', "Investment scam, we don't link from there (BADREF-004). ");
				$this->inmatch($fromhost, 'justfree.com', 'Referer spam, just gone (BADREF-005). ');
				$this->inmatch($fromhost, 'cat-tree-house.com', 'Referer spam (BADREF-006). ');
				$this->inmatch($fromhost, 'cash-blog', 'Referer spam cash-blog, cashed-out (BADREF-007). ');
				$this->inmatch($fromhost, 'iforexvideo', 'Referer spam cash-blog, i-for-ex-you (BADREF-008). ');
				$this->inmatch($fromhost, 'bankinfodata.net', 'Referer spam cash-blog, i-for-ex-you (BADREF-009). ');
				$this->inmatch($fromhost, 'mskhirakurves', 'You came from a http_referer slandering pharmacy scam/porn/linksite (BADREF-010). ');
				$this->inmatch($fromhost, 'healingstartswithus', 'You came from a http_referer slandering pharmacy scam/porn/linksite (BADREF-011). ');
				$this->inmatch($fromhost, 'sobacos', 'You came from a http_referer slandering pharmacy scam/porn/linksite (BADREF-012). ');
				$this->inmatch($fromhost, 'icetv.ru', 'You came from a http_referer slandering pharmacy scam/porn/linksite (BADREF-013). ');
				$this->inmatch($fromhost, 'gayxzone', 'You came from a http_referer slandering pharmacy scam/porn/linksite (BADREF-014). ');
				$this->inmatch($fromhost, '.xzone', 'You came from a http_referer slandering pharmacy scam/porn/linksite (BADREF-015). ');
				$this->inmatch($fromhost, 'pyce.info', 'You came from a http_referer slandering pharmacy scam/porn/linksite (BADREF-016). ');
				$this->inmatch($fromhost, 'dvd5.com.ua', 'You came from a http_referer slandering pharmacy scam/porn/linksite (BADREF-017). ');
				$this->inmatch($fromhost, 'facialforum.net', 'You came from a http_referer slandering pharmacy scam/porn/linksite (BADREF-018). ');
				$this->inmatch($fromhost, 'doctoryuval.com', 'You came from a http_referer slandering pharmacy scam/porn/linksite (BADREF-019). ');
				$this->inmatch($fromhost, 'eyeglassesonlineshop', 'You came from a http_referer slandering pharmacy scam/porn/linksite (BADREF-020). ');
				$this->inmatch($fromhost, 'webscutest.com', 'You came from a http_referer slandering pharmacy scam/porn/linksite (BADREF-021). ');
				$this->inmatch($fromhost, 'powernetshop.at', 'You came from a http_referer slandering pharmacy scam/porn/linksite (BADREF-022). ');
				$this->inmatch($fromhost, 'adultfriendfinder', 'You came from a http_referer slandering pharmacy scam/porn/linksite (BADREF-023). ');
				$this->inmatch($fromhost, 'starlogic.biz', 'Referer spamming for customers is a bad idea - bogus hosting RBN (BADREF-024). ');
				$this->inmatch($fromhost, 'typegetrich', 'Type and get rich? Not if we can help it. Go away kid, ya bother me (BADREF-025). ');
				$this->inmatch($fromhost, '3w1.eu', 'Strange Polish referer spam. Not linked from there. Claims to be a pagerank site (BADREF-026). ');
				$this->inmatch($fromhost, 'refblock.com', "Bad referer. Anonymity is a bad thing in the security industry. Come in straight, or don't come at all (BADREF-027). ");
				$this->inmatch($fromhost, 'netvibes.com', 'You came from a http_referer slandering pharmacy scam/porn/linksite (BADREF-028). '); //65c
				$this->inmatch($fromhost, 'trafficfaker.com', 'Traffic faking, stat swelling, referer dropping, not welcome here (BADREF-029). '); //70b
				$this->inmatch($fromhost, 'evuln.com', 'Vulnerability Scanner (BADREF-030). '); //72a
				$this->inmatch($fromhost, 'autosurf', 'No automatic surfing please (BADREF-031). '); //73b
				$this->regexmatch($fromhost, ":80(?:\D|$)", 'Bad http_referer! (BADREF-032). '); //74c
				//  $this->inmatch($fromhost,'diesel.net.ru','Known refspam host found in HTTP_REFERER, please paste URL in a new browser window (BADREF-033). '); //74c
				//  $this->inmatch($fromhost,'rustelekom.net','Known refspam host found in HTTP_REFERER, please paste URL in a new browser window (BADREF-034). '); //74c
				$this->inmatch($fromhost, 'massagemiracle', 'Referer Spam detected. Have a happy ending (BADREF-034). '); //74d
				$this->inmatch($fromhost, 'myyogamassage', 'Referer Spam detected. Have a happy ending (BADREF-036). '); //74d
				$this->inmatch($fromhost, 'killmalware.com', 'Vulnerability Scanner (BADREF-037). '); //74d
				$this->inmatch($fromhost, 'deltasearch', 'HTTP_REFERER detection of MALWARE Delta Search. Your machine is heavily infected with MALWARE. Please go to http://malwaretips.com/blogs/delta-virus-removal/ and follow the removal instructions before you try to access this site, or use any password on your system again! This site has blocked you to protect your login. (BADREF-038.0). '); //74d
				$this->inmatch($fromhost, 'delta-search', 'HTTP_REFERER detection of MALWARE Delta Search. Your machine is heavily infected with MALWARE. Please go to http://malwaretips.com/blogs/delta-virus-removal/ and follow the removal instructions before you try to access this site, or use any password on your system again! This site has blocked you to protect your login. (BADREF-038.1). '); //74d
				$this->minmatch($fromhost, 'http://', 2, 'You came from way too many websites at once! (BADREF-039). '); //74d
				$this->inmatch($fromhost, 'netcomber.com', 'You came from a site which provides this site no benefit (BADREF-040). '); //74d


				// Scan words.

				$this->inmatch($fromhost, 'pharma.', 'You came from a http_referer slandering pharmacy scam/porn/linksite (REFSPAM-001). ');
				$this->inmatch($fromhost, 'drugstore', 'You came from a http_referer slandering pharmacy scam/porn/linksite (REFSPAM-002). ');
				$this->inmatch($fromhost, 'drugs.com/', 'You came from a http_referer slandering pharmacy scam/porn/linksite (REFSPAM-003). ');

				if ( !(($this->inmatch($fromhost, 'ssex', '')) || ($this->inmatch($fromhost, 'middlessex', ''))) )
				{
					$this->inmatch($fromhost, 'sex.', 'You came from a http_referer slandering pharmacy scam/porn/linksite (REFSPAM-004). ');
				}
				if ( !(($this->inmatch($fromhost, 'ssex', '')) || ($this->inmatch($fromhost, 'middlessex', ''))) )
				{
					$this->inmatch($fromhost, '.sex', 'You came from a http_referer slandering pharmacy scam/porn/linksite (REFSPAM-005). ');
				}

				$this->inmatch($fromhost, 'adult.', 'You came from a http_referer slandering pharmacy scam/porn/linksite (REFSPAM-006). ');
				$this->inmatch($fromhost, '.adult', 'You came from a http_referer slandering pharmacy scam/porn/linksite (REFSPAM-007). ');
				$this->inmatch($fromhost, 'drugs-', 'You came from a http_referer slandering pharmacy scam/porn/linksite (REFSPAM-008). ');
				$this->inmatch($fromhost, '.box.net', 'You came from a http_referer slandering pharmacy scam/porn/linksite (REFSPAM-009). '); //67b
				$this->inmatch($fromhost, 'meet-women', 'You came from a http_referer slandering pharmacy scam/porn/linksite (REFSPAM-010). ');
				$this->inmatch($fromhost, '-24h.', 'HTTP_REFERER pollution of serverlogs with spam ad word -24h., we do not link from there (REFSPAM-011). ');
				$this->inmatch($fromhost, 'pillz', 'HTTP_REFERER pollution of serverlogs with spam ad word pillz, we do not link from there (REFSPAM-012). ');
				$this->inmatch($fromhost, 'geriforte', 'HTTP_REFERER pollution of serverlogs with spam ad word geriforte, we do not link from there (REFSPAM-013). ');
				$this->inmatch($fromhost, 'derma', 'HTTP_REFERER pollution of serverlogs with spam ad word derma, we do not link from there (REFSPAM-014). ');
				$this->inmatch($fromhost, 'vitol', 'HTTP_REFERER pollution of serverlogs with spam ad word vitol, we do not link from there (REFSPAM-015). ');
				$this->inmatch($fromhost, 'laxative', 'HTTP_REFERER pollution of serverlogs with spam ad word laxative, we do not link from there (REFSPAM-016). ');
				$this->inmatch($fromhost, 'ginkgo', 'HTTP_REFERER pollution of serverlogs with spam ad word ginkgo, we do not link from there (REFSPAM-017). ');
				$this->inmatch($fromhost, 'ginko', 'HTTP_REFERER pollution of serverlogs with spam ad word ginko, we do not link from there (REFSPAM-018). ');
				$this->inmatch($fromhost, 'biloba', 'HTTP_REFERER pollution of serverlogs with spam ad word biloba, we do not link from there (REFSPAM-019). ');
				$this->inmatch($fromhost, 'melaleuca', 'HTTP_REFERER pollution of serverlogs with spam ad word melaleuca, we do not link from there (REFSPAM-020). ');
				$this->inmatch($fromhost, 'levitra', 'HTTP_REFERER pollution of serverlogs with spam ad word levitra, we do not link from there (REFSPAM-021). ');
				$this->inmatch($fromhost, 'nolvadex', 'HTTP_REFERER pollution of serverlogs with spam ad word nolvadex, we do not link from there (REFSPAM-022). ');
				$this->inmatch($fromhost, 'paxil', 'HTTP_REFERER pollution of serverlogs with spam ad word paxil, we do not link from there (REFSPAM-023). ');
				$this->inmatch($fromhost, 'plavix', 'HTTP_REFERER pollution of serverlogs with spam ad word paxil, we do not link from there (REFSPAM-024). ');
				$this->inmatch($fromhost, 'deltasone', 'HTTP_REFERER pollution of serverlogs with spam ad word deltasone, we do not link from there (REFSPAM-025). ');
				$this->inmatch($fromhost, 'sterapred', 'HTTP_REFERER pollution of serverlogs with spam ad word sterapred, we do not link from there (REFSPAM-026). ');
				$this->inmatch($fromhost, 'synthroid', 'HTTP_REFERER pollution of serverlogs with spam ad word synthroid, we do not link from there (REFSPAM-027). ');
				$this->inmatch($fromhost, 'lipitor', 'HTTP_REFERER pollution of serverlogs with spam ad word lipitor, we do not link from there (REFSPAM-028). ');
				$this->inmatch($fromhost, 'lexap', 'HTTP_REFERER pollution of serverlogs with spam ad word lexap, we do not link from there (REFSPAM-029). ');
				$this->inmatch($fromhost, 'cialis ', 'HTTP_REFERER pollution of serverlogs with spam ad word cialis, we do not link from there (REFSPAM-030). ');
				$this->inmatch($fromhost, 'viagra', 'HTTP_REFERER pollution of serverlogs with spam ad word viagra, we do not link from there (REFSPAM-031). ');
				$this->inmatch($fromhost, 'porn', 'HTTP_REFERER pollution of serverlogs with spam ad word porn, we do not link from there (REFSPAM-032). ');
				$this->inmatch($fromhost, 'pr0n', 'HTTP_REFERER pollution of serverlogs with spam ad word pr0n, we do not link from there (REFSPAM-033). ');
				$this->inmatch($fromhost, 'boob', 'HTTP_REFERER pollution of serverlogs with spam ad word boob, we do not link from there (REFSPAM-034). ');
				$this->inmatch($fromhost, 'tentacle', 'HTTP_REFERER pollution of serverlogs with spam ad word tentacle, we do not link from there (REFSPAM-035). ');
				$this->inmatch($fromhost, 'slimy', 'HTTP_REFERER pollution of serverlogs with spam ad word slimy, we do not link from there (REFSPAM-036). ');
				$this->inmatch($fromhost, 'hentai', 'HTTP_REFERER pollution of serverlogs with spam ad word hentai, we do not link from there (REFSPAM-037). ');
				$this->inmatch($fromhost, 'cigar', 'HTTP_REFERER pollution of serverlogs with spam ads ad word cigar, we do not link from there (REFSPAM-038). ');
				//  $this->inmatch($fromhost,'smoke','HTTP_REFERER pollution of serverlogs with spam ads ad word smoke, we do not link from there (REFSPAM-039). '); //Removed 71
				$this->inmatch($fromhost, 'menthol', 'HTTP_REFERER pollution of serverlogs with spam ads ad word smoke, we do not link from there (REFSPAM-040). ');
				$this->inmatch($fromhost, 'propecia', 'HTTP_REFERER pollution of serverlogs with spam ads ad word propecia, we do not link from there (REFSPAM-041). ');
				$this->inmatch($fromhost, 'rogaine', 'HTTP_REFERER pollution of serverlogs with spam ads ad word rogaine, we do not link from there (REFSPAM-042). ');
				$this->inmatch($fromhost, 'erotic', 'HTTP_REFERER pollution of serverlogs with spam ads ad word erotic, we do not link from there (REFSPAM-043). ');
				$this->inmatch($fromhost, 'erotik', 'HTTP_REFERER pollution of serverlogs with spam ads ad word erotik, we do not link from there (REFSPAM-044). ');
				$this->inmatch($fromhost, 'lesbian', 'HTTP_REFERER pollution of serverlogs with spam ads ad word lesbian, we do not link from there (REFSPAM-045). ');
				$this->inmatch($fromhost, 'bdsm', 'HTTP_REFERER pollution of serverlogs with spam ads ad word bdsm, we do not link from there (REFSPAM-046). ');
				$this->inmatch($fromhost, 'lolita', 'HTTP_REFERER pollution of serverlogs with spam ads ad word lolita, we do not link from there (REFSPAM-047). ');
				$this->inmatch($fromhost, 'nude', 'HTTP_REFERER pollution of serverlogs with spam ads ad word nude, we do not link from there (REFSPAM-048). ');
				$this->inmatch($fromhost, ' kiddy', 'HTTP_REFERER pollution of serverlogs with spam ads ad word kiddy, we do not link from there (REFSPAM-049). ');
				$this->inmatch($fromhost, 'bestiality', 'HTTP_REFERER pollution of serverlogs with spam ads ad word bestiality, we do not link from there (REFSPAM-050). ');
				$this->inmatch($fromhost, 'beastiality', 'HTTP_REFERER pollution of serverlogs with spam ads ad word beastiality, we do not link from there (REFSPAM-051). ');
				$this->inmatch($fromhost, 'shemale', 'HTTP_REFERER pollution of serverlogs with spam ads ad word shemale, we do not link from there (REFSPAM-052). ');
				$this->inmatch($fromhost, 'incest', 'HTTP_REFERER pollution of serverlogs with spam ads ad word incest, we do not link from there (REFSPAM-053). ');
				$this->inmatch($fromhost, 'neurontin', 'HTTP_REFERER pollution of serverlogs with spam ads ad word neurontin, we do not link from there (REFSPAM-054). ');
				$this->inmatch($fromhost, 'gabapentin', 'HTTP_REFERER pollution of serverlogs with spam ads ad word gabapentin, we do not link from there (REFSPAM-055). ');
				$this->inmatch($fromhost, 'avelox', 'HTTP_REFERER pollution of serverlogs with spam ads ad word avelox, we do not link from there (REFSPAM-056). '); //65c
				$this->inmatch($fromhost2, '/TOPSITES', 'HTTP_REFERER pollution of serverlogs with spam ads ad word topsites, we do not link from there (REFSPAM-057). ');
				$this->inmatch($fromhost, 'naked', 'HTTP_REFERER pollution of serverlogs with spam ads ad word naked, we do not link from there (REFSPAM-058). '); //70d
				$this->inmatch($fromhost, 'finddotcom', 'HTTP_REFERER pollution of serverlogs with spam ad for finddotcom.com (REFSPAM-059). ');
				$this->inmatch($fromhost, 'kamagra', 'HTTP_REFERER pollution of serverlogs with spam ad word kamagra, we do not link from there (REFSPAM-060). ');
				$this->inmatch($fromhost, 'liker.profile', 'liker.profile HTTP_REFERER spam-stuffer detection (REFSPAM-061). '); //74a
				$this->inmatch($fromhost, 'prosti', 'HTTP_REFERER pollution of serverlogs with spam ad word prosti, we do not link from there (REFSPAM-062). '); //74a
				$this->inmatch($fromhost, 'forex', 'HTTP_REFERER pollution of serverlogs with spam ad word forex, we do not link from there (REFSPAM-063). '); //74a
				$this->inmatch($fromhost, 'xanax', 'HTTP_REFERER pollution of serverlogs with spam ad word xanax, we do not link from there (REFSPAM-064). '); //74a

			}
		}

	}





}
