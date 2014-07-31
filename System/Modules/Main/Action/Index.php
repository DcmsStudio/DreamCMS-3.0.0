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
 * @package      Main
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Main_Action_Index extends Controller_Abstract
{

	/**
	 *
	 * Fixture for tests
	 *
	 * @param bool $isUpdate
	 * @return array (id, parent, data)
	 */
	public function getFixture ( $isUpdate = false )
	{

		$data = 'This is some dummy data for use in the \'tests\' \\\'.';
		if ( $isUpdate )
		{$data = '$isUpdate This is some dummy data for use in the \'tests\' \\\'.';
			return array (
				'id'     => 1,
				'parent' => 0,
				'text'   => $data
			);
		}

		return array (
			array (
				'id'     => 1,
				'parent' => 0,
				'text'   => $data
			),
			array (
				'id'     => 2,
				'parent' => 5,
				'text'   => $data
			)
		);
	}

	public function execute ()
	{/*
		$x3 = $this->db->insert('%tp%blocale')->values($this->getFixture(true))->values($this->getFixture());
		$x12 = $this->db->select('*')->where('b.id', 'BETWEEN', array(1, 26))->group_by('published', 'name')->order_by('published', 'DESC')->limit(1, 25);
		echo "<br/>select :\n" . $x12 . "\n\n<br/>insert:\n" . $x3 ;
		exit;

		  Library::enableErrorHandling();

		  $x4 = $this->db->select('COUNT(*) AS counted')->from(array('%tp%component'))->where('category', '=', 1)->compile($this->db); die($x4); exit;
		  try {


		  $x1 = $this->db->select('*')
		  ->from(array('%tp%locale', 'a'))
		  ->join(array('%tp%locale', 'b'), 'INNER')->on('a.id', '>', 'b.id')
		  ->where('b.id', '=', true)->and_where('a.id', '=', 1);
		  }
		  catch (Exception $e)
		  {
		  throw new BaseException();
		  }

		  $x11 = $this->db->select('id', 'title', 'name')->where('b.id', 'BETWEEN', array(1, 26))->group_by('published', 'name')->order_by('published', 'DESC')->limit(1, 25);

		  $x12 = $this->db->select('*')->where('b.id', 'BETWEEN', array(1, 26))->group_by('published', 'name')->order_by('published', 'DESC')->limit(1, 25);


		  $x2 = $this->db->update('%tp%blocale')->set($this->getFixture(true))->where('b.id', '=', true);
		  $x3 = $this->db->insert('%tp%blocale')->values($this->getFixture())->values($this->getFixture());

		  $x4 = $this->db->delete('%tp%blocale')->where('a.id', '=', 5);

		  $x4 = $this->db->select('COUNT(*) AS counted')->from(array('%tp%component'))->where('category', '=', 1);


		  echo "select:\n" . $x1 . "\n\n<br/>select 2:\n" . $x11 . "\n\n<br/>select 3:\n" . $x12 . "\n\n<br/>update:\n" . $x2 . "\n\n<br/>insert:\n" . $x3 . "\n\n<br/>insert:\n" . $x4;
		  exit;


		 */


		/*
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


		#$this->Template->process('test', array('condition' => 'joe'), true);
		#Library::addNavi('Start');
		#$this->load('Breadcrumb');
		#$this->Breadcrumb->add('Startseite');


		if ( $this->_post('getContentTrans') )
		{
			echo Library::json(array (
			                         'success' => true,
			                         'code'    => CONTENT_TRANS
			                   ));
			exit;
		}


		if ( $this->_post('getAuthKey') )
		{
			if ( User::isAdmin() )
			{
				$uiq = User::getUserUiqKey();
				Cookie::set('loginpermanet', $uiq, $GLOBALS[ 'SESSIONTIMEOUT' ]);
				Cookie::set('uhash', $uiq, $GLOBALS[ 'SESSIONTIMEOUT' ]);


				echo Library::json(array (
				                         'success' => true,
				                         'authKey' => $uiq
				                   ));
				exit;
			}
			else
			{
				Library::sendJson(false, trans('Die Anfrage wurde mangels Berechtigung des Clients nicht durchgeführt. @' . __LINE__));
			}
		}

		$name = $this->Router->getDocumentName(false);


		if ( $this->input('error') == '400' )
		{
			$this->Page->error(400, trans('Der Server konnte die Syntax der Anforderung nicht interpretieren.'));
		}
		else if ( $this->input('error') == '401' )
		{
			$this->Page->error(401, trans('Die Anfrage erfordert eine Authentifizierung.'));
		}
		else if ( $this->input('error') == '403' )
		{
			$this->Page->error(403, trans('Die Anfrage wurde mangels Berechtigung des Clients nicht durchgeführt.'));
		}
		else if ( $this->input('error') == '404' || ($name && REQUEST && !preg_match('#(/main/)#i', REQUEST) && !$this->Document->getDocumentID()) )
		{
			$this->Page->error(404, trans('Die von Ihnen aufgerufene Seite existiert.'));
		}
		else if ( $this->input('error') == '500' )
		{
			$this->Page->error(500, trans('Der Server kann die Anforderung aufgrund eines Fehlers nicht ausführen.'));
		}
		else
		{


			//$this->Document->setLayout('main-index');
			$this->Document->enableSiteCaching(3600);
			$this->Template->process('main/index', array (), true);
		}


		exit;
	}

}

?>