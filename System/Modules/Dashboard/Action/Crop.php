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
 * @package
 * @version      3.0.0 Beta
 * @category
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Crop.php
 */
class Dashboard_Action_Crop extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isBackend() )
		{

			$source = $this->_post('source');
			if (substr($source, 0, 1) == '/')
			{
				$source = substr($source, 1);
			}

			if (substr($source, 0, 7) == 'public/')
			{
				$source = substr($source, 7);
			}

			if ( !file_exists(PUBLIC_PATH . $source) )
			{
				Library::sendJson(false, trans('Bilddatei nicht vorhanden'));
			}

			$oldcrop = $this->_post('oldcrop');
			if ( is_file(PUBLIC_PATH . $oldcrop) )
			{
				unlink(PUBLIC_PATH . $oldcrop);
			}


			// reset
			if ($this->_post('do') === 'clear') {

				if ( is_file(PUBLIC_PATH . $source) )
				{
					unlink(PUBLIC_PATH . $source);
				}

				Library::sendJson(true);
			}







			$original = Library::getFilename($source);
			$ext      = strtolower(Library::getExtension($original));


			if ( $ext === 'jpg' || $ext === 'jpeg' || $ext === 'png' || $ext === 'gif' )
			{
				$w = (int)$this->_post('width');
				$h = (int)$this->_post('height');
				$x = (int)$this->_post('x');
				$y = (int)$this->_post('y');

				$chain = array (array (
					'crop',
					array (
						'width'       => $w,
						'height'      => $h,
						'align'       => $x,
						'valign'      => $y,
						'keep_aspect' => true,
						'shrink_only' => false
					)
				));

				$originalName = preg_replace('#\.' . $ext . '$#', '', $original);
				$originalName = str_replace(array('.', ' '), '-', $originalName);

				$saveType     = ( $ext === 'jpg' || $ext === 'gif' ? 'jpeg' : $ext );

				//$cacheName  = $originalName . '-crop-' . $w . '-' . $h . '_' . $x . '_' . $y . '.' . $ext;



				// create crop directory into the image dir
				$cache_path = str_replace($original, '', PUBLIC_PATH . $source) .'.croped/';

				// $cache_path = PAGE_PATH . '.cache/img/croped/';
				if ( !is_dir($cache_path) )
				{
					Library::makeDirectory($cache_path);
				}



				Library::enableErrorHandling();
				error_reporting(E_ALL);

				$img = ImageTools::create($cache_path);
				$img->setQuality(80);
				$img->disablePathPatch();
				// die($originalName . '-crop-' . $w . '-' . $h . '_' . $x . '_' . $y);
				$img->setOutputFilename($originalName . '-crop-' . $w . '-' . $h . '_' . $x . '_' . $y);

				$_data = $img->process(array (
				                             'source' => Library::formatPath(PUBLIC_PATH . $source),
				                             'output' => $saveType,
				                             'chain'  => $chain
				                       ));


				if ( $_data[ 'path' ] )
				{

					echo Library::json(array (
					                         'success' => true,
					                         'src'     => str_replace(PUBLIC_PATH, '', $_data[ 'path' ]),
					                         'width'   => $_data[ 'width' ],
					                         'height'  => $_data[ 'height' ],
					                   ));
					exit;

				}
				else
				{
					Library::sendJson(false, trans('Bild konnte nicht verarbeitet werden'));
				}
			}
			else
			{
				Library::sendJson(false, trans('Unbekannte Bilddatei'));
			}
		}
	}

}