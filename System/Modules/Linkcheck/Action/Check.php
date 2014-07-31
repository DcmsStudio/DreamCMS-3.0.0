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
 * @package      Linkcheck
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Check.php
 */
class Linkcheck_Action_Check extends Controller_Abstract
{

	/**
	 * @var array
	 */
	protected $followUrls = array ();

	/**
	 * @var array
	 */
	protected $settings = array (
		'timeoutTime' => 5
	);

	/**
	 * @var null
	 */
	protected $extractedLinks = null;

	/**
	 * @var string
	 */
	protected $baseUrl = '';

	/**
	 * @var
	 */
	protected $tempScanedUrls;

	/**
	 * @var bool
	 */
	protected $scanDeepLinks = false;

	/**
	 * @var array
	 */
	protected $links = array ();

	/**
	 * @var int
	 */
	private $valid = 0;

	/**
	 * @var int
	 */
	private $invalid = 0;

	/**
	 * @var int
	 */
	private $sleep = 0;

	/**
	 * @var int
	 */
	private $stop = 0;

	/**
	 * @var string
	 */
	private $currenturl = '';

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		Library::disableErrorHandling();

		@ini_set('output_buffering', 0);
		@ini_set('zlib.output_compression', 0);
		@ini_set('implicit_flush', 1);
		@apache_setenv('no-gzip', 1);


		if ( ob_get_level() )
		{
			$bytes = ob_get_length();
			ob_flush();
		}
		ob_implicit_flush(true);
		@ob_end_flush();
		@set_time_limit(0);

		# ob_start();


		header('Content-Type: text/HTML; charset=utf-8');
		header('Content-Encoding: none; ');

		$this->checkLink();

		die('ok');
	}

	/**
	 * @return bool
	 */
	private function checkStop ()
	{

		$data = file_get_contents(CACHE_PATH . 'linkcheck.tmp');

		return ($data === '1' ? true : false);
	}

	public function sendStop ()
	{

		if ( file_exists(CACHE_PATH . 'linkcheck-db.tmp') )
		{
			@unlink(CACHE_PATH . 'linkcheck-db.tmp');
			@unlink(CACHE_PATH . 'linkcheck.tmp');
		}

		echo '<script type="text/javascript">top.finished()</script>';
		@ob_flush();
		@flush();
		exit;
	}

	private function checkLink ()
	{

		file_put_contents(CACHE_PATH . 'linkcheck.tmp', '0');
		$baseurl = HTTP::input('baseurl');

		if ( empty($baseurl) )
		{
			Error::raise(trans('Sie haben keine Start URL angegeben!'));
		}

		$this->checkExternal = (HTTP::input('external') && HTTP::input('external') == 1) ? true : false;

		// follow internal links?
		$followInternal      = (HTTP::input('followinternal') && HTTP::input('followinternal') == 1) ? true : false;
		$this->scanDeepLinks = $followInternal;

		$start = $baseurl;

		$file = Library::getFilename($baseurl);
		if ( $file != '' && $file != $baseurl )
		{
			$baseurl = str_replace($file, '', $baseurl);
		}
		$this->baseUrl = urldecode($baseurl);


		Session::save('LinkCheckURL', '');


		$this->load('LinkChecker');


		$this->tempScanedUrls = array ();
		$this->links          = array ();
		$this->getPagesUrls(array (
		                          $start
		                    ));

		register_shutdown_function(array (
		                                 $this,
		                                 'sendStop'
		                           ));


		if ( !is_array($this->links) || !count($this->links) )
		{
			echo '<html><head></head><body><script type="text/javascript">top.finished()</script></body></html>';
			@ob_flush();
			@flush();
			exit;
		}

		#$this->model->cleanData();


		echo '<html><head></head><body>';

		@ob_flush();
		@flush();

		$this->checkLinks();

		#   echo '<script type="text/javascript">top.finished()</script>';
		#    @ob_flush();
		#   @flush();

		file_put_contents(CACHE_PATH . 'linkcheck.tmp', '1');
		exit;
	}

	/**
	 * @param $baseurls
	 */
	private function getPagesUrls ( $baseurls )
	{

		$request = new Request();
		$request->__set('useragent', 'DreamCMS/' . VERSION . ' (Linkchecker)');
		$request->__set('timeout', 20);

		$this->links = array ();
		foreach ( $baseurls as $baseurl )
		{

			if ( $this->checkStop() )
			{
				$this->followUrls     = array ();
				$this->tempScanedUrls = null;
				$this->scanDeepLinks  = false;
				$this->sendStop();

				return;
			}

			$request->send($baseurl, null, 'GET');

			$error = $request->__get('error');
			if ( $error )
			{
				continue;
			}

			#die( $request->__get('response') );

			$links = $this->LinkChecker->getLinks($request->__get('response'), $this->checkExternal);

			if ( !is_array($links) || !count($links) )
			{
				continue;
			}

			$this->links = array_merge($this->links, $links);
		}


		# $this->links = array_unique($this->links);
	}

	/**
	 * @return bool
	 */
	private function checkLinks ()
	{

		Session::save('LinkCheckSleep', false);
		$this->sleep = false;
		#$this->model->writeData($this->valid, $this->invalid, $this->currenturl, $this->sleep, $this->stop);

		$tempUrls = array ();

		$len = strlen($this->baseUrl);

		if ( file_exists(CACHE_PATH . 'linkcheck-db.tmp') )
		{
			$this->tempScanedUrls = unserialize(file_get_contents(CACHE_PATH . 'linkcheck-db.tmp'));
		}

		# $this->links = array_unique($this->links);

		foreach ( $this->links as $idx => $r )
		{
			if ( $this->checkStop() )
			{
				$this->followUrls     = array ();
				$this->tempScanedUrls = null;
				$this->scanDeepLinks  = false;
				$this->sendStop();

				return;
			}

			if ( !isset($r[ 'attributes' ][ 'href' ]) )
			{
				continue;
			}

			$link  = $r[ 'attributes' ][ 'href' ];
			$_link = $this->LinkChecker->fixUrl($link);
			if ( substr($_link, 0, $len) != $this->baseUrl )
			{
				continue;
			}

			if ( isset($this->tempScanedUrls[ $link ]) )
			{
				#     echo('<script type="text/javascript">alert("'.substr ($_link, 0, $len).' -> '.$this->baseUrl.'");</script>');
				#  ob_flush();
				# flush();
				continue;
			}
			#die("$_link $this->baseUrl ". (stripos($_link, $this->baseUrl) === false ? '0' : '1'));

			$this->tempScanedUrls[ $link ] = true;

			/*
			  if (!$this->LinkChecker->isValidUrl($link, $this->checkExternal))
			  {
			  continue;
			  } */


			$this->currenturl = $link;


			Session::save('LinkCheckURL', $link);
			// Session::write();
			#$this->model->writeData($this->valid, $this->invalid, $this->currenturl, $this->sleep, $this->stop);
			#    echo '<script type="text/javascript">console.log("check ' . $link .' '. session_id() . '")</script>';
			#  @ob_flush();
			#    @flush();


			$linkstate = $this->LinkChecker->checkSingeLink($link, $this->checkExternal);


			// add
			if ( $this->scanDeepLinks && $linkstate[ 'isok' ] == true )
			{
				$this->followUrls[ ] = $link;
			}

			if ( $linkstate[ 'isok' ] == true )
			{
				$this->valid++;
			}
			else
			{
				$this->invalid++;
			}

			$this->model->writeData($this->valid, $this->invalid, $this->currenturl, $this->sleep, $this->stop);


			echo '<script type="text/javascript">top.setCheckedLink("' . $link . '","' . ($linkstate[ 'isok' ] == true ?
					"true" : $linkstate[ 'errormessage' ]) . '")</script>';
			@ob_flush();
			@flush();

			usleep(10000);
		}

		$this->followUrls = array_unique($this->followUrls);

		file_put_contents(CACHE_PATH . 'linkcheck-db.tmp', serialize($this->tempScanedUrls));

		$this->tempScanedUrls = array ();
		$this->links          = array (); // reset

		if ( count($this->followUrls) && $this->scanDeepLinks )
		{
			// better performance
			if ( $this->checkStop() )
			{
				$this->followUrls     = array ();
				$this->tempScanedUrls = null;
				$this->scanDeepLinks  = false;
				$this->sendStop();

				return;
			}

			Session::save('LinkCheckSleep', true);
			$this->sleep = true;
			# $this->model->writeData($this->valid, $this->invalid, $this->currenturl, $this->sleep, $this->stop);
			#
			echo '<script type="text/javascript">top.setCheckedLink("", "2")</script>';
			@ob_flush();
			@flush();

			usleep(10000);

			$this->links = array (); // reset

			$this->getPagesUrls($this->followUrls);
			if ( !$this->checkStop() && count($this->links) )
			{
				$this->checkLinks();
			}
		}

		return true;
	}

}

?>