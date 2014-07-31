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
 * @file         Editblock.php
 */
class Layouter_Action_Editblock extends Layouter_Helper_Base
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$cols     = HTTP::input('cols');
			$_name    = HTTP::input('contentbox');
			$layoutid = (int)HTTP::input('layoutid');
			$dataid   = (int)HTTP::input('dataid');
			$block    = HTTP::input('layoutblock'); // the name of the block
			#Library::sendJson(false, print_r(HTTP::input(), true)  );

			$rs     = $this->db->query('SELECT * FROM %tp%layout_settings WHERE layoutid = ? AND `name` = ? AND cols = ?', $layoutid, $block, $cols)->fetch();
			$blocks = explode(',', $rs[ 'settings' ]);

			if ( !$rs[ 'blockid' ] )
			{
				Library::sendJson(false, sprintf('Block not exists (%s)', $block));
			}

			if ( !in_array($_name, $blocks) )
			{
				Library::sendJson(false, sprintf('This Block-Content not exists (%s)', $_name));
			}


			#Library::sendJson(false, sprintf('This Block-Content not exists (%s)', $block));
			$isModel = false;

			if ( preg_match('/^modul_/', $_name) )
			{
				$list = explode('_', $_name);

				array_pop($list); // remove identifier
				$function  = array_pop($list);
				$modelname = strtolower(array_pop($list));

				if ( empty($function) || empty($modelname) )
				{
					Library::sendJson(false, sprintf('Invalid Block Modul (%s)', $_name));
				}

				$model = Model::getModelInstance($modelname);

				if ( !method_exists($model, $function) )
				{
					Library::sendJson(false, sprintf('Invalid Block Modul method (%s)', $function));
				}

				$isModel = true;
			}
			else
			{
				$names = explode('_', $_name);
				array_pop($names); // remove identifier
				$boxtype = $names[ 0 ];
			}


			if ( $dataid === 0 )
			{
				$rsx = $this->db->query('SELECT d.*, dt.*
                                      FROM %tp%layout_data AS d 
                                      LEFT JOIN %tp%layout_data_trans AS dt ON(dt.dataid = d.id)
                                      WHERE d.blockid = ? AND d.layoutid = ? AND d.`blockname` = ? AND 
                                      (dt.`lang` = ? OR dt.iscorelang=1 AND
                                      NOT EXISTS (SELECT a.dataid FROM %tp%layout_data_trans AS a WHERE a.dataid = d.id AND a.`lang` = ?) ) 
                                      GROUP BY d.id LIMIT 1', $rs[ 'blockid' ], $layoutid, $_name, CONTENT_TRANS, CONTENT_TRANS)->fetch();
			}
			elseif ( $dataid )
			{
				$rsx = $this->db->query('SELECT d.*, dt.*
                                      FROM %tp%layout_data AS d 
                                      LEFT JOIN %tp%layout_data_trans AS dt ON(dt.dataid = d.id)
                                      WHERE d.id = ? AND 
                                      (dt.`lang` = ? OR dt.iscorelang=1 AND
                                      NOT EXISTS (SELECT a.dataid FROM %tp%layout_data_trans AS a WHERE a.dataid = d.id AND a.`lang` = ?) ) 
                                      GROUP BY d.id LIMIT 1', $dataid, CONTENT_TRANS, CONTENT_TRANS)->fetch();
			}

			// disable or activate box content
			if ( $this->input('disable') && $rsx[ 'id' ] )
			{
				demoadm();
				$this->db->query('UPDATE %tp%layout_data SET visible = ? WHERE id = ?', ($rsx[ 'visible' ] ? 0 :
					1), $rsx[ 'id' ]);


				$this->clearLayoutCache($layoutid, $rsx[ 'blockid' ]);

				echo Library::json(array (
				                         'success'  => true,
				                         'disabled' => ($rsx[ 'visible' ] ? 1 : 0)
				                   ));
				exit;
			}


			$originalDataID = $rsx[ 'id' ];


			// link to other content box
			if ( $rsx[ 'relid' ] )
			{

				$rsx = $this->db->query('SELECT d.*, dt.*
                                       FROM %tp%layout_data AS d 
                                       LEFT JOIN %tp%layout_data_trans AS dt ON(dt.dataid = d.id)
                                       WHERE d.id = ? AND 
                                       (dt.`lang` = ? OR dt.iscorelang=1 AND
                                       NOT EXISTS (SELECT a.dataid FROM %tp%layout_data_trans AS a WHERE a.dataid = d.id AND a.`lang` = ?) ) 
                                       GROUP BY d.id', $rsx[ 'relid' ], CONTENT_TRANS, CONTENT_TRANS)->fetch();
			}

			if ( !$rsx[ 'id' ] )
			{
				$rsx         = $this->db->query('SELECT * FROM %tp%layout_data WHERE blockid = ? AND layoutid = ? AND `blockname` = ? ', $rs[ 'blockid' ], $layoutid, $_name)->fetch();
				$rsx[ 'id' ] = 0;
			}

			if ( HTTP::input('send') )
			{
				demoadm();

				$content = HTTP::input('content');
				$title   = HTTP::input('title');


				if ( $isModel )
				{
					$content = HTTP::input('modul');
				}

				if ( is_array($content) )
				{
					$content = Library::serialize($content);
				}


				if ( $rsx[ 'id' ] )
				{


					$langFound = $this->db->query('SELECT dataid FROM %tp%layout_data_trans WHERE dataid = ? AND `lang` = ?', $rsx[ 'id' ], CONTENT_TRANS)->fetch();

					if ( $langFound[ 'dataid' ] > 0 )
					{
						$this->db->query('UPDATE %tp%layout_data_trans SET title = ?, `value`= ? WHERE dataid = ? AND `lang` = ?', $title, $content, $rsx[ 'id' ], CONTENT_TRANS);
					}
					else
					{
						$this->db->query('INSERT INTO %tp%layout_data_trans
                    (dataid, title, `value`, `lang`, iscorelang) 
                    VALUES(?,?,?,?,?) ', $rsx[ 'id' ], $title, $content, CONTENT_TRANS, 0);
					}

					$this->clearLayoutCache($layoutid, $rs[ 'blockid' ]);

					echo Library::json(array (
					                         'success' => true,
					                         'msg'     => trans('Layout Block-Daten wurde aktualisiert'),
					                         'dataid'  => $originalDataID
					                   ));
				}
				else
				{
					$this->db->query('INSERT INTO %tp%layout_data
                    (blockid, layoutid, blockname, visible, ismodulwidget, `type`) 
                    VALUES(?,?,?,?,?,?) ', $rs[ 'blockid' ], $layoutid, $_name, 1, 0, '');


					$newID = $this->db->insert_id();


					$this->db->query('INSERT INTO %tp%layout_data_trans
                    (dataid, title, `value`, `lang`, iscorelang) 
                    VALUES(?,?,?,?,?) ', $newID, $title, $content, CONTENT_TRANS, 1);


					echo Library::json(array (
					                         'success' => true,
					                         'msg'     => trans('Layout Block-Daten wurde hinzugefügt'),
					                         'dataid'  => $newID,
					                         'newid'   => $newID
					                   ));
				}


				exit;
			}


			if ( $isModel )
			{
				$rsx[ 'blockdata' ] = $rsx;
				$rsx[ 'layoutid' ]  = $layoutid;
				$modeldata          = $model->getLayouterForm($modelname, $function, $rsx);


				$data[ 'formlabel' ] = $modeldata[ 'formlabel' ];
				$data[ 'form' ]      = $modeldata[ 'form' ];
			}
			else
			{
				$rsx[ 'blockdata' ] = $rsx;
				$rsx[ 'layoutid' ]  = $layoutid;
				switch ( $boxtype )
				{
					case 'html-content':
						$data[ 'formlabel' ] = trans('HTML Code');
						$data[ 'toolbar' ]   = $this->Template->process('layout/html-content', $rsx, null, 'toolbar');
						$data[ 'form' ]      = $this->Template->process('layout/html-content', $rsx);
						break;
					case 'php-content':
						$data[ 'formlabel' ] = trans('PHP Code');
						$data[ 'toolbar' ]   = $this->Template->process('layout/php-content', $rsx, null, 'toolbar');
						$data[ 'form' ]      = $this->Template->process('layout/php-content', $rsx);
						break;
					case 'image-content':
						$data[ 'formlabel' ] = trans('Bild');
						$data[ 'toolbar' ]   = $this->Template->process('layout/image-content', $rsx, null, 'toolbar');
						$data[ 'form' ]      = $this->Template->process('layout/image-content', $rsx);
						break;
					case 'other-content':
						$data[ 'formlabel' ] = trans('Sonstiger Inhalt');
						$data[ 'toolbar' ]   = $this->Template->process('layout/other-content', $rsx, null, 'toolbar');
						$data[ 'form' ]      = $this->Template->process('layout/other-content', $rsx);
						break;
				}
			}


			$data[ 'form' ] = str_replace($data[ 'toolbar' ], '', $data[ 'form' ]);


			$data[ 'itemdata' ]    = $rs;
			$data[ 'success' ]     = true;
			$data[ 'maincontent' ] = $this->Template->process('main', array (
			                                                                'output' => $data[ 'form' ]
			                                                          ), null);
			echo Library::json($data);
			exit;
		}
	}

}

?>