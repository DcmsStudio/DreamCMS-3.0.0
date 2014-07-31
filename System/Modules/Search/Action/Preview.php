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
 * @package      Search
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Preview.php
 */
class Search_Action_Preview extends Search_Helper_Base
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$this->getPreview();
		}
	}

	private function getPreview ()
	{

		$id = (int)$this->input('id');

		$data = $this->model->getSearchedItem($id);


		if ( $data[ 'id' ] && $data[ 'controller' ] )
		{
			$model = false;

			if ( substr($data[ 'location' ], 0, 7) === 'plugin/' )
			{
				$location   = substr($data[ 'location' ], 7);
				$loc        = explode('/', $location);
				$pluginName = strtolower($loc[ 0 ]);

				if ( Plugin::isPlugin($pluginName) )
				{
					$this->load('Plugin');
					$className = 'Addon_' . ucfirst(strtolower($pluginName)) . '_Action_Run';

					$this->load($className, '_RunAction');
					$executer            = $this->_RunAction->_initController(false, ucfirst(strtolower($pluginName)));
					$executer->isAddon   = true;
					$executer->addonName = strtolower($pluginName);

					if ( $executer->model === null )
					{
						$executer->model = Model::getModelInstance('Addon_' . ucfirst(strtolower($pluginName)));
						$model           = $executer->model;
					}

					if ( !is_object($model) || !is_object($executer->model) )
					{
						throw new BaseException('Invalid Model Instance');
					}
				}
			}
			else
			{
				$model = Model::getModelInstance($data[ 'controller' ]);
			}


			if ( is_object($model) )
			{
				$contentid           = $data[ 'contentid' ];
				$data                = call_user_func_array(array (
				                                                  $model,
				                                                  'getSerachItem'
				                                            ), array (
				                                                     $data[ 'contentid' ]
				                                               ));
				$data[ 'success' ]   = true;
				$data[ 'id' ]        = $id;
				$data[ 'contentid' ] = $contentid;
				echo Library::json($data);
				exit;
			}

			Library::sendJson(true);
		}
		else
		{
			Library::sendJson(true);
		}
	}

}
