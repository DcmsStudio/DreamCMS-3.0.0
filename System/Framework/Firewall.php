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
 * @file         Firewall.php
 */
class Firewall extends Firewall_Abstract
{

	/**
	 * @var bool
	 */
	protected $_isInited = false;

	/**
	 * @var array
	 */
	protected $_configKeys = array ();

	/**
	 * @var string
	 */
	protected $_xss_hash = '';

	/**
	 * @var null
	 */
	protected $_badIps = null;
	protected $isAdmin = false;

	/**
	 *
	 */
	public function __construct ()
	{

		// extract boolean config options
		foreach ( $this->config as $key => $value )
		{
			if ( is_bool($value) )
			{
				$this->_configKeys[ $key ] = $key;
			}
		}
	}

	public function __destruct ()
	{

		$this->_configKeys   = null;
		$this->config        = null;
		$this->range_ip_deny = null;
		$this->range_ip_spam = null;
		$this->xxs_rules     = null;
		$this->sql_rules     = null;
		$this->bot_rules     = null;
		$this->url_rules     = null;
		$this->cookie_rules  = null;
	}

	/**
	 *
	 * @param array $config
	 * @throws Firewall_Exception
	 * @return Firewall
	 */
	public function setConfig ( array $config )
	{

		foreach ( $config as $key => $value )
		{
			if ( !isset($this->_configKeys[ $key ]) )
			{
				throw new Firewall_Exception('Firewall Configuration option "' . $key . '" not found.');
			}

			$this->config[ $key ] = (bool)$value;
		}

		return $this;
	}

	/**
	 *
	 * @return Firewall
	 */
	public function initFirewall ()
	{

		$this->config[ 'logfile' ] = DATA_PATH . 'logs/firewall-log.txt';

		if ( $this->config[ 'unsetglobals' ] )
		{
			$this->unsetGlobals();
		}

		if ( defined('ADM_SCRIPT') && ADM_SCRIPT )
		{
			$this->isAdmin = true;
			$this->config[ 'protection_xss_attack' ] = false;
		}

		/**
		 *
		 */
		define('FIREWALL_GET_QUERY_STRING', $this->get_query_string());
		/**
		 *
		 */
		define('FIREWALL_USER_AGENT', remove_invisible_characters($this->get_user_agent()) );


		/**
		 *
		 */
		define('FIREWALL_GET_IP', $this->get_ip());
		/**
		 *
		 */
		define('FIREWALL_GET_HOST', $this->gethostbyaddr());
		/**
		 *
		 */
		define('FIREWALL_GET_REFERER', $this->get_referer());
		/**
		 *
		 */
		define('FIREWALL_GET_REQUEST_METHOD', $this->get_request_method());
		/**
		 *
		 */
		define('FIREWALL_REGEX_UNION', '#\w?\s?union\s\w*?\s?(select|all|distinct|insert|update|drop|delete)#is');
		/**
		 *
		 */
		define('FIREWALL_REQUEST_URI', strip_tags(rawurldecode($this->get_env('REQUEST_URI'))));

		$this->layout = str_replace('{REQUEST_URI}', FIREWALL_REQUEST_URI, $this->layout);


		if ( FIREWALL_GET_IP && $this->config[ 'protection_bad_ips' ] === true )
		{
			$db = Database::getInstance();
            $long = ip2long(FIREWALL_GET_IP);
			$r  = $db->query('SELECT * FROM %tp%spammers WHERE spammer_iplong = ? LIMIT 1', $long)->fetch();

			if ( $r[ 'spammer_id' ] )
			{
				$db->query('UPDATE %tp%spammers SET spammer_count = spammer_count + 1, lastvisit = ? WHERE spammer_id = ?', time(), $r[ 'spammer_id' ]);

				$this->log('Spammer IP');
				die('Protection SPAM IPs active, this IP range is not allowed !');
			}
		}

		if ( !trim(FIREWALL_USER_AGENT) )
		{
			$this->log('Dos attack');
			die('Invalid user agent ! Stop it ...');
		}

		if ( FIREWALL_GET_IP != FIREWALL_GET_HOST ) {
			$this->testHoster(FIREWALL_GET_HOST);
		}

		$this->testFromHost();

		return $this;
	}

	/**
	 * Run the Firewall
	 * stops the application automaticly
	 *
	 *
	 * @return void
	 */
	public function run ()
	{

		// Search Engines do not need to see the firewall
		$agent_mode     = 0;
		$get_user_agent = FIREWALL_USER_AGENT;
		foreach ( $this->SearchEngineUserAgent as $Agent )
		{
			if ( strstr($get_user_agent, $Agent) && $this->check_cookie() === false )
			{
				$agent_mode++;
			}
		}

		/**
		 * return if is a search engine
		 */
		if ( $agent_mode !== 0 )
		{
			return;
		}


		/** protection bots */
		if ( $this->config[ 'protection_bots' ] === true && FIREWALL_USER_AGENT )
		{
			$check = str_replace($this->bot_rules, '*', strtolower(FIREWALL_USER_AGENT));
			if ( strtolower(FIREWALL_USER_AGENT) !== $check || (empty($check) && FIREWALL_USER_AGENT ) )
			{
				$this->log('Bots attack');
				die('Bot attack detected ! stop it ...');
			}
		}

		if ( $this->config[ 'protection_server_ovh' ] === true && FIREWALL_GET_HOST !== FIREWALL_GET_IP )
		{
			if ( stristr(FIREWALL_GET_HOST, 'ovh') )
			{
				$this->log('OVH Server list');
				die('Protection OVH Server active, this IP range is not allowed !');
			}
		}

		if ( $this->config[ 'protection_server_ovh_by_ip' ] === true )
		{
			$ip = explode('.', FIREWALL_GET_IP);
			if ( $ip[ 0 ] . '.' . $ip[ 1 ] === '87.98' or $ip[ 0 ] . '.' . $ip[ 1 ] === '91.121' or $ip[ 0 ] . '.' . $ip[ 1 ] === '94.23' or $ip[ 0 ] . '.' . $ip[ 1 ] === '213.186' or $ip[ 0 ] . '.' . $ip[ 1 ] === '213.251' )
			{
				$this->log('OVH Server IP');
				die('Protection OVH Server active, this IP range is not allowed !');
			}
		}

		if ( $this->config[ 'protection_server_kimsufi' ] === true && FIREWALL_GET_HOST !== FIREWALL_GET_IP )
		{
			if ( stristr(FIREWALL_GET_HOST, 'kimsufi') )
			{
				$this->log('KIMSUFI Server list');
				die('Protection KIMSUFI Server active, this IP range is not allowed !');
			}
		}

		if ( $this->config[ 'protection_server_dedibox' ] === true && FIREWALL_GET_HOST !== FIREWALL_GET_IP )
		{
			if ( stristr(FIREWALL_GET_HOST, 'dedibox') )
			{
				$this->log('DEDIBOX Server list');
				die('Protection DEDIBOX Server active, this IP range is not allowed !');
			}
		}

		if ( $this->config[ 'protection_server_dedibox_by_ip' ] === true )
		{
			$ip = explode('.', FIREWALL_GET_IP);
			if ( $ip[ 0 ] . '.' . $ip[ 1 ] == '88.191' )
			{
				$this->log('DEDIBOX Server IP');
				die('Protection DEDIBOX Server active, this IP is not allowed !');
			}
		}

		if ( $this->config[ 'protection_server_digicube' ] === true && FIREWALL_GET_HOST !== FIREWALL_GET_IP )
		{
			if ( stristr(FIREWALL_GET_HOST, 'digicube') )
			{
				$this->log('DIGICUBE Server list');
				die('Protection DIGICUBE Server active, this IP range is not allowed !');
			}
		}

		if ( $this->config[ 'protection_server_digicube_by_ip' ] === true )
		{
			$ip = explode('.', FIREWALL_GET_IP);
			if ( $ip[ 0 ] . '.' . $ip[ 1 ] == '95.130' )
			{
				$this->log('DIGICUBE Server IP');
				die('Protection DIGICUBE Server active, this IP is not allowed !');
			}
		}


		if ( $this->config[ 'protection_range_ip_deny' ] === true )
		{
			$range_ip = explode('.', FIREWALL_GET_IP);

			if ( in_array($range_ip[ 0 ], $this->range_ip_deny) )
			{
				$this->log('IPs reserved list');
				die('Protection died IPs active, this IP range is not allowed !');
			}
		}


		/**
		 * protect Cookies
		 */
		if ( $this->config[ 'protection_cookies' ] === true )
		{
			if ( $this->config[ 'protection_cookies' ] === true )
			{
				foreach ( $_COOKIE as $value )
				{
					$check = str_replace($this->cookie_rules, '*', $value);
					if ( $value != $check )
					{
						$this->log('Cookie protect');
						unset($value);
					}
				}
			}
			if ( $this->config[ 'protection_post' ] === true )
			{
				foreach ( $_POST as $value )
				{
					$check = str_replace($this->cookie_rules, '*', $value);
					if ( $value != $check )
					{
						$this->log('POST protect');
						unset($value);
					}
				}
			}

			if ( $this->config[ 'protection_get' ] === true )
			{
				foreach ( $_GET as $value )
				{
					$check = str_replace($this->cookie_rules, '*', $value);
					if ( $value != $check )
					{
						$this->log('GET protect');
						unset($value);
					}
				}
			}
		}


		/** protection de l'url */
		if ( $this->config[ 'protection_url' ] === true && FIREWALL_GET_QUERY_STRING !== '' )
		{
			$check = str_replace($this->url_rules, '', FIREWALL_GET_QUERY_STRING);
			$check = str_replace('<' . '?' . 'php', '', FIREWALL_GET_QUERY_STRING);
			$check = str_replace('<' . '?', '', FIREWALL_GET_QUERY_STRING);

			if ( FIREWALL_GET_QUERY_STRING != '' && FIREWALL_GET_QUERY_STRING != $check )
			{
				$this->log('URL protect');
				die('Protection url active, string not allowed !');
			}
		}


		/** Posting from other servers in not allowed */
		if ( $this->config[ 'protection_request_server' ] === true )
		{
			if ( FIREWALL_GET_REQUEST_METHOD == 'POST' )
			{
				if ( isset($_SERVER[ 'HTTP_REFERER' ]) )
				{
					if ( !stripos($_SERVER[ 'HTTP_REFERER' ], $_SERVER[ 'HTTP_HOST' ], 0) )
					{
						$this->log('Posting from another server');
						die('Posting from another server not allowed !');
					}
				}
			}
		}

		/** protection contre le vers santy */
		if ( $this->config[ 'protection_santy' ] === true )
		{
			$check = str_replace($this->santy_rules, '*', strtolower(FIREWALL_REQUEST_URI));
			if ( strtolower(FIREWALL_REQUEST_URI) != $check )
			{
				$this->log('Santy');
				die('Attack Santy detected ! Stop it ...');
			}
		}


		/** Invalid request method check */
		if ( $this->config[ 'protection_request_method' ] === true )
		{
			if ( strtolower(FIREWALL_GET_REQUEST_METHOD) !== 'get' && strtolower(FIREWALL_GET_REQUEST_METHOD) !== 'head' && strtolower(FIREWALL_GET_REQUEST_METHOD) !== 'post' && strtolower(FIREWALL_GET_REQUEST_METHOD) !== 'put' )
			{
				$this->log('Invalid request');
				die('Invalid request method check ! Stop it ...');
			}
		}

		/** protection dos attaque */
		if ( $this->config[ 'protection_dos' ] === true )
		{
			if ( !defined('FIREWALL_USER_AGENT') || FIREWALL_USER_AGENT === '-' || FIREWALL_USER_AGENT === '' )
			{
				$this->log('Dos attack');
				die('Invalid user agent ! Stop it ...');
			}
		}


		/** protection union sql attaque */
		if ( $this->config[ 'protection_union_sql' ] === true )
		{
			$stop  = false;
			$check = str_replace($this->sql_rules, '*', FIREWALL_GET_QUERY_STRING);

			if ( FIREWALL_GET_QUERY_STRING != $check )
			{
				$stop = true;
			}

			if ( !$stop && preg_match(FIREWALL_REGEX_UNION, FIREWALL_GET_QUERY_STRING) )
			{
				$stop = true;
			}

			if ( !$stop && preg_match('/([OdWo5NIbpuU4V2iJT0n]{5}) /', rawurldecode(FIREWALL_GET_QUERY_STRING)) )
			{
				$stop = true;
			}

			if ( !$stop && strstr(rawurldecode(FIREWALL_GET_QUERY_STRING), '*') )
			{
				$stop = true;
			}

			if ( $stop )
			{
				$this->log('Union attack');
				die('Union attack detected ! stop it ......');
			}
		}


		/** protection click attack */
		if ( $this->config[ 'protection_click_attack' ] === true )
		{
			if ( FIREWALL_GET_QUERY_STRING != str_replace($this->click_rules, '*', FIREWALL_GET_QUERY_STRING) )
			{
				$this->log('Click attack');
				die('Click attack detected ! stop it .....');
			}
		}


		/** protection XSS attack */
		if ( $this->config[ 'protection_xss_attack' ] === true && !$this->isAdmin  )
		{

			$request  = rawurldecode(FIREWALL_GET_QUERY_STRING);

			// Remove Invisible Characters and validate entities in URLs
			$request2 = remove_invisible_characters($request);

			// force xxs clean only on get method
			if ( $request != HTTP::getClean($request2, ($this->isAdmin ? false : true)) )
			{
				$this->log('XSS attack');
				die('XSS attack detected ! stop it 1...');
			}

			$request  = $this->get_query_string();

			foreach ( $this->xxs_rules as $rule )
			{
				$check = preg_replace('#' . preg_quote($rule, '#') . '#i', '*', $request);

				if ( FIREWALL_GET_QUERY_STRING !== $check )
				{
					$this->log('XSS attack');
					die('XSS attack detected ! stop it 2... ');
				}
			}
		}


		return true;
	}

	/**
	 * XSS Hash
	 *
	 * Generates the XSS hash if needed and returns it.
	 *
	 * @return string XSS hash
	 */
	public function xss_hash ()
	{

		if ( $this->_xss_hash === '' )
		{
			$this->_xss_hash = md5(uniqid(mt_rand()));
		}

		return $this->_xss_hash;
	}

	/**
	 * Validate URL entities
	 *
	 * @param string $str
	 * @return string
	 */
	protected function _validate_entities ( $str )
	{

		/*
		 * Protect GET variables in URLs
		 */

		// 901119URL5918AMP18930PROTECT8198
		$str = preg_replace('|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-]+)|i', $this->xss_hash() . '\\1=\\2', $str);

		/*
		 * Validate standard character entities
		 *
		 * Add a semicolon if missing. We do this to enable
		 * the conversion of entities to ASCII later.
		 */
		$str = preg_replace('#(&\#?[0-9a-z]{2,})([\x00-\x20])*;?#i', '\\1;\\2', $str);

		/*
		 * Validate UTF16 two byte encoding (x00)
		 *
		 * Just as above, adds a semicolon if missing.
		 */
		$str = preg_replace('#(&\#x?)([0-9A-F]+);?#i', '\\1\\2;', $str);

		/*
		 * Un-Protect GET variables in URLs
		 */

		return str_replace($this->xss_hash(), '&', $str);
	}

}
