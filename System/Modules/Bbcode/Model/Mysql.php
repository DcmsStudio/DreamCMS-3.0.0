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
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Bbcode_Model_Mysql extends Model
{

	/**
	 *
	 */
	public function __construct ()
	{

		parent::__construct();
	}

	/**
	 * @param int $id
	 * @return type
	 */
	public function getBBcodeByID ( $id = 0 )
	{

		return $this->db->query("SELECT * FROM %tp%bbcodes WHERE bbcodeid = ?", $id)->fetch();
	}

	public function getBBcodeTags ( )
	{
		$dbBBCodes = $this->db->query("SELECT bbcodetag FROM %tp%bbcodes GROUP BY bbcodetag ORDER BY bbcodetag ASC")->fetchAll();

		$dbBBCodes = (!is_array($dbBBCodes) ? array() : $dbBBCodes);

		$core = BBCode::getCoreBBCodes();
		sort($core);
		foreach ($core as $c)
		{
			$dbBBCodes[] = array('bbcodetag' => $c);
		}

		return $dbBBCodes;
	}

	/**
	 *
	 *
	 * @return array array('result', 'total')
	 */
	public function getGridQuery ()
	{

		$sort = ' ASC';

		switch ( $GLOBALS[ 'sort' ] )
		{
			case 'asc':
				$sort = " ASC";
				break;

			case 'desc':
			default:
				$sort = " DESC";
				break;
		}

		switch ( $GLOBALS[ 'orderby' ] )
		{
			case 'bbcodetag':
				$order = " ORDER BY bbcodetag";
				break;

			case 'published':
				$order = " ORDER BY published";
				break;

			case 'bbcodeexample':
				$order = " ORDER BY bbcodeexample";
				break;

			default:
				$order = " ORDER BY bbcodetag";
				break;
		}

		$limit = $this->getPerpage();
		$page  = $this->getCurrentPage();


		$sql = "SELECT COUNT(*) AS total FROM %tp%bbcodes ";
		$r   = $this->db->query($sql)->fetch();


		return array (
			'result' => $this->db->query("SELECT * FROM %tp%bbcodes " . $order . $sort . " LIMIT " . ($limit * ($page - 1)) . "," . $limit)->fetchAll(),
			'total'  => $r[ 'total' ]
		);
	}

	/**
	 *
	 * @param      $idKey
	 * @param      $multiIdKey
	 * @param bool $mode default is false
	 * @internal param int $publish
	 * @internal param $int /array $ids
	 */
	public function deleteBBCode ( $idKey, $multiIdKey, $mode = false )
	{

		$data = $this->getMultipleIds($idKey, $multiIdKey);

		if ( !$data[ 'id' ] && !$data[ 'isMulti' ] )
		{
			Error::raise("Invalid ID");
		}

		if ( !$mode )
		{
			$this->load('Trash');

			$this->Trash->setTrashTable('%tp%bbcodes');
			$this->Trash->setTrashTableLabel('BBCode');


			if ( $data[ 'isMulti' ] )
			{

				$result = $this->db->query("SELECT * FROM %tp%bbcodes WHERE bbcodeid IN(0," . $data[ 'id' ] . ")")->fetchAll();

				$_labels = array ();

				foreach ( $result as $r )
				{
					// Move to Trash
					$trashData            = array ();
					$trashData[ 'data' ]  = $r;
					$trashData[ 'label' ] = $r[ 'bbcodetag' ];

					$_labels[ ] = $r[ 'bbcodetag' ];

					// move the BBCode to trash
					$this->Trash->addTrashData($trashData);
					$this->Trash->moveToTrash();
				}


				$this->db->query('DELETE FROM %tp%bbcodes  WHERE bbcodeid IN(0,' . $data[ 'id' ] . ')');


				Cache::delete('bbcodes');


				Library::log(sprintf("Deleting BBCode \"%s\".", implode(', ', $_labels)));
				Library::sendJson(true, trans('BBCodes wurde erfolgreich gelöscht'));
				exit;
			}
			else
			{
				$r = $this->db->query("SELECT * FROM %tp%bbcodes WHERE bbcodeid IN(0," . $data[ 'id' ] . ")")->fetch();

				// Move to Trash
				$trashData            = array ();
				$trashData[ 'data' ]  = $r;
				$trashData[ 'label' ] = $r[ 'bbcodetag' ];

				$_labels[ ] = $r[ 'bbcodetag' ];

				// move the BBCode to trash
				$this->Trash->addTrashData($trashData);
				$this->Trash->moveToTrash();


				$this->db->query('DELETE FROM %tp%bbcodes  WHERE bbcodeid = ?', $data[ 'id' ]);

				Cache::delete('bbcodes');

				Library::log("Has delete the BBCode `" . $r[ 'bbcodetag' ] . "`.");
				Library::sendJson(true, sprintf(trans('BBCode `%s` wurde erfolgreich gelöscht'), $r[ 'bbcodetag' ]));

				exit;
			}
		}
	}

	/**
	 *
	 * @param integer $id
	 * @param array   $data
	 * @param null    $transdata
	 * @return integer
	 */
	public function save ( $id = 0, $data = array (), $transdata = null )
	{


		$attribues = $data['attribues'];
		$attribues = count($attribues) ? serialize($attribues) : '';
		$allowedchildren = implode('|', $data['allowedchildren']);


		if ( !$id )
		{

			$this->db->query('INSERT INTO %tp%bbcodes (bbcodetag,bbcodereplacement,bbcodeexample,bbcodeexplanation,params,multiuse, attribues, allowedchildren)
                    VALUES (?,?,?,?,?,?,?,?)', trim((string)$data[ 'bbcodetag' ]), $data[ 'bbcodereplacement' ], trim((string)$data[ 'bbcodeexample' ]), trim((string)$data[ 'bbcodeexplanation' ]), (int)$data[ 'params' ], (int)$data[ 'multiuse' ], $attribues, $allowedchildren);

			return $this->db->insert_id();
		}
		else
		{

			$this->db->query('UPDATE %tp%bbcodes SET
                    bbcodetag=?,
                    bbcodereplacement=?,
                    bbcodeexample=?,
                    bbcodeexplanation=?,
                    params=?,
                    multiuse = ?,
                    attribues = ?,
                    allowedchildren = ?
                    WHERE bbcodeid = ?', trim((string)$data[ 'bbcodetag' ]), $data[ 'bbcodereplacement' ], trim((string)$data[ 'bbcodeexample' ]), trim((string)$data[ 'bbcodeexplanation' ]), (int)$data[ 'params' ], (int)$data[ 'multiuse' ], $attribues, $allowedchildren, $id);

			return $id;
		}
	}

}

?>