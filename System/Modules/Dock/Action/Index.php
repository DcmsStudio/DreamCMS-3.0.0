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
 * @package      Dock
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Dock_Action_Index extends Controller_Abstract
{

	/**
	 *
	 * Fixture for tests
	 *
	 * @return array (id, parent, data)
	 */
	public function getFixture ()
	{

		$data = "This is some dummy data for use in the tests.";

		return array (
			array (
				'id'     => 1,
				'parent' => 0,
				'text'   => $data
			),
			array (
				'id'     => 2,
				'parent' => 0,
				'text'   => $data
			),
			array (
				'id'     => 3,
				'parent' => 0,
				'text'   => $data
			),
			array (
				'id'     => 4,
				'parent' => 0,
				'text'   => $data
			),
			array (
				'id'     => 5,
				'parent' => 1,
				'text'   => $data
			),
			array (
				'id'     => 6,
				'parent' => 1,
				'text'   => $data
			),
			array (
				'id'     => 7,
				'parent' => 2,
				'text'   => $data
			),
			array (
				'id'     => 8,
				'parent' => 3,
				'text'   => $data
			),
			array (
				'id'     => 9,
				'parent' => 6,
				'text'   => $data
			),
			array (
				'id'     => 10,
				'parent' => 6,
				'text'   => $data
			),
			array (
				'id'     => 11,
				'parent' => 6,
				'text'   => $data
			)
		);
	}

	public function execute ()
	{ /*
      $model = Model::getModelInstance();
      echo print_r( $model->getConfig() );
      echo print_r($model->getTranslationFields('news'));
      die( $model->test('dfdf asd asasd qr zru fthfd') );

      Debug::store('before');
      $fixture = $this->getFixture();

      $tree = new Tree();
      $tree->setupData($fixture, 'id', 'parent');
      print_r($tree->getTree());

      $it = new Tree_TreeRecursiveIterator($tree, new Tree_TreeIterator($tree->getTree()), true);


      echo $it->toArray();




      $node = $tree->getNode(6);

      $v = $node->getValue();
      print_r( $v );
      #$tree->freeMem();$tree = null;
      Debug::store('after');

      exit;

     */


		Library::addNavi('Start');


		$this->load('Breadcrumb');
		$this->Breadcrumb->add('Start');

		$int = memory_get_usage();
		echo($this->Template->process('main/index', array (), true));
		echo 'Compiler MEM:' . Tools::formatSize(memory_get_usage() - $int);

		echo Debug::write();


		// die("Startseite");
	}

}

?>