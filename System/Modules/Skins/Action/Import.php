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
 * @package      Skins
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Import.php
 */
class Skins_Action_Import extends Controller_Abstract
{

	private static $allowedMimes = array (
		'xml' => array (
			'text/xml',
			'application/xml'
		),
		'tar' => 'application/x-tar',
		'zip' => 'application/zip'
	);

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		if ( $this->input('mode') == 'upload' )
		{
			$this->upload();
		}

		if ( $this->input('send') )
		{
			$this->executeImport();
		}


		$data                  = array ();
		$data[ 'defaultpath' ] = str_replace(ROOT_PATH, '', PAGE_PATH . 'upload/');
		Library::addNavi(trans('Frontend Skins Übersicht'));
		Library::addNavi(trans('Skin Importieren'));

		// $this->Template->addScript(BACKEND_JS_URL . 'upload');
		$this->Template->process('skins/import', $data, true);
		exit;
	}


	/**
	 * @param string $id
	 * @return string
	 */
	private function get_server_var ( $id )
	{

		return isset( $_SERVER[ $id ] ) ? $_SERVER[ $id ] : '';
	}

	/**
	 * Check new file name for invalid simbols. Return name if valid
	 *
	 * @param $n
	 * @return string $n file name
	 * @return string
	 */
	private function checkName ( $n )
	{

		$n = strip_tags(trim($n));
		if ( '.' === substr($n, 0, 1) )
		{
			return false;
		}

		return preg_match('|^[^\\/\<\>:]+$|', $n) ? $n : false;
	}

	/**
	 *
	 * @param string $tmpName
	 * @return bool
	 */
	private function isUploadAllow ( $tmpName )
	{

		$mime = Library::getMimeType($tmpName);

		foreach ( self::$allowedMimes as $ext => $data )
		{
			if ( is_array($data) )
			{
				if ( in_array($mime, $data) )
				{
					return true;
				}
			}
			else if ( $mime == $data )
			{
				return true;
			}
		}

		return false;
	}

	private function upload ()
	{

		$upload = isset( $_FILES[ 'Filedata' ] ) ? $_FILES[ 'Filedata' ] : null;
		// Parse the Content-Disposition header, if available:
		$file_name = $this->get_server_var('HTTP_CONTENT_DISPOSITION') ? rawurldecode(preg_replace('/(^[^"]+")|("$)/', '', $this->get_server_var('HTTP_CONTENT_DISPOSITION'))) : null;

		// Parse the Content-Range header, which has the following form:
		// Content-Range: bytes 0-524287/2000000
		$content_range = $this->get_server_var('HTTP_CONTENT_RANGE') ? preg_split('/[^0-9]+/', $this->get_server_var('HTTP_CONTENT_RANGE')) : null;
		$size          = $content_range ? $content_range[ 3 ] : null;


		$name     = $file_name ? $file_name : ( isset( $upload[ 'name' ] ) ? $upload[ 'name' ] : null );
		$temp     = isset( $upload[ 'tmp_name' ] ) ? $upload[ 'tmp_name' ] : null;
		$size     = $size ? $size : ( isset( $upload[ 'size' ] ) ? $upload[ 'size' ] : $this->get_server_var('CONTENT_LENGTH') );
		$filetype = isset( $upload[ 'type' ] ) ? $upload[ 'type' ] : $this->get_server_var('CONTENT_TYPE');

		if ( $name === null || $temp === null )
		{
			$status[ 'error' ] = 'Invalid name';
		}

		if ( !isset( $status[ 'error' ] ) )
		{
			$uploadPath = UPLOAD_PATH . 'skins/';
			$fileupload = new Upload( $upload, true, $uploadPath );
			if ( $fileupload->success() )
			{
				$basepath = $fileupload->getPath();
				$mime     = Library::getMimeType($basepath);

				if ( ( $name = $this->checkName(( $name ? $name : $temp )) ) === false )
				{
					$status[ 'error' ] = 'Invalid name ' . ( ( $name ? $name : $temp ) );
					unlink($basepath);
				}
				elseif ( $this->isUploadAllow($basepath) !== true )
				{
					$status[ 'error' ] = 'Not allowed file type ' . ( $mime );
					unlink($basepath);
				}
				else
				{
					$status[ "filepath" ] = $basepath;
					$status[ "filename" ] = $name;
					$status[ "filesize" ] = Library::formatSize((int)filesize($basepath));
					$status[ "status" ]   = trans('Speichern erfolgreich');
					$status[ "size" ]     = Library::formatSize((int)$size);
					$status[ 'path' ]     = str_replace(ROOT_PATH, '', $basepath);
				}
			}
			else
			{
				$status[ 'error' ] = $fileupload->getError();
			}
		}


		if ( isset( $status[ 'error' ] ) )
		{
			$status[ 'success' ] = false;
		}
		else
		{
			$status[ 'success' ] = true;
		}

		echo Library::json($status);
		exit;
	}


	private function executeImport ()
	{

		$_uploadpath        = $this->input('uploadpath');
		$skinimagedir       = $this->input('skinimagedir');
		$templatefoldername = $this->input('templatefoldername');
		$importtitle        = $this->input('importtitle');

		$skinimagedirClean = preg_replace('#([^a-z0-9_\-]*)#', '', $skinimagedir);
		if ( $skinimagedir && $skinimagedir != $skinimagedirClean )
		{
			Library::sendJson(false, trans('Nicht erlaubte Bezeichnung des Skin Image Ordners'));
		}

		$templatefoldernameClean = preg_replace('#([^a-z0-9_\-]*)#', '', $templatefoldername);
		if ( $templatefoldername && $templatefoldername != $templatefoldernameClean )
		{
			Library::sendJson(false, trans('Nicht erlaubte Bezeichnung des Template Ordners'));
		}

		if ( $_uploadpath )
		{
			$path = str_replace(array ( '../', '../../', './' ), '', $_uploadpath);

			if ( $_uploadpath !== $path )
			{
				Library::sendJson(false, trans('Pfad fehler'));
			}

			if ( !file_exists(ROOT_PATH . $path) )
			{
				Library::sendJson(false, trans('Skin Datei existiert nicht'));
			}

			$usepath = ROOT_PATH . $path;
		}
		else
		{
			$_path = $this->input('filepath');
			$path  = str_replace(array ( '../', '../../', './' ), '', $_path);

			if ( $_path !== $path )
			{
				Library::sendJson(false, trans('Pfad fehler'));
			}

			if ( substr($path, 0, 1) == '/' )
			{
				$path = substr($path, 1);
			}

			$usepath = PAGE_PATH . 'upload/' . $path;
		}

		$ext = Library::getExtension($usepath);
		if ( $ext == 'xml' )
		{

			set_error_handler(array ( 'Xml', 'HandleXmlError' ));

			$dom = new DOMDocument( '1.0' );
			$dom->load($usepath);

			restore_error_handler();

			$xml = new Xml( null );
			$arr = array_shift($xml->createArray($dom)); // shift root

			if ( isset( $arr[ 'skin' ] ) )
			{
				if ( !isset( $arr[ 'templates' ] ) || !count($arr[ 'templates' ]) )
				{
					Library::sendJson(false, trans('Die Xml Datei enthält keine Templates zum Importieren!'));
				}

				$skinData = $arr[ 'skin' ];
				unset( $skinData[ 'id' ], $skinData[ 'pageid' ] );

				if ( !$skinimagedir )
				{
					$x        = 0;
					$baseName = $skinData[ 'img_dir' ] . '-import';
					while ( is_dir(ROOT_PATH . SKIN_IMG_URL_PATH . $skinData[ 'img_dir' ]) )
					{
						$skinData[ 'img_dir' ] = $baseName . $x;
						++$x;
					}

					$skinimagedir = $skinData[ 'img_dir' ];
				}

				if ( !$templatefoldername )
				{
					$x        = 0;
					$baseName = $skinData[ 'templates' ] . '-import';
					while ( is_dir(TEMPLATES_PATH . $skinData[ 'templates' ]) )
					{
						$skinData[ 'templates' ] = $baseName . $x;
						++$x;
					}

					$templatefoldername = $skinData[ 'templates' ];
				}

				$skinData[ 'pageid' ]      = PAGEID;
				$skinData[ 'default_set' ] = 0;
				$skinData[ 'img_dir' ]     = $skinimagedir;
				$skinData[ 'templates' ]   = $templatefoldername;
				$skinData[ 'iscore' ]      = 0;


				if (!$importtitle)
				{
					$skinData[ 'title' ]   = $skinData[ 'title' ] . ' - Import '. date('d.m.Y, H:i:s');
				}
				else
				{
					$skinData[ 'title' ]   = $importtitle;
				}



				#		print_r($arr[ 'templates' ]['template']);
				#		exit;


				$newid = $this->model->processSkinImport($skinData);


				if ( isset( $arr[ 'templates' ][ 'template' ] ) && is_array($arr[ 'templates' ][ 'template' ]) )
				{

					Library::makeDirectory(TEMPLATES_PATH . $templatefoldername);
					@chmod(TEMPLATES_PATH . $templatefoldername, 0777);

					foreach ( $arr[ 'templates' ][ 'template' ] as $r )
					{
						$this->model->processTemplateImport($newid, $r);

						if ( $r[ 'group_name' ] )
						{
							Library::makeDirectory(TEMPLATES_PATH . $templatefoldername . '/' . $r[ 'group_name' ]);
							@chmod(TEMPLATES_PATH . $templatefoldername . '/' . $r[ 'group_name' ], 0777);

							file_put_contents(TEMPLATES_PATH . $templatefoldername . '/' . $r[ 'group_name' ] . '/' . $r[ 'templatename' ] . '.html', $r[ 'content' ]);

							@chmod(TEMPLATES_PATH . $templatefoldername . '/' . $r[ 'group_name' ] . '/' . $r[ 'templatename' ] . '.html', 0777);
						}
						else
						{
							file_put_contents(TEMPLATES_PATH . $templatefoldername . '/' . $r[ 'templatename' ] . '.html', $r[ 'content' ]);

							@chmod(TEMPLATES_PATH . $templatefoldername . '/' . $r[ 'templatename' ] . '.html', 0777);
						}
					}

					unset( $arr[ 'templates' ] );
				}


				if ( isset( $arr[ 'skinfiles' ][ 'files' ] ) && is_array($arr[ 'skinfiles' ][ 'files' ]) )
				{

					Library::makeDirectory(ROOT_PATH . SKIN_IMG_URL_PATH . $skinimagedir);

					foreach ( $arr[ 'skinfiles' ][ 'files' ] as $r )
					{
						$file = $r[ 'file' ];

						if ( $file[ 'pathname' ] )
						{
							if ( $file[ 'pathname' ] )
							{
								Library::makeDirectory(ROOT_PATH . SKIN_IMG_URL_PATH . $skinimagedir . '/' . $file[ 'pathname' ]);

								$fh = fopen(ROOT_PATH . SKIN_IMG_URL_PATH . $skinimagedir . '/' . $file[ 'pathname' ] . '/' . $file[ 'filename' ], 'wb');
								fwrite($fh, gzuncompress(base64_decode($file[ 'data' ])));
								fclose($fh);
							}
							else
							{
								$fh = fopen(ROOT_PATH . SKIN_IMG_URL_PATH . $skinimagedir . '/' . $file[ 'filename' ], 'wb');
								fwrite($fh, gzuncompress(base64_decode($file[ 'data' ])));
								fclose($fh);
							}
						}
					}
				}

				Library::sendJson(true, trans('Skin Import abgeschlossen'));
			}
			else
			{
				Library::sendJson(false, trans('Die Xml Datei enthält keinen DreamCMS Skin!'));
			}


			exit;


		}
		elseif ( $ext == 'zip' )
		{

		}
		else
		{
			Library::sendJson(false, trans('Unbekanntes Dateiformat'));
		}
	}

}

?>