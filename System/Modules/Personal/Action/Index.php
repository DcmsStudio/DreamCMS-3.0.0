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
 * @package      Personal
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Personal_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$default = array (
			'dockposition' => 'center',
			'dockautohide' => false,
			'mintoappicon' => false
		);

		$this->load('Personal');

		if ( HTTP::input('send') )
		{
			$personal = User::getPersonalSettings();
			$currents = $this->Personal->get("personal", 'settings');


			$this->Personal->set("personal", 'settings', array (
			                                                   'gridlimit'          => ((int)HTTP::input('gridlimit') > 0 ?
					                                                   HTTP::input('gridlimit') :
					                                                   (int)$personal[ 'gridlimit' ]),
			                                                   'contenttranslation' => ((int)HTTP::input('contenttranslation') > 0 ?
					                                                   HTTP::input('contenttranslation') :
					                                                   Locales::getLocaleId()),
			                                                   'guilanguage'        => (trim(HTTP::input('guilanguage')) ?
					                                                   HTTP::input('guilanguage') : 'de_DE'),
			                                                   'skin'               => (trim(HTTP::input('skin')) ?
					                                                   HTTP::input('skin') : 'default'),
			                                                   'desktopbackground'  => (trim(HTTP::input('desktopbackground')) ?
					                                                   HTTP::input('desktopbackground') :
					                                                   $currents[ 'desktopbackground' ]),
			                                                   'wysiwyg'            => (string)HTTP::input('wysiwyg')
			                                             ));


			$currents = $this->Personal->get("dock", 'settings');

			$currents[ 'dockposition' ]      = (string)HTTP::input('dockposition');
			$currents[ 'dockautohide' ]      = HTTP::input('dockautohide') ? true : false;
			$currents[ 'mintoappicon' ]      = HTTP::input('mintoappicon') ? true : false;
			$currents[ 'dockHeight' ]        = !isset($currents[ 'dockHeight' ]) ? 40 : $currents[ 'dockHeight' ];
			$currents[ 'activeItems' ]       = !is_array($currents[ 'activeItems' ]) ? array () :
				$currents[ 'activeItems' ];
			$currents[ 'dockItems' ]         = !is_array($currents[ 'dockItems' ]) ? array () :
				$currents[ 'dockItems' ];
			$currents[ 'desktopbackground' ] = $currents[ 'dockItems' ] ? $currents[ 'dockItems' ] : false;
			$this->Personal->set("dock", 'settings', $currents);


			Library::sendJson(true, trans('Deine Dashboard-Einstellungen wurden gespeichert'));
		}

		$data                         = array ();
		$data[ 'skins' ]              = $this->getThemes();
		$data[ 'desktopbackgrounds' ] = $this->getDesktopBackgrounds($data[ 'skins' ]);


		$data[ 'contentlocales' ] = $this->db->query('SELECT title, flag, id FROM %tp%locale WHERE contentlanguage = 1 ORDER BY title')->fetchAll();
		$data[ 'guilocales' ]     = $this->db->query('SELECT title, code, flag, id FROM %tp%locale WHERE guilanguage = 1 ORDER BY title')->fetchAll();


		$personal           = User::getPersonalSettings();
		$currents           = $this->Personal->get("personal", 'settings');
		$data[ 'personal' ] = $currents;

		$data[ 'dockopts' ] = $this->Personal->get('dock', 'settings', $default);

		Library::addNavi(trans('Persönliche Backend Einstellungen'));
		$this->Template->process('personal/settings', $data, true);
	}

	/**
	 *
	 * @return array
	 */
	private function getThemes ()
	{

		$themes = Cache::get('be_themes');
		if ( is_null($themes) )
		{
			$theme_configs = glob(PUBLIC_PATH . 'html/style/*/skininfo.php');

			foreach ( $theme_configs as $config_file )
			{
				$_info           = array ();
				$theme[ 'name' ] = basename(dirname($config_file));

				include $config_file;

				$theme[ 'title' ] = $theme[ 'name' ] . ' (' . $_info[ 'title' ] . ')';
				$themes[ ]        = $theme;
			}

			Cache::write('be_themes', $themes);
		}

		return $themes;
	}

	/**
	 *
	 * @param array $skins
	 * @return array
	 */
	private function getDesktopBackgrounds ( $skins )
	{

		$_backgrounds = array ();

		if ( is_array($skins) )
		{
			foreach ( $skins as $r )
			{
				$backgrounds = Library::getFiles(PUBLIC_PATH . 'html/style/' . $r[ 'name' ] . '/img/desktop-backgrounds/', false);

				if ( count($backgrounds) )
				{
					$cache_path = PUBLIC_PATH . 'html/style/' . $r[ 'name' ] . '/img/desktop-backgrounds/.cache';

					if ( !is_dir($cache_path) )
					{
						Library::makeDirectory($cache_path);
					}

					chmod($cache_path, 0777);

					$img = ImageTools::create($cache_path);

					foreach ( $backgrounds as $f )
					{
						if ( $f[ 'filename' ] != '.' && $f[ 'filename' ] != '..' && $f[ 'filename' ] && strpos($f[ 'path' ], '/.cache') === false && file_exists(Library::formatPath($f[ 'path' ] . $f[ 'filename' ])) )
						{
							$ext = strtolower(Library::getExtension($f[ 'filename' ]));

							if ( $ext === 'jpg' || $ext === 'jpeg' || $ext === 'png' )
							{

								$data = $img->process(array (
								                            'source' => Library::formatPath($f[ 'path' ] . $f[ 'filename' ]),
								                            'output' => 'jpeg',
								                            'chain'  => array (
									                            0 => array (
										                            0 => 'resize',
										                            1 => array (
											                            'width'       => 120,
											                            'height'      => 90,
											                            'keep_aspect' => true,
											                            'shrink_only' => false
										                            )
									                            )
								                            ),
								                      ));
								if ( $data[ 'path' ] )
								{
									$path     = str_replace(PUBLIC_PATH, '', ROOT_PATH . $data[ 'path' ]);
									$filename = Library::getFilename($path);
									$path     = str_replace('/' . $filename, '/', $path);

									$_backgrounds[ ] = array (
										'filename' => $filename,
										'path'     => str_replace($filename, '', $data[ 'path' ]),
										'original' => $f[ 'filename' ]
									);
								}
								else
								{
									Library::log('Could not get the Desktop background Image! (' . Library::formatPath($f[ 'path' ] . $f[ 'filename' ]) . ')', 'warn');
								}
							}
						}
					}
				}
			}
		}

		return $_backgrounds;
	}

}

?>