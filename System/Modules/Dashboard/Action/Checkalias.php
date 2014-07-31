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
 * @package      Dashboard
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Checkalias.php
 */
class Dashboard_Action_Checkalias extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$pageType = HTTP::input('pagetype');

		if ( empty($pageType) )
		{
			Library::sendJson(false, trans('Seiten Typ wurde nicht mit übergeben. Daher ist keine überprüfung des Alias möglich.') . ' Code:' . __LINE__);
		}

		$base = HTTP::input('base'); // (titel der seite) Original String
		$data = HTTP::input('data'); // String der aktuell im feld ist
		$mode = HTTP::input('mode');

		$suffix = HTTP::input('suffix');

		$original_alias = HTTP::input('current');
		$contentid      = (int)HTTP::input('contentid');


		$table = Session::get('CURRENTCONTENT_TBL');
		$pk    = Session::get('CURRENTCONTENT_TBL_PK');


		$aliasRegistry = new AliasRegistry();
		$exists        = $aliasRegistry->aliasExists(array (
		                                                   'alias'         => $data,
		                                                   'suffix'        => $suffix,
		                                                   'documenttitle' => $base
		                                             ), $pageType, null);


		if ( $exists && ($contentid && $aliasRegistry->getErrorAliasID() != $contentid) )
		{
			Library::log(sprintf('Alias Builder has found many errors! (Application Item) The Alias `%s` already exists!', $aliasRegistry->getAlias()), 'warn');
			Library::sendJson(false, sprintf(trans('Der Alias "%s" existiert bereits!'), $aliasRegistry->getAlias()));
		}
		else
		{
			echo Library::json(array (
			                         'success' => true,
			                         'url'     => '' . $aliasRegistry->getAlias(),
			                         'str'     => $aliasRegistry->getAlias()
			                   ));
			exit();
		}


		Library::sendJson(false, trans('Invalid Identifier check!'));
	}

}
