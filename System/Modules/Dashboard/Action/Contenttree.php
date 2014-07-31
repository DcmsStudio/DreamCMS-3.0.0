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
 * @file         Contenttree.php
 */
class Dashboard_Action_Contenttree extends Controller_Abstract
{

	/**
	 * @var
	 */
	protected $_appid;

	/**
	 * @var
	 */
	protected $_catid;

	/**
	 * @var
	 */
	protected $_modul;

	/**
	 * @var int
	 */
	protected $_level = 0;

	/**
	 * @var
	 */
	protected $_nodetable;

	/**
	 * @var
	 */
	protected $opentree;

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			if ( HTTP::input('refresh') )
			{
				Cache::delete('pages-tree', 'data');
			}

			$modules = $this->getTreeData();
			Ajax::Send(true, array (
			                       'modules' => $modules
			                 ));
			exit;
		}
	}

	public function getTreeData(  ) {

		$modules = $this->getModules();


		//print_r($modules);exit;

		$modul   = strtolower(HTTP::input('modul'));
		$modulid = (int)HTTP::input('modulid');
		$level   = (int)HTTP::input('level');
		$appid   = (int)HTTP::input('appid');
		$catid   = ($this->input('catid') ? (int)$this->input('catid') : null);

		$appmodules_cats        = null;
		$appmodules_items       = null;
		$activeappmodules_cats  = null;
		$activeappmodules_items = null;

		$active_pages_context = Cookie::get('active_pages_context');


		$this->appid  = $appid;
		$this->catid  = $catid;
		$this->modul  = strtolower($modul);
		$this->_level = $level;


		//$opentree = DCMS_adminoptions::GetOption('active_pages_context', 'opened', '');

		if ( $opentree === null || $opentree == '' )
		{
			$this->opentree = array ();
		}
		else
		{
			$this->opentree = $opentree;
		}

		if ( HTTP::input('removeNode') )
		{
			$this->removeFromOpenTree();
			Library::sendJson(true);
		}
		else
		{
			$this->addToOpenTree();
		}


		foreach ( $modules as $idx => $r )
		{
			$r[ 'module' ] = strtolower($r[ 'module' ]);

			if ( $r[ 'module' ] == 'apps' || $r[ 'module' ] == 'appscat' || $r[ 'module' ] == 'appsitem' )
			{
				unset($modules[ $idx ]);
				continue;
			}


			if ( $modul && $modul != $r[ 'module' ] )
			{
				unset($modules[ $idx ]);
				continue;
			}


			$reg = $this->getApplication()->getModulRegistry($modul);

			if ( !$reg )
			{
				continue;
			}


			$treeactions  = isset($reg[ 'definition' ][ 'treeactions' ]) && is_array($reg[ 'definition' ][ 'treeactions' ]) ?
				$reg[ 'definition' ][ 'treeactions' ] : array ();
			$modulactions = isset($reg[ 'definition' ][ 'modulactions' ]) && is_array($reg[ 'definition' ][ 'modulactions' ]) ?
				$reg[ 'definition' ][ 'modulactions' ] : array ();
			$tables       = isset($reg[ 'definition' ][ 'metatables' ]) && is_array($reg[ 'definition' ][ 'metatables' ]) ?
				$reg[ 'definition' ][ 'metatables' ] : array ();


			if ( is_array($modulactions) )
			{
				$modules[ $idx ][ 'actions' ] = $this->buildTreeActions($modulactions, $r[ 'mid' ], null, 0);
			}


			if ( $modul )
			{
				if ( is_array($tables) )
				{
					foreach ( $tables as $table => $rs )
					{
						if ( strpos($table, '_cat') !== false )
						{
							$modules[ $idx ][ 'isapp' ] = 0;
							$categories                 = $this->loadCategories($table, $rs[ 'primarykey' ], false, $catid, $r[ 'module' ]);

							foreach ( $categories as $i => $c )
							{
								$categories[ $i ][ 'locked' ] = $c[ 'locked' ];
								$categories[ $i ][ 'module' ] = $r[ 'module' ];
								$categories[ $i ][ 'id' ]     = 'm' . $r[ 'mid' ] . '_c' . $c[ 'id' ];
								$categories[ $i ][ 'catid' ]  = $c[ $rs[ 'primarykey' ] ];
								$categories[ $i ][ 'mid' ]    = $r[ 'mid' ];

								$categories[ $i ][ 'actions' ] = $this->buildTreeActions($treeactions[ $table ], $c[ $rs[ 'primarykey' ] ], null, 0);

								if ( $catid != $c[ 'parentid' ] )
								{
									unset($categories[ $i ]);
								}
							}

							$modules[ $idx ][ 'categories' ] = $categories;
						}

						if ( strpos($table, '_cat') === false )
						{

							$items = $this->loadContents($table, $rs[ 'primarykey' ], false, $catid, $r[ 'module' ]);
							foreach ( $items as $i => $c )
							{
								$items[ $i ][ 'locked' ]    = $c[ 'locked' ];
								$items[ $i ][ 'level' ]     = $level + 1;
								$items[ $i ][ 'catid' ]     = $catid;
								$items[ $i ][ 'mid' ]       = $r[ 'mid' ];
								$items[ $i ][ 'contentid' ] = $c[ 'id' ];
								$items[ $i ][ 'module' ]    = $r[ 'module' ];

								#print_r($treeactions[ $table ]);
								#echo $table;
								# exit;

								$items[ $i ][ 'actions' ] = $this->buildTreeActions($treeactions[ $table ], $c[ 'id' ], $catid, 0);
							}

							$modules[ $idx ][ 'items' ] = $items;
						}
					}
				}
			}
			else
			{

			}
		}


		$applications = array ();
		if ( is_array($appmodules_cats) )
		{
			foreach ( $appmodules_cats as $idx => $rows )
			{
				$tmp = $appmodules_cats[ $idx ];

				if ( is_array($appmodules_items[ $idx ]) )
				{
					$tmp[ 'items' ] = $appmodules_items[ $idx ][ 'items' ];
				}

				$applications[ ] = $tmp;
			}
		}

		return array_merge($modules, $applications);
	}

	private function addToOpenTree ()
	{

		$this->opentree[ $this->modul ][ ] = array (
			$this->appid,
			$this->catid
		);
		# DCMS_adminoptions::SetOption('active_pages_context', 'opened', $this->opentree);
	}

	private function removeFromOpenTree ()
	{

		if ( isset($this->opentree[ $this->modul ]) )
		{
			foreach ( $this->opentree[ $this->modul ] as $idx => $row )
			{
				if ( $row[ 0 ] == $this->appid && $row[ 1 ] == $this->catid )
				{
					unset($this->opentree[ $this->modul ][ $idx ]);
				}
			}
		}

		#  DCMS_adminoptions::SetOption('active_pages_context', 'opened', $this->opentree);
	}

	/**
	 * get all content modules
	 *
	 * @return array
	 */
	private function getModules ()
	{

		$modules = $this->getApplication()->loadFrontendModules();

		foreach ( $modules as $idx => $r )
		{
			if ( $r[ 'module' ] === 'apps' )
			{
				unset($modules[ $idx ]);
				continue;
			}


			if ( empty($r[ 'metatables' ]) || !$r[ 'allowmetadata' ] || !trim($r[ 'treeactions' ]) )
			{
				unset($modules[ $idx ]);
				continue;
			}

			$rs                   = array ();
			$rs[ 'is_folder' ]    = 1;
			$rs[ 'level' ]        = 0;
			$rs[ 'mid' ]          = $r[ 'id' ];
			$rs[ 'id' ]           = strtolower($r[ 'module' ]) . '_' . $r[ 'id' ];
			$rs[ 'metatables' ]   = $r[ 'metatables' ];
			$rs[ 'published' ]    = $r[ 'published' ];
			$rs[ 'treeactions' ]  = $r[ 'treeactions' ];
			$rs[ 'modulactions' ] = $r[ 'modulactions' ];
			$rs[ 'module' ]       = strtolower($r[ 'module' ]);
			$rs[ 'name' ]         = $r[ 'modulelabel' ];

			$modules[ $idx ] = $rs;
		}

		return $modules;
	}

	/**
	 *
	 * @param array   $actions
	 * @param integer $id
	 * @param integer $catid default is null
	 * @param integer $appid
	 * @param string  $modul
	 * @return array
	 */
	function buildTreeActions ( $actions, $id, $catid = null, $appid = 0, $modul = '' )
	{

		foreach ( $actions as $k => $v )
		{
			if ( $k == 'lockunlock_data' && is_array($v) )
			{
				continue;
			}

			if ( $k == 'mod-publish' && !$appid && $id )
			{
				$v .= '&id=%s';
			}

			$_id = $id;

			if ( $k == 'edit-item' )
			{
				$_id = $id;
			}
			else if ( $k == 'add-item' )
			{
				$_id = $catid;
			}
			else if ( $k == 'add-cat' || $k == 'edit-cat' )
			{
				$_id = $catid;
			}


			$_v            = sprintf($v, (string)$_id);
			$_v            = str_replace('#tp#', '%tp%', $_v);
			$actions[ $k ] = $_v . ($appid > 0 ? '&appid=' . $appid : '');
		}

		return $actions;
	}

	/**
	 *
	 * @param array $actions
	 * @return array
	 */
	function buildTreeActionLabels ( $actions )
	{

		foreach ( $actions as $k => $v )
		{
			$actions[ $k ][ 'label' ] = ''; //sprintf($v, $id) . ( $appid > 0 ? '&appid=' . $appid : '');
		}

		return $actions;
	}

	/**
	 *
	 * @param string $apptype
	 * @return string
	 */
	private function getAppIcon ( $apptype )
	{

		return HTML_URL . 'img/apps/' . $apptype . '-small.png';
	}

	/**
	 * @param int   $parentid
	 * @param       $primarykey
	 * @param       $cats
	 * @param array $arr
	 * @return array
	 */
	private function getAllParentCategories ( $parentid = 0, $primarykey, $cats, $arr = array () )
	{

		foreach ( $cats as $r )
		{
			if ( $r[ 'id' ] == $parentid )
			{
				$arr[ ] = $r;
				$arr    = $this->getAllParentCategories($r[ 'parentid' ], $primarykey, $cats, $arr);

				#$arr = array_reverse($arr);
			}
		}

		return $arr;
	}

	/**
	 * @param int   $id
	 * @param       $primarykey
	 * @param       $cats
	 * @param array $arr
	 * @return array
	 */
	private function getAllChildCategories ( $id = 0, $primarykey, $cats, $arr = array () )
	{

		foreach ( $cats as $r )
		{
			if ( $r[ 'parentid' ] == $id )
			{
				$arr[ ] = $r;
				$arr    = $this->getAllChildCategories($r[ 'id' ], $primarykey, $cats, $arr);
			}
		}

		return $arr;
	}

	/**
	 * @param        $table
	 * @param        $primarykey
	 * @param bool   $isapp
	 * @param null   $catid
	 * @param string $modul
	 * @return array
	 */
	private function loadCategories ( $table, $primarykey, $isapp = false, $catid = null, $modul = '' )
	{

		$table = '%tp%' . $table;
		$ret   = array ();
		$cats  = array ();

		$existsDraft  = $this->db->fieldExists($table, 'draft');
		$existsLocked = $this->db->fieldExists($table, 'locked');

		if ( $this->db->tbl_exists($table . '_trans') )
		{
			$exists = $this->db->fieldExists($table, 'ordering');

			$transq2 = $this->buildTransWhere($table, 't1.' . $primarykey, 't2');
			if ( $isapp !== false )
			{


				$cats = $this->db->query('SELECT
                                            t1.' . $primarykey . ',
                                            t1.parentid,
                                            t1.published AS published,
                                            t2.title AS name,
                                            a.apptype,
                                            ' . ($existsLocked ? 't1.locked' : 'l.contentid') . ' AS locked
                                         FROM ' . $table . ' AS t1
                                         LEFT JOIN ' . $table . '_trans AS t2 ON(t2.' . $primarykey . ' = t1.' . $primarykey . ')
                                         LEFT JOIN %tp%applications AS a ON(a.appid = t1.appid)
                                         LEFT JOIN %tp%contentlock AS l ON(l.contentid = t1.' . $primarykey . ' AND modul = ?)
                                         WHERE
                                            t1.appid = ? AND ' . $transq2 . '
                                         GROUP BY t1.' . $primarykey . ' 
                                         ORDER BY ' . ($exists ? ' t1.parentid ASC, t1.ordering ASC' :
						't1.parentid ASC, name ASC'), $modul, $isapp)->fetchAll();
			}
			else
			{
				$cats = $this->db->query('SELECT
                                            t1.' . $primarykey . ',
                                            t1.parentid,
                                            t1.published AS published,
                                            t2.title AS name,
                                            ' . ($existsLocked ? 't1.locked' : 'l.contentid') . ' AS locked
                                          FROM ' . $table . ' AS t1
                                          LEFT JOIN ' . $table . '_trans AS t2 ON(t2.' . $primarykey . ' = t1.' . $primarykey . ')
                                          LEFT JOIN %tp%contentlock AS l ON(l.contentid = t1.' . $primarykey . ' AND modul = ?)
                                          WHERE
                                            ' . $transq2 . '
                                          GROUP BY t1.' . $primarykey . ' 
                                          ORDER BY ' . ($exists ? 't1.parentid ASC, t1.ordering ASC' :
						't1.parentid ASC, name ASC'), $modul)->fetchAll();
			}
		}
		else
		{
			$exists = $this->db->fieldExists($table, 'ordering');

			$cats = $this->db->query('SELECT
                                            t1.' . $primarykey . ',
                                            t1.parentid,
                                            t2.published,
                                            t1.title AS name,
                                            ' . ($existsLocked ? 't1.locked' : 'l.contentid') . ' AS locked
                                      FROM ' . $table . ' AS t1
                                      LEFT JOIN %tp%contentlock AS l ON(l.contentid = t1.' . $primarykey . ' AND modul = ?)
                                      WHERE t1.lang = ?
                                      GROUP BY t1.' . $primarykey . ' 
                                      ORDER BY' . ($exists ? ' t1.ordering ASC' :
					' name ASC'), $modul, CONTENT_TRANS)->fetchAll();
		}


		$cat_tree = array ();


		if ( is_array($cats) )
		{

			$tree = new Tree();
			$tree->setupData($cats, $primarykey, 'parentid');
			$cat_tree = $tree->buildRecurseArray();

			foreach ( $cat_tree as $idx => $r )
			{

				if ( $r[ 'parentid' ] != $catid && $catid !== null )
				{
					unset($cat_tree[ $idx ]);
					continue;
				}

				$cat_tree[ $idx ][ 'is_folder' ] = 1;
				$cat_tree[ $idx ][ 'level' ]     = $r[ 'level' ] + 1;
			}


			unset($cats);
		}
		else
		{
			$cat_tree = $cats;
			foreach ( $cat_tree as $idx => $r )
			{
				if ( $r[ 'parentid' ] != $catid && $catid !== null )
				{
					unset($cat_tree[ $idx ]);
					continue;
				}
				$cat_tree[ $idx ][ 'level' ] = 1;
			}

			unset($cats);
		}

		if ( $exists )
		{
			usort($cat_tree, "Dashboard_Action_Contenttree::sortCats");
		}

		return $cat_tree;
	}

	/**
	 * @param $a
	 * @param $b
	 * @return int
	 */
	public function sortCats ( $a, $b )
	{

		$a = $a[ 'ordering' ];
		$b = $b[ 'ordering' ];


		return ($a > $b) ? -1 : 1;
	}

	/**
	 * get all pages from a modul for Ajax request
	 *
	 * @param string   $table
	 * @param string   $primarykey
	 * @param bool|int $isapp
	 * @param integer  $catid
	 * @param string   $modul
	 * @return array
	 */
	private function loadContents ( $table, $primarykey, $isapp = false, $catid = 0, $modul = '' )
	{

		$table = '%tp%' . $table;


		$catkey = 'catid';
		switch ( $this->modul )
		{
			case 'apps':
				$catkey = 'catid';
				break;
			case 'news':
				$catkey = 'cat_id';
				break;
		}

		$existsDraft  = $this->db->fieldExists($table, 'draft');
		$existsLocked = $this->db->fieldExists($table, 'locked');

		$items = array ();
		if ( $this->db->tbl_exists($table . '_trans') )
		{

			$addDraftField = 't1.draft AS draft,';
			$transq2       = $this->buildTransWhere($table, 't1.' . $primarykey, 't2');

			$exists = $this->db->fieldExists($table, 'isindexpage');

			if ( $isapp !== false )
			{
				$items = $this->db->query('SELECT
                                                t1.' . $primarykey . ' AS id,
                                                \'page\' AS typename,
                                                a.apptype,
                                                0 AS is_folder,
                                                ' . ($this->_level + 2) . ' AS level,
                                           ' . $addDraftField . '
                                           /* IF(t2.title != \'\', 0, 1 ) AS lngerr, */
                                           t1.published AS published,
                                           t2.title  AS name' . ($exists ? ', t1.isindexpage ' : '') . ',
                                           ' . ($existsLocked ? 't1.locked' : 'l.contentid') . ' AS locked
                                           FROM ' . $table . ' AS t1
                                           LEFT JOIN ' . $table . '_trans AS t2 ON(t2.' . $primarykey . ' = t1.' . $primarykey . ')
                                           LEFT JOIN %tp%applications AS a ON(a.appid = t1.appid)
                                           LEFT JOIN %tp%contentlock AS l ON(l.contentid = t1.' . $primarykey . ' AND modul = ?)
                                           WHERE
                                                t1.appid = ? AND
                                                t1.' . $catkey . ' = ? AND 
                                                ' . $transq2 . '
                                           GROUP BY t1.' . $primarykey . '
                                           ORDER BY name', $modul, $isapp, $catid)->fetchAll();
			}
			else
			{
				if ( $catid )
				{
					$items = $this->db->query('SELECT t1.' . $primarykey . ' AS id,
                                                \'page\' AS typename,
                                                0 AS is_folder,
                                                ' . ($this->_level + 1) . ' AS level,
                                                ' . $addDraftField . '
                                                t1.published AS published,
                                                t2.title  AS name' . ($exists ? ', t1.isindexpage ' : '') . ',
                                           ' . ($existsLocked ? 't1.locked' : 'l.contentid') . ' AS locked
                                           FROM ' . $table . ' AS t1
                                           LEFT JOIN ' . $table . '_trans AS t2 ON(t2.' . $primarykey . ' = t1.' . $primarykey . ' )
                                               LEFT JOIN %tp%contentlock AS l ON(l.contentid = t1.' . $primarykey . ' AND modul = ?)
                                           WHERE t1.' . $catkey . ' = ? AND 
                                                ' . $transq2 . '
                                           GROUP BY t1.' . $primarykey . '
                                           ORDER BY name', $modul, $catid)->fetchAll();
				}
				else if ( !$catid && preg_match('/pages/', $table) )
				{
					$items = $this->db->query('SELECT t1.' . $primarykey . ' AS id,
                                                \'page\' AS typename,
                                                0 AS is_folder,
                                                ' . ($this->_level + 1) . ' AS level,
                                                ' . $addDraftField . '
                                                t1.published AS published,
                                                t2.title AS name' . ($exists ? ', t1.isindexpage ' : '') . ',
                                           ' . ($existsLocked ? 't1.locked' : 'l.contentid') . ' AS locked
                                           FROM ' . $table . ' AS t1
                                           LEFT JOIN ' . $table . '_trans AS t2 ON(t2.' . $primarykey . ' = t1.' . $primarykey . ' )
                                           LEFT JOIN %tp%contentlock AS l ON(l.contentid = t1.' . $primarykey . ' AND modul = ?)
                                           WHERE t1.' . $catkey . ' = ? AND ' . $transq2 . '
                                           GROUP BY t1.' . $primarykey . '
                                           ORDER BY name', $modul, $catid)->fetchAll();
				}
			}
		}
		else
		{
			$items = $this->db->query('SELECT t1.' . $primarykey . ' AS id,
                                            \'page\' AS typename,
                                            0 AS is_folder,
                                            t1.published,
                                            ' . ($this->_level + 1) . ' AS level,
                                            t1.title AS name,
                                       ' . ($existsLocked ? 't1.locked' : 'l.contentid') . ' AS locked
                                       FROM ' . $table . ' AS t1
                                       LEFT JOIN %tp%contentlock AS l ON(l.contentid = t1.' . $primarykey . ' AND modul = ?)
                                       WHERE t1.lang = ?
                                       t1.' . $catkey . ' = ?
                                       GROUP BY t1.' . $primarykey . ' 
                                       ORDER BY t1.title ASC', $modul, CONTENT_TRANS, $catid)->fetchAll();
		}

		return $items;
	}

}
