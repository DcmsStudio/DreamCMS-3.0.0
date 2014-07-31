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
 * @package      
 * @version      3.0.0 Beta
 * @category     
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         FileIndexer.php
 */
if ( class_exists('Fileman_Helper_Index'))
{

	$indexer = new Fileman_Helper_Index();
	$indexer->setIndexBasePath(PAGE_PATH);
	$indexer->addExclude(PAGE_CACHE_PATH)
		// do not index all others
		->addExclude(PAGE_PATH .'.tmb/')
		->addExclude(PAGE_PATH .'.trash/')
		->addExclude('.DS_Store')
		->addExclude('.htaccess')
		->addExclude('.htpasswd')
		->addExclude('.config.php')
		//	->addExclude('.md')
		//	->addExclude('.svn')
		//	->addExclude('.git')
		->addExclude('Thumbs.db');
	$indexer->updateIndex();

}