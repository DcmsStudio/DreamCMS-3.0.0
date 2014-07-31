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
 * @package      Fileman
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Fileman_Action_Index extends Fileman_Helper_Base
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$mode = $this->input('mode', 'string'); // if mode "media" use as mediamanager

		if ( !Session::get('browsePath', false) )
		{
			Session::save('browsePath', PAGE_PATH);
		}

		if ( $mode !== null && is_string($mode) )
		{
			Session::save('fmMode', $mode);
		}


		$this->configure(array ());

		if ( $this->input('init') )
		{
			$ts = $this->utime();

			$this->_result[ 'disabled' ] = array_values($this->options[ 'disabled' ]);
			$this->_result[ 'params' ] = array (
				'dotFiles'   => $this->options[ 'dotFiles' ],
				'uplMaxSize' => ini_get('upload_max_filesize'),
				'archives'   => array (),
				'extract'    => array (),
				'url'        => $this->options[ 'fileURL' ] ? $this->options[ 'fileURL' ] : ''
			);

			if ( isset($this->_commands[ 'archive' ]) || isset($this->_commands[ 'extract' ]) )
			{
				$this->_checkArchivers();

				if ( isset($this->_commands[ 'archive' ]) )
				{
					$this->_result[ 'params' ][ 'archives' ] = $this->options[ 'archiveMimes' ];
				}

				if ( isset($this->_commands[ 'extract' ]) )
				{
					$this->_result[ 'params' ][ 'extract' ] = array_keys($this->options[ 'archivers' ][ 'extract' ]);
				}
			}

			// clean thumbnails dir
			if ( $this->options[ 'tmbDir' ] )
			{
				srand((double)microtime() * 1000000);
				if ( rand(1, 200) <= $this->options[ 'tmbCleanProb' ] )
				{
					$ts2 = $this->utime();
					$ls  = scandir($this->options[ 'tmbDir' ]);
					for ( $i = 0, $s = count($ls); $i < $s; $i++ )
					{
						if ( '.' != $ls[ $i ] && '..' != $ls[ $i ] )
						{
							@unlink($this->options[ 'tmbDir' ] . DIRECTORY_SEPARATOR . $ls[ $i ]);
						}
					}
				}
			}

			if ( $this->input('selectfile') )
			{
				$_path = $this->input('selectfile');

				$out   = false;
				$path = realpath($_path);
				$path = str_replace($this->root, '', $path);

				if ( substr($path, 0, 1) == '/' )
				{
					$path = substr($path, 1);
				}

				if ( is_file($this->root .'/'. $path) && is_readable($this->root .'/'. $path))
				{
					$this->_result[ 'hash' ] = $this->_hash($this->root .'/'. $path);

					// change work directory
					$cwd = Session::get('cwd', '');

					$file = Library::getFilename($path);
					$path = str_replace('/'.$file, '', $path);

					if ( $cwd != $this->root .'/'. $path ) {

						$personal = new Personal;
						$personal->set('filemanager', 'path', array ('path' => $this->root .'/'. $path ));
						Session::save('cwd', $this->root .'/'. $path );
					}
				}
			}


			$this->_open(1);
			Ajax::Send(true, $this->_result);

			exit;



			header("Content-Type: " . ($cmd == 'upload' ? 'text/html' : 'application/json'));
			header("Connection: close");
			echo json_encode($this->_result);
			exit();
		}

		#  print_r($this->options);
		# exit;
		$data                 = array ();
		$data[ 'nopadding' ]  = true;
		$data[ 'scrollable' ] = false;
		$data[ 'fm' ]         = array (
			'mode'   => (is_string($mode) ? $mode : 'fm'),
			'dirSep' => $this->separator
		);


		if ( $mode === 'fileselector' )
		{
            $data[ 'use_tinymce' ] = false;
			$this->Template->process('fileman/inline-filemanager', $data, true);
			exit;
		}

		if ( strpos($mode, 'tinymce') !== false )
		{
			$data[ 'use_tinymce' ] = true;

			$this->Template->process('fileman/inline-filemanager', $data, true);
			exit;
		}



		Library::addNavi(trans('Dateimanager'));

		$this->Template->process('fileman/index', $data, true);
	}

}

?>