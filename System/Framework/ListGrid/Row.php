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
 * @category     Framework
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Row.php
 */
class ListGrid_Row
{

	/**
	 * @var array
	 */
	private $aHeaders = array ();

	/**
	 * @var array
	 */
	private $aFields = array ();

	/**
	 * @var bool
	 */
	private $addCheckAll = false;

	/**
	 *
	 * @var array
	 */
	public $rowData = array ();

	/**
	 * @var null
	 */
	public $id = null;

	/**
	 * @var int
	 */
	public $indexFields = 0;

	protected $labelColumn = false;

	private $removeColumns = array();

    /**
     *
     * @param array $headers
     * @param bool $addCheckAll
     * @param bool $labelColumn
     */
	public function __construct ( &$headers, $addCheckAll = false, $labelColumn = false )
	{
		$this->aHeaders    = &$headers;
		$this->addCheckAll = $addCheckAll;
		$this->labelColumn = $labelColumn;
	}

	/**
	 *
	 * @param integer $fieldname
	 * @param string  $sHTML
	 * @return \ListGrid_Row
	 */
	public function addField ( $fieldname, $sHTML )
	{

		$this->aFields[ $fieldname ][ 'content' ] = $sHTML;
		return $this;
	}

    /**
     * @return array
     */
    public function getRemovedColumns() {
		return $this->removeColumns;
	}

	/**
	 * @return string
	 */
	public function display ()
	{

		static $y;

		$ret = "\r\n<table width=\"100%\">\r\n";
		$ret .= "\t<tr>\r\n";

		if ( $this->addCheckAll )
		{
			$ret .= '
		<td width="1%">
                    <div><input type="checkbox" class="checkall" name="ids[]" value="' . $this->id . '" title="' . trans('Auswählen/Abwählen') . '"/></div>
		</td>';
		}


		$remove = array();

		foreach ( $this->aHeaders as $fieldname => &$opts )
		{

			$fieldContent = ( isset( $this->aFields[ $fieldname ][ 'content' ] ) && is_string($this->aFields[ $fieldname ][ 'content' ]) ? $this->aFields[ $fieldname ][ 'content' ] : '' );


			if ( $fieldContent )
			{

				preg_match_all('#<a([^>]*)href=(["\'])([^\2]*)\2([^>]*)>(.*)</a>#isU', $fieldContent, $match, PREG_SET_ORDER);

				if ( isset( $match[ 0 ] ) )
				{
					foreach ( $match as $idx => $s )
					{
						$icon  = $match[ $idx ][ 5 ];
						$url   = $match[ $idx ][ 3 ];
						$label = false;

						if ( empty( $icon ) )
						{
							continue;
						}
						else
						{
							preg_match('#title=(["\'])([^\1]*)\1#isU', $icon, $m);
							if ( !$m[ 2 ] )
							{
								preg_match('#alt=(["\'])([^\1]*)\1#isU', $icon, $m);
								if ( !$m[ 2 ] )
								{
									continue;
								}
								else
								{
									$label = trim($m[ 2 ]);
								}
							}
							else
							{
								$label = trim($m[ 2 ]);
							}
						}

						$type = false;
						if ( strpos($url, '=edit') !== false )
						{
							$type = 'edit';
						}
						elseif ( strpos($url, '=delete') !== false || strpos($url, '=remove') !== false )
						{
							$type = 'delete';
						}
						elseif ( strpos($url, 'action=index') === false && preg_match('#action=([\w_\-]+?)#isU', $url, $ac) )
						{
							$type = $ac[ 1 ];
						}

						// trim url
						$url                                         = preg_replace('#.*\?(.*)#U', '$1', $url);
						$this->aFields[ $this->labelColumn ][ 'actions' ][ ] = array ( 'label' => $label, 'url' => $url, 'type' => $type );
					}

					if (stripos($fieldname, 'options') !== false && $type != false ) {
						$remove[] = $fieldname;
					}
				}


				preg_match_all('#<a([^>]*)onclick=(["\'])([^\2]*)\2([^>]*)>(.*)</a>#isU', $fieldContent, $match, PREG_SET_ORDER);
				if ( isset( $match[ 0 ] ) )
				{

					foreach ( $match as $idx => $s )
					{

						$url = $match[ $idx ][ 3 ];

						if ( stripos($url, 'changePublish') === false )
						{
							continue;
						}

						$label = false;
						preg_match('#title=(["\'])([^\1]*)\1#isU', $match[ $idx ][ 0 ], $m);
						if ( !$m[ 2 ] )
						{
							preg_match('#alt=(["\'])([^\1]*)\1#isU', $match[ $idx ][ 0 ], $m);
							if ( !$m[ 2 ] )
							{
								continue;
							}
							else
							{
								$label = trim($m[ 2 ]);
							}
						}
						else
						{
							$label = trim($m[ 2 ]);
						}


						$isPublished = true;
						preg_match('#src=(["\'])([^\1]*)\1#isU', $match[ $idx ][ 5 ], $m);
						if ( $m[ 2 ] )
						{
							if ( strpos($m[ 2 ], 'online') !== false )
							{
								$isPublished = 1;
							}
							else if ( strpos($m[ 2 ], 'clock') !== false )
							{
								$isPublished = 2;
							}
							else
							{
								$isPublished = 0;
							}
						}


						$type = 'publish';


						// trim url
						preg_match('#,(["\'])([^\1]*)\1#is', $url, $m);
						$url = $m[ 2 ];

						$this->aFields[ $this->labelColumn ][ 'actions' ][ ] = array (
							'label'     => $label,
							'url'       => $url,
							'type'      => $type,
							'published' => $isPublished
						);

					}

					if ( (stripos($fieldname, 'options') !== false || stripos($fieldname, 'publish') !== false ) && $type === 'publish') {
						$remove[] = $fieldname;
					}
				}
			}
		}

		$this->removeColumns = array_unique($remove);


		foreach ( $this->aHeaders as $fieldname => $opts )
		{
			if ( $this->labelColumn && $fieldname == 'options' || in_array($fieldname, $this->removeColumns) )
			{
				continue;
			}

			$fieldContent = ( isset( $this->aFields[ $fieldname ][ 'content' ] ) && is_string($this->aFields[ $fieldname ][ 'content' ]) ? $this->aFields[ $fieldname ][ 'content' ] : '' );
			$css          = '';


			if ( isset( $opts[ 'class' ] ) && !empty( $opts[ 'class' ] ) )
			{
				$css .= $opts[ 'class' ];
			}

			if ( isset( $opts[ 'nowrap' ] ) && !empty( $opts[ 'nowrap' ] ) )
			{
				$css .= ' nowrap';
			}

			$ret .= '
            <td' . ( intval($opts[ "width" ]) > 0 ? ' width="' . $opts[ 'width' ] . '"' : '' ) . ( $css ? ' class="' . $css . '"' : '' ) . '><div>
                ';



			$ret .= '<div class="row-label">';
			$ret .= trim($fieldContent);
			$ret .= '</div>';


			if ( $fieldname === $this->labelColumn && isset( $this->aFields[ $fieldname ][ 'actions' ] ) )
			{
				$ret .= '<div class="opt">';





				foreach ( $this->aFields[ $fieldname ][ 'actions' ] as $idx => $rs )
				{
					if ( $rs[ 'url' ] && !preg_match('#javascript:#is', $rs[ 'url' ]) )
					{
						$hash = md5($rs[ 'url' ]);

						if ( $rs[ 'type' ] === 'edit' )
						{
							$l = trans('Bearbeiten');
							$ret .= <<<E
								<a href="admin.php?{$rs['url']}" class="edit doTab"><span class="fa fa-cog"></span><span class="pub-label">{$l}</span></a>
E;
						}
						elseif ( $rs[ 'type' ] === 'delete' )
						{
							$l = trans('Löschen');
							$ret .= <<<E
								<a href="admin.php?{$rs['url']}" class="delete delconfirm"><span></span><span class="pub-label">{$l}</span></a>
E;
						}
						elseif ( $rs[ 'type' ] === 'publish' )
						{


							if ( $rs[ 'published' ] == 0 )
							{
								$l = trans('Deaktiviert');
								$ret .= <<<E
							<a href="javascript:void(0)" rel="pub{$hash}-{$idx}" publishurl="{$rs['url']}" class="unpublished"><span id="pub{$hash}-{$idx}" class="unpublished fa fa-refresh"></span><span class="pub-label">{$l}</span></a>
E;
							}
							elseif ( $rs[ 'published' ] == 1 )
							{
								$l = trans('Aktiviert');
								$ret .= <<<E
							<a href="javascript:void(0)" rel="pub{$hash}-{$idx}" publishurl="{$rs['url']}" class="published"><span id="pub{$hash}-{$idx}" class="published fa fa-refresh"></span><span class="pub-label">{$l}</span></a>
E;
							}
							elseif ( $rs[ 'published' ] == 2 )
							{
								$l = trans('Zeitgesteuert');
								$ret .= <<<E
							<span class="timecontrol"><span class="fa fa-clock-o"></span><span class="pub-label">{$l}</span>
E;
							}
						}
						else
						{
							$label = $rs['label'];
							if (strpos($rs['url'], '=managemods') !== false) {
								$label = trans('Moderatoren');
							}






							$ret .= <<<E
							<a href="admin.php?{$rs['url']}" class="doTab"><span class="fa fa-cog"></span><span class="pub-label">{$label}</span></a>
E;
						}


						$ret .= <<<E
						<i></i>
E;
					}
				}
				$ret .= '</div>';

				unset($this->aFields[ $fieldname ][ 'actions' ]);
			}

			$ret .= '</div>';
			$ret .= '
            </td>';
		}

		$ret .= "\t</tr>\r\n";
		$ret .= "\r\n</table>";


		unset($this->aFields[ $fieldname ]);


		return $ret;
	}

}
