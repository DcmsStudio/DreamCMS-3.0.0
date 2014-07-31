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
 * @package      Locale
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Save.php
 */
class Locale_Action_Save extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$id  = (int)HTTP::input('id');
		$arr = array (
			'title'               => HTTP::input('title'),
			'flag'                => HTTP::input('flag'),
			'code'                => HTTP::input('code'),
			'wincode'             => HTTP::input('wincode'),
			'decimal'             => HTTP::input('decimal'),
			'thousands'           => HTTP::input('thousands'),
			'dateformat'          => HTTP::input('dateformat'),
			'timeformat'          => HTTP::input('timeformat'),
			'datetime_format'     => HTTP::input('datetime_format'),
			'fulldate_format'     => HTTP::input('fulldate_format'),
			'fulldatetime_format' => HTTP::input('fulldatetime_format'),
			'timezone'            => HTTP::input('timezone'),
			'guilanguage'         => HTTP::input('guilanguage'),
			'contentlanguage'     => HTTP::input('contentlanguage')
		);

		$loc = $arr[ 'code' ] ? $arr[ 'code' ] : 'de_DE';
		Cache::delete('locale_' . $loc);


		demoadm();

		$model = Model::getModelInstance('locale');

		if ( $id )
		{
			$model->save($id, $arr);
		}
		else
		{
			$model->save(0, $arr);
		}
	}

}

?>