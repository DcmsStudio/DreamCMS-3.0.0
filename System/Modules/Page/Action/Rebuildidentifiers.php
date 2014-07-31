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
 * @package      Page
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Rebuildidentifiers.php
 */
class Page_Action_Rebuildidentifiers extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$this->load('AliasRegistry');
		$method = (HTTP::post('method') == 'all' ? true : false);
		$suffix = HTTP::post('suffix');


		$modul = Module::getInstance()->getModul(CONTROLLER);

		$rows   = $this->db->query('SELECT title, id, alias, suffix, lang FROM %tp%pages_trans')->fetchAll();
		$errors = array ();
		foreach ( $rows as $r )
		{
			$aliasExists = $this->AliasRegistry->aliasExists(array (
			                                                       'alias'         => $r[ 'alias' ],
			                                                       'suffix'        => ($suffix ? $suffix :
					                                                       $r[ 'suffix' ]),
			                                                       'documenttitle' => $r[ 'title' ]
			                                                 ), CONTROLLER);


			if ( $aliasExists && ($r[ 'id' ] && $this->AliasRegistry->getErrorAliasID() != $r[ 'id' ]) )
			{
				$msg       = sprintf(trans('Der Alias "%s" existiert bereits!'), $this->AliasRegistry->getAlias());
				$errors[ ] = $msg;
			}
			else
			{
				$this->db->query('UPDATE %tp%pages_trans SET alias = ?, suffix = ? WHERE id= ? AND `lang` = ?', $this->AliasRegistry->getAlias(), $this->AliasRegistry->getSuffix(), $r[ 'id' ], $r[ 'lang' ]);


				$this->AliasRegistry->registerAlias(array (
				                                          'modulid'    => $modul[ 'id' ],
				                                          'controller' => 'page',
				                                          'action'     => 'index',
				                                          'appid'      => 0,
				                                          'contentid'  => $r[ 'id' ],
				                                          'alias'      => $this->AliasRegistry->getAlias(),
				                                          'suffix'     => $this->AliasRegistry->getSuffix(),
				                                          'lang'       => $r[ 'lang' ]
				                                    ));
			}
		}


		unset($rows);


		if ( count($errors) )
		{
			Library::log('Alias Builder has found many errors!<br/>' . implode('', $errors), 'warn');
			echo Library::json(array (
			                         'success' => false,
			                         'errors'  => implode('<br/>', $errors)
			                   ));
			exit;
		}

		Library::sendJson(true, trans('Identifiers wurden erfolgreich neu geschrieben.'));
	}

}

?>