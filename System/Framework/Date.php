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
 * @file         Date.php
 */
class Date
{

	/**
	 * @var null
	 */
	private $_date = null;

	/**
	 * @var null
	 */
	private $_format = null;

	/**
	 *
	 * @param integer/string $date optional
	 */
	public function __construct ( $date = null )
	{

		if ( $date !== null )
		{
			$this->_date = $date;
		}
	}

	/**
	 *
	 * @param string $date
	 * @return Date
	 */
	public function setDate ( $date )
	{
		$this->_date = $date;

		return $this;
	}

	/**
	 *
	 * @param string $format
	 * @throws BaseException
	 * @return string
	 */
	public function format ( $format )
	{

		if ( is_null($this->_date) )
		{
			throw new BaseException('Invalid date input!');
		}

		if ( is_string($this->_date) )
		{
			$stamp = $this->getTimestamp();

			if ( $stamp === false )
			{
				throw new BaseException(sprintf('invalid date input! `%s`', $this->_date));
			}

			return date($format, $this->getTimestamp());
		}
		else
		{
			return date($format, $this->_date);
		}
	}

	/**
	 *
	 * @return integer/false the unix timestamp and returns false if not a valid date input
	 */
	public function getTimestamp ()
	{
		if ( is_int($this->_date) )
		{
			return $this->_date;
		}
		else
		{
			return strtotime($this->_date);
		}
	}

	/**
	 * @param int $time
	 * @param string $dateFormat default is "d.m.Y, H:i"
	 * @param string $hourFormat default is "H:i"
	 * @param bool $addExactNames default is false
	 * @return string
	 */
	public function formatOnlineDate ( $time, $dateFormat = 'd.m.Y, H:i', $hourFormat = 'H:i', $addExactNames = false )
	{
		if (!intval($time))
		{
			return '';
		}

		$zerohour = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));

		if ( $time + 60 > TIMESTAMP && $addExactNames )
		{
			$online = trans('gerade online');
		}
		else if ( $time + 3600 > TIMESTAMP && $addExactNames )
		{
			$online = sprintf(trans('vor %s Minuten'), (round((TIMESTAMP - $time) / 60, 0)));
		}
		else if ( $time >= $zerohour && $addExactNames )
		{
			$online = sprintf(trans('heute, um %s Uhr'), date($hourFormat, $time));
		}
		else if ( $time >= $zerohour - 86400 && $addExactNames )
		{
			$online = sprintf(trans('gestern, um %s Uhr'), date($hourFormat, $time));
		}
		else
		{
			$online = date($dateFormat, $time);
		}

		return $online;
	}
	/**
	 * @param int $time
	 * @param string $dateFormat default is "d.m.Y, H:i"
	 * @param string $hourFormat default is "H:i"
	 * @param bool $addExactNames default is false
	 * @return string
	 */
	public function formatPostDate ( $time, $dateFormat = 'd.m.Y, H:i', $hourFormat = 'H:i', $addExactNames = false )
	{
		if (!(int)$time)
		{
			return '';
		}

		$zerohour = mktime(0, 0, 0, date('m', TIMESTAMP), date('d', TIMESTAMP), date('Y', TIMESTAMP));

		if ( $time + 60 > TIMESTAMP && $addExactNames )
		{
			$online = trans('gerade eben');
		}
		else if ( $time + 3600 > TIMESTAMP && $addExactNames )
		{
			$online = sprintf(trans('vor etwa %s Minuten'), (round((TIMESTAMP - $time) / 60, 0)));
		}
		else if ( $time >= $zerohour && $addExactNames )
		{
			$online = sprintf(trans('heute, um %s Uhr'), date($hourFormat, $time));
		}
		else if ( $time >= $zerohour - 86400 && $addExactNames )
		{
			$online = sprintf(trans('gestern, um %s Uhr'), date($hourFormat, $time));
		}
		else
		{
			$online = date($dateFormat, $time);
		}

		return $online;
	}
}
