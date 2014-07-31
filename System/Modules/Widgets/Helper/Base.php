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
 * @package      Widgets
 * @version      3.0.0 Beta
 * @category     Helper
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Widgets_Helper_Base extends Controller_Abstract
{

	/**
	 *
	 * @var array
	 */
	private static $loaded;

	/**
	 *
	 * @var string
	 */
	protected $_widget_path;

	/**
	 * @var string
	 */
	protected $_key = '';

	/**
	 * @var int
	 */
	protected $_id = 0;

	/**
	 * @var array
	 */
	protected $_config = array ();

	/**
	 * @var null
	 */
	protected $_sclass = null;

	/**
	 * @var int
	 */
	protected $_widgetcols = 4;

	/**
	 * @var
	 */
	protected $_output;

	/**
	 * @var null
	 */
	protected $_widgetName = null;

	/**
	 * @var null
	 */
	protected $_name = null;

	/**
	 * @var null
	 */
	protected $_module = null;

	/**
	 * @var array
	 */
	protected $_widgetData = array ();

    protected $_userwidgets = array();

    /**
     * @todo build modul widgets
     * @return array
     */
    public function getAllWidgets()
    {
		return Library::getDirs(WIDGET_PATH);
	}

	/**
	 *
	 * @param string  $name
	 * @param integer $id
	 * @param bool    $getHtmlOnly
	 * @return
     * @todo build modul widgets
	 */
	public function configWidget ( $name, $id, $getHtmlOnly = false )
	{

		$widget_id         = (int)$id;
		$arr               = $this->get_user_widget_by_id($widget_id);


		$this->_widgetName = $arr[ 'widget' ];
		$config = !empty($arr[ 'config' ]) ? unserialize($arr[ 'config' ]) : array ();

		//$this->load( 'Widget' );
		$widget = new Widget();
		$output = $widget
            ->viewMode(Widget::CONFIG)
            ->setID($widget_id)
            ->setConfig($config)
            ->setName($arr[ 'widget' ])
            ->run();

		if ( $getHtmlOnly )
		{
			return $output;
		}
        Ajax::Send(true, array (
            'key'     => $arr[ 'widget' ],
            'id'      => (int)$id,
            'title'   => $arr[ 'label' ],
            'output'  => $output
        ));
		exit;
	}

	/**
	 *
	 * @param string  $name
	 * @param integer $id
	 * @param bool    $getHtmlOnly
	 * @return
     * @todo build modul widgets
	 */
	public function runWidget ( $name, $id, $getHtmlOnly = false )
	{

		$widget_id         = (int)$id;
		$arr               = $this->getUserConfig((int)$id);


		$this->_widgetName = $arr[ 'widget' ];

		$config = !empty($arr[ 'config' ]) ? unserialize($arr[ 'config' ]) : array ();

		//$this->load( 'Widget' );
		$widget = new Widget();
		$output = $widget
            ->setConfig($config)
            ->setName($arr[ 'widget' ])
            ->setID((int)$id)
            ->viewMode(Widget::SHOW)
            ->run();

		unset($widget);

		if ( $getHtmlOnly )
		{
			return $output;
		}


        Ajax::Send(true, array (
            'key'     => $arr[ 'widget' ],
            'id'      => (int)$id,
            'title'   => $arr[ 'label' ],
            'output'  => $output
        ));

		exit;
	}

	/**
	 *
	 * @param string $name
	 * @return string|false
     *
     * @todo build modul widgets
	 */
	public function loadWidget ( $name = null )
	{

		return 'Widget_' . ucfirst(($name ? strtolower($name) : strtolower($this->_widgetName)));
	}

	/**
	 *
	 * @param integer $id
	 */
	public function getUserConfig ( $id = 0 )
	{

		$arr = $this->get_user_widget_by_id($id);

		if ( !$arr[ 'id' ] )
		{
			trigger_error('Widget not found!', E_USER_ERROR);
		}

		$this->_id     = $arr[ 'id' ];
		$this->_key    = $arr[ 'widget' ];
		$this->_config = unserialize($arr[ 'config' ]);

        return $arr;
	}

	/**
	 *
	 * @param integer $_id
	 * @return array
	 */
	public function get_user_widget_by_id ( $_id = 0 )
	{
        $widgets = $this->get_user_widgets();
        $id = (($_id > 0) ? $_id : (int)$this->input('id'));

        foreach ($widgets as $r) {
            if ($id == $r['id']) {
                return $r;
            }
        }

		return $this->db->query('SELECT * FROM %tp%users_widget WHERE userid = ? AND id = ? LIMIT 1', User::getUserId(), (int)$id)->fetch();
	}

	/**
	 *
	 * @return array
	 */
	public function get_user_widgets ()
	{
        static $cache;

        if (!is_array($cache)) {
            $cache = array();
        }
        else {
            return $cache;
        }

		$cache = $this->db->query('SELECT * FROM %tp%users_widget WHERE userid=? ORDER BY col ASC, pos ASC', User::getUserId())->fetchAll();
        return $cache;
	}

	/**
	 *
	 * @return array
	 */
	public function get_user_widgets_keys ()
	{
        static $cache;

        if (!is_array($cache)) {
            $cache = array();
        }
        else {
            return $cache;
        }

		$result = $this->db->query('SELECT * FROM %tp%users_widget WHERE userid=? ORDER BY col ASC, pos ASC', User::getUserId())->fetchAll();
		foreach ( $result as $r )
		{
            $cache[ $r[ 'widget' ] ] = $r;
		}

		return $cache;
	}

	/**
	 *
	 * @param string $widget
	 * @return array
	 */
	public function getWidgetByName ( $widget )
	{
		return $this->db->query('SELECT * FROM %tp%widget WHERE widgetkey=? LIMIT 1', $widget)->fetch();
	}

	/**
	 *
	 * @param integer $widgetid
	 * @return array
	 */
	public function getWidgetById ( $widgetid )
	{
		return $this->db->query('SELECT * FROM %tp%widget WHERE id=? LIMIT 1', $widgetid)->fetch();
	}

	/**
	 *
	 * @return string javascript (var widgets = ...)
	 */
	public function setWidgetSession ()
	{

		$installed = array ();
		$sql       = "SELECT * FROM %tp%widget LIMIT 100";
		$result    = $this->db->query($sql)->fetchAll();
		foreach ( $result as $r )
		{
			$installed[ $r[ 'widgetkey' ] ] = $r;
		}

		$userwidgets = $this->get_user_widgets();
		$widgets     = array ();
		foreach ( $userwidgets as $key => $r )
		{
			$rs              = $installed[ $r[ 'widget' ] ];

			$widgets[ $key ] = array (
				'id'             => $r[ 'id' ],
				'name'           => (isset($r[ 'label' ]) && !empty($r[ 'label' ]) ? $r[ 'label' ] : $rs[ 'title' ]),
				'key'            => $r[ 'widget' ],
				'col'            => $r[ 'col' ],
				'pos'            => $r[ 'pos' ],
				'configurable'   => $rs[ 'configurable' ],
				'collapsible'    => $r[ 'collapsible' ],
				'externalconfig' => $rs[ 'externalconfig' ],
				'left'           => $r[ 'left' ],
				'top'            => $r[ 'top' ],
			);

            if ( $r[ 'id' ] ) {
                $widgets[ $key ][ 'content_html' ] = $this->runWidget($r[ 'widget' ], $r[ 'id' ], true);


                if ( $rs[ 'configurable' ] )
                {
                    $widgets[ $key ][ 'settings_html' ] = $this->configWidget($r[ 'widget' ], $r[ 'id' ], true);
                }
            }

			// $widgets .= ( $widgets ? ',' : '') . '{"id":"' . $r[ 'id' ] . '","name":"' . $rs[ 'title' ] . '","key":"' . $r[ 'widget' ] . '","col":"' . $r[ 'col' ] . '","pos":"' . $r[ 'pos' ] . '","configurable":"' . $rs[ 'configurable' ] . '","collapsible":"' . $r[ 'collapsible' ] . '","externalconfig":"' . $rs[ 'externalconfig' ] . '","x":"' . $r[ 'x' ] . '","y":"' . $r[ 'y' ] . '"}';
		}

		// $code = Library::json($widgets); //'var widgets = [' . $widgets . '];';


		$tmp = $widgets;
		foreach ( $tmp as $idx => &$r) {
			unset($r['content_html']);

			if (isset($r['settings_html'])) {
				unset($r['settings_html']);
			}
		}

		Session::save('WIDGETS', $tmp);

		return $widgets;
	}

}

?>