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
 * @package      Cache
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Console.php
 */
class Cache_Config_Console
{

	public static function getHelp ()
	{

		return array (
			'clear'          => trans('Löscht den einfachen Cache'),
			'clearfull'      => trans('Löscht den gesamten Cache außer den Seiten Cache'),
			'clearpagecache' => trans('Löscht den Seiten Cache'),
		);
	}

	/**
	 * @param $param
	 */
	public static function clear ()
	{

		Cli::p('Clearing Cache...');
		if ( IS_CLI )
		{
			Cli::Status(0, 5);
			usleep(2000);
			Cache::setCachePath(PAGE_CACHE_PATH);
			Cli::Status(1, 5);
			usleep(2000);
			Cache::clear('admin');
			Cli::Status(2, 5);
			usleep(1000);
			Cache::clear('component');
			Cli::Status(3, 5);
			usleep(1000);
			Cache::clear('data');
			Cli::Status(4, 5);
			usleep(1000);
			try
			{

				Cache::clear('templates');
				li::Status(5, 5);
			}
			catch ( Exception $e )
			{
				#Error::raise('Unhandled Exception: ' . $e->getMessage(), 'PHP', $e->getCode(), $e->getFile(), $e->getLine());
			}
		}
		else
		{
			Cache::setCachePath(PAGE_CACHE_PATH);
			Cli::p('Clear PAGE_CACHE_PATH');
			Cache::clear('admin');
			Cli::p('Clear admin');
			Cache::clear('component');
			Cli::p('Clear component');
			Cache::clear('data');
			Cli::p('Clear data');

			try
			{
				Cache::clear('templates');
				Cli::p('Clear templates');
			}
			catch ( Exception $e )
			{
				#Error::raise('Unhandled Exception: ' . $e->getMessage(), 'PHP', $e->getCode(), $e->getFile(), $e->getLine());
			}
		}

		Library::log('Clearing Cache (Smart Cache Clear)', 'info');

		usleep(1000);
		Cli::p('Clear Cache ok...');
	}

	public static function clearfull ()
	{

		Cli::p('Clearing Fullcache...');

		if ( IS_CLI )
		{
			Cli::Status(0, 6);
			usleep(2000);

			Cache::setCachePath(CACHE_PATH);
			Cli::Status(1, 6);
			usleep(2000);

			Cache::clear('compiled');
			Cli::Status(2, 6);
			usleep(1000);

			Cache::clear('admin');
			Cli::Status(3, 6);
			usleep(1000);

			Cache::clear('component');
			Cli::Status(4, 6);
			usleep(1000);

			Cache::clear('data');
			Cli::Status(5, 6);
			usleep(1000);

			Cache::clear('templates');
			Cli::Status(6, 6);
			usleep(1000);
		}
		else
		{
			Cache::setCachePath(CACHE_PATH);
			Cli::p('Clear CACHE_PATH');
			Cache::clear('compiled');
			Cli::p('Clear compiled');
			Cache::setCachePath(PAGE_CACHE_PATH);
			Cli::p('Clear PAGE_CACHE_PATH');
			Cache::clear('admin');
			Cli::p('Clear admin');
			Cache::clear('component');
			Cli::p('Clear component');
			Cache::clear('data');
			Cli::p('Clear data');
			Cache::clear('templates');
			Cli::p('Clear templates');
		}
		usleep(1000);
		Library::log('Clearing Cache (Full Cache Clear)', 'info');

		Cli::p('Clear Fullcache ok...');
	}

	public static function clearpagecache ()
	{

		Cli::p('Clearing Pagecache...');

		if ( IS_CLI )
		{
			Cache::setCachePath(PAGE_CACHE_PATH);

			usleep(1000);
			Cli::Status(1, 5);

			Cache::clear('outputcache');

			usleep(1000);
			Cli::Status(2, 5);

			Cache::clear('pagedatacache');

			usleep(1000);
			Cli::Status(3, 5);

			Cache::clear('runnercache');

			usleep(1000);
			Cli::Status(4, 5);

			Cache::clear('sitecache');

			usleep(1000);
			Cli::Status(5, 5);
		}
		else
		{
			Cache::setCachePath(PAGE_CACHE_PATH);
			Cli::p('Clear PAGE_CACHE_PATH');

			Cache::clear('outputcache');
			Cli::p('Clear outputcache');

			Cache::clear('pagedatacache');
			Cli::p('Clear pagedatacache');

			Cache::clear('runnercache');
			Cli::p('Clear runnercache');


			Cache::clear('sitecache');
			Cli::p('Clear sitecache');
		}

		usleep(1000);
		Library::log('Clearing Page Cache', 'info');
		Cli::p('Clear Pagecache ok...');
	}

}
