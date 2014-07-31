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
 * @package      Layouter
 * @version      3.0.0 Beta
 * @category     Helper
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Layouter_Helper_Base extends Controller_Abstract
{

	/**
	 * @param int $relID
	 * @param     $inData
	 * @return array
	 */
	public function findBlockData ( $relID = 0, $inData )
	{

		foreach ( $inData as $r )
		{
			if ( $r[ 'id' ] === $relID )
			{
				return $r;
			}
		}

		return array ();
	}

	/**
	 * Will clear the Cache files for the Layout
	 *
	 * @param integer  $layoutid
	 * @param bool|int $blockid
	 */
	public function clearLayoutCache ( $layoutid, $blockid = false )
	{

		if ( is_int($blockid) && $blockid > 0 )
		{
			Cache::delete('layoutBlocks', 'data/layout/' . $layoutid);
			$f     = new File('', true);
			$files = glob(PAGE_CACHE_PATH . 'data/layout/' . $layoutid . '/layoutBlocksData-' . $blockid . '-*');
			foreach ( $files as $file )
			{
				$file = str_replace(ROOT_PATH, '', $file);
				$f->delete($file);
			}
		}
		else
		{
			Cache::delete('layoutBlocks', 'data/layout/' . $layoutid);
			Cache::clear('data/layout/' . $layoutid, true);
		}
	}

	/**
	 *
	 * @param integer  $layoutid
	 * @param bool|int $blockid used if add a new contentbox
	 */
	public function updateLayoutBlockids ( $layoutid, $blockid = false )
	{

		$_rs     = $this->db->query('SELECT blockids FROM %tp%layouts WHERE id = ?', $layoutid)->fetch();
		$_blocks = array ();

		if ( is_int($blockid) && $blockid > 0 )
		{
			$_blocks    = explode(',', $_rs[ 'blockids' ]);
			$_blocks[ ] = $blockid;
		}
		else
		{
			$result = $this->db->query('SELECT blockid FROM %tp%layout_settings WHERE layoutid = ?', $layoutid)->fetchAll();

			foreach ( $result as $r )
			{
				$_blocks[ ] = $r[ 'blockid' ];
			}
		}

		$this->db->query('UPDATE %tp%layouts SET blockids = ? WHERE id = ?', implode(',', $_blocks), $layoutid);
	}

	/**
	 *
	 * @param array $row
	 * @return mixed return array if has subcols and retuns null if not has subcols
	 */
	public function extractSubCols ( $row )
	{

		$list = explode(',', $row[ 'settings' ]);

		$tmp         = array ();
		$subcols     = array ();
		$subcolItems = array ();

		foreach ( $list as $idx => $listname )
		{
			if ( !trim($listname) )
			{
				continue;
			}

			// extract subcols
			if ( $row[ 'subcolhtml' ] !== null && trim($row[ 'subcolhtml' ]) !== '' )
			{

				preg_match('#\[START:' . preg_quote($listname, '#') . '\](.*)\[/END:' . preg_quote($listname, '#') . '\]#isU', $row[ 'subcolhtml' ], $match);

				if ( $match[ 1 ] )
				{

					$subcols[ $listname ] = $match[ 1 ];
					preg_match_all('#\[([a-z0-9_\-]*)\]#isU', $subcols[ $listname ], $matches);
					$subcolItems[ $listname ] = $matches[ 1 ];
					$tmp                      = array_merge($tmp, $matches[ 1 ]);
				}


				/*
				  preg_match_all( '#\[START:([a-zA-Z0-9_]+?)\]#s', $row[ 'subcolhtml' ], $matches );

				  foreach ( $matches[ 1 ] as $subid )
				  {
				  if ( $subid == $listname )
				  {
				  preg_match( '#\[START:' . $subid . '\](.*)\[/END:' . $subid . '\]#isU', $row[ 'subcolhtml' ], $subhtml );
				  $subcols[ $subid ] = $subhtml[ 1 ];

				  preg_match_all( '#\[([a-z0-9_\-]+?)\]#is', $subhtml[ 1 ], $matches );
				  $subcolItems[ $subid ] = $matches[ 1 ];
				  $tmp                   = array_merge( $tmp, $matches[ 1 ] );

				  $row[ 'subcolhtml' ] = str_replace( '[START:' . $subid . ']' . $subhtml[ 1 ] . '[END:' . $subid . ']', '', $row[ 'subcolhtml' ] );

				  // $row[ 'subcolhtml' ] = preg_replace( '#\[START:' . $subid . '\]' . preg_quote( $subhtml[ 1 ], '#' ) . '\[/END:' . $subid . '\]#isU', '', $row[ 'subcolhtml' ] );
				  }
				  }

				 */

				if ( $listname !== 'contentPlaceholder' )
				{
					//  echo "\n\n".$listname . "\n";
					//  print_r( $subcols );
					//   print_r( $subcolItems );
				}

				$matches = array ();
				preg_match_all('#\[([a-z0-9_\-]*)\]#isU', $row[ 'subcolhtml' ], $matches);

				$subcolItems[ 'ROOT' ] = array ();
				foreach ( $matches[ 1 ] as $v )
				{
					if ( (isset($subcolItems[ 'ROOT' ]) && !in_array($v, $subcolItems[ 'ROOT' ])) && $v != 'contentPlaceholder' )
					{
						$subcolItems[ 'ROOT' ][ ] = $v;
					}
				}
			}
		}


		if ( !count($subcols) )
		{
			//return null;
		}

		return array (
			'subcolitems' => $subcolItems,
			'subcols'     => $subcols
		);
	}

	/**
	 *
	 */
	public function renderlayout ( $data )
	{

		if ( !isset($data[ 'template' ]) || empty($data[ 'template' ]) )
		{
			// error
		}

		$this->load('Layouter');
		$this->Layouter->init($data[ 'template' ]);
		$this->Layouter->loadStyleGuide();
		$code = $this->Layouter->processStyle();

		# $code = str_replace('&#39;', "'", $code);
		$this->Layouter->saveProcessedStyle($code);

		return true;
	}

}
