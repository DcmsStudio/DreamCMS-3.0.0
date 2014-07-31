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
 * @package      Menues
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Menues_Action_Index extends Controller_Abstract
{

	/**
	 * @var
	 */
	private $jsTree;

	/**
	 * @var
	 */
	private $showedChildItems;

	/**
	 * @var
	 */
	private $showedLevel;

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$data = array ();
		if ( IS_AJAX && $this->input('getmenu') )
		{
			$id   = (int)$this->input('id');
			$menu = $this->model->getMenuByID($id);

			$menus[ 'menuitems' ] = $this->model->getMenuItemsByMenuID(($menu[ 'menuitems' ] ? $menu[ 'menuitems' ] :
				0));
			$menus[ 'success' ]   = true;
			echo Library::json($menus);
			exit;
		}


		/**
		 * Overview Menüs
		 */
		$menues = $this->model->getMenus();

		$this->load('Usergroup');
		$data[ 'usergroups' ] = $this->Usergroup->getAllUsergroups();
		$pages                = $this->model->getPages();

		foreach ( $menues as $r )
		{
			$r[ 'menues' ] = $this->model->getMenuItemsByMenuID(($r[ 'menuitems' ] ? $r[ 'menuitems' ] : 0));

			foreach ( $r[ 'menues' ] as $idx => $row )
			{
				if ( $row[ 'usergroups' ] )
				{
					$r[ 'menues' ][ $idx ][ 'accesslist' ] = explode(',', $row[ 'usergroups' ]);
				}
				else
				{
					$r[ 'menues' ][ $idx ][ 'usergroups' ] = array ();
				}

				$_da            = array ();
				$_da            = $r[ 'menues' ][ $idx ];
				$_da[ 'pages' ] = $pages;
				//       $r[ 'menues' ][ $idx ][ 'parentpages' ] = $this->loadParentPages( $_da );


				if ( $row[ 'type' ] == 'spacer' || $row[ 'type' ] == 'megamenu' || $row[ 'type' ] == 'folder' )
				{
					$r[ 'menues' ][ $idx ][ 'pagetypehtml' ] = $this->model->loadMenuType($r[ 'menues' ][ $idx ]);
				}
			}


			$data[ 'menu' ][ ] = $r;
		}

		$data[ 'type' ]  = false;
		$data[ 'title' ] = null;

		$this->Template->process('menu/list_menues', $data, true);

		exit;


		if ( HTTP::input('do') || HTTP::input('operation') )
		{
			$this->load('Personal');
			$this->showedChildItems = $this->Personal->get("menuTreeChilds", 'show');
			$this->showedLevel      = $this->Personal->get("menuTreeChilds", 'level');


			if ( !is_array($this->showedChildItems) )
			{
				$this->showedChildItems = unserialize($this->showedChildItems);
			}
			else
			{
				$this->showedChildItems = array ();
			}


			$this->jsTree = new DocumentTree('%tp%page', array (
			                                                   "id"        => "id",
			                                                   "parentid"  => "parentid",
			                                                   "position"  => "ordering",
			                                                   "type"      => "type",
			                                                   "is_folder" => "is_folder"
			                                             ));


			if ( HTTP::input('do') === 'reconstruct' )
			{
				$this->reconstructMenu();
			}
			elseif ( HTTP::input('do') === 'analyze' )
			{
				$this->analyseMenuDatabase();
			}
			elseif ( HTTP::input('operation') !== '' )
			{

				/**
				 *
				 *
				 *
				 *
				 */
				$this->getOperator(HTTP::input('operation'));
			}


			throw new BaseException('Invalid request to build JsTree!');
		}

		$data = array ();
		$this->Template->process('menu/list_menuitems', $data, true);
	}

	/**
	 *
	 */
	private function reconstructMenu ()
	{

		$rest = $this->jsTree->_reconstruct();

		Cache::delete('fe_menucache');
		Cache::delete('ordered_menu');

		$result = $this->db->query('SELECT id, parentid FROM %tp%page WHERE breadcrumb = 1 AND pageid = ?', PAGEID)->fetchAll();
		foreach ( $result as $r )
		{
			$childs = $this->db->query('SELECT COUNT(id) AS total FROM %tp%page WHERE breadcrumb = 1 AND parentid = ?', $r[ 'id' ])->fetch();

			$isfolder = 0;
			if ( (int)$childs[ 'total' ] > 0 )
			{
				$isfolder = 1;
			}

			$menuactive = $this->db->query('SELECT COUNT(id) AS total FROM %tp%page WHERE breadcrumb = 1 AND mpublished = 1 AND parentid = ?', $r[ 'id' ])->fetch();
			$this->db->query('UPDATE %tp%page SET is_folder = ?, activesubitems = ? WHERE id = ?', $isfolder, (int)$menuactive[ 'total' ], $r[ 'id' ]);
		}

		die($rest);
	}

	/**
	 *
	 */
	private function analyseMenuDatabase ()
	{

		echo $this->jsTree->_analyze();
		die();
	}

	/**
	 *
	 * @param string $op
	 * @throws BaseException
	 */
	private function getOperator ( $op )
	{

		$htmlresult = null;

		if ( $op === 'get_childrenhtml' )
		{
			$htmlresult = $this->getTreeHtml(HTTP::input());
		}


		if ( (strpos("_", $op) !== 0 && method_exists($this->jsTree, $op)) || $htmlresult )
		{
			Cache::delete('fe_menucache');
			Cache::delete('ordered_menu');

			if ( $op !== 'get_childrenhtml' )
			{
				$htmlresult = $this->jsTree->{$op}(HTTP::input());
			}

			$sql    = 'SELECT id, parentid FROM %tp%page WHERE breadcrumb = 1 ORDER BY lft';
			$result = $this->db->query($sql)->fetchAll();
			foreach ( $result as $r )
			{
				$childs = $this->db->query('SELECT COUNT(id) AS total FROM %tp%page WHERE breadcrumb = 1 AND parentid = ' . $r[ 'id' ])->fetch();

				$isfolder = 0;
				if ( (int)$childs[ 'total' ] > 0 )
				{
					$isfolder = 1;
				}


				$menuactive = $this->db->query('SELECT COUNT(id) AS total FROM %tp%page WHERE breadcrumb = 1 AND mpublished = 1 AND parentid = ' . $r[ 'id' ])->fetch();


				$this->db->query('UPDATE %tp%page
                        SET is_folder = ?, activesubitems = ?
                        WHERE id = ?', $isfolder, (int)$menuactive[ 'total' ], $r[ 'id' ]);
			}


			header("HTTP/1.0 200 OK");
			if ( $op != 'get_childrenhtml' )
			{
				header('Content-type: text/json; charset=utf-8');
			}
			else
			{
				header('Content-Type: text/html; charset=UTF-8');

				$htmlresult = Strings::fixLatin($htmlresult);
			}

			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Pragma: no-cache");

			echo $htmlresult;


			exit;
		}
		else
		{
			throw new BaseException('Invalid request operator to build JsTree!');
		}
	}

	/**
	 *
	 * @param array $data
	 * @return string html code
	 */
	public function getTreeHtml ( $data )
	{

		$tmp = $this->jsTree->_get_children((int)$data[ "id" ]);
		if ( (int)$data[ "id" ] === 1 && count($tmp) === 0 )
		{
			$this->jsTree->_create_default();
			$tmp = $this->jsTree->_get_children((int)$data[ "id" ]);
		}


		$_ids = array ();
		foreach ( $tmp as $k => $v )
		{
			$_ids[ ] = $k;
		}


		/**
		 * language select
		 */
		$transq1 = $this->buildTransWhere('page', 'p.id', 'pt');

		$sql = "SELECT p.id, pt.iscorelang, pt.lang, pt.title FROM %tp%page AS p
                LEFT JOIN %tp%page_trans AS pt ON (pt.id=p.id) 
                WHERE p.id IN(" . implode(',', $_ids) . ") AND p.pageid = ? AND " . $transq1 . " GROUP BY p.id";

		$translations = $this->db->query($sql, PAGEID)->fetchAll();
		foreach ( $translations as $r )
		{

			$tmp[ $r[ 'id' ] ][ 'transtitle' ] = $r[ 'title' ];
			$tmp[ $r[ 'id' ] ][ 'iscorelang' ] = $r[ 'iscorelang' ];
			$tmp[ $r[ 'id' ] ][ 'lang' ]       = $r[ 'lang' ];
		}


		$result = array ();
		if ( (int)$data[ "id" ] === 0 )
		{
			return '';
		}

		$wdel     = trans('Website löschen');
		$del      = trans('Menüpunkt %s löschen');
		$pubt     = trans('Menüpunkt %s aktivieren/deaktivieren');
		$et       = trans('Menüpunkt %s Bearbeiten');
		$insafter = trans('Neuen Menüpunkt danach %s einfügen');
		$inschild = trans('Neuen Untermenüpunkt einfügen %s');

		$html       = '';
		$ul_start   = false;
		$lastparent = 0;
		foreach ( $tmp as $k => $v )
		{
			$css = '';
			if ( !$v[ 'mpublished' ] )
			{
				$css = 'disabled';
			}


			if ( empty($v[ "type" ]) )
			{
				$v[ "type" ] = 'page';
			}


			$_unTransCss = '';
			if ( empty($v[ 'iscorelang' ]) && $v[ "lang" ] != CONTENT_TRANS )
			{
				$_unTransCss = 'red';
			}
			else
			{
				$v[ "title" ] = $v[ "transtitle" ];
			}


			$v[ "title" ] = utf8_convert_encoding($v[ "title" ], 'utf-8');

			$parentid = $v[ $this->jsTree->fields[ "parentid" ] ];
			$pubstate = $v[ 'published' ];
			$pub      = $v[ 'mpublished' ] ? 'online.gif' : 'offline.gif';

			$_del      = sprintf($del, $v[ "title" ]);
			$_pubt     = sprintf($pubt, $v[ "title" ]);
			$_et       = sprintf($et, $v[ "title" ]);
			$_insafter = sprintf($insafter, $v[ "title" ]);
			$_inschild = sprintf($inschild, $v[ "title" ]);


			$rgt = $v[ 'rgt' ];
			$lft = $v[ 'lft' ];

			if ( $v[ 'type' ] != 'rootpage' )
			{
				$btns = <<<EOF
           <span onclick="changePublish('pub{$k}', 'admin.php?adm=menues&action=publish&id={$k}&published={$pubstate}')"><img src="html/style/default/img/{$pub}" width="16" height="16" title="{$_pubt}" alt="" id="pub{$k}"/></span>
           <span rel="admin.php?adm=menues&action=edit&id={$k}"><img src="html/style/default/img/edit.png" alt="" title="{$_et}" /></span>
           <span rel="admin.php?adm=menues&action=index&parent={$k}"><img src="html/style/default/img/tree/document-insert.png" alt="" title="{$_inschild}"/></span>
           <span rel="admin.php?adm=menues&action=index&parent={$parentid}&after={$k}"><img src="html/style/default/img/tree/document-insert-after.png" alt="" title="{$_insafter}" /></span>
           <span class="delconfirm ajax" rel="admin.php?adm=menues&action=delete&operation=remove_node&id={$k}&lft={$lft}&rgt={$rgt}"><img src="html/style/default/img/delete.gif" alt="" title="{$_del}" /></span>
EOF;

				//
			}
			else
			{
				$btns = <<<EOF
           <span onclick="changePublish('pub{$k}', 'admin.php?adm=menues&action=publish&id={$k}&published={$pubstate}')"><img src="html/style/default/img/{$pub}" width="16" height="16" title="{$_pubt}" alt="" id="pub{$k}"/></span>
           <span rel="admin.php?adm=menues&action=edit&id={$k}"><img src="html/style/default/img/edit.png" alt="" title="{$_et}" /></span>
           <span class="disabled" rel="javascript:void(0)"><img src="html/style/default/img/tree/document-insert.png" alt="" title=""/></span>
           <span class="disabled" rel="javascript:void(0)"><img src="html/style/default/img/tree/document-insert-after.png" alt="" title="" /></span>
           <span class="delconfirm ajax site disabled" rel="javascript:void(0)"><img src="html/style/default/img/delete.gif" alt="" title="{$wdel}" /></span>
EOF;
			}


			$title = $v[ "title" ];
			$state = ((int)$v[ $this->jsTree->fields[ "rgt" ] ] - (int)$v[ $this->jsTree->fields[ "lft" ] ] > 1) ?
				"closed" : "";
			$rel   = $v[ "type" ];


			if ( $v[ 'is_folder' ] > 0 )
			{
				$css .= ' folder jstree-closed';
			}

			if ( $v[ 'type' ] == 'rootpage' )
			{

				if ( $ul_start )
				{
					$ul_start = false;
					#$html .= '</ul></li>';
				}

				$html .= <<<EOF
    <li class="{$state}{$css}" rel="{$rel}" id="node_{$k}">
EOF;


				$ul_start = true;
				$html .= <<<EOF
        <div class="secondrow">
            <span style="float:left">
                <a href="javascript:void(0)" class="{$_unTransCss}">{$title}</a>
            </span>
            <span class="tree-buttons" style="float:right">{$btns}</span>
        </div>
</li>
EOF;
			}
			else
			{
				$html .= <<<EOF
    <li class="{$state}{$css}" rel="{$rel}" id="node_{$k}">
EOF;

				$html .= <<<EOF
        <div class="secondrow">
            <span style="float:left">
            <a href="javascript:void(0)" class="{$_unTransCss}">{$title}</a>
            </span>
            <span class="tree-buttons" style="float:right">{$btns}</span>
        </div>
EOF;
				$html .= '</li>';
			}
		}

		return $html;
	}

}

?>