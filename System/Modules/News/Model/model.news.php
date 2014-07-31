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
 * @package      News
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         model.news.php
 */
class NewsModel extends Model
{

	/**
	 * This variable specifies that this module does have blocks that can be added to the website on the site layout page of the control panel
	 *
	 * @var boolean
	 */
	public $hasBlocks = true;

	/**
	 * This variable specifies that this module has a layout that can be changed on the site layout page.
	 *
	 * @var boolean
	 */
	public $hasLayout = true;

	/**
	 * @var bool
	 */
	public $skipFindIndexByAlias = true;

	/**
	 * @var array
	 */
	public $layoutBlocks = array ();

	/**
	 * Used in Class SystemManager
	 *
	 * @return array
	 */
	public function getModulDefinition ()
	{

		return array (
			'modulelabel'       => trans('Nachrichten'),
			'allowmetadata'     => true,
			'moduledescription' => null,
			'version'           => '0.2',
			'metatables'        => array (
				'news'            => array (
					'controller' => 'news',
					'action'     => 'item',
					'primarykey' => 'id',
					'type'       => 'contents'
				),
				'news_categories' => array (
					'controller' => 'news',
					'action'     => 'index',
					'primarykey' => 'id',
					'type'       => 'categories'
				),
			),
			'modulactions'      => array (
				'edit-item'   => 'adm=news&action=edit_news&id=%s',
				'add-cat'     => 'adm=news&action=edit_cats',
				'mod-publish' => 'adm=modules&action=publish',
			),
			'treeactions'       => array (
				'news'            => array (
					'edit-item' => 'adm=news&action=edit_news&id=%s',
					'add-item'  => 'adm=news&action=add&catid=%s',
					'publish'   => 'adm=news&action=publish&id=%s'
				),
				'news_categories' => array (
					'add-item' => 'adm=news&action=add&catid=%s',
					'edit-cat' => 'adm=news&action=edit_cats&cat_id=%s',
					'add-cat'  => 'adm=news&action=edit_cats',
					'publish'  => 'adm=news&action=catpublish&cat_id=%s'
				),
			)
		);
	}

	/**
	 * This function returns the modul label/title
	 *
	 * @return string
	 */
	public function getModulLabel ()
	{

		$def = $this->getModulDefinition();

		return $def[ 'modulelabel' ];
	}

	/**
	 * This function returns the array of module blocks with any modifications or additions that need to be made.
	 *
	 * @return array The array of blocks for the site layout page in the control panel
	 */
	public function getLayoutBlocks ()
	{

		/**
		 * This is a list of the blocks that can be dragged onto the site layout page.
		 *
		 * @var unknown_type
		 */
		$this->layoutBlocks = array (
			array (
				'id'            => 'getCategories',
				'name'          => trans('News Kategorien'),
				'icon'          => 'categorie-text.png',
				'layouter-form' => '',
			),
			array (
				'id'            => 'getTopNews',
				'name'          => trans('Top News'),
				'icon'          => 'thumb-up.png',
				'layouter-form' => 'topnews',
				'params'        => array (
					'limit'    => 5,
					'order'    => 'hits',
					'template' => 'topnews'
				)
			),
			array (
				'id'            => 'getTopRatedNews',
				'name'          => trans('Top Bewertete News'),
				'icon'          => 'star-half.png',
				'layouter-form' => 'topnews',
				'params'        => array (
					'limit'    => 5,
					'template' => 'topnews'
				)
			),
			array (
				'id'            => 'getRecentNews',
				'name'          => trans('Aktuelle News'),
				'icon'          => 'new-text.png',
				'layouter-form' => '',
				'params'        => array (
					'limit'    => 5,
					'template' => 'topnews'
				)
			),
			array (
				'id'            => 'getNewsTags',
				'name'          => trans('News Tags'),
				'icon'          => 'tags-label.png',
				'layouter-form' => 'newstags',
				'params'        => array (
					'order'    => 'rand',
					'template' => 'tags'
				)
			)
		);


		$blocks = $this->layoutBlocks;
		foreach ( $blocks as $key => $block )
		{
			//$blocks[$key]['icon'] = IWP_MODULES_URI . '/' . $this->moduleName . '/images/' . $blocks[$key]['icon'];
			//$blocks[$key]['name'] = $blocks[$key]['name'];
		}

		return $blocks;
	}

	/**
	 *
	 * @return array
	 */
	public function getTranslateFields ()
	{

		return array (
			'news'            => array (
				'id'    => array (
					'type'      => 'int',
					'length'    => 10,
					'default'   => 0,
					'index'     => true,
					'isprimary' => true
				),
				'title' => array (
					'type'   => 'varchar',
					'length' => 200
				),
				'text'  => array (
					'type' => 'text'
				)
			),
			'news_categories' => array (
				'id'           => array (
					'type'      => 'int',
					'length'    => 10,
					'default'   => 0,
					'index'     => true,
					'isprimary' => true
				),
				'title'        => array (
					'type'   => 'varchar',
					'length' => 200
				),
				'description'  => array (
					'type' => 'text'
				),
				'teaserheader' => array (
					'type'   => 'varchar',
					'length' => 250
				),
			)
		);
	}

	/**
	 *
	 * @param integer $limit
	 * @return string
	 */
	public function getRecentNews ( $limit = 5 )
	{

		return $this->getTopNews('created', $limit);
	}

	/**
	 *
	 * @param string|\type $order
	 * @param integer      $limit
	 * @return string
	 */
	public function getTopRatedNews ( $order = 'rating', $limit = 5 )
	{

		return $this->getTopNews('rating', $limit);
	}

	/**
	 *
	 * @param string|\type $order
	 * @param integer      $limit
	 * @return string
	 */
	public function getTopNews ( $order = '', $limit = 5 )
	{

		$time = time();

		$order    = $this->getModulParam('order');
		$limit    = $this->getModulParam('limit', 5);
		$template = $this->getModulParam('template');

		$where = "((n.publish_off>=? OR n.publish_off=0) AND (n.publish_on=0 OR n.publish_on <= ?))
                    AND n.usergroups IN(0, ?) AND n.pageid = ?";

		switch ( $order )
		{
			default:
			case 'hits':
				$order = 'n.hits DESC';
				break;

			case 'rating':
				$order = 'n.rating DESC';
				break;

			case 'created':
				$order = 'n.created DESC';
				break;
		}


		$returns = Cache::get('getTopNews-' . md5($order . $limit . PAGEID . User::getGroupId()));
		if ( !$returns )
		{
			$transq1 = $this->buildTransWhere('news', 'n.id', 'nt');
			$transq2 = $this->buildTransWhere('news_categories', 'c.id', 'ct');

			$sql = "SELECT n.*, nt.title, nt.alias, nt.suffix, ct.title AS category,
                (SELECT COUNT(*) FROM %tp%comments WHERE post_id = n.id AND modul='news') AS comments,
				u.username AS author,
				u1.username AS modifauthor
                FROM %tp%news AS n
                LEFT JOIN %tp%news_categories AS c ON(c.id=n.cat_id)
                LEFT JOIN %tp%news_trans AS nt ON(nt.id=n.id AND nt.draft = 0)
                LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id=c.id)
                LEFT JOIN %tp%users AS u ON (u.userid = n.created_by)
                LEFT JOIN %tp%users AS u1 ON (u1.userid = n.modifed_by)
		    WHERE
                " . $where . ' AND ' . $transq1 . ' AND ' . $transq2 . " AND c.published=1
            GROUP BY n.id
	 		ORDER BY {$order} LIMIT {$limit}";

			$returns = $this->db->query($sql, $time, $time, User::getGroupId(), PAGEID)->fetchAll();
			Cache::write('getTopNews-' . md5($order . $limit . PAGEID . User::getGroupId()), $returns);
		}


		$data = array ();
		foreach ( $returns as $r )
		{
			$r[ 'rewrite' ]       = ($r[ 'alias' ] ? $r[ 'alias' ] . '.' . $r[ 'suffix' ] : $r[ 'id' ]);
			$r[ 'url' ]           = 'news/item/' . $r[ 'rewrite' ];
			$data[ 'topnews' ][ ] = $r;
		}

		$data[ 'blockdata' ] = $this->getModulParam('attributes');


		return $this->Template->process('cms/' . $template, $data);
	}

	/**
	 *
	 */
	public function getData ()
	{

		$cat_id     = (int)HTTP::input('cat_id');
		$max_levels = 20;
		$cats       = $this->getCategories();

		$arr = array (
			''               => '---------------------------',
			'online'         => 'Online News',
			'offline'        => 'Offline News',
			'archived'       => 'Archivierte News',
			'draft'          => 'in Bearbeitung',
			'online_offline' => 'Online &amp; Offline News',
		);


		$states = array ();
		foreach ( $arr as $k => $v )
		{
			$states[ $k ] = $v;
		}

		$tmp = array ();
		foreach ( $cats as $r )
		{
			$tmp[ $r[ 'catid' ] ] = $r[ 'title' ];
		}

		$this->load('Grid');
		$this->Grid->initGrid('news', 'id', 'date', 'desc');

		$this->Grid->addFilter(array (
		                             array (
			                             'name'  => 'q',
			                             'type'  => 'input',
			                             'value' => '',
			                             'label' => 'Suchen nach',
			                             'show'  => true,
			                             'parms' => array (
				                             'size' => '40'
			                             )
		                             ),
		                             array (
			                             'name'   => 'cat_id',
			                             'type'   => 'select',
			                             'select' => $tmp,
			                             'label'  => 'Kategorie',
			                             'show'   => false
		                             ),
		                             array (
			                             'name'   => 'state',
			                             'type'   => 'select',
			                             'select' => $states,
			                             'label'  => 'Status',
			                             'show'   => false
		                             ),
		                             array (
			                             'name'  => 'untrans',
			                             'type'  => 'checkbox',
			                             'value' => 1,
			                             'label' => 'nicht Übersetzte',
			                             'show'  => true
		                             )
		                       ));

		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             "field"   => "title",
			                             "content" => 'Titel',
			                             "sort"    => "title",
			                             "default" => true,
			                             'type'    => 'alpha label'
		                             ),
		                             array (
			                             "field"   => "created",
			                             "content" => 'Datum',
			                             "sort"    => "date",
			                             "default" => true,
			                             'width'   => '10%',
			                             'nowrap'  => true,
			                             'type'    => 'date'
		                             ),
		                             array (
			                             "field"   => "modifed",
			                             "content" => 'Bearbeitet am',
			                             "sort"    => "moddate",
			                             "default" => false,
			                             'width'   => '10%',
			                             'nowrap'  => true,
			                             'type'    => 'date'
		                             ),
		                             array (
			                             "field"   => "created_user",
			                             "content" => 'Autor',
			                             'width'   => '10%',
			                             "sort"    => "createdby",
			                             "default" => false,
			                             'nowrap'  => true,
			                             'type'    => 'alpha'
		                             ),
		                             array (
			                             "field"   => "modifed_user",
			                             "content" => 'Bearbeiter',
			                             'width'   => '10%',
			                             "sort"    => "modifedby",
			                             "default" => false,
			                             'nowrap'  => true,
			                             'type'    => 'alpha'
		                             ),
		                             array (
			                             "field"   => "hits",
			                             "content" => 'Hits',
			                             "sort"    => "hits",
			                             'width'   => '5%',
			                             "default" => true,
			                             'align'   => 'tc',
			                             'type'    => 'num'
		                             ),
		                             array (
			                             "field"   => "cat_title",
			                             "content" => 'Kategorie',
			                             "sort"    => "cat",
			                             'width'   => '10%',
			                             "default" => true,
			                             'nowrap'  => true,
			                             'type'    => 'alpha'
		                             ),
		                             array (
			                             "field"   => "published",
			                             "content" => 'Aktiv',
			                             "sort"    => "published",
			                             'width'   => '5%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                             array (
			                             "field"   => "comments",
			                             "content" => 'Kommentare',
			                             "sort"    => "comments",
			                             'width'   => '9%',
			                             "default" => false,
			                             'align'   => 'tc',
			                             'type'    => 'num'
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => 'Optionen',
			                             'width'   => '7%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));


		switch ( $GLOBALS[ 'sort' ] )
		{
			case 'asc':
				$sort = " ASC";
				break;

			case 'desc':
			default:
				$sort = " DESC";
				break;
		}

		switch ( $GLOBALS[ 'orderby' ] )
		{
			case 'date':
				$order = " ORDER BY n.created";
				break;

			case 'moddate':
				$order = " ORDER BY n.modifed";
				break;

			case 'hits':
				$order = " ORDER BY n.hits";
				break;

			case 'published':
				$order = " ORDER BY nt.draft " . $sort . ", n.draft " . $sort . ", n.published";
				break;

			case 'cat':

				if ( $sectionid == 0 )
				{
					$order = " ORDER BY ct.title";
				}
				else
				{
					$order = " ORDER BY ct.title";
				}
				break;

			case 'comments':
				$order = " ORDER BY comments";
				break;

			case 'title':
			default:
				$order = " ORDER BY nt.title";

				break;
		}

		$where[ ] = 'n.pageid = ' . PAGEID;

		// ====================================================
		// Status der News
		// ====================================================
		switch ( HTTP::input('state') )
		{

			case 'online':
				$where[ ] = ' n.published = 1' . PUBLISH_MODE;
				break;

			case 'offline':
				$where[ ] = ' n.published = 0' . UNPUBLISH_MODE;
				break;

			case 'archived':
				$where[ ] = ' n.published = ' . ARCHIV_MODE;
				break;

			case 'draft':
				$where[ ] = ' nt.draft = 1 OR n.draft=1 OR n.published = ' . DRAFT_MODE;
				break;

			case 'online_offline':
				$where[ ] = ' n.published >= ' . UNPUBLISH_MODE;
				break;

			case 'online_offline':
			default:
				$where[ ] = ' n.published >= ' . UNPUBLISH_MODE;
				break;
		}


		// mark untranslated news
		if ( HTTP::input('untrans') )
		{
			$where[ ] = ' nt.iscorelang = 1 AND nt.lang != ' . $this->db->quote(CONTENT_TRANS);
		}

		$search = '';
		$search = HTTP::input('q');
		$search = trim((string)strtolower($search));
		$all    = null;

		if ( $cat_id > 0 )
		{
			$where[ ] = "n.cat_id={$cat_id}";
		}

		$_s = '';
		if ( $search != '' )
		{
			$search = str_replace("*", "%", $search);
			$search = Library::cleanSearchString($search);
			$search = explode(" ", $search);
			foreach ( $search as $w )
			{
				if ( trim($w) != '' )
				{
					$_s = " AND ( LOWER(nt.title) LIKE " . $this->db->quote("%{$w}%") . " OR LOWER(nt.text) LIKE " . $this->db->quote("%{$w}%") . ")";
				}
			}
		}

		$transq1 = $this->buildTransWhere('news', 'n.id', 'nt');
		$transq2 = $this->buildTransWhere('news_categories', 'c.id', 'ct');

		// get the total number of records
		$sql = "SELECT COUNT(n.id) AS total FROM %tp%news AS n
                LEFT JOIN %tp%news_trans AS nt ON (nt.id=n.id) LEFT JOIN %tp%news_categories AS c ON(c.id=n.cat_id)
                LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id = c.id)
                WHERE " . $transq1 . ' AND ' . $transq2 . " " . (count($where) ? ' AND ' . implode(' AND ', $where) :
				"") . ($_s ? $_s : '') . '';
		$r   = $this->db->query($sql)->fetch();

		$total             = $r[ 'total' ];
		$limit             = $this->get_perpage(); // oder $GLOBALS['perpage']
		$this->dataresults = $total;
		$pages             = ceil($total / $limit);

		if ( HTTP::input('page') > 0 )
		{
			$page = (int)HTTP::input('page');

			if ( $page == 0 )
			{
				$page = 1;
			}
		}
		else
		{
			$page = 1;
		}


		$query  = "SELECT n.*, nt.lang, nt.title, ct.title AS cat_title,
				(SELECT COUNT(*) FROM %tp%comments WHERE post_id = n.id AND modul='news') AS comments,
				u1.username AS created_user,
				u2.username AS modifed_user
                FROM %tp%news AS n
                LEFT JOIN %tp%news_trans AS nt ON (nt.id=n.id)
                LEFT JOIN %tp%news_categories AS c ON(c.id=n.cat_id)
                LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id = c.id)
                LEFT JOIN %tp%users AS u1 ON(u1.userid=n.created_by)
                LEFT JOIN %tp%users AS u2 ON(u2.userid=n.modifed_by)

            WHERE " . $transq1 . ' AND ' . $transq2 . (count($where) ? ' AND ' . implode(' AND ', $where) : "") . ($_s ?
				$_s : '') . " GROUP BY n.id" . $order . ' ' . $sort . " LIMIT " . ($limit * ($page - 1)) . "," . $limit;
		$result = $this->db->query($query)->fetchAll();
		$list   = '';
		$now    = time();

		$add_to_link = '';
		if ( HTTP::input('page', '!empty') )
		{
			$add_to_link .= '&amp;page=' . (int)HTTP::input('page');
		}
		if ( HTTP::input('orderby', '!empty') )
		{
			$add_to_link .= '&amp;orderby=' . (string)HTTP::input('orderby');
		}
		if ( HTTP::input('sort', '!empty') )
		{
			$add_to_link .= '&amp;sort=' . HTTP::input('sort');
		}
		if ( HTTP::input('state', '!empty') )
		{
			$add_to_link .= '&amp;state=' . HTTP::input('state');
		}
		if ( HTTP::input('q', '!empty') )
		{
			$add_to_link .= '&amp;q=' . HTTP::input('q');
		}
		if ( HTTP::input('untrans', '!empty') )
		{
			$add_to_link .= '&amp;untrans=1';
		}

		$e = trans('`%s` barbeiten');


		$im = BACKEND_IMAGE_PATH;
		foreach ( $result as $rs )
		{
			//$list .= $adm_tpls->news_list_row($rs);

			if ( ($now <= $rs[ 'publish_on' ] || !$rs[ 'publish_on' ]) && $rs[ 'published' ] == 1 )
			{
				$img = 'online.gif';
				$alt = trans('Veröffentlicht');
			}
			else if ( $rs[ 'published' ] == 2 && $rs[ 'publish_on' ] > 0 && $rs[ 'publish_off' ] > 0 && ($now <= $rs[ 'publish_on' ] && $now <= $rs[ 'publish_off' ])
			)
			{
				$img = 'online.gif';
				$alt = trans('Zeitgesteuert Veröffentlicht');
			}
			else if ( $rs[ 'published' ] == -1 )
			{
				$img = 'online.gif';
				$alt = trans('In Bearbeitung');
			}
			else if ( $rs[ 'published' ] == -2 )
			{
				$img = 'online.gif';
				$alt = trans('Veröffentlicht');
			}
			else if ( $rs[ 'published' ] == 2 )
			{
				$img = 'clock.png';
				$alt = trans('Zeitgesteuert');
			}
			else if ( $rs[ 'publish_off' ] > 0 && $now > $rs[ 'publish_off' ] && $rs[ 'published' ] == 1 )
			{
				$img = 'offline.gif';
				$alt = trans('nicht Veröffentlicht da abgelaufen');
			}
			else
			{
				$img = "offline.gif";
				$alt = trans('nicht Veröffentlicht');
			}


			$pubicon = $this->getGridState($rs[ 'published' ], 'pubs' . $rs[ 'id' ], $rs[ 'publish_on' ], $rs[ 'publish_off' ]);
			$to      = (!empty($r[ 'published' ]) ? 'unpublish' : 'publish');

			//$rs['typetitle'] = $this->article_type($rs['typeid']);
			//$rows .= $adm_tpls->list_articles_row($rs);

			$chkhtml = '<a href="javascript:void(0);" onclick="changePublish(\'pubs' . $rs[ 'id' ] . '\',\'admin.php?adm=news&action=publish&id=' . $rs[ 'id' ] . '\');">' . $pubicon . '</a>';


			if ( $this->isDraft("admin.php?adm=news&amp;action=edit_news&amp;id={$rs['id']}&amp;edit=1") )
			{
				$chkhtml = $this->DRAFT_ICON;
			}


			$_e = htmlspecialchars(sprintf($e, $rs[ "title" ]));

			$rs[ 'created' ] = date('d.m.Y, H:i', $rs[ 'created' ]);
			if ( $rs[ 'modifed' ] )
			{
				$rs[ 'modifed' ] = date('d.m.Y, H:i', $rs[ 'modifed' ]);
			}
			else
			{
				$rs[ 'modifed' ] = '';
			}

			$rs[ 'options' ] = <<<EOF
        <a class="appendmenu ajax" href="admin.php?adm=news&amp;action=edit_news&amp;id={$rs['id']}&amp;tomenu=1"><img src="{$im}add-to-menu.png" border="0" alt="" title="Zum Menü hinzufügen" /></a>
		<a class="doTab" href="admin.php?adm=news&amp;action=edit_news&amp;id={$rs['id']}&amp;edit=1{$add_to_link}"><img src="{$im}edit.gif" border="0" alt="{$_e}" title="{$_e}" /></a>
		<a class="delconfirm ajax" href="admin.php?adm=news&amp;action=delete_news&amp;id={$rs['id']}{$add_to_link}"><img src="{$im}delete.gif" border="0" alt="Löschen" title="Löschen" /></a>
EOF;

			$fcss = ($rs[ 'lang' ] != CONTENT_TRANS ? 'wtrans' : null);

			/*
			  $row = & $lAdmin->addrow($rs['id'], $rs);
			  $len = strlen($rs["title"]);
			  $row->addviewfield("title", $rs["title"], $fcss);
			  $row->addviewfield("created", $rs['created']);
			  $row->addviewfield("modifed", $rs['modifed']);
			  $row->addviewfield("created_user", $rs["created_user"]);
			  $row->addviewfield("modifed_user", $rs["modifed_user"]);
			  $row->addviewfield("hits", $rs["hits"]);
			  $row->addviewfield("cat_title", $rs["cat_title"]);
			  $row->addviewfield("published", $chkhtml);
			  $row->addviewfield("options", $rs['options']);
			 *
			 */


			$len = strlen($rs[ "title" ]);
			$row = $this->Grid->addRow($rs);
			$row->addFieldData("title", $rs[ "title" ], $fcss);
			$row->addFieldData("created", $rs[ 'created' ]);
			$row->addFieldData("modifed", $rs[ 'modifed' ]);
			$row->addFieldData("created_user", $rs[ "created_user" ]);
			$row->addFieldData("modifed_user", $rs[ "modifed_user" ]);
			$row->addFieldData("hits", $rs[ "hits" ]);
			$row->addFieldData("cat_title", $rs[ "cat_title" ]);
			$row->addFieldData("published", $chkhtml);
			$row->addFieldData("options", $rs[ 'options' ]);
		}


		$griddata = $this->Grid->renderData();

		$data[ 'success' ]  = true;
		$data[ 'total' ]    = $total;
		$data[ 'sort' ]     = $GLOBALS[ 'sort' ];
		$data[ 'orderby' ]  = $GLOBALS[ 'orderby' ];
		$data[ 'datarows' ] = $griddata[ 'rows' ];

		echo Library::json($data);
		exit;
	}

	/**
	 *
	 * @param string $alias
	 * @return mixed (array/bool)
	 */
	public function findItemByAlias ( $alias = '' )
	{

		if ( isset($GLOBALS[ 'FRONTEND' ]) )
		{
			$time    = time();
			$publish = ' AND c.published=1 AND n.draft = 0 AND
					(n.publish_off>=' . $time . ' OR n.publish_off=0) AND
					((n.publish_on>0 AND n.publish_on <= ' . $time . ') OR n.created <= ' . $time . ')';
		}

		$ac = defined('ACTION') ? ACTION : $GLOBALS[ 'tmp_ACTION' ];
		switch ( $ac )
		{
			case 'index':
				$catid   = (int)HTTP::input('catid') ? (int)HTTP::input('catid') : 0;
				$time    = time();
				$transq1 = $this->buildTransWhere('news_categories', 'c.id', 'ct');

				$sql = 'SELECT c.*, ct.*
                        FROM %tp%news_categories AS c
                        LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id=c.id)
                        WHERE
                            c.access IN(0,' . User::getGroupId() . ') AND c.published=1
                            AND c.pageid = ? AND ' . ($catid ? 'ct.id = ?' : 'ct.alias = ?') . ' AND ' . $transq1 . '
                        GROUP BY c.id';

				return $this->db->query($sql, PAGEID, ($catid ? $catid : $alias));
				break;

			case 'show':
			case 'item':


				$transq1 = $this->buildTransWhere('news', 'n.id', 'nt');
				$transq2 = $this->buildTransWhere('news_categories', 'c.id', 'ct');

				$sql = 'SELECT n.*, nt.*, c.clickanalyse AS catclickanalyse,
                            ct.title AS category, ct.title AS cattitle, ct.alias AS catalias, ct.suffix AS catsuffix,
                            c.clickanalyse AS catclickanalyse,
                            c.cacheable AS catcacheable,
                            c.cachetime AS catcachetime,
                            c.cachegroups AS catcachegroups,
                            c.access AS cat_access,
                            c.published AS catpublished,
                            u.username AS newsauthor,
                            u1.username AS modifyauthor,
                            (SELECT COUNT(*) FROM %tp%comments WHERE post_id = n.id AND modul=\'news\') AS comments
                        FROM %tp%news AS n
                        LEFT JOIN %tp%news_trans AS nt ON(nt.id=n.id )
                        LEFT JOIN %tp%news_categories AS c ON(c.id=n.cat_id)
                        LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id=c.id)
                        LEFT JOIN %tp%users AS u ON (u.userid = n.created_by)
                        LEFT JOIN %tp%users AS u1 ON (u1.userid = n.modifed_by )
                        WHERE
                            n.usergroups IN(0,' . User::getGroupId() . ') AND n.published=1' . $publish . '
                            AND n.pageid = ? AND nt.alias = ? AND ' . $transq1 . ' AND ' . $transq2 . '
                        GROUP BY n.id';

				return $this->db->query($sql, PAGEID, $alias);
				break;
		}

		return false;
	}

	/**
	 * @param int $id
	 * @return Database_Adapter_Pdo_RecordSet
	 */
	public function findItemByID ( $id = 0 )
	{

		$time = time();
		$com  = Comments::getCountQuery(array (
		                                      'prefix' => 'com',
		                                      'joinon' => 'n.id',
		                                      'source' => 'news'
		                                ));


		$transq1 = $this->buildTransWhere('news', 'n.id', 'nt');
		$transq2 = $this->buildTransWhere('news_categories', 'c.id', 'ct');

		$publish = '';
		if ( isset($GLOBALS[ 'FRONTEND' ]) )
		{
			$time    = time();
			$publish = ' AND c.published=1 AND n.draft = 0 AND
					(n.publish_off>=' . $time . ' OR n.publish_off=0) AND
					((n.publish_on>0 AND n.publish_on <= ' . $time . ') OR n.created <= ' . $time . ')';
		}

		$sql = 'SELECT n.*, nt.*,
                            ct.title AS category, ct.title AS cattitle, ct.alias AS catalias, ct.suffix AS catsuffix,
                            c.access AS cat_access,
                            c.clickanalyse AS catclickanalyse,
                            c.cacheable AS catcacheable,
                            c.cachetime AS catcachetime,
                            c.cachegroups AS catcachegroups,
                            c.published AS catpublished,
                            u.username AS newsauthor,
                            u1.username AS modifyauthor,
                            (SELECT COUNT(*) FROM %tp%comments WHERE post_id = n.id AND modul=\'news\') AS comments
                        FROM %tp%news AS n
                        LEFT JOIN %tp%news_trans AS nt ON(nt.id=n.id )
                        LEFT JOIN %tp%news_categories AS c ON(c.id=n.cat_id)
                        LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id=c.id)
                        LEFT JOIN %tp%users AS u ON (u.userid = n.created_by)
                        LEFT JOIN %tp%users AS u1 ON (u1.userid = n.modifed_by )
                        WHERE
                            n.usergroups IN(0,' . User::getGroupId() . ') AND n.published=1' . $publish . '
                            AND n.pageid = ? AND nt.id = ? AND ' . $transq1 . ' AND ' . $transq2 . '
                        GROUP BY n.id';

		return $this->db->query($sql, PAGEID, $id);
	}

	/**
	 * get all news categories
	 *
	 * @return array
	 */
	public function getCategories ()
	{

		$transq = $this->buildTransWhere('news_categories', 't.id', 't');

		$publish = '';
		if ( isset($GLOBALS[ 'FRONTEND' ]) )
		{
			$publish = ' AND c.published=1';
		}


		return $this->db->query('SELECT c.*, t.*, COUNT(n.id) AS totalnews
                                 FROM %tp%news_categories AS c

                                /*  LEFT JOIN %tp%news_trans AS nt ON(nt.id=n.id) */
                                LEFT JOIN %tp%news_categories_trans AS t ON(t.id=c.id)
                                LEFT JOIN %tp%news AS n ON(n.cat_id=c.id)
                                WHERE c.pageid = ?' . $publish . ' AND ' . $transq . '
                                GROUP BY c.id
                                ORDER BY c.parentid, title', PAGEID)->fetchAll();
	}

	/**
	 *
	 * @param int   $id
	 * @param array $data
	 * @return int
	 */
	public function saveCatTranslation ( $id = 0, $data )
	{

		$access           = (is_array($data[ 'access' ]) ? $data[ 'access' ] : array (
			0
		));
		$data[ 'access' ] = implode(',', $access);

		/**
		 * create Password
		 */
		if ( $data[ 'password' ] != "" )
		{
			$this->load('Crypt');
			$data[ 'password' ] = $this->Crypt->encrypt($data[ 'password' ]);
		}

		$coredata = array (
			'password'    => $data[ 'password' ],
			'parentid'    => (int)$data[ 'parentid' ],
			'pageid'      => PAGEID,
			'newscounter' => 0,
			'access'      => $data[ 'access' ],
			'moderators'  => $data[ 'moderators' ],
			'cancomment'  => (int)$data[ 'cancomment' ],
			'teaserimage' => $data[ 'teaserimage' ],
			'language'    => $data[ 'language' ],
			'rollback'    => 0
		);

		if ( !is_array(HTTP::input('documentmeta')) )
		{
			$coredata[ 'published' ] = $data[ 'published' ];
		}


		$this->load('ContentTranslation');
		$this->ContentTranslation->prepareTranslationTables('news');


		$transfields = $this->getTranslateFields();

		$data[ 'description' ] = $data[ 'cat_description' ];


		$transData = array (
			'table'       => 'news_categories',
			'transfields' => $transfields[ 'news_categories' ],
			'data'        => $data
		);

		if ( !$id )
		{
			$tmp                 = $coredata;
			$tmp[ 'iscorelang' ] = 1;

			$str = $this->db->compile_db_insert_string($coredata);
			$sql = "INSERT INTO %tp%news_categories ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})";
			$this->db->query($sql);
			$newid = $this->db->insert_id();

			$transData[ 'id' ]    = $newid;
			$transData[ 'isnew' ] = true;

			$this->ContentTranslation->saveContentTranslation($transData);
		}
		else
		{
			$str = $this->db->compile_db_update_string($coredata);
			$sql = "UPDATE %tp%news_categories SET $str WHERE id=?";
			$this->db->query($sql, $id);

			$transData[ 'id' ] = $id;
			$this->ContentTranslation->saveContentTranslation($transData);
		}

		if ( HTTP::input('documentmeta') )
		{
			$metaData = array (
				'table'      => 'news_categories',
				'primarykey' => 'id',
				'id'         => $transData[ 'id' ],
				'data'       => HTTP::input('documentmeta')
			);

			$this->ContentTranslation->saveMainMetadata($metaData);
		}

		return $transData[ 'id' ];
	}

	/**
	 *
	 * @param int   $id
	 * @param array $data
	 * @return int
	 */
	public function saveNewsTranslation ( $id = 0, $data )
	{

		$access               = (is_array($data[ 'access' ]) ? $data[ 'access' ] : array (
			0
		));
		$data[ 'usergroups' ] = implode(',', $access);
		$data[ 'text' ]       = $data[ 'content' ];

		$coredata = array (
			'cat_id'       => (int)$data[ 'cat_id' ],
			'pageid'       => PAGEID,
			'tags'         => $data[ 'tags' ],
			'usergroups'   => $data[ 'usergroups' ],
			'keywords'     => $data[ 'keywords' ],
			'links_extern' => $data[ 'links_extern' ],
			'isfeed'       => (int)$data[ 'isfeed' ],
			'feed_link'    => $data[ 'feed_link' ],
			'can_comment'  => (int)$data[ 'can_comment' ],
			'hits'         => 0,
			'created_by'   => (int)User::getUserId(),
			'created'      => time(),
			'modifed_by'   => (int)User::getUserId(),
			'modifed'      => time(),
			'rollback'     => 0
		);

		if ( !is_array(HTTP::input('documentmeta')) )
		{
			$coredata[ 'published' ] = $data[ 'published' ];
		}


		$this->load('ContentTranslation');
		$this->ContentTranslation->prepareTranslationTables('news');


		$transfields = $this->getTranslateFields();
		$transData   = array (
			'table'       => 'news',
			'transfields' => $transfields[ 'news' ],
			'data'        => $data
		);

		if ( !$id )
		{
			$tmp                      = $coredata;
			$tmp[ 'iscorelang' ]      = 1;
			$coredata[ 'modifed' ]    = 0;
			$coredata[ 'modifed_by' ] = 0;

			$str = $this->db->compile_db_insert_string($coredata);
			$sql = "INSERT INTO %tp%news ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})";
			$this->db->query($sql);
			$newid = $this->db->insert_id();

			$transData[ 'id' ]    = $newid;
			$transData[ 'isnew' ] = true;

			$transData[ 'controller' ] = 'news';
			$transData[ 'action' ]     = 'show';

			$this->ContentTranslation->saveContentTranslation($transData);
		}
		else
		{
			unset($coredata[ 'created' ], $coredata[ 'created_by' ], $coredata[ 'hits' ]);

			$str = $this->db->compile_db_update_string($coredata);
			$sql = "UPDATE %tp%news SET $str WHERE id=?";
			$this->db->query($sql, $id);

			$transData[ 'id' ]         = $id;
			$transData[ 'controller' ] = 'news';
			$transData[ 'action' ]     = 'show';

			$this->ContentTranslation->saveContentTranslation($transData);
		}

		if ( HTTP::input('documentmeta') )
		{
			$metaData = array (
				'table'      => 'news',
				'primarykey' => 'id',
				'id'         => $transData[ 'id' ],
				'data'       => HTTP::input('documentmeta')
			);

			$this->ContentTranslation->saveMainMetadata($metaData);
		}

		return $transData[ 'id' ];
	}

	/**
	 * Delete a News Item by ID
	 * deleting news, news trans, alias registry and searchindex of this newsitem
	 *
	 * @param integer $id
	 * @return bool
	 */
	public function deleteNews ( $id )
	{

		$indexer = new Search();

		$this->load('AliasRegistry');
		$this->load('Trash');

		$this->Trash->setTrashTable('%tp%news');
		$this->Trash->setTrashTableLabel('News Item');


		$r = $this->db->query('SELECT * FROM %tp%news WHERE id = ?', $id)->fetch();
		$this->db->query('DELETE FROM %tp%news WHERE id = ?', $id);

		// Move to Trash
		$trashData                 = array ();
		$trashData[ 'data' ]       = $r;
		$trashData[ 'label' ]      = $r[ 'title' ];
		$trashData[ 'trans_data' ] = $this->db->query('SELECT * FROM %tp%news_trans WHERE id = ?', $id)->fetchAll();

		$this->Trash->addTrashData($trashData);
		$this->Trash->moveToTrash();

		// unregister alias
		$this->AliasRegistry->removeAlias($trashData[ 'trans_data' ][ 'alias' ], 'news', 'show');

		// remove Search Index
		$indexer->deleteIndex('news', CONTENT_TRANS, 'news/item/', $id);

		// Remove Cache
		Cache::delete('newsText-' . $id, 'data/news');

		return true;
	}

	/**
	 * get all news categories
	 *
	 * @param int $catid
	 * @return array
	 */
	public function getCategorie ( $catid = 0 )
	{

		$transq  = $this->buildTransWhere('news_categories', 'c.id', 't');
		$publish = '';
		if ( isset($GLOBALS[ 'FRONTEND' ]) )
		{
			$publish = ' AND c.published=1';
		}

		return $this->db->query('SELECT c.*, t.*
                                 FROM %tp%news_categories AS c
                                 LEFT JOIN %tp%news_categories_trans AS t ON(t.id=c.id)
                                 WHERE c.pageid = ? AND c.id = ? AND ' . $transq . $publish . '
                                 ORDER BY c.parentid, title', CONTENT_TRANS, PAGEID, $catid)->fetchAll();
	}

	/**
	 * give the news with the translation
	 * if $language is null returns the current translation by defined CONTENT_TRANS
	 *
	 * @param type   $id
	 * @param string $language default is null
	 * @param bool   $useCat
	 * @return array (record => ... , trans => ...)
	 */
	public function getVersioningRecord ( $id, $language = null, $useCat = false )
	{

		$table = ($useCat !== false ? 'news_categories' : 'news');

		if ( $language === null )
		{
			$record = $this->db->query('SELECT n.* FROM %tp%' . $table . ' AS n WHERE n.id = ?', $id)->fetch();
			$trans  = $this->db->query('SELECT t.* FROM %tp%' . $table . '_trans AS t WHERE t.id = ? AND t.lang = ?', $id, CONTENT_TRANS)->fetch();
		}
		else
		{
			$record = $this->db->query('SELECT n.* FROM %tp%' . $table . ' AS n WHERE n.id = ?', $id)->fetch();
			$trans  = $this->db->query('SELECT t.* FROM %tp%' . $table . '_trans AS t WHERE t.id = ? AND t.lang = ?', $id, $language)->fetch();
		}

		return array (
			'record' => $record,
			'trans'  => $trans
		);
	}

	/**
	 * create a newsitem version
	 *
	 * @param array $original
	 * @param bool  $useCat
	 * @return bool
	 */
	public function createVersion ( $original = array (), $useCat = false )
	{

		if ( !isset($original[ 'record' ]) )
		{
			return false;
		}

		$current = $this->getVersioningRecord($original[ 'record' ][ 'id' ], $original[ 'trans' ][ 'lang' ], $useCat);

		if ( !is_array($current[ 'trans' ]) )
		{
			$current[ 'trans' ] = array ();
		}


		if ( !$useCat )
		{
			// Versioning the currenct record if changed if not changed back
			$original[ 'record' ][ 'modifed_by' ] = $current[ 'record' ][ 'modifed_by' ];
			$original[ 'record' ][ 'modifed' ]    = $current[ 'record' ][ 'modifed' ];
		}
		else
		{
			$original[ 'record' ][ 'password' ] = $current[ 'record' ][ 'password' ];
		}
		$result = array_diff($original[ 'record' ], $current[ 'record' ]);

		// test translation diff
		$resultTrans = array_diff($original[ 'trans' ], $current[ 'trans' ]);

		$table = ($useCat !== false ? 'news_categories' : 'news');

		if ( count($resultTrans) > 0 || count($result) > 0 )
		{

			#Library::sendJson(false, 'T:'.print_r($resultTrans,true).' C:'.print_r($result,true));
			$versions = new Versioning();
			$versions->createVersion($original[ 'record' ][ 'id' ], $table, $current[ 'record' ], $current[ 'trans' ]);
		}
	}

	/**
	 * create a newscat version
	 *
	 * @param array $original
	 * @return bool
	 */
	public function createCatVersion ( $original = array () )
	{

		if ( !isset($original[ 'record' ]) )
		{
			return false;
		}

		$current = $this->getVersioningRecord($original[ 'record' ][ 'id' ], $original[ 'trans' ][ 'lang' ], true);

		if ( !is_array($current[ 'trans' ]) )
		{
			$current[ 'trans' ] = array ();
		}

		$result = array_diff($original[ 'record' ], $current[ 'record' ]);

		// Versioning the currenct record if changed if not changed back
		#$original['trans']['modifed_by'] = $current['trans']['modifed_by'];
		#$original['trans']['modifed'] = $current['trans']['modifed'];
		// test translation diff
		$resultTrans = array_diff($original[ 'trans' ], $current[ 'trans' ]);

		if ( count($resultTrans) > 0 || count($result) > 0 )
		{
			$versions = new Versioning();
			$versions->createVersion($original[ 'record' ][ 'id' ], 'news_categories', $current[ 'record' ], $current[ 'trans' ]);
		}
	}

	/**
	 *
	 * @param integer $id
	 * @param bool    $useCat
	 * @return bool
	 */
	public function hasTranslation ( $id = 0, $useCat = false )
	{

		$table = ($useCat ? 'news_categories' : 'news');
		$trans = $this->db->query('SELECT id FROM %tp%' . $table . '_trans WHERE id = ? AND lang = ?', $id, CONTENT_TRANS)->fetch();

		if ( $trans[ 'id' ] )
		{
			return true;
		}

		return false;
	}

	/**
	 * will rollback the temporary translated content
	 *
	 * @param integer $id
	 * @param bool    $useCat
	 * @return type
	 */
	function rollbackTranslation ( $id, $useCat = false )
	{

		$table = ($useCat ? 'news_categories' : 'news');
		$this->db->query('DELETE FROM %tp%' . $table . '_trans WHERE `rollback` = 1 AND id = ? AND lang = ?', $id, CONTENT_TRANS);
	}

	/**
	 * Copy the original translation to other translation
	 *
	 * @param integer $id
	 * @param bool    $useCat
     * @return bool
	 */
	public function copyOriginalTranslation ( $id, $useCat = false )
	{

		$table = ($useCat ? 'news_categories' : 'news');

		$r = $this->db->query('SELECT lang FROM %tp%' . $table . '_trans WHERE id = ? AND iscorelang = 1', $id)->fetch();
		if ( CONTENT_TRANS == $r[ 'lang' ] )
		{
			return false;
		}

		$trans                 = $this->db->query('SELECT t.* FROM %tp%' . $table . '_trans AS t WHERE t.id = ? AND t.lang = ?', $id, $r[ 'lang' ])->fetch();
		$trans[ 'lang' ]       = CONTENT_TRANS;
		$trans[ 'rollback' ]   = 1;
		$trans[ 'iscorelang' ] = 0;

		$f      = array ();
		$fields = array ();
		$values = array ();
		foreach ( $trans as $key => $value )
		{
			$fields[ ] = $key;
			$f[ ]      = '?';
			$values[ ] = $value;
		}

		$this->db->query('INSERT INTO %tp%' . $table . '_trans (' . implode(',', $fields) . ') VALUES(' . implode(',', $f) . ')', $values);
        return true;
	}

}

?>