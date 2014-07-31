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
 * @package      Dashboard
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Tree.php
 */
class Dashboard_Action_Tree extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$id = (int)HTTP::input('id');

		$this->load('Page');
		$this->load('Personal');

		$activeTreeNode  = $this->Personal->get("sidebar", 'activetreenode');
		$activeTreeNodes = $this->Personal->get("sidebar", 'activetreenodes');

		if ( $id > 0 )
		{
			$activeTreeNodes = $id;
			//$this->Personal->set("sidebar", 'activetreenodes', $id);

			$arr = $this->Page->getChildrenNodes($id);
			Ajax::Send(true, array (
			                       'nodes'  => $arr,
			                       'length' => count($arr)
			                 ));
			exit();
		}

		if ( !(int)$activeTreeNodes )
		{
			$activeTreeNodes = 1;
			$this->Personal->set("sidebar", 'activetreenodes', $activeTreeNodes);
		}

		$sites = $this->Page->getChildrenNodes(1, 'title');
		$root  = Json::encode($sites);

		$arr2 = $this->Page->openTo($activeTreeNodes);
		$menu = Json::encode($arr2);


		$data = '/* var treeData = [{"id":"9999999","name":"' . (Settings::get('pagename') ? Settings::get('pagename') :
				'Default Site') . '","type":"site","icon":null,"is_folder":"1","parent":"0","level":"0","url":"","alias":"","suffix":null}];  */
var openTree = ' . $activeTreeNodes . ';
var treeData = ' . $root . ';
var openData = ' . $menu . ';
    $(document).ready(function(){
        // buildSiteMenuTree(treeData, openData, openTree);
    });
';


		$output = new Output();
		$output->appendOutput($data)->addHeader('Content-Type', 'application/javascript')->sendOutput();
	}

}
