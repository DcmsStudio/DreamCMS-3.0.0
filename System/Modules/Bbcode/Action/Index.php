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
 * @package      Bbcode
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Bbcode_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			BBCode::markitup_smilies();
		}
		else
		{

			$this->load('Grid');
			$this->Grid
				->initGrid('bbcodes', 'bbcodeid', 'bbcodetag', 'asc')
				->setGridDataUrl('admin.php?adm=bbcode');


			$this->Grid->addFilter(array (
			                             array (
				                             'name'  => 'q',
				                             'type'  => 'input',
				                             'value' => '',
				                             'label' => 'Suchen nach',
				                             'show'  => true,
				                             'parms' => array (
					                             'size' => '40'
				                             )
			                             ),
			                       ));

			$this->Grid->addHeader(array (
			                             // sql feld						 header	 	sortieren		standart
			                             array (
				                             'islabel' => true,
				                             "field"   => "bbcodetag",
				                             "content" => 'BBCode tag',
				                             'width'   => '15%',
				                             "sort"    => "name",
				                             "default" => true
			                             ),
			                             array (
				                             "field"   => "bbcodeexample",
				                             "content" => 'BBCode Beispiel',
				                             "sort"    => "description",
				                             "default" => true
			                             ),
			                             array (
				                             "field"   => "published",
				                             "sort"    => "published",
				                             "content" => 'Publish',
				                             'width'   => '6%',
				                             "default" => true,
				                             'align'   => 'tc'
			                             ),
			                             array (
				                             "field"   => "options",
				                             "content" => 'Optionen',
				                             'width'   => '10%',
				                             "default" => true,
				                             'align'   => 'tc'
			                             ),
			                       ));

			$this->Grid->addActions(array (
			                              "delete" => array (
				                              'label' => trans('Löschen'),
				                              'msg'   => trans('Ausgewählte BBCodes werden gelöscht. Dies hat zur folge, das Inhalte in denen BBCodes verwendet werden nicht mehr korrekt angezeigt werden. Wollen Sie fortsetzen?')
			                              )
			                        ));

			$result = $this->model->getGridQuery();
			foreach ( $result[ 'result' ] as $rs )
			{

				$edit            = $this->linkIcon("adm=bbcode&action=edit&id={$rs['bbcodeid']}", 'edit', htmlspecialchars(sprintf(trans('BBCode `%s` bearbeiten'), $rs[ "bbcodetag" ])));
				$delete          = $this->linkIcon("adm=bbcode&action=delete&id={$rs['bbcodeid']}", 'delete');
				$published       = $this->getGridState(($rs[ 'draft' ] ? DRAFT_MODE :
					$rs[ 'published' ]), $rs[ 'id' ], $rs[ 'publishon' ], $rs[ 'publishoff' ], 'admin.php?adm=bbcode&action=publish&id=');
				$rs[ 'options' ] = <<<EOF
             {$edit} &nbsp; {$delete}
EOF;

				$row = $this->Grid->addRow($rs);
				$row->addFieldData("bbcodetag", $rs[ "bbcodetag" ]);
				$row->addFieldData("bbcodeexample", $rs[ 'bbcodeexample' ]);
				$row->addFieldData("published", $published);
				$row->addFieldData("options", $rs[ 'options' ]);
			}

			$griddata = $this->Grid->renderData($result[ 'total' ]);

			if ( HTTP::input('getGriddata') )
			{
				$data[ 'success' ]  = true;
				$data[ 'total' ]    = $result[ 'total' ];
				$data[ 'datarows' ] = $griddata[ 'rows' ];

				echo Library::json($data);
				exit;
			}


			Library::addNavi(trans('BBCodes'));
			$this->Template->process('bbcodes/index', array (), true);
		}
	}

}
