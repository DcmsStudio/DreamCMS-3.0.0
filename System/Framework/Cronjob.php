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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Framework
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Cronjob.php
 */

/** @noinspection PhpUndefinedClassInspection */
class Cronjob extends Loader
{

	/**
	 *
	 * @var Cronjob
	 */
	protected static $objInstance = null;

	protected $type = 'intern';

	protected $time_now = 0;

	protected $date_now = array ();

	protected $cron_key = "";

	/**
	 * Prevent cloning of the object (Singleton)
	 */
	final private function __clone ()
	{

	}

	/**
	 * Return the current object instance (Singleton)
	 *
	 * @return object
	 */
	public static function getInstance ()
	{

		if ( is_null(self::$objInstance) )
		{
			self::$objInstance = new Cronjob();
		}

		self::$objInstance->init();

		return self::$objInstance;
	}

	public function init ()
	{

		if ( HTTP::input('cron') )
		{
			$this->type     = 'cron';
			$this->cron_key = substr(trim((string)HTTP::input('cron')), 0, 32);
		}

		$this->time_now             = time();
		$this->date_now[ 'minute' ] = intval(gmdate('i', $this->time_now));
		$this->date_now[ 'hour' ]   = intval(gmdate('H', $this->time_now));
		$this->date_now[ 'wday' ]   = intval(gmdate('w', $this->time_now));
		$this->date_now[ 'mday' ]   = intval(gmdate('d', $this->time_now));
		$this->date_now[ 'month' ]  = intval(gmdate('m', $this->time_now));
		$this->date_now[ 'year' ]   = intval(gmdate('Y', $this->time_now));
	}

    /**
     * @param $type
     * @internal param string $key
     */
	public function setType ( $type )
	{
		$this->type = $type;
	}

	private function initDB ()
	{

		if ( !$this->db->table_exists('%tp%schedule') )
		{

		}

	}

	/**
	 * @todo IN NEXT VERSION
	 * @return type
	 *
	 */
	static function runScheduledTasks ()
	{

		if ( is_file(DATA_PATH . '.scheduler_running') )
		{
			// sort of very poor sanity check...
			if ( filemtime(DATA_PATH . '.scheduler_running') < ( time() - 3600 ) )
			{
				// if the lock file is older than an hour, assume something crashed and unlock the scheduler...
				unlink(DATA_PATH . '.scheduler_running');

				if ( IS_CLI )
				{
					echo "Scheduler seems stuck - removing sanity check file.\n";
				}
			}
			else
			{
				if ( IS_CLI )
				{
					echo "Scheduler is already running - skipping.\n";
				}

				return;
			}
		}


		$old_umask = umask(0);
		file_put_contents(DATA_PATH . '.scheduler_running', 'scheduler running...');
		umask($old_umask);

		// Start Cron

		$db    = Database::getInstance();
		$tasks = $db->query('SELECT * FROM %tp%schedule WHERE enabled = 1 AND next_run <= ' . time())->fetchAll();

		if ( !empty( $tasks ) )
		{
			foreach ( $tasks as $task )
			{
				if ( IS_CLI )
				{
					echo "Running task " . $task[ 'name' ] . ": ";
				}

				self::runTask($task);

				if ( IS_CLI )
				{
					echo "done!\n";
				}
			}
		}
		else
		{
			if ( IS_CLI )
			{
				echo "No tasks to run.\n";
				$next = Cache::get('scheduler_next_run');

				// dbg($next);
				if ( $next )
				{
					echo "Next run time for tasks: " . Locales::formatFullDateTime($next) . "\n";
				}
			}
		}

		$db->free();

		self::cacheNextRunTime();

		if ( is_file(DATA_PATH . '.scheduler_running') )
		{
			unlink(DATA_PATH . '.scheduler_running');
		}

		return;
	}

	/**
	 * @todo IN NEXT VERSION
	 * @param type $task
	 */
	public static function runTask ( $task )
	{

		$db = Database::getInstance();

		$schedule = unserialize($task[ 'schedule_data' ]);
		$task     = array_merge($task, $schedule);
		unset( $task[ 'schedule_data' ] );

		// run task
		switch ( $task[ 'task_type' ] )
		{
			case 'include' :

				if ( is_file($task[ 'path' ]) && !is_dir($task[ 'path' ]) )
				{
					include_once $task[ 'path' ];
				}
				else
				{
					$task_result_message = 'The specified task path (' . $task[ 'path' ] . ') for task `' . $task[ 'name' ] . '` is invalid - the task could not be run.';
				}
				break;

			case 'component' :

				$component_path = SiteTools::getComponentPath($task[ 'component' ]);
				if ( !empty( $component_path ) )
				{
					include_once $component_path;
				}
				else
				{
					$task_result_message = 'The specified task component (' . $task[ 'component' ] . ') for task `' . $task[ 'name' ] . '` is invalid - the task could not be run.';
				}
				break;

			case 'plugin' :

				$plugin = Plugin::getPlugin($task[ 'plugin' ], true, true);
				if ( $plugin !== false )
				{
					if ( method_exists($plugin, $task[ 'method' ]) )
					{
						$task_result_message = call_user_func(array (
						                                            $plugin,
						                                            $task[ 'method' ]
						                                      ));
					}
					else
					{
						$task_result_message = 'Plugin `' . $task[ 'plugin' ] . '` has no method `' . $task[ 'method' ] . '`. Task aborted.';
					}
				}
				else
				{
					$task_result_message = 'Plugin `' . $task[ 'plugin' ] . '` does not exist. Task aborted.';
				}
				break;

			default :
				$task_result_message = 'Task type not specified in task data.';
				break;
		}


		// end run task
		// log task output?
		if ( $task[ 'log' ] )
		{
			$task_result_message = !empty( $task_result_message ) ? $task_result_message : 'No task result message was provided.';
			$db->query('INSERT INTO %tp%schedule_log SET task_name = ?, task_id = ?, run_time = NOW(), message = ?', $task[ 'name' ], $task[ 'id' ], $task_result_message);
		}

		// update run time
		$next_run = $task[ 'schedule_type' ] != 'once' ? self::getNextRunTime($task) : null;
		$enabled  = $task[ 'schedule_type' ] != 'once' ? 1 : 0;
		$db->query('UPDATE %tp%schedule SET job_lastrun = ?, enabled = ?, next_run = ? WHERE id = ?', TIMESTAMP, $enabled, $next_run, $task[ 'id' ]);
	}

	/**
	 * Run the task
	 *
	 * @param integer $id
	 */
	public function runCronjob ( $id = null )
	{

		if ( $id !== null )
		{
			/**
			 * @todo In the next version
			 */
		}

		if ( !is_file(DATA_PATH . '.scheduler_running') )
		{
			$old_umask = umask(0);
			file_put_contents(DATA_PATH . '.scheduler_running', 'scheduler running...');
			umask($old_umask);
		}

		if ( $this->type === 'intern' )
		{
			//-----------------------------------------
			// Loaded by our image...
			// ... get next job
			//-----------------------------------------

			$jobs = $this->db->query('SELECT * FROM %tp%cronjob_manager WHERE job_enabled = 1 AND job_next_run <= ? ORDER BY job_next_run ASC', $this->time_now)->fetchAll();

			foreach ( $jobs as $this_task )
			{

				if ( is_file(CRONJOBS_PATH . $this_task[ 'job_filename' ]) )
				{

					//-----------------------------------------
					// Got it, now update row and run..
					//-----------------------------------------
					$newdate = $this->generate_next_run($this_task);
					//  die('h:'.$this->run_hour .' min:'.$this->run_minute .' s:0 M:'. $this->run_month .' d:'.$this->run_day .' Y:'. $this->run_year);

					$this->db->query('UPDATE %tp%cronjob_manager SET job_lastrun = ?, job_next_run=? WHERE job_id=?', TIMESTAMP, $newdate, $this_task[ 'job_id' ]);

					//   $this->save_next_run_stamp( $newdate );


					include( CRONJOBS_PATH . $this_task[ 'job_filename' ] );
				}
			}

			$this->db->free();
		}
		else
		{
			//-----------------------------------------
			// Cron.. load from cron key
			//-----------------------------------------

			if ( $id ) {
				$this_task = $this->db->query_first('SELECT * FROM %tp%cronjob_manager WHERE job_enabled = 1 AND job_id=?', $id);
			}
			else {
				$this_task = $this->db->query_first('SELECT * FROM %tp%cronjob_manager WHERE job_enabled = 1 AND job_cronkey=?', $this->cron_key);
			}



			if ( $this_task[ 'job_id' ] )
			{
				if ( is_file(CRONJOBS_PATH . $this_task[ 'job_filename' ]) )
				{

					//-----------------------------------------
					// Got it, now update row and run..
					//-----------------------------------------
					$newdate = $this->generate_next_run($this_task);

					$this->db->query('UPDATE %tp%cronjob_manager SET job_lastrun = ?, job_next_run=? WHERE job_id=?', TIMESTAMP, $newdate, $this_task[ 'job_id' ]);

					//  $this->save_next_run_stamp( $newdate );


					include( CRONJOBS_PATH . $this_task[ 'job_filename' ] );
				}
			}
		}

		if ( file_exists(DATA_PATH . '.scheduler_running') && is_file(DATA_PATH . '.scheduler_running') )
		{
			@unlink(DATA_PATH . '.scheduler_running');
		}
	}

	public static function cacheNextRunTime ()
	{

		$res = Database::getInstance()->query('SELECT MIN(next_run) AS next_run_time FROM %tp%schedule WHERE enabled = 1 AND next_run != 0')->fetch();
		Cache::write('scheduler_next_run', $res[ 'next_run_time' ]);
	}

	/**
	 * Update next run variable in the systemvars cache
	 *
	 * @param integer $next_time_stamp
	 */
	public function save_next_run_stamp ( $next_time_stamp = 0 )
	{

		$cache_array = array ();
		$sql         = "SELECT * FROM %tp%cache_stores WHERE cache_var='system'";
		$cache       = $this->db->query_first($sql);

		$cache_array                   = unserialize(stripslashes($cache[ 'cache_value' ]));
		$cache_array[ 'job_next_run' ] = $next_time_stamp;

		$sql = "UPDATE %tp%cache_stores SET cache_value='" . addslashes(serialize($cache_array)) . "' WHERE cache_var='system'";
		$this->db->query($sql);
	}

	/**
	 * Generate next_run unix timestamp
	 *
	 * @param array|\type $task
	 * @global type       $db
	 * @global type       $fct
	 * @global type       $cp
	 * @return type
	 */
	private function generate_next_run ( $task = array () )
	{


		$next_time = 0;

		$this->run_day    = $this->date_now[ 'wday' ];
		$this->run_minute = $this->date_now[ 'minute' ];
		$this->run_hour   = $this->date_now[ 'hour' ];
		$this->run_month  = $this->date_now[ 'month' ];
		$this->run_year   = $this->date_now[ 'year' ];

		$current_time = time();


		$day_set     = false;
		$weekday_set = false;
		$hour_set    = false;
		$min_set     = false;

		if ( $task[ 'job_month_day' ] == -1 )
		{

		}
		else
		{
			$day_set = true;
		}

		if ( $task[ 'job_week_day' ] == -1 )
		{

		}
		else
		{
			$day_set     = false;
			$weekday_set = true;
		}

		if ( $task[ 'job_hour' ] == -1 )
		{

		}
		else
		{
			$hour_set = true;
		}

		if ( $task[ 'job_minute' ] == -1 )
		{

		}
		else
		{
			$min_set = true;
		}


		// weekly
		if ( $weekday_set )
		{
			// interval
			if ( $min_set || $hour_set )
			{
				if ( $min_set && $hour_set )
				{
					$current_day = date('w', $current_time);
					$day         = $task[ 'job_week_day' ];
					$days_diff   = $day - $current_day;
					if ( $days_diff > 0 )
					{
						$run_day = date('d', $current_time) + $days_diff;
					}
					else
					{
						$run_day = date('d', $current_time) + ( $days_diff + 7 ); // add a week!
					}
					$hours     = $task[ 'job_hour' ];
					$minutes   = $task[ 'job_minute' ];
					$next_time = mktime($hours, $minutes, 0, date('n', $current_time), $run_day, date('y', $current_time));
				}
				elseif ( $min_set && !$hour_set )
				{
					$current_day = date('w', $current_time);
					$day         = $task[ 'job_week_day' ];
					$days_diff   = $day - $current_day;
					if ( $days_diff > 0 )
					{
						$run_day = date('d', $current_time) + $days_diff;
					}
					else
					{
						$run_day = date('d', $current_time) + ( $days_diff + 7 ); // add a week!
					}

					if ( $this->run_hour > 23 )
					{
						$hours = 0;
					}
					else
					{
						$hours = $this->run_hour + 1;
					}


					$minutes   = $task[ 'job_minute' ];
					$next_time = mktime($hours, $minutes, 0, date('n', $current_time), $run_day, date('y', $current_time));
				}
				elseif ( $hour_set && !$min_set )
				{
					$current_day = date('w', $current_time);
					$day         = $task[ 'job_week_day' ];
					$days_diff   = $day - $current_day;
					if ( $days_diff > 0 )
					{
						$run_day = date('d', $current_time) + $days_diff;
					}
					else
					{
						$run_day = date('d', $current_time) + ( $days_diff + 7 ); // add a week!
					}
					$hours     = $task[ 'job_hour' ];
					$minutes   = 1;
					$next_time = mktime($hours, $minutes, 0, date('n', $current_time), $run_day, date('y', $current_time));
				}
			}
			else
			{
				$hours     = 0;
				$minutes   = 0;
				$next_time = mktime($hours, $minutes, 0, date('n', $current_time), date('d', $current_time), date('y', $current_time));
				if ( $next_time < $current_time )
				{
					$next_time = mktime($hours, $minutes, 0, date('n', $current_time), date('d', $current_time) + 1, date('y', $current_time));
				}
			}
		} // monthly
		elseif ( $day_set )
		{
			// interval
			if ( $min_set || $hour_set )
			{
				if ( $min_set && $hour_set )
				{
					$hours   = $task[ 'job_hour' ];
					$minutes = $task[ 'job_minute' ];
					$day     = $task[ 'job_month_day' ];

					$days_in_month        = date('t', $current_time);
					$current_day_of_month = date('j', $current_time);
					$month                = date('n', $current_time);

					if ( $day < $current_day_of_month )
					{
						$month += 1;
					}

					$day = $day > $days_in_month ? $days_in_month : $day;

					$next_time = mktime($hours, $minutes, 0, $month, $day, date('y', $current_time));

					if ( $next_time <= $current_time )
					{
						$days_in_month = date('t', mktime(0, 0, 0, ( $month + 1 ), 1, date('y', $current_time)));
						$day           = $task[ 'job_month_day' ];
						$day           = $day > $days_in_month ? $days_in_month : $day;
						$next_time     = mktime($hours, $minutes, 0, ( $month + 1 ), $day, date('y', $current_time));
					}
				}
				elseif ( $min_set && !$hour_set )
				{

					if ( $this->run_hour > 23 )
					{
						$hours = 0;
					}
					else
					{
						$hours = $this->run_hour + 1;
					}


					$minutes = $task[ 'job_minute' ];
					$day     = $task[ 'job_month_day' ];

					$days_in_month        = date('t', $current_time);
					$current_day_of_month = date('j', $current_time);
					$month                = date('n', $current_time);

					if ( $day < $current_day_of_month )
					{
						$month += 1;
					}

					$day = $day > $days_in_month ? $days_in_month : $day;

					$next_time = mktime($hours, $minutes, 0, $month, $day, date('y', $current_time));

					if ( $next_time <= $current_time )
					{
						$days_in_month = date('t', mktime(0, 0, 0, ( $month + 1 ), 1, date('y', $current_time)));
						$day           = $task[ 'job_month_day' ];
						$day           = $day > $days_in_month ? $days_in_month : $day;
						$next_time     = mktime($hours, $minutes, 0, ( $month + 1 ), $day, date('y', $current_time));
					}
				}
				elseif ( $hour_set && !$min_set )
				{
					$hours   = $task[ 'job_hour' ];
					$minutes = 0;
					$day     = $task[ 'job_month_day' ];

					$days_in_month        = date('t', $current_time);
					$current_day_of_month = date('j', $current_time);
					$month                = date('n', $current_time);

					if ( $day < $current_day_of_month )
					{
						$month += 1;
					}


					$day = $day > $days_in_month ? $days_in_month : $day;

					$next_time = mktime($hours, $minutes, 0, $month, $day, date('y', $current_time));

					if ( $next_time <= $current_time )
					{
						$days_in_month = date('t', mktime(0, 0, 0, ( $month + 1 ), 1, date('y', $current_time)));
						$day           = $task[ 'job_month_day' ];
						$day           = $day > $days_in_month ? $days_in_month : $day;
						$next_time     = mktime($hours, $minutes, 0, ( $month + 1 ), $day, date('y', $current_time));
					}
				}
			}
			else
			{
				$hours   = 0;
				$minutes = 0;
				$day     = $task[ 'job_month_day' ];

				$days_in_month        = date('t', $current_time);
				$current_day_of_month = date('j', $current_time);
				$month                = date('n', $current_time);

				if ( $day < $current_day_of_month )
				{
					$month += 1;
				}

				$day = $day > $days_in_month ? $days_in_month : $day;

				$next_time = mktime($hours, $minutes, 0, $month, $day, date('y', $current_time));

				if ( $next_time <= $current_time )
				{
					$days_in_month = date('t', mktime(0, 0, 0, ( $month + 1 ), 1, date('y', $current_time)));
					$day           = $task[ 'job_month_day' ];
					$day           = $day > $days_in_month ? $days_in_month : $day;
					$next_time     = mktime($hours, $minutes, 0, ( $month + 1 ), $day, date('y', $current_time));
				}
			}
		}
		else
		{
			// interval
			if ( $min_set || $hour_set )
			{
				if ( $min_set && $hour_set )
				{
					$hours     = $task[ 'job_hour' ];
					$minutes   = $task[ 'job_minute' ];
					$next_time = mktime($hours, $minutes, 0, date('n', $current_time), date('d', $current_time), date('y', $current_time));
					if ( $next_time < $current_time )
					{
						$next_time = mktime($hours, $minutes, 0, date('n', $current_time), date('d', $current_time) + 1, date('y', $current_time));
					}
				}
				elseif ( $min_set && !$hour_set )
				{

					$minutes   = $task[ 'job_minute' ];
					$next_time = mktime(0, $minutes, 0, date('n', $current_time), date('d', $current_time), date('y', $current_time));
					if ( $next_time < $current_time )
					{

						if ( $this->run_hour > 23 )
						{
							$hours = 0;
						}
						else
						{
							$hours = $this->run_hour + 1;
						}


						$next_time = mktime($hours, $minutes, 0, date('n', $current_time), date('d', $current_time) + 1, date('y', $current_time));
					}
				}
				elseif ( $hour_set && !$min_set )
				{
					$hours     = $task[ 'job_hour' ];
					$minutes   = 0;
					$next_time = mktime($hours, $minutes, 0, date('n', $current_time), date('d', $current_time), date('y', $current_time));

					if ( $next_time < $current_time )
					{
						$next_time = mktime($hours, $minutes, 0, date('n', $current_time), date('d', $current_time) + 1, date('y', $current_time));
					}
				}
			}
			else
			{
				$next_time = mktime(0, 0, 0, date('n', $current_time), date('d', $current_time), date('y', $current_time)) + 3600;
			}
		}

		return $next_time;

	}

	/**
	 * Add on a month for the next run time..
	 */
	private function _add_month ()
	{

		if ( $this->date_now[ 'month' ] == 12 )
		{
			$this->run_month = 1;
			$this->run_year++;
		}
		else
		{
			$this->run_month++;
		}
	}

	/**
	 * Add on a day for the next run time..
	 *
	 * @param integer $days
	 */
	private function _add_day ( $days = 1 )
	{

		if ( $this->date[ 'mday' ] >= ( gmdate('t', $this->time_now) - $days ) )
		{
			$this->run_day = ( $this->date[ 'mday' ] + $days ) - date('t', $this->time_now);
			$this->_add_month();
		}
		else
		{
			$this->run_day += $days;
		}
	}

	/**
	 * Add on a hour for the next run time...
	 *
	 * @param integer $hour
	 */
	private function _add_hour ( $hour = 1 )
	{

		if ( $this->date_now[ 'hour' ] >= ( 24 - $hour ) )
		{
			$this->run_hour = ( $this->date_now[ 'hour' ] + $hour ) - 24;
			$this->_add_day();
		}
		else
		{
			$this->run_hour += $hour;
		}
	}

	/**
	 * Add on a minute...
	 *
	 * @param integer $mins
	 */
	private function _add_minute ( $mins = 1 )
	{

		if ( $this->date_now[ 'minute' ] >= ( 60 - $mins ) )
		{
			$this->run_minute = ( $this->date_now[ 'minute' ] + $mins ) - 60;
			$this->_add_hour();
		}
		else
		{
			$this->run_minute += $mins;
		}
	}

}
