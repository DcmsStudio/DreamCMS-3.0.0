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
 * @file         Recompile.php
 */
class Skins_Action_Recompile extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$id   = (int)$this->input('skinid');
		$skin = $this->model->getSkinByID($id);

		if ( !$skin[ 'id' ] )
		{
			Library::sendJson(false, trans('Kann den Skin nicht finden'));
		}

		Library::recursiveDelete(PAGE_CACHE_PATH . 'templates/' . $skin[ 'templates' ] . '/compiled/');



		$templates = $this->model->getTemplatesBySkinId($id);

		$compiler = new Compiler( TEMPLATES_PATH . $skin[ 'templates' ] . '/', PAGE_CACHE_PATH . 'templates/' . $skin[ 'templates' ] . '/compiled/', PAGE_CACHE_PATH . 'templates/' . $skin[ 'templates' ] . '/template/' );

		$compiler->compileOnly = true;


		$layout = array('layout' => array(
			'template' => CACHE_PATH . 'layout/layout_html5-styled.html'
		));












		foreach ( $templates as $r )
		{

			if ( $r[ 'group_name' ] )
			{
				$path = $r[ 'group_name' ] . '/' . $r[ 'templatename' ] . '.html';
			}
			else
			{
				$path = $r[ 'templatename' ] . '.html';
			}

			if ( $path === 'container.html' || $path === 'container_blog.html' || $path === 'container_content_left.html') {
				continue;
			}



			$compiler->get($path, $layout);
			$compiler->free();

		}



		Library::log('Has recompile the Templates for the skin "' . $skin[ 'title' ] . '" ');
		Library::sendJson(true, sprintf(trans('Compilierung des Skins `%s` wurden erneuert'), $skin[ 'title' ]));

	}
}