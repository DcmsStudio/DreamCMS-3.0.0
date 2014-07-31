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
 * @package      Sitemap
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         index.php
 */
class Sitemap_Action_Index extends Controller_Abstract
{

	/**
	 * @var
	 */
	private $_url;

	public function execute ()
	{

		/*

		  <urlset xmlns="http://www.google.com/schemas/sitemap/0.84">
		  foreach($entries as $entry){
		  print '<url>'."\n";
		  print '  <loc>'.$hostUrl.$entryUrl.'</loc>'."\n";
		  print '  <lastmod>'.@date('Y-m-h', wDocumentData::getData($entry, 'modificationTime')).'</lastmod>'."\n";
		  //print '  <changefreq>weekly</changefreq>';
		  print '</url>'."\n";
		  }
		  </urlset>

		 */
		Tracking::init();
		$spider = Tracking_Spider::getSpider();

		$this->_url        = Settings::get('portalurl');
		$tmp               = Model::getModelInstance('menues')->getMenu(true);
		$data[ 'sitemap' ]['generic'] = is_array($tmp) ? $tmp : array ();


		// error_reporting( E_ALL );
		$modulregistry = $this->getApplication()->getModulRegistry();
		$googleoutput  = '';


		$modules = array ();

		if ( $spider )
		{
			$googleoutput .= '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.google.com/schemas/sitemap/0.84">';

			// basics
			foreach ( $data[ 'sitemap' ] as $r )
			{
				$googleoutput .= '<url><loc>' . $this->fixFullUrl($r[ 'url' ]) . '</loc><lastmod>' . date('Y-m-h', TIMESTAMP) . '</lastmod><changefreq>hourly</changefreq><priority>1.0</priority></url>';
			}
		}


		foreach ( $modulregistry as $modulname => $opts )
		{
			$model = Model::getModelInstance($modulname);

			if ( is_object($model) && method_exists($model, 'getSitemap') )
			{

				if ( $spider )
				{
					$map = $model->getSitemap(true); // returns only an array
					if ( is_string($map) && !empty($map) )
					{
						$modules[ ] = array (
							'modul'   => $modulname,
							'label'   => $opts[ 'definition' ][ 'modulelabel' ],
							'sitemap' => $map
						);
					}
					else
					{
						foreach ( $map as $r )
						{
							$date = (isset($r[ 'modifed' ]) && $r[ 'modifed' ] > 0 ? $r[ 'modifed' ] :
								(isset($r[ 'created' ]) && $r[ 'created' ] > 0 ? $r[ 'created' ] : TIMESTAMP));
							$googleoutput .= '<url><loc>' . $this->fixFullUrl($r[ 'url' ]) . '</loc><lastmod>' . @date('Y-m-h', $date) . '</lastmod><changefreq>daily</changefreq><priority>0.7</priority></url>';
						}
					}
				}
				else
				{

					$map = $model->getSitemap(false); // returns only an string

					if ( is_string($map) && !empty($map) )
					{
						$modules[ ] = array (
							'modul'   => $modulname,
							'label'   => $opts[ 'definition' ][ 'modulelabel' ],
							'sitemap' => $map
						);
					}
				}
			}
		}

		$_plugins = Plugin::getInteractivePlugins();

		foreach ( $_plugins as $r )
		{
			if ( $r[ 'run' ] && $r[ 'published' ] && !$r[ 'offline' ] )
			{
				$model = Plugin::getModel($r[ 'key' ]);

				if ( is_object($model) && method_exists($model, 'getSitemap') )
				{
					if ( $spider )
					{
						$map = $model->getSitemap(true); // returns only an array

						foreach ( $map as $r )
						{
							$date = (isset($r[ 'modifed' ]) && $r[ 'modifed' ] > 0 ? $r[ 'modifed' ] :
								(isset($r[ 'created' ]) && $r[ 'created' ] > 0 ? $r[ 'created' ] : TIMESTAMP));
							$googleoutput .= '<url><loc>' . $this->fixFullUrl($r[ 'url' ]) . '</loc><lastmod>' . @date('Y-m-h', $date) . '</lastmod><changefreq>daily</changefreq><priority>0.5</priority></url>';
						}
					}
					else
					{

						$map = $model->getSitemap(false); // returns only an string

						if ( is_string($map) && !empty($map) )
						{
							$modules[ ] = array (
								'modul'   => $r[ 'key' ],
								'label'   => $r[ 'name' ],
								'sitemap' => $map
							);
						}
					}
				}
			}
		}


		$data[ 'sitemap' ][ 'modulesmap' ] = $modules;

        /**
         * Build Pagebreadcrumbs
         */
        $this->Breadcrumb->add('Sitemap');
        $this->Document->disableSiteCaching();

		// Output for google
		if ( $spider )
		{
			$googleoutput .= '</urlset>';

			$this->load('Output');
			$this->Output->setMode(Output::XML);
			$this->Output->appendOutput($googleoutput)->sendOutput();
			unset($googleoutput);
			exit;
		}

		// Set the Page Layout
		$this->Template->process('sitemap/index', $data, true);

		exit();
	}

	/**
	 *
	 * @param string $inputUrl
	 * @return string
	 */
	private function fixFullUrl ( $inputUrl )
	{

		if ( substr($inputUrl, 0, 1) !== '/' )
		{
			$inputUrl = '/' . $inputUrl;
		}
		if ( substr($inputUrl, 0, strlen($this->_url)) !== $this->_url )
		{
			$inputUrl = $this->_url . $inputUrl;
		}

		return $inputUrl;
	}

}

?>