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
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Locale_Model_Mysql extends Model
{

	/**
	 * @var string
	 */
	protected $TableName = 'locale';

	/**
	 *
	 */
	public function __construct ()
	{

		parent::__construct();
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getLocaleById ( $id = 0 )
	{

		return $this->db->query('SELECT * FROM %tp%locale WHERE id = ?', $id)->fetch();
	}

	/**
	 *
	 * @param string $order
	 * @return array
	 */
	public function getLocales ( $order )
	{

		return $this->db->query('SELECT id, title FROM %tp%locale ORDER BY ' . $order)->fetchAll();
	}

	/**
	 *
	 * @return array
	 */
	public function getData ()
	{

		switch ( $GLOBALS[ 'sort' ] )
		{
			case 'asc':
				$sort = " ASC";
				break;

			case 'desc':
				$sort = " DESC";
				break;

			default:
				$sort = "DESC";
				break;
		}

		switch ( $GLOBALS[ 'orderby' ] )
		{
			case 'code':
				$order = " ORDER BY code";
				break;

			case 'wincode':
				$order = " ORDER BY wincode";
				break;

			case 'guilanguage':
				$order = " ORDER BY guilanguage";
				break;

			case 'contentlanguage':
				$order = " ORDER BY contentlanguage";
				break;

			case 'title':
			default:
				$order = " ORDER BY title";
				break;
		}

		$r = $this->db->query("SELECT COUNT(*) AS total FROM %tp%locale")->fetch();

		$limit = $this->getPerpage();
		$page  = (int)HTTP::input('page') ? (int)HTTP::input('page') : 1;

		$data             = array ();
		$data[ 'result' ] = $this->db->query("SELECT * FROM %tp%locale" . $order . ' ' . $sort . " LIMIT " . ($limit * ($page - 1)) . "," . $limit)->fetchAll();
		$data[ 'total' ]  = $r[ 'total' ];


		return $data;
	}

	/**
	 *
	 * @param integer $id
	 * @param array   $params
	 * @param null    $transdata
	 * @return int|void
	 */
	public function save ( $id = 0, $params = array (), $transdata = null )
	{

		if ( $id )
		{
			$str = $this->db->compile_db_update_string($params);
			$sql = "UPDATE %tp%locale SET {$str} WHERE id = {$id}";
			$this->db->query($sql);

			Library::log(sprintf(trans("Locale `%s` aktualisiert."), $params[ 'title' ]));
			Library::sendJson(true, sprintf(trans("Locale `%s` aktualisiert."), $params[ 'title' ]));
		}
		else
		{
			$str = $this->db->compile_db_insert_string($params);
			$sql = "INSERT %tp%locale ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})";
			$this->db->query($sql);

			$id = $this->db->insert_id();

			Library::log(sprintf(trans("Locale `%s` erfolgreich erstellt."), $params[ 'title' ]));
			echo Library::json(array (
			                         'success' => true,
			                         'msg'     => sprintf(trans("Locale `%s` erfolgreich erstellt."), $params[ 'title' ]),
			                         'newid'   => $id
			                   ));
			exit;
		}
	}

	/**
	 * @return array
	 */
	public function getTranslationData ()
	{

		switch ( $GLOBALS[ 'sort' ] )
		{
			case 'asc':
				$sort = " ASC";
				break;

			case 'desc':
				$sort = " DESC";
				break;

			default:
				$sort = " DESC";
				break;
		}

		switch ( $GLOBALS[ 'orderby' ] )
		{
			case 'translation':
				$order = " ORDER BY translation";
				break;
			case 'locale':
				$order = " ORDER BY loc.code";
				break;
			case 'original':
			default:
				$order = " ORDER BY original";
				break;
		}


		$search = (string)HTTP::input('q');
		$search = trim(strtolower($search));


		$page = (int)HTTP::input('page') ? (int)HTTP::input('page') : 1;


		$_s = '';
		if ( $search != '' )
		{
			$search = str_replace("*", "%", $search);
			$_s     = " (t.original LIKE " . $this->db->quote($search . "%") . " OR t.translation LIKE " . $this->db->quote($search . "%") . ")";
			$page   = 1;
		}

		$locale = (int)HTTP::input('locale');
		if ( $locale )
		{
			$locale = ($_s ? ' AND ' : '') . " t.localeid = " . $locale;
		}
		else
		{
			$locale = '';
		}

		// get the total number of records
		$sql   = "SELECT COUNT(t.id) AS total FROM %tp%locale_translations AS t" . (($_s || $locale) ?
				' WHERE ' . $_s . $locale : '');
		$r     = $this->db->query_first($sql);
		$total = $r[ 'total' ];
		$limit = $this->getPerpage();


		$sql = "SELECT t.*, loc.code FROM %tp%locale_translations AS t
                LEFT JOIN %tp%locale AS loc ON(loc.id = t.localeid)" . (($_s || $locale) ? ' WHERE ' . $_s . $locale :
				'') . $order . $sort . " LIMIT " . ($limit * ($page - 1)) . "," . $limit;


		$data             = array ();
		$data[ 'result' ] = $this->db->query($sql)->fetchAll();
		$data[ 'total' ]  = $total;


		return $data;
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getTranslation ( $id = 0 )
	{

		return $this->db->query('SELECT * FROM %tp%locale_translations WHERE id = ?', $id)->fetch();
	}

	/**
	 * @return array
	 */
	public function getDefaultLocaleId ()
	{

		$default = Settings::get('locale');
		$sql     = "SELECT id FROM %tp%locale WHERE `code` = " . $this->db->quote($default);
		$id      = $this->db->query_first($sql);

		return $id;
	}

}

?>