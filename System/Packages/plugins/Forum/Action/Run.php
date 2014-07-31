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
 * @category     Plugin s
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Run.php
 */
class Addon_Forum_Action_Run extends Addon_Forum_Helper_Base
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			$this->getFrontend();
		}
		else
		{
			$this->getBackend();
		}
	}

	private function getTree(&$data) {
		foreach ( $data as &$r )
		{
			if ( isset($r['subforums']['subforums'])) {
				$r['children'] = $r['subforums']['subforums'];
			}

			if ( is_array($r['subforums']) ) {
				$r['children'] = $this->getTree($r['subforums']);
			}
		}
	}

	private function getFrontend ()
	{

		$data = array ();

		$this->initCache();


		if (IS_AJAX && $this->input('getforums'))
		{
			$data = array();

			$data['success'] = true;
			$data['forums'] = array();

			$tree = $this->childForumList();


			foreach ($tree as $r)
			{
				$data['forums'][] = array(
					'forumid' => $r['forumid'],
					'title' => $r['title'],
					'containposts' => intval( $r['containposts'] ),
					'children' => ( is_array($r['children']) ? $r['children'] : false)
				);

			}

			echo Library::json($data);
			exit;
		}




		$forumid   = (HTTP::input('forumid') ? intval(HTTP::input('forumid')) : 0);
		$forumname = (HTTP::input('forumname') ? HTTP::input('forumname') : '');

		$this->currentForumID = $forumid;
		$parents              = $this->getParents($forumid);

        // do redirect location, if is on and has url
        if (isset($this->forum_by_id[ $forumid ]))
        {
            $f = $this->forum_by_id[ $forumid ];
            if ( $f['redirect_url'] && $f['redirect_on'] ) {

                Library::redirect($f['redirect_url']);
                exit;

            }
        }





		$this->buildBreadCrumb($parents);

		$childs = $this->getChildren($forumid);
		$totalthreads     = 0;
		$totalposts       = 0;

		$tmpData = array ();

		if ( !$forumid )
		{

			if ( isset($this->forum_cache[ 'root' ]) )
			{

				foreach ( $this->forum_cache[ 'root' ] as $idx => &$forum_data1 )
				{
					// create folder icon
					if ( $forum_data1[ 'lastposttime' ] > User::get('lastvisit') )
					{
						$forum_data1[ 'forumicon' ] = 'new';
					}
					$acc = explode(',', $forum_data1[ 'access' ]);
					if ( !in_array(0, $acc) && !in_array(User::getGroupId(), $acc) )
					{
						$forum_data1[ 'forumicon' ] .= '-locked';
					}

					$forum_data1[ 'parent' ] = 0;

					if ( !Settings::get('showforumdescription') )
					{
						unset($forum_data1[ 'description' ]);
					}

					$cat_data = $forum_data1;

					$totalthreads += intval($forum_data1[ 'threadcounter' ]);
					$totalposts += intval($forum_data1[ 'postcounter' ]);

					$tmp = false;
					if ( isset($this->forum_cache[ $forum_data1[ 'forumid' ] ]) && is_array($this->forum_cache[ $forum_data1[ 'forumid' ] ]) )
					{
						foreach ( $this->forum_cache[ $forum_data1[ 'forumid' ] ] as $id => &$forum_data )
						{
							// clean lastpost title
							$forum_data[ 'lastposttitle' ] = preg_replace('#(RE: RE: RE:|RE: RE:| RE:)#sS', 'RE:', $forum_data[ 'lastposttitle' ]);

							$totalthreads += intval($forum_data[ 'threadcounter' ]);
							$totalposts += intval($forum_data[ 'postcounter' ]);


							if ( $forum_data[ 'access' ] != '' && !in_array(User::getGroupId(), explode(',', $forum_data[ 'access' ])) && !in_array(0, explode(',', $forum_data[ 'access' ])) )
							{
								continue;
							}
							// Get all subforum stats and calculate
							$subforums = $this->childForums($forum_data[ 'forumid' ]);
							$threads   = $forum_data[ 'threadcounter' ];
							$posts     = $forum_data[ 'postcounter' ];

							if ( isset($subforums[ 'subforums' ]) && is_array($subforums[ 'subforums' ]) )
							{
								foreach ( $subforums[ 'subforums' ] as $sub )
								{
									$threads += $sub[ 'threadcounter' ];
									$posts += $sub[ 'postcounter' ];

									#$forum_data[ 'threadcounter' ] = $threads;
									#$forum_data[ 'postcounter' ] = $posts;


									$totalthreads += intval($sub[ 'threadcounter' ]);
									$totalposts += intval($sub[ 'postcounter' ]);

									if ( intval($cat_data[ 'lastposttime' ]) < intval($sub[ 'lastposttime' ]) )
									{
										$cat_data[ 'lastposttime' ]     = $sub[ 'lastposttime' ];
										$cat_data[ 'lastpostthreadid' ] = $sub[ 'lastpostthreadid' ];
										$cat_data[ 'lastpostuserid' ]   = $sub[ 'lastpostuserid' ];
										$cat_data[ 'lastpostusername' ] = $sub[ 'lastpostusername' ];
										$cat_data[ 'lastposttitle' ]    = $sub[ 'lastposttitle' ];
									}

									if ( intval($forum_data[ 'lastposttime' ]) < intval($sub[ 'lastposttime' ]) )
									{
										$forum_data[ 'lastposttime' ]     = $sub[ 'lastposttime' ];
										$forum_data[ 'lastpostthreadid' ] = $sub[ 'lastpostthreadid' ];
										$forum_data[ 'lastpostuserid' ]   = $sub[ 'lastpostuserid' ];
										$forum_data[ 'lastpostusername' ] = $sub[ 'lastpostusername' ];
										$forum_data[ 'lastposttitle' ]    = $sub[ 'lastposttitle' ];
									}

									if ( intval($forum_data1[ 'lastposttime' ]) < intval($sub[ 'lastposttime' ]) )
									{
										$forum_data1[ 'lastposttime' ]     = $sub[ 'lastposttime' ];
										$forum_data1[ 'lastpostthreadid' ] = $sub[ 'lastpostthreadid' ];
										$forum_data1[ 'lastpostuserid' ]   = $sub[ 'lastpostuserid' ];
										$forum_data1[ 'lastpostusername' ] = $sub[ 'lastpostusername' ];
										$forum_data1[ 'lastposttitle' ]    = $sub[ 'lastposttitle' ];
									}
								}

							}
							/*
							 * @todo real post counter or with the thread post?
							 */
							//$forum_data[ 'postcounter' ] -= intval($forum_data[ 'threadcounter' ]);

							$forum_data[ 'threadcounter' ] = (string)$forum_data[ 'threadcounter' ];
							$forum_data[ 'postcounter' ]   = (string)$forum_data[ 'postcounter' ];


							$forum_data[ 'moderators' ] = Library::unempty($forum_data[ 'moderators' ]);


							$tmp[ ]                        = array_merge($forum_data, $subforums);
						}
					}

					$cat_data[ 'moderators' ] = is_array($cat_data[ 'moderators' ]) ? Library::unempty($cat_data[ 'moderators' ]) : array();
					$cat_data[ 'cat_start' ] = true;

					$tmpData[ ] = $cat_data;

					if ( is_array($tmp) )
					{
						$tmpData = array_merge($tmpData, $tmp);
					}

					$tmpData[ ] = array (
						'cat_end' => true
					);

					$tmp = null;
				}
			}
			#echo "\n\nEND:";
			#print_r($tmpData);
			#exit;

			// $this->PageCache->setCacheID( $forumid );
		}
		else
		{

			$data[ 'forum' ] = (isset($this->forum_by_id[ $forumid ]) ? $this->forum_by_id[ $forumid ] : false);


			if ( !$data[ 'forum' ] )
			{
				$this->Page->send404(trans('Hoppla dieses Forum existiert leider nicht!'));
			}



			$acl =  explode(',', $data[ 'forum' ][ 'access' ]) ;

			if ( !in_array(User::getGroupId(), $acl) && !in_array(0, $acl) )
			{
				$this->Page->sendAccessError(trans('Sie besitzen nicht die nötigen Rechte um sich das Forum anzusehen!'));
				exit;
			}

			$this->forum = $data[ 'forum' ];




            if ($this->forum['redirect_on'] && Validation::isValidUrl($this->forum['redirect_url']))
            {
                Library::redirect($this->forum['redirect_url']);
                exit;
            }


			if ( !Settings::get('showforumdescription') )
			{
				unset($data[ 'forum' ][ 'description' ]);
			}


			if ( isset($this->forum_cache[ $forumid ]) && is_array($this->forum_cache[ $forumid ]))
			{



				$forum_data1[ 'parent' ] = 0;
				$cat_data                = $this->forum_by_id[ $forumid ];




				# $cat_data[ 'threadcounter' ] = intval( $cat_data[ 'threadcounter' ] );
				# $cat_data[ 'postcounter' ]   = intval( $cat_data[ 'postcounter' ] );


				if ( isset($data[ 'moderators' ]) && is_array($data[ 'moderators' ]) && count($data[ 'moderators' ]) )
				{
					$cat_data[ 'moderators' ] = $data[ 'moderators' ];
				}

				$cat_data[ 'cat_start' ] = true;
				$tmp                     = null;

				#$posts  = $cat_data['postcounter'];
				foreach ( $this->forum_cache[ $forumid ] as $idx => &$forum_data )
				{

					if ( !in_array($forum_data[ 'forumid' ], $childs) )
					{
						continue;
					}

					if ( $forum_data[ 'access' ] != '' && !in_array(User::getGroupId(), explode(',', $forum_data[ 'access' ])) && !in_array(0, explode(',', $forum_data[ 'access' ])) )
					{
						continue;
					}

					// clean lastpost title
					$forum_data[ 'lastposttitle' ] = preg_replace('#(RE: RE: RE:|RE: RE:| RE:)#sS', 'RE:', $forum_data[ 'lastposttitle' ]);

					$forum_data[ 'moderators' ] = (is_array($forum_data[ 'moderators' ]) ? $forum_data[ 'moderators' ] :
						$this->model->getForumModerators($forum_data[ 'forumid' ]));


					$forum_data[ 'moderators' ] = Library::unempty($forum_data[ 'moderators' ]);

					// clean lastpost title
					$forum_data[ 'lastposttitle' ] = preg_replace('#(RE: RE: RE:|RE: RE:| RE:)#sS', 'RE:', $forum_data[ 'lastposttitle' ]);

					// Get all subforum stats and calculate
					$subforums = $this->childForums($forum_data[ 'forumid' ], array());

					$threads = 0;
					if ( isset($subforums['subforums']) && is_array($subforums['subforums'])) {

						foreach ( $subforums['subforums'] as $sub )
						{
							if ( intval($forum_data[ 'lastposttime' ]) < intval($sub[ 'lastposttime' ]) )
							{
								$forum_data[ 'lastpostid' ]   = $sub[ 'lastpostid' ];

								$forum_data[ 'lastposttime' ]     = $sub[ 'lastposttime' ];
								$forum_data[ 'lastpostthreadid' ] = $sub[ 'lastpostthreadid' ];
								$forum_data[ 'lastpostuserid' ]   = $sub[ 'lastpostuserid' ];
								$forum_data[ 'lastpostusername' ] = $sub[ 'lastpostusername' ];
								$forum_data[ 'lastposttitle' ]    = $sub[ 'lastposttitle' ];
							}

							// clean lastpost title
							$forum_data[ 'lastposttitle' ] = preg_replace('#(RE: RE: RE:|RE: RE:| RE:)#sS', 'RE:', $forum_data[ 'lastposttitle' ]);


							#$forum_data[ 'threadcounter' ] += intval($sub[ 'threadcounter' ]);
						#	$forum_data[ 'postcounter' ] += intval($sub[ 'postcounter' ]);
							$sub[ 'postcounter' ] = (string)$sub[ 'postcounter' ];
							$sub[ 'threadcounter' ] += (string)$sub[ 'threadcounter' ];
						}

						$forum_data[ 'subforums' ] = $subforums;
					}

					/*
					 * @todo real post counter or with the thread post?
					 */
					$forum_data[ 'postcounter' ] -= intval($forum_data[ 'threadcounter' ]);
					$forum_data[ 'threadcounter' ] = intval($forum_data[ 'threadcounter' ]) ? (string)$forum_data[ 'threadcounter' ] : '0';
					$forum_data[ 'postcounter' ]   = intval($forum_data[ 'postcounter' ]) ? (string)$forum_data[ 'postcounter' ] : '0';

					$forum_data = array_merge($forum_data, $subforums);
					$tmp[ ]     = $forum_data;
				}

#exit;


				$cat_data[ 'threadcounter' ] = (string)$cat_data[ 'threadcounter' ];
				$cat_data[ 'postcounter' ] = (string)$cat_data[ 'postcounter' ];

				$tmpData[ ] = $cat_data;

				if ( !is_null($tmp) )
				{
					$tmpData = array_merge($tmpData, $tmp);
				}

				$tmpData[ ] = array (
					'cat_end' => true
				);
				$tmp        = null;
			}

			#print_r($tmpData);exit;

			$data[ 'moderators' ] = ( isset($this->forum_cache[ $forumid ][ 'moderators' ]) && is_array($this->forum_cache[ $forumid ][ 'moderators' ]) ?
				$this->forum_cache[ $forumid ][ 'moderators' ] : $this->model->getForumModerators($forumid));


			$userid          = User::getUserId();
			$ismod           = false;
			$data[ 'ismod' ] = false;
			$mod             = false;

			foreach ( $data[ 'moderators' ] as $_rs )
			{
				if ( $userid == $_rs[ 'userid' ] )
				{
					$mod           = $_rs[ 'permissions' ] ? unserialize($_rs[ 'permissions' ]) : false;
					$ismod         = true;
					break;
				}
			}


			if ( is_array($mod) )
			{
				$data[ 'ismod' ] = true;
				$data[ 'mod' ]   = $mod[ 'perm' ];
				unset($mod);
			}











			if ( $this->forum[ 'containposts' ] )
			{
				$this->loadPostings($data[ 'ismod' ]);

				$data[ 'paging' ] = $this->pages;
				$data[ 'posts' ]  = $this->postcache;
			}
		}



		$data[ 'totalthreads' ] = $totalthreads;
		/*
		 * @todo real post counter or with the thread post?
		 */
		$data[ 'totalposts' ] = $totalposts - $totalthreads;
		$data[ 'forums' ]     = $tmpData;
		$data[ 'forumjump' ]  = $this->catcache;


		if (IS_AJAX)
		{
			list($paging, $list) = $this->Template->process('board/forumindex', $data, null, 'pageing,forumposts');

			echo Library::json(array('success' => true, 'paging' => $paging, 'threadrows' => $list));
			exit;
		}


		$users     = $this->db->query('SELECT s.userid, u.username, u.invisible
								   FROM %tp%session AS s
								   LEFT OUTER JOIN %tp%users AS u ON(u.userid = s.userid)
								   WHERE s.location LIKE ?
								   ORDER BY u.username ASC', '/forum%')->fetchAll();
		$userCount = 0;
		$guests    = 0;
		$invisible = 0;
		foreach ( $users as $u )
		{
			if ( !$u[ 'userid' ] )
			{
				++$guests;
			}
			else
			{
				++$userCount;
			}
			if ( $u[ 'invisible' ] )
			{
				++$invisible;
			}
		}


		$data[ 'count' ][ 'total' ]     = $guests + $userCount;
		$data[ 'count' ][ 'guests' ]    = $guests;
		$data[ 'count' ][ 'invisible' ] = $invisible;
		$data[ 'count' ][ 'users' ]     = $userCount;
		$data[ 'activeusers' ]          = $users;

		$att = $this->model->countAttachments();
		$data[ 'totalattachments' ] = $att['attachmentcounter'];
       # $data = array_merge($this->Template->getTemplateData(), $data);

		$this->Template->process('board/forumindex', $data, true);
	}

	private function getBackend ()
	{
		$this->updateModeratorsCache();

		$this->getForumTree(0, 0);

		$listTreeView = new ListGrid(false);
		$listTreeView->addheader(array (
		                               array (
			                               "field"   => "handle",
			                               "class"   => "handle",
			                               "content" => '',
			                               "default" => true,
			                               'width'   => '1%'
		                               ),
		                               array (
			                               'islabel' => true,
			                               "field"   => "title",
			                               "class"   => "title",
			                               "content" => 'Forum',
			                               "default" => true
		                               ),
		                               array (
			                               "field"   => "threadcounter",
			                               "class"   => "items tc",
			                               "content" => 'Themen',
			                               'width'   => '100',
			                               "default" => true,
			                               'align'   => 'tc'
		                               ),
		                               array (
			                               "field"   => "postcounter",
			                               "class"   => "items tc",
			                               "content" => 'Beiträge',
			                               'width'   => '100',
			                               "default" => true,
			                               'align'   => 'tc'
		                               ),
		                               array (
			                               "field"   => "publish",
			                               "class"   => "items tc",
			                               "content" => 'Aktiv',
			                               'width'   => '60',
			                               "default" => true,
			                               'align'   => 'tc'
		                               ),
		                               array (
			                               "field"   => "options",
			                               "class"   => "options tr",
			                               "content" => 'Optionen',
			                               'width'   => '100',
			                               "default" => true,
			                               'align'   => 'tr'
		                               )
		                         ));

		$html = $this->loadHtmlTree(0, $html, $listTreeView);

		if ( $this->input('getGridData') )
		{
			echo Library::json(array (
			                         'success' => true,
			                         'rows'    => $html
			                   ));
			exit;
		}


		$data[ 'header' ]    = $listTreeView->getHeader();
		$data[ 'forums' ]    = $html;
		$data[ 'nopadding' ] = true;
		unset($listTreeView);

		Library::addNavi('Foren');



		// $this->Template->addScript('Packages/plugins/Forum/asset/css/backend.css', true);

        $this->Template->process('forum/index', $data, true);
		exit;
	}

	/**
	 *
	 * @param integer  $parentid
	 * @param string   $html
	 * @param ListGrid $listTreeView
	 * @return string
	 */
	private function loadHtmlTree ( $parentid = 0, &$html, ListGrid $listTreeView )
	{

		if ( !is_array($this->cats[ $parentid ]) )
		{
			return $html;
		}

		$t1    = trans('Forum %s bearbeiten');
		$t2    = trans('Forum %s löschen');
		$t3    = trans('Forum %s Moderatoren');
		$trans = trans('Forum Aktivieren/deaktivieren');

		$im = BACKEND_IMAGE_PATH;

		foreach ( $this->cats[ $parentid ] as $idx => $rows )
		{
			foreach ( $rows as $r )
			{

				$t1 = sprintf($t1, $r[ 'title' ]);
				$t2 = sprintf($t2, $r[ 'title' ]);
				$t3 = sprintf($t3, $r[ 'title' ]);


				if ( $parentid == 0 && $r[ 'parent' ] == 0 )
				{
					$html .= '
                        <li id="forum_' . $r[ 'forumid' ] . '" class="tree-grid-row">
                            <div class="row" id="data-' . $r[ 'forumid' ] . '">';

					$option = '';

					if ( $r[ 'containposts' ] )
					{
						$option = '<a class="doTab" href="admin.php?adm=plugin&plugin=forum&action=managemods&id=' . $r[ 'forumid' ] . '"><img src="' . $im . 'worker.png" width="16" height="16" title="' . $t3 . '" alt="" /></a> ';
					}


					$option .= <<<EOF

    
    <a class="doTab" href="admin.php?adm=plugin&plugin=forum&action=editforum&id={$r['forumid']}"><img src="{$im}edit.png" width="16" height="16" title="{$t1}" alt=""/></a>
    <a class="delconfirm" href="admin.php?adm=plugin&plugin=forum&action=deleteforum&id={$r['forumid']}"><img src="{$im}delete.png" width="16" height="16" title="{$t2}" alt="" /></a>

EOF;

					$icon = $r[ 'published' ] ? 'online.png' : 'offline.png';

					$publish = <<<EOF

        <a href="javascript:void(0);" onclick="changePublish('pub{$r['forumid']}','admin.php?adm=plugin&plugin=forum&action=publishforum&id={$r['forumid']}')">
            <img src="{$im}{$icon}" width="16" height="16" title="{$trans}" alt="" id="pub{$r['forumid']}"/>
        </a>
EOF;

					$row = $listTreeView->addRow($r[ 'forumid' ], $r);
					$row->addField('handle', '');
					$row->addField('title', $r[ 'title' ]);
					$row->addField('threadcounter', $r[ 'threadcounter' ]);
					$row->addField('postcounter', $r[ 'postcounter' ]);
					$row->addField('publish', $publish);
					$row->addField('options', $option);

					$html .= $row->display();
					unset($row);

					$html .= '</div>';


					if ( isset($this->cats[ $r[ 'forumid' ] ]) )
					{
						$html .= '
                                <ul>';
						// Alle Childs
						$this->loadHtmlTree($r[ 'forumid' ], $html, $listTreeView);
						$html .= '
                                </ul>';
					}

					$html .= '

                        </li>';
				}
				else
				{
					if ( $r[ 'parent' ] == $parentid )
					{
						$icon   = $r[ 'published' ] ? 'online.png' : 'offline.png';
						$option = '';
						if ( $r[ 'containposts' ] )
						{
							$option = '<a class="doTab" href="admin.php?adm=plugin&plugin=forum&action=managemods&id=' . $r[ 'forumid' ] . '"><img src="' . $im . 'worker.png" width="16" height="16" title="' . $t3 . '" alt="" /></a> ';
						}
						$option .= <<<EOF

    <a class="doTab" href="admin.php?adm=plugin&plugin=forum&action=editforum&id={$r['forumid']}"><img src="images/edit.png" width="16" height="16" title="{$t1}" alt=""/></a>
    <a class="delconfirm" href="admin.php?adm=plugin&plugin=forum&action=deleteforum&id={$r['forumid']}"><img src="images/delete.png" width="16" height="16" title="{$t2}" alt=""/></a>

EOF;

						$publish = <<<EOF

        <a href="javascript:void(0);" onclick="changePublish('pub{$r['forumid']}','admin.php?adm=plugin&plugin=forum&action=publishforum&id={$r['forumid']}')">
            <img src="images/{$icon}" width="16" height="16" title="{$trans}" alt="" id="pub{$r['forumid']}"/>
        </a>
EOF;

						$html .= '
                            <li id="forum_' . $r[ 'forumid' ] . '" class="tree-grid-row">
                                <div class="row" id="data-' . $r[ 'forumid' ] . '">';
						//$html .= $r['title'];

						$row = $listTreeView->addRow($r[ 'forumid' ], $r);
						$row->addField('handle', '');
						$row->addField('title', $r[ 'title' ]);
						$row->addField('threadcounter', $r[ 'threadcounter' ]);
						$row->addField('postcounter', $r[ 'postcounter' ]);
						$row->addField('publish', $publish);
						$row->addField('options', $option);
						$html .= $row->display();


						unset($row);

						$html .= '</div>';


						// Alle Childs
						if ( isset($this->cats[ $r[ 'forumid' ] ]) )
						{
							$html .= '<ul>';
							$this->loadHtmlTree($r[ 'forumid' ], $html, $listTreeView);
							$html .= '</ul>';
						}
						$html .= '

                            </li>';
					}
				}
			}
		}

		return $html;
	}

}
