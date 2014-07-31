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
 * @package      Cronjobs
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Cronjobs_Model_Mysql extends Model
{

	/**
	 *
	 */
	public function __construct ()
	{

		parent::__construct();
	}

	/**
	 *
	 *
	 * @return array
	 */
	public function getGridData ()
	{


		$search = '';
		$search = HTTP::input('q');
		$search = trim((string)strtolower($search));
		$all    = null;

		$_s = '';

		if ( $search != '' )
		{
			$search = $this->db->quote('%' . str_replace("*", "%", $search) . '%');

			switch ( HTTP::input('searchin') )
			{
				case 'name':
					$_s .= " AND job_title LIKE " . $search;
					break;
				case 'metatables':
					$_s .= " AND job_description LIKE " . $search;
					break;
				default:
					$_s .= " AND ( LOWER(job_title) LIKE " . $search;
					$_s .= "OR LOWER(job_description) LIKE " . $search . ")";
					break;
			}
		}


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
			case 'job_description':
				$order = " ORDER BY job_description";
				break;
			case 'job_title':
				$order = " ORDER BY job_title";
				break;
			case 'job_month_day':
				$order = " ORDER BY job_month_day";
				break;

			case 'job_week_day':
				$order = " ORDER BY job_week_day";
				break;
			case 'job_hour':
				$order = " ORDER BY job_hour";
				break;
			case 'job_minute':
				$order = " ORDER BY job_minute";
				break;
			case 'published':
				$order = " ORDER BY job_enabled";
				break;
			default:
				$order = " ORDER BY job_title";
				break;
		}

		// get the total number of records
		$r = $this->db->query('SELECT COUNT(job_id) AS total FROM %tp%cronjob_manager' . ($_s ? ' WHERE ' . $_s :
				''))->fetch();

		$total = $r[ 'total' ];
		$limit = $this->getPerpage();
		$page  = $this->getCurrentPage();


		$result = $this->db->query('SELECT * FROM %tp%cronjob_manager' . ($_s ? ' WHERE ' . $_s :
				'') . ' ' . $order . $sort . ' LIMIT ' . ($limit * ($page - 1)) . "," . $limit)->fetchAll();

		return array (
			'result' => $result,
			'total'  => $r[ 'total' ]
		);
	}

	/**
	 *
	 * @return array
	 */
	public function getCronJobs ()
	{

		return $this->db->query('SELECT * FROM %tp%cronjob_manager WHERE job_enabled=1')->fetchAll();
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getCronJob ( $id )
	{

		return $this->db->query('SELECT * FROM %tp%cronjob_manager WHERE job_id=?', $id)->fetch();
	}

	/**
	 *
	 * @param integer $id
	 * @param array   $data
	 * @return integer
	 */
	public function saveCronJob ( $id, $data )
	{

		if ( $id )
		{
			$this->db->query('UPDATE %tp%cronjob_manager
                     SET job_title = ?, job_description = ?, job_filename = ?, job_week_day = ?, job_month_day = ?,job_hour = ?,job_minute = ?,job_log = ?, job_next_run = 0 
                     WHERE job_id=?', $data[ 'job_title' ], $data[ 'job_description' ], $data[ 'job_filename' ], $data[ 'job_week_day' ], $data[ 'job_month_day' ], $data[ 'job_hour' ], $data[ 'job_minute' ], $data[ 'job_log' ], $id);

			Library::log('Update the cronjob `' . $data[ 'job_title' ] . '` ID: ' . $id);
		}
		else
		{
			$this->db->query('INSERT INTO %tp%cronjob_manager
                     (job_title, job_description, job_filename, job_next_run, job_week_day, job_month_day,job_hour,job_minute,job_cronkey,job_log,job_enabled,job_key,job_safemode) 
                     VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)', $data[ 'job_title' ], $data[ 'job_description' ], $data[ 'job_filename' ], 0, $data[ 'job_week_day' ], $data[ 'job_month_day' ], $data[ 'job_hour' ], $data[ 'job_minute' ], '', $data[ 'job_log' ], 1, '', 0);
			$id = $this->db->insert_id();

			Library::log('Create the cronjob `' . $data[ 'job_title' ] . '` ID: ' . $id);
		}

		return $id;
	}

	/**
	 *
	 * @param integer $id
	 * @return string
	 */
	public function deleteCronJob ( $id )
	{

		$r = $this->getCronJob($id);
		$this->db->query('DELETE FROM %tp%cronjob_manager WHERE job_id=?', $id);
		Library::log('Delete the cron jon ' . $r[ 'job_title' ], 'warn');

		return $r[ 'job_title' ];
	}

	/**
	 *
	 * @param integer $id
	 * @return integer
	 */
	public function changePublishCronJob ( $id )
	{

		$r  = $this->getCronJob($id);
		$on = ($r[ 'job_enabled' ] ? 0 : 1);
		$this->db->query('UPDATE %tp%cronjob_manager SET job_enabled = ? WHERE job_id=?', $on, $id);
		Library::log('Change the cron job `' . $r[ 'job_title' ] . '` to ' . ($on ? 'ON' : 'OFF'), 'info');

		return $on;
	}

}

?>