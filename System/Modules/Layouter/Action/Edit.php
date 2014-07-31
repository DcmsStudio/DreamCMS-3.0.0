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
 * @package      Layouter
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edit.php
 */
class Layouter_Action_Edit extends Layouter_Helper_Base
{

	/**
	 * @var null
	 */
	protected static $layoutSections = null;

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$skinid = (int)HTTP::input('skinid');
			$id     = (int)HTTP::input('id');


			if ( !$skinid )
			{
				Error::raise(trans('Es wurde keine Skin ID übergeben!'));
			}

			if ( HTTP::input('send') )
			{

				demoadm();
				$data = HTTP::input();


				if ( trim((string)$data[ 'title' ]) == '' )
				{
					Library::sendJson(false, trans('Der Layout Titel ist erforderlich'));
				}

				if ( (int)$data[ 'static' ] && !(int)$data[ 'width' ][ 'value' ] )
				{
					Library::sendJson(false, trans('Das Layout soll statisches sein, aber Sie haben kein breite angegeben!'));
				}


				if ( (int)$data[ 'defaultlayout' ] > 0 )
				{
					$this->db->query("UPDATE %tp%layouts SET defaultlayout = 0 WHERE pageid = ?", PAGEID);
					$this->db->query("UPDATE %tp%layouts SET defaultlayout = 1 WHERE pageid = ? AND id = ?", PAGEID, $id);
				}


				$data[ 'headerheight' ] = serialize($data[ 'headerHeight' ]);
				$data[ 'footerheight' ] = serialize($data[ 'footerHeight' ]);

				$data[ 'customfooterheight' ] = serialize($data[ 'customfooterheight' ]);
				$data[ 'customheaderheight' ] = serialize($data[ 'customheaderheight' ]);


				$data[ 'widthleft' ]  = serialize($data[ 'colsLeft' ]);
				$data[ 'widthright' ] = serialize($data[ 'colsRight' ]);
				$data[ 'width' ]      = serialize($data[ 'width' ]);


				if ( strpos($data[ 'cols' ], 'cols3') !== false )
				{
					$data[ 'order' ] = $data[ 'order_3' ];
				}
				elseif ( strpos($data[ 'cols' ], 'cols2') !== false )
				{
					$data[ 'order' ] = $data[ 'order_2' ];
				}
				else
				{
					$data[ 'order' ] = '';
				}


				/**
				 * prepare the layout and save to extra file
				 */
				$this->renderlayout($data);

				if ( !$id )
				{
					$str = $this->db->compile_db_insert_string(array (
					                                                 'title'              => trim((string)$data[ 'title' ]),
					                                                 'skinid'             => $skinid,
					                                                 'pageid'             => PAGEID,
					                                                 'header'             => (int)$data[ 'header' ] ? 1 : 0,
					                                                 'footer'             => (int)$data[ 'footer' ] ? 1 : 0,
					                                                 'defaultlayout'      => (int)$data[ 'defaultlayout' ] ? 1 : 0,
					                                                 'cols'               => (string)$data[ 'cols' ],
					                                                 'column_order'       => (string)$data[ 'order' ],
					                                                 'doctype'            => (string)$data[ 'doctype' ],
					                                                 'template'           => (string)$data[ 'template' ],
					                                                 'aggregate'          => (int)$data[ 'aggregate' ] ? 1 : 0,
					                                                 'stylesheet'         => trim($data[ 'stylesheet' ]),
					                                                 'headtags'           => trim($data[ 'headtags' ]),
					                                                 'script'             => trim($data[ 'script' ]),
					                                                 'static'             => (int)$data[ 'static' ] ? 1 : 0,
					                                                 'width'              => (string)$data[ 'width' ],
					                                                 'align'              => (string)$data[ 'align' ],
					                                                 'onload'             => (string)$data[ 'onload' ],
					                                                 'cssclass'           => (string)$data[ 'cssclass' ],
					                                                 'headerheight'       => $data[ 'headerheight' ],
					                                                 'footerheight'       => $data[ 'footerheight' ],
					                                                 'customheader'       => (int)$data[ 'customheader' ] ? 1 : 0,
					                                                 'customfooter'       => (int)$data[ 'customfooter' ] ? 1 : 0,
					                                                 'customfooterheight' => $data[ 'customfooterheight' ],
					                                                 'customheaderheight' => $data[ 'customheaderheight' ],
					                                                 'widthleft'          => $data[ 'widthleft' ],
					                                                 'widthright'         => $data[ 'widthright' ],
					                                                 'created'            => time(),
					                                                 'modified'           => 0,
					                                                 'modules'            => serialize($data[ 'modul' ]),
					                                           ));


					$this->db->query("INSERT INTO %tp%layouts ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})");
					$id      = $this->db->insert_id();
					$message = trans('Layout wurde erstellt.');
				}
				else
				{
					$str = $this->db->compile_db_update_string(array (
					                                                 'title'              => trim((string)$data[ 'title' ]),
					                                                 'header'             => (int)$data[ 'header' ] ? 1 : 0,
					                                                 'footer'             => (int)$data[ 'footer' ] ? 1 : 0,
					                                                 'defaultlayout'      => (int)$data[ 'defaultlayout' ] ? 1 : 0,
					                                                 'cols'               => $data[ 'cols' ],
					                                                 'column_order'       => (string)$data[ 'order' ],
					                                                 'doctype'            => $data[ 'doctype' ],
					                                                 'template'           => $data[ 'template' ],
					                                                 'aggregate'          => (int)$data[ 'aggregate' ] ? 1 : 0,
					                                                 'stylesheet'         => trim($data[ 'stylesheet' ]),
					                                                 'headtags'           => trim($data[ 'headtags' ]),
					                                                 'script'             => trim($data[ 'script' ]),
					                                                 'static'             => (int)$data[ 'static' ] ? 1 : 0,
					                                                 'width'              => $data[ 'width' ],
					                                                 'align'              => $data[ 'align' ],
					                                                 'onload'             => $data[ 'onload' ],
					                                                 'cssclass'           => $data[ 'cssclass' ],
					                                                 'headerheight'       => $data[ 'headerheight' ],
					                                                 'footerheight'       => $data[ 'footerheight' ],
					                                                 'customheader'       => (int)$data[ 'customheader' ] ? 1 : 0,
					                                                 'customfooter'       => (int)$data[ 'customfooter' ] ? 1 : 0,
					                                                 'customfooterheight' => $data[ 'customfooterheight' ],
					                                                 'customheaderheight' => $data[ 'customheaderheight' ],
					                                                 'widthleft'          => $data[ 'widthleft' ],
					                                                 'widthright'         => $data[ 'widthright' ],
					                                                 'modified'           => time(),
					                                                 'modules'            => serialize($data[ 'modul' ]),
					                                           ));
					$this->db->query("UPDATE %tp%layouts SET {$str} WHERE id = ?", $id);
					$message = trans('Layout wurde aktualisiert.');
				}

				$this->addLastEdit($id, 'Layout ' . trim((string)$data[ 'title' ]));

				$this->clearLayoutCache($id, 0);

				$this->updateLayoutBlockids($id);


				echo Library::json(array (
				                         'success' => true,
				                         'newid'   => $id,
				                         'msg'     => $message
				                   ));
				exit;
			}


			//      $this->updateLayoutBlockids( $id );


			$ldata = $this->db->query("SELECT * FROM %tp%layouts WHERE id = ?", $id)->fetch();
			$skin  = $this->db->query("SELECT * FROM %tp%skins WHERE id = ?", $skinid)->fetch();

			$sdata = $this->getStyler($id);

			$data = array_merge((array)$ldata, (array)$sdata);

			$files = glob(DATA_PATH . 'layouts/*.html');

			foreach ( $files as $idx => $file )
			{
				$_filename     = Library::getFilename($file);
				$ext           = Library::getExtension($_filename);
				$filename      = str_replace('.' . $ext, '', $_filename);
				$files[ $idx ] = array (
					'value' => $filename,
					'title' => $filename
				);
			}

			// layout Templates
			$data[ 'layouts' ] = $files;
			unset( $files );


			// get all saved layout content boxes
			$all = $this->db->query('SELECT d.*, dt.title, dt.dataid
                                    FROM %tp%layout_data AS d 
                                    LEFT JOIN %tp%layout_data_trans AS dt ON(dt.dataid = d.id) 
                                    WHERE d.layoutid > 0 AND d.relid = 0 AND (dt.`lang` = ? OR dt.iscorelang=1 AND
                                    NOT EXISTS (SELECT a.dataid FROM %tp%layout_data_trans AS a WHERE a.dataid = d.id AND a.`lang` = ?) )
                                    ORDER BY dt.title ASC', CONTENT_TRANS, CONTENT_TRANS)->fetchAll();


			$data[ 'saved_contentboxes' ] = array ();
			$saved                        = json_decode($data[ 'savedItems' ]);

			foreach ( $all as $r )
			{
				foreach ( $saved as $k => $items )
				{
					if ( in_array($r[ 'blockname' ], explode(',', $items)) )
					{
						//continue 2;
					}
				}

				$s = explode('_', $r[ 'blockname' ]);
				if ( !$s[ 0 ] )
				{
					continue;
				}

				if ( $s[ 0 ] != 'modul' )
				{
					$arr = array (
						'blockid' => $r[ 'blockname' ],
						'type'    => $s[ 0 ],
						'id'      => $s[ 3 ]
					);
				}
				else
				{

					$icon = null;
					$path = MODULES_PATH . ucfirst($s[ 1 ]) . '/Resources/' . ucfirst($s[ 1 ]) . '_16x16.png';
					if ( file_exists($path) )
					{
						$icon = '../Modules/' . ucfirst($s[ 1 ]) . '/Resources/' . ucfirst($s[ 1 ]) . '_16x16.png';
					}


					$arr = array (
						'blockid'   => $r[ 'blockname' ],
						'icon'      => $icon,
						'type'      => $s[ 0 ],
						'id'        => $s[ 3 ],
						'modulname' => $s[ 1 ],
						'function'  => $s[ 2 ],
					);
				}

				$r = array_merge($r, $arr);


				$data[ 'saved_contentboxes' ][ ] = $r;
			}
			unset( $all );


			// Layout Blocks Modules
			$modules = $this->getLayoutSections();

			foreach ( $modules as $key => $value )
			{
				$data[ 'modul_types' ][ ] = array (
					'value' => $key,
					'title' => $value
				);
			}
			unset( $modules );

			// Layout Module
			$data[ 'modules' ] = unserialize($data[ 'modules' ]);


			$data[ 'headerheight' ]       = unserialize($data[ 'headerheight' ]);
			$data[ 'footerheight' ]       = unserialize($data[ 'footerheight' ]);
			$data[ 'customfooterheight' ] = unserialize($data[ 'customfooterheight' ]);
			$data[ 'customheaderheight' ] = unserialize($data[ 'customheaderheight' ]);


			$data[ 'widthleft' ]  = unserialize($data[ 'widthleft' ]);
			$data[ 'widthright' ] = unserialize($data[ 'widthright' ]);
			$data[ 'width' ]      = unserialize($data[ 'width' ]);
			$data[ 'skinid' ]     = $skinid;

			Library::addNavi(trans('Layouter'));
			Library::addNavi(sprintf(trans('Layouts für den Skin `%s`'), $skin[ 'title' ]));
			Library::addNavi(sprintf(trans('Layout `%s` für den Skin `%s`'), $ldata[ 'title' ], $skin[ 'title' ]));


			//$this->Template->addScript(BACKEND_JS_URL . 'dcms.layouter.js');
			$this->Template->process('layout/edit', $data, true);
			exit;
		}
	}

	/**
	 *
	 * @staticvar array $dataPrepared
	 * @param integer $blockid
	 * @param array   $layoutBlockData
	 * @return array
	 */
	private function getBlockTitel ( $blockid, $layoutBlockData )
	{

		//static $dataPrepared;

		//	if ( !is_array($dataPrepared) )
		//	{
		foreach ( $layoutBlockData as $rs )
		{
			$dataPrepared[ $rs[ 'blockname' ] ][ $rs[ 'lang' ] ] = $rs[ 'title' ];
			if ( $rs[ 'iscorelang' ] )
			{
				$dataPrepared[ $rs[ 'blockname' ] ][ 'corelang' ] = $rs[ 'title' ];
			}
		}
		//	}


		if ( isset( $dataPrepared[ $blockid ][ CONTENT_TRANS ] ) )
		{
			return $dataPrepared[ $blockid ][ CONTENT_TRANS ];
		}
		else
		{
			return $dataPrepared[ $blockid ][ 'corelang' ];
		}
	}

	/**
	 *
	 * @param string $listname the blockname
	 * @param array  $row
	 * @param        $_layoutBlockData
	 * @param        $blockname
	 * @return array
	 */
	private function prepareColItem ( $listname, $row, &$_layoutBlockData, $blockname )
	{

		$data              = null;
		$names             = explode('_', $listname);
		$data[ 'classes' ] = '';
		$identifier        = array_pop($names);
		$_identifier       = '';
		$moduleName        = '';
		$lowername         = strtolower($names[ 0 ]);

		if ( $listname == 'html-content_615' )
		{
			#	print_r($_layoutBlockData);
			#	echo $lowername;
		}

		//print_r($_layoutBlockData);
		foreach ( $_layoutBlockData as $r )
		{
			if ( $listname != $r[ 'blockname' ] )
			{
				//echo ($listname .' > '. $r[ 'blockname' ]);
				continue;
			}
			else
			{
				$data              = $r;
				$data[ 'dataid' ]  = $r[ 'id' ];
				$data[ 'relid' ]   = $r[ 'relid' ];
				$data[ 'visible' ] = $r[ 'visible' ];

				if ( $lowername == "modul" )
				{
					//die($listname);
					$identifier = array_pop($names);
					$data[ 'classes' ] .= ' ' . $row[ 'name' ];

					// block name is stored in the title field... arrrghh
					$blockName  = $identifier;
					$moduleName = str_replace('modul_', '', strtolower($names[ 1 ]));

					$model = Model::getModelInstance($moduleName);

					$blockId           = 'modul_' . $moduleName . '_' . $_identifier;
					$data[ 'blockid' ] = $listname;

					if ( $model->getModulDefinitionKey('hasBlocks') )
					{
						$moduleBlocks = $model->getLayoutBlocks();

						if ( is_array($moduleBlocks) && !empty( $moduleBlocks ) )
						{
							$found = false;
							foreach ( $moduleBlocks as $key => $thisBlock )
							{
								if ( !$found && strtolower($thisBlock[ 'id' ]) == strtolower($blockName) )
								{
									$found              = true;
									$data[ 'type' ]     = $listname;
									$data[ 'typeText' ] = $thisBlock[ 'name' ];
									//$data['classes'] .= ' modul_' . $moduleName . '_' . $thisBlock['id'];
									$data[ 'name' ] = $thisBlock[ 'name' ];
									$data[ 'icon' ] = $thisBlock[ 'icon' ];

									break;
								}
							}
						}
					}
					else
					{

						$blocktitle = $this->getBlockTitel($listname, $_layoutBlockData);
						$blocktitle = ( empty( $blocktitle ) ? $names[ 0 ] : $blocktitle );

						$data[ 'name' ]    = $blocktitle;
						$data[ 'blockid' ] = $listname;
					}
				}
				else
				{
					if ( $listname == 'html-content_615' )
					{
				//		print_r($_layoutBlockData);
				//		die( $names[ 0 ] );
					}


					$_blocktitle = $this->getBlockTitel($listname, $_layoutBlockData);
					$blocktitle  = ( empty( $_blocktitle ) ? $names[ 0 ] : $_blocktitle . ' (' . $names[ 0 ] . ')' );

					$data[ 'name' ] = $blocktitle; //$names[0];
					#$data['classes'] .= ' ' . $row['name'];
					$data[ 'classes' ] .= ' ' . $names[ 0 ];
					$data[ 'blockid' ] = $listname;
					$data[ 'type' ]    = $names[ 0 ] /* . $identifier*/
					;
				}

				#break;
			}
		}
		if ( $listname == 'html-content_615' )
		{
			#		print_r($data);
			#	echo $lowername;

			#exit;
		}
		$data[ 'typeClass' ] = 'layoutItem' . ucfirst(strtolower($moduleName));

		return $data;
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	private function getStyler ( $id )
	{

		$modules = $this->db->query('SELECT module, id FROM %tp%module WHERE pageid = ? AND published > 0', PAGEID)->fetchAll();


		/**
		 * get layout
		 */
		$layoutData = $this->db->query("SELECT * FROM %tp%layouts WHERE id = ?", $id)->fetch();

		/**
		 * get layout settings
		 */
		$layoutSettings = $this->db->query('SELECT * FROM %tp%layout_settings WHERE layoutid = ?', $layoutData[ 'id' ])->fetchAll();


		/**
		 * Read layout blocks with current language.
		 *
		 * @todo If change the content language then run ajax an update the current layouter!
		 */
		$_layoutBlockDataTemp = $this->db->query('SELECT relid, id, blockname FROM %tp%layout_data WHERE layoutid = ?', $layoutData[ 'id' ])->fetchAll();

		// cache the ids of layout data
		$ids        = array (
			0
		);
		$cacheNames = array ();
		foreach ( $_layoutBlockDataTemp as $r )
		{
			if ( $r[ 'relid' ] )
			{
				$ids[ ] = $r[ 'id' ];
			}
			$ids[ ] = ( $r[ 'relid' ] ? $r[ 'relid' ] : $r[ 'id' ] );
		}

		unset( $_layoutBlockDataTemp );


		$ids = array_unique($ids);

		#die(implode( ',', $ids ));
		/**
		 * get the layout data
		 */
		$layoutBlockData = $this->db->query('SELECT d.*, dt.*
                                             FROM %tp%layout_data AS d 
                                             INNER JOIN %tp%layout_data_trans AS dt ON(dt.dataid=d.id)
                                             WHERE d.id IN(' . implode(',', $ids) . ') AND
                                             (dt.`lang` = ? OR dt.iscorelang = 1 AND
                                                NOT EXISTS (SELECT a.dataid FROM %tp%layout_data_trans AS a WHERE a.dataid = dt.dataid AND a.`lang` = ?) 
                                             )', CONTENT_TRANS, CONTENT_TRANS)->fetchAll();


		/**
		 *
		 *                  /\
		 *                 /  \
		 *                  ||
		 *
		 *   THIS MUST ADD IN LAYOUTER FRAMEWORK CLASS (getLayoutBlock) !!!!!!!!
		 *
		 *
		 */
		$baseData = $layoutBlockData;
		foreach ( $layoutBlockData as &$r )
		{
			if ( $r[ 'relid' ] )
			{
				$tmp = $this->findBlockData($r[ 'relid' ], $baseData);

				if ( isset( $tmp[ 'id' ] ) )
				{
					$r[ 'title' ] = $tmp[ 'title' ];
					$r[ 'value' ] = $tmp[ 'value' ];
					//$r[ 'blockname' ] = $r[ 'blockname' ];
					//$r = array_merge( $tmp, $rnew );
				}
			}
		}

		// print_r( $layoutBlockData );exit;


		$layoutSelected = array ();
		$dbSections     = array ();
		$modulesHTML    = array ();

		$savedChildSiblingsList = '';


		foreach ( $layoutSettings as $row )
		{
			if ( $row[ 'name' ] == 'layoutSet' )
			{
				$layoutSelected[ $row[ 'template' ] ] = $row[ 'value' ];
			}
			else
			{
				$dbSections[ $row[ 'name' ] ] = $row[ 'settings' ];
			}
		}


		$tmpModules = array ();


		/**
		 * create module html code for UL list
		 */
		/*
		  foreach ( $modules as $r )
		  {
		  $model      = Model::getModelInstance( $r[ 'module' ] );
		  $moduleCode = $r[ 'module' ];


		  if ( $r[ 'module' ] == 'apps' )
		  {
		  continue;
		  }


		  if ( !method_exists( $model, 'getModulLabel' ) )
		  {
		  continue;
		  }

		  $moduleName = $model->getModulLabel();

		  if ( $model->getModulDefinitionKey( 'hasBlocks' ) )
		  {
		  $moduleBlocks = $model->getLayoutBlocks();

		  if ( !is_array( $moduleBlocks ) || empty( $moduleBlocks ) )
		  {
		  // the hasBlocks variable is checked again because the getLayoutBlocks function might alter that
		  continue;
		  }

		  foreach ( $moduleBlocks as $thisBlock )
		  {
		  $blockId = 'modul_' . $moduleCode . '_' . $thisBlock[ 'id' ];


		  if ( !isset( $modulesHTML[ $moduleCode ] ) )
		  {
		  $modulesHTML[ $moduleCode ][ 'blocks' ]    = '';
		  $modulesHTML[ $moduleCode ][ 'labelname' ] = $moduleName;
		  }

		  $data = array(
		  'relid'     => 0,
		  'id'        => $r[ 'id' ],
		  'blockid'   => $blockId,
		  'typeClass' => $r[ 'module' ],
		  'name'      => $thisBlock[ 'name' ],
		  'icon'      => $thisBlock[ 'icon' ]
		  );

		  if ( file_exists( ROOT_PATH . JS_URL . 'layout/admin.layoutblocks-' . strtolower( $moduleCode ) . '.js' ) )
		  {
		  $data[ 'layoutblockscript' ] = JS_URL . 'layout/admin.layoutblocks-' . strtolower( $moduleCode ) . '.js';
		  }

		  $tmpModules[ ] = $data;

		  $modulesHTML[ $moduleCode ][ 'blocks' ] .= $this->Template->process( 'layout/module-list', $data ) . "\n";
		  }
		  }
		  }
		 */
		$tmp = array ();
		/*
		  foreach ( $modulesHTML as $k => $row )
		  {
		  $tmp[ ] = $row;
		  }
		 */
		$modulesHTML = $tmp;


		/**
		 *
		 */
		$tempItems   = array ();
		$tempSubcols = array ();


		foreach ( $layoutSettings as $row )
		{
			$data      = $row;
			$blockname = $row[ 'name' ];


			$list = explode(',', $row[ 'settings' ]);

			$subcols     = array ();
			$subcolItems = array ();
			$subsettings = $this->extractSubCols($row);

			foreach ( $list as $listname )
			{
				if ( trim($listname) && $listname !== 'contentPlaceholder' )
				{

					$names    = explode('_', $listname);
					$isSubCol = false;
					$itemdata = false;


					//$tempItems[$blockname]['blockid'] = $row['blockid'];
					//$tempItems[$blockname]['_blockID'] = $row['blockid'];
					// echo isset( $subsettings[ 'subcols' ][ $listname ] ) ? $subsettings[ 'subcols' ][ $listname ] : '';

					if ( $subsettings !== null && isset( $subsettings[ 'subcols' ][ $listname ] ) )
					{
						#	echo "\n\nListname 1: ".$listname."\n-----------\n";
						$tempSubcols[ $blockname ]            = $subsettings[ 'subcolitems' ];
						$tempItems[ $blockname ][ $listname ] = array (
							'_blockID' => $row[ 'blockid' ],
							'code'     => $subsettings[ 'subcols' ][ $listname ]
						);
					}
					else
					{
						#	echo "\n\nListname 2: ".$listname."\n-----------\n";
						$itemdata = $this->prepareColItem($listname, $row, $layoutBlockData, $blockname);

						if ( is_array($itemdata) )
						{
							$tempItems[ $blockname ][ $listname ] = array (
								'_blockID' => $row[ 'blockid' ],
								'code'     => $this->Template->process('layout/module-itemlink', $itemdata)
							);
						}

						unset($itemdata);
					}
				}
			}
		}


		$nextDynId = 0;

	#	print_r($tempSubcols);

		/**
		 * replace subcol placeholder items
		 */
		foreach ( $tempSubcols as $blockname => $rows )
		{
			$itemsData = $tempItems[ $blockname ];
			$blockid   = $tempItems[ $blockname ][ '_blockID' ];


			foreach ( $rows as $listname_key => $html_items )
			{
				$itemHtmlCode = '';
				if ( $listname_key == '_blockID' )
				{
					#unset($tempItems[$blockname]['_blockID']);
					#continue 1;
				}

			#	echo "\n\nlistname_key: ".$listname_key;


				if ( $listname_key !== 'ROOT' )
				{
					if ( isset( $itemsData[ $listname_key ] ) )
					{
						$itemHtmlCode = $itemsData[ $listname_key ];

						if ( is_array($itemHtmlCode) )
						{
							$itemHtmlCode = $itemHtmlCode[ 'code' ];
						}

						// Library::sendJson(false, $itemsData[$listname_key]);

						if ( !is_numeric($itemsData[ $listname_key ][ '_blockID' ]) )
						{
							continue;
						}



						preg_match('#(subdyn_id([^"]*))#isU', $listname_key, $match);
						$subdynid = $listname_key;


						if (isset($dbSections[$blockname])) {
							$found = false;
							$n = explode(',', $dbSections[$blockname]);
							foreach ($n as $nn) {
								if ($nn === $subdynid) {
									$found = true;
									break;
								}
							}

							#print_r($itemsData[$subdynid]['code']);

						}



					#	print_r($dbSections);
					#die('subdynid: '.$subdynid . ' '.$blockname);

						foreach ( $html_items as $itemID )
						{
							$itemHtmlCode = str_replace('[' . $itemID . ']', $itemsData[ $itemID ][ 'code' ], $itemHtmlCode);
							// unset( $tempItems[ $blockname ][ $itemID ] );
						}

						preg_match_all('#"dyn_id([\d]+?)"#isU', $itemHtmlCode, $matches);
						if ( is_array($matches[ 1 ]) )
						{
							foreach ( $matches[ 1 ] as $index => $idx )
							{
								$nextDynId = ( $idx > $nextDynId ? $idx : $nextDynId );
							}
						}

						if ( $blockid )
						{
							$tempItems[ $blockname ][ $listname_key ][ ] = array (
								'code'     => $itemHtmlCode,
								'_blockID' => $blockid
							);
						}
					}


					//continue;
				}
				else
				{

					foreach ( $itemsData as $itemID => $v )
					{

						#	 echo "\n----------------\n" . $itemID . ' _> ' . print_r( $v, true ) . "\n----------------\n";

						if ( is_int($itemID) )
						{
							#unset( $tempItems[ $blockname ][ $itemID ] );
						}
						if ( is_string($v) )
						{
							$tempItems[ $blockname ][ $itemID ] = array (
								'code'     => $v,
								'_blockID' => $blockid
							);
						}
					}
				}
			}
		}



		if ( !$layoutSelected )
		{
			$layoutSelected = '3columns';
		}

		$templateColumns = array ();
		$sections        = array (
			"layoutBoxLeft",
			"layoutBoxRight",
			"layoutBoxMiddle",
			"layoutBoxBottom",
			"layoutBoxTop",
			"layoutCustomBoxTop",
			"layoutCustomBoxBottom"
		);

		$skipItems         = array ();
		$savedListHTML     = array ();
		$savedSubColLayout = array ();

		foreach ( $tempItems as $blockname => &$subs )
		{
			foreach ( $subs as $idx => $item )
			{
				if ( isset( $skipItems[ $blockname ] ) || isset( $skipItems[ $idx ] ) )
				{

				}
				else
				{

					if ( stripos($item[ 'code' ], 'subdyn') !== false || stripos($item[ 'code' ], 'subcol') !== false )
					{
						$item[ 'code' ] = $this->findSubItems($subs, $item[ 'code' ], $skipItems);
					}

					// clean
					if (stripos($item[ 'code' ], 'subdyn_id') !== false && substr_count($item[ 'code' ], 'subdyn_id') > 1) {
						preg_match_all('#"(subdyn_id[\d]+?)"#isU', $item[ 'code' ], $matches, PREG_SET_ORDER);

						if (count($matches) > 1) {
							array_shift($matches);
						}

						foreach ($matches as $m) {
							if ( $subs[$m[1]] )
							{
								unset($subs[$m[1]]);
							}
						}
					}


					$item[ 'code' ] = preg_replace('#\[/?(START|END):subdyn_id\d*\]#is', '', $item[ 'code' ]);

					$savedListHTML[ ] = array (
						'block'   => $blockname,
						'item'    => $item[ 'code' ],
						'blockid' => $item[ '_blockID' ]
					);
				}
			}
		}


		$data                             = array ();
		$data[ 'templateColumns' ]        = Json::encode($templateColumns);
		$data[ 'selectedLayouts' ]        = Json::encode($layoutSelected);
		$data[ 'savedList' ]              = Json::encode($savedListHTML);
		$data[ 'savedSubColLayout' ]      = Json::encode($savedSubColLayout);
		$data[ 'savedChildSiblingsList' ] = $savedChildSiblingsList;
		$data[ 'areModulesWithBlocks' ]   = !empty( $modulesHTML );
		$data[ 'modulesList' ]            = $modulesHTML;

		$data[ 'savedItems' ] = Json::encode($dbSections);
		$data[ 'nextDynID' ]  = ( $nextDynId > 0 ? ( $nextDynId + 1 ) : 1 );
		# print_r( $data );
		# exit;
		$data[ 'availeble_modules' ] = $tmpModules;

		return $data;

		#   Library::sendJson( false, print_r( $data, true ) );
	}

	/**
	 * @param $arr
	 * @param $instr
	 * @param $skipItems
	 * @return mixed
	 */
	private function findSubItems ( $arr, $instr, &$skipItems )
	{

		foreach ( $arr as $blockname => $item )
		{
			if ( stripos($item[ 'code' ], 'subcolumns') !== false )
			{
				continue;
			}

			if ( stripos($instr, '[' . $blockname . ']') !== false )
			{
				$skipItems[ $blockname ] = $blockname;
				$instr                   = str_replace('[' . $blockname . ']', $item[ 'code' ], $instr);
			}
		}

		preg_match_all('#"dyn_id([\d]+)"#is', $instr, $matches);
		if ( is_array($matches[ 1 ]) )
		{
			foreach ( $matches[ 1 ] as $index => $idx )
			{
				$nextDynId = ( $idx > $nextDynId ? $idx : $nextDynId );
			}
		}


		return $instr;
	}

	/**
	 *
	 * @staticvar array $tmpResult
	 * @return array
	 */
	private function getFrontentModule ()
	{

		static $tmpResult;

		if ( !is_array($tmpResult) )
		{

			$modtranslation = array ();
			require_once( DATA_PATH . 'system/frontend_modules.php' );

			$optionGroups = array ();
			$options      = array ();


			foreach ( $modtranslation as $ctl => $arr )
			{
				if ( $ctl != '_other' )
				{

					# $options[$ctl][] = array('title' => $modtranslation[$ctl][0]);
					# $modtranslation[$ctl][0] = null;
					$grouplabel = ( is_array($modtranslation[ $ctl ]) ? array_shift($modtranslation[ $ctl ]) : array () );
					foreach ( $modtranslation[ $ctl ] as $act => $labels )
					{
						if ( !isset( $optionGroups[ $ctl ] ) )
						{
							$options[ $ctl ][ ]   = array (
								'controller' => '',
								'action'     => '',
								'title'      => $grouplabel
							);
							$optionGroups[ $ctl ] = true;
						}

						$options[ $ctl ][ ] = array (
							'controller'     => $ctl,
							'action'         => $act,
							'title'          => $labels[ 0 ],
							'advanced_title' => ( isset( $labels[ 1 ] ) ? $labels[ 1 ] : '' )
						);
					}
				}
				else
				{
					$grouplabel = ( is_array($modtranslation[ $ctl ]) ? array_shift($modtranslation[ $ctl ]) : array () );
					foreach ( $modtranslation[ $ctl ] as $other => $row )
					{
						if ( isset( $row[ 0 ] ) )
						{
							$options[ $ctl ][ ] = array (
								'controller'     => $other,
								'action'         => $other,
								'title'          => $row[ 0 ],
								'advanced_title' => ( isset( $row[ 1 ] ) ? $row[ 1 ] : '' )
							);
						}
						else
						{
							foreach ( $row as $act => $labels )
							{
								if ( !isset( $optionGroups[ $ctl ] ) )
								{
									$options[ $ctl ][ ]   = array (
										'controller' => '',
										'action'     => '',
										'title'      => $grouplabel
									);
									$optionGroups[ $ctl ] = true;
								}

								if ( is_array($labels) )
								{
									$options[ $ctl ][ ] = array (
										'controller'     => $other,
										'action'         => $act,
										'title'          => $labels[ 0 ],
										'advanced_title' => ( isset( $labels[ 1 ] ) ? $labels[ 1 ] : '' )
									);
								}
							}
						}
					}
				}
			}

			$tmpResult = array ();
			foreach ( $options as $row )
			{
				foreach ( $row as $r )
				{
					$tmpResult[ ] = $r;
				}
			}
		}

		return $tmpResult;
	}

	/**
	 *
	 * @return array
	 */
	protected function getLayoutSections ()
	{

		if ( is_null(self::$layoutSections) )
		{
			self::$layoutSections = array ();


			foreach ( $this->getFrontentModule() as $r )
			{
				if ( $r[ 'controller' ] )
				{
					self::$layoutSections[ $r[ 'controller' ] . '-' . ( $r[ 'action' ] ? $r[ 'action' ] : 'index' ) ] = $r[ 'title' ];
				}
			}


			$installedPlugins = Plugin::getInstalledPlugins();
			foreach ( $installedPlugins as $key => $r )
			{
				if ( $r[ 'run' ] )
				{
					self::$layoutSections[ 'plugin-' . $key . '-' . ( $r[ 'action' ] ? $r[ 'action' ] : 'run' ) ] = $r[ 'name' ] . ' (' . ucfirst($r[ 'action' ] ? $r[ 'action' ] : 'run') . ')';
				}
			}


			// self::$layoutSections = Library::getSystemModules();

			/*
			  $a = Application::getInstance();
			  $apps = $a->getApps();

			  foreach ( $apps as $r )
			  {
			  self::$layoutSections[$r['apptype'] . '-' . $r['appid'] . '-item'] = sprintf(trans('Anwendung `%s` Inhalts Ansicht'), $r['title']);
			  self::$layoutSections[$r['apptype'] . '-' . $r['appid'] . '-cat'] = sprintf(trans('Anwendung `%s` Kategirien Ansicht'), $r['title']);
			  }

			 */
		}

		return self::$layoutSections;
	}

}

?>