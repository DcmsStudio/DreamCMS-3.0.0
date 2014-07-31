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
 * @package      Eventmanager
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Eventmanager_Model_Mysql extends Model
{

	/**
	 *
	 * @return array array('result', 'total')
	 */
	public function getGridData ()
	{

		switch ( $GLOBALS[ 'sort' ] )
		{
			case "desc":
			default:
				$_sortby = "desc";
				break;
			case "asc":
				$_sortby = "asc";
				break;
		}

		switch ( $GLOBALS[ 'orderby' ] )
		{
			case "description":
				$_orderby = "description";
				break;

			case "context":
				$_orderby = "context";
				break;

			case "event":
			default:
				$_orderby = "event";
				break;
		}


		// get the total number of records
		$r = $this->db->query('SELECT COUNT(*) AS total FROM %tp%event')->fetch();


		$total = $r[ 'total' ];
		$limit = $this->getPerpage();
		$page  = $this->getCurrentPage();

		return array (
			'result' => $this->db->query("SELECT * FROM %tp%event ORDER BY {$_orderby} {$_sortby} LIMIT " . ($limit * ($page - 1)) . "," . $limit)->fetchAll(),
			'total'  => $total
		);
	}

	/**
	 *
	 * @param array $events
	 * @return array
	 */
	public function getEventHooks ( $events )
	{

		$_events = Library::unempty($events);

		if ( !count($_events) )
		{
			return array (
				'hooks'       => array (),
				'found_hooks' => array ()
			);
		}


		$hooks       = $this->db->query('SELECT `event`, COUNT(*) AS counted FROM %tp%event_hook WHERE `event` IN(\'' . implode('\',\'', $_events) . '\') GROUP BY `event`')->fetchAll();
		$found_hooks = array ();
		foreach ( $hooks as $hook )
		{
			$found_hooks[ $hook[ 'event' ] ] = $hook[ 'counted' ];
		}

		return array (
			'hooks'       => $hooks,
			'found_hooks' => $found_hooks
		);
	}

	/**
	 * @return array
	 */
	public function getComponents ()
	{

		return $this->db->query("SELECT com.*, comc.name AS cat_name, comc.description AS cat_description, comc.system AS system
		FROM %tp%component AS com
		LEFT JOIN %tp%component_category AS comc ON(comc.id=com.category)
		ORDER BY comc.display_order ASC")->fetchAll();
	}

	/**
	 * @param $event
	 * @return array
	 */
	public function getEventHook ( $event )
	{

		return $this->db->query('SELECT * FROM %tp%event_hook WHERE `event` = ? ORDER BY run_order ASC', $event)->fetchAll();
	}

	/**
	 * @param $event
	 * @return type
	 */
	public function getEvent ( $event )
	{

		return $this->db->query('SELECT * FROM %tp%event WHERE `event` = ?', $event)->fetch();
	}

	/**
	 * @param $event
	 * @param $component
	 */
	public function addComponentToHook ( $event, $component )
	{

		$count = $this->db->query('SELECT COUNT(*) AS total FROM %tp%event_hook WHERE event = ? AND `type` = \'component\' AND `handler`= ?', $event, $component)->fetch();
		if ( $count[ 'total' ] )
		{
			Error::raise(trans('Cannot hook component - it is already hooked to this event.'));
		}

		$row = $this->db->query('SELECT MAX(run_order) AS max_run_order FROM %tp%event_hook WHERE event = ?', $event)->fetch();
		$mru = $row[ 'max_run_order' ] + 1;
		$this->db->query('INSERT INTO %tp%event_hook SET `type` = \'component\', event = ?, `handler` = ?, run_order = ?', $event, $component, $mru);

		Cache::delete('event_hooks');

		Library::log('Add the handler `' . $component . '` for event hook `' . $event . '`.');
	}

	/**
	 * @param $event
	 * @param $handler
	 */
	public function unhookComponent ( $event, $handler )
	{

		$this->db->query('DELETE FROM %tp%event_hook WHERE `type` = \'component\' AND event = ? AND `handler` = ?', $event, $handler);

		Cache::delete('event_hooks');

		Library::log('Remove the handler `' . $handler . '` from event hook `' . $event . '`.');
	}

	/**
	 *
	 * @param array $hooks
	 * @param array $enabled_hooks
	 */
	public function updateHookOrder ( $hooks, $enabled_hooks )
	{

		$this->db->begin();

		foreach ( $hooks as $run_order => $hook )
		{
			if ( !trim($hook) )
			{
				continue;
			}

			list($type, $handler, $event) = explode(':', $hook);
			$enabled = isset($enabled_hooks[ $type ][ $handler ][ $event ]) ? 1 : 0;

			$this->db->query('UPDATE %tp%event_hook SET run_order = ?, hook_enabled = ?
                        WHERE `type` = ? AND handler = ? AND event = ?', $run_order, $enabled, $type, $handler, $event);
		}

		$this->db->commit();


		Library::log('Update the Hook ordering for event `' . $event . '`.');
	}

}

?>