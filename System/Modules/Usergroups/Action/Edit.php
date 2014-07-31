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
 * @package      Usergroups
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edit.php
 */
class Usergroups_Action_Edit extends Controller_Abstract
{

	/**
	 * @var array
	 */
	protected $_permKeys = array ();

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$id = (int)HTTP::input('id');

		$group = array ();


		$this->_permKeys = Permission::initFrontendPermissions();

		/*
		  $this->load('App');
		  $appPerms = $this->App->registerPermissions();
		  foreach($appPerms as $key => $data)
		  {
		  $this->_permKeys['usergroup'][$key] = $data;
		  }
		 */

		if ( $id )
		{
			$model = Model::getModelInstance('usergroups');
			$group = $model->getGroupByID($id);
		}


		$res = $this->getEditHTML($group);

		$tab_title   = array ();
		$default_tab = '';
		$cat_code    = '';

		foreach ( $res[ 'cats' ] as $k )
		{
			$cat_code .= $cat_code ? ' | <a href="#' . $k[ 'id' ] . '">' . $k[ 'title' ] . '</a>' :
				'<a href="#' . $k[ 'id' ] . '">' . $k[ 'title' ] . '</a>';

			array_push($tab_title, $k[ 'title' ]);

			if ( !$default_tab )
			{
				$default_tab = $k[ 'title' ];
			}
		}


		$data                                 = array ();
		$data[ 'usergroup' ][ 'dashboard' ]   = ($group[ 'dashboard' ] ? true : false);
		$data[ 'usergroup' ][ 'title' ]       = $group[ 'title' ];
		$data[ 'usergroup' ][ 'description' ] = $group[ 'description' ];
		$data[ 'usergroup' ][ 'groupid' ]     = $id;
		$data[ 'usergroup' ][ 'grouptype' ]   = $group[ 'grouptype' ];

		$data[ 'usergroup' ][ 'grouptypes' ] = array ();
		foreach ( Usergroup::getUsergroupTypes() as $value => $translation )
		{
			$data[ 'usergroup' ][ 'grouptypes' ][ ] = array (
				'value' => $value,
				'label' => $translation
			);
		}

		$data[ 'defaulttab' ]     = $default_tab;
		$data[ 'tab_titles' ]     = $tab_title;
		$data[ 'tab_containers' ] = $res[ 'output' ];

		Library::addNavi(trans('Benutzergruppen Übersicht'));
		Library::addNavi(($group[ 'title' ] ? sprintf(trans('Benutzergruppe `%s` bearbeiten'), $group[ 'title' ]) :
			trans('Benutzergruppe erstellen')));


		$this->Template->addScript(BACKEND_JS_URL . 'dcms.perms.js');
		$this->Template->process('group/edit', $data, true);
	}

	/**
	 * @param array $group
	 * @return array
	 */
	private function getEditHTML ( $group = array () )
	{

		$group_perm = array ();

		if ( !empty($group[ 'groupid' ]) )
		{
			$group_perm = unserialize($group[ 'permissions' ]);
		}

		$form_code = '';
		$tabs      = array ();
		$x         = 0;
		foreach ( $this->_permKeys[ 'usergroup' ] as $key => $rows )
		{
			$tablabel       = $rows[ 'tablabel' ];
			$tabdescription = !empty($rows[ 'tabdescription' ]) ?
				'<div class="p5 mb5">' . $rows[ 'tabdescription' ] . '</div>' : '';

			array_push($tabs, array (
			                        'id'    => $x,
			                        'title' => $tablabel
			                  ));


			// the first row is the tab Label. also remove it
			array_shift($rows);

			$groupfieldname = 'perm[' . $key . ']';

			$form_code .= <<<EOF
<div id="tc{$x}" class="tab-content" style="display:none">
    <div class="box">
        <h2>{$tablabel}</h2>
        <div class="box-inner perm-{$key}">
            {$tabdescription}
EOF;
			// if exists the usergroup permission then use it
			$usergroup_db_perm = isset($group_perm[ $key ]) ? $group_perm[ $key ] : array ();

			foreach ( $rows as $fieldname => $field )
			{
				$html_fieldname = 'perm[' . $key . '][' . $fieldname . ']';


				$rel         = (!empty($field[ 'require' ]) ? ' rel="' . $field[ 'require' ] . '"' : '');
				$description = (!empty($field[ 'description' ]) ?
					'<span class="note">' . $field[ 'description' ] . '</span>' : '');
				$label       = $field[ 'label' ];

				switch ( $field[ 'type' ] )
				{
					case 'checkbox':
					case 'radio':
						$checked = ((isset($usergroup_db_perm[ $fieldname ]) && $usergroup_db_perm[ $fieldname ]) || ($field[ 'default' ] && !isset($usergroup_db_perm[ $fieldname ])) ?
							' checked="checked"' : '');
						$form_code .= <<<EOF

                            <label{$rel} for="{$key}-{$fieldname}"><input class="form-control" type="{$field['type']}" id="{$key}-{$fieldname}" name="{$html_fieldname}" value="1"{$checked} /> {$label}{$description}</label>
EOF;
						break;
					case 'text':
						$value = (!empty($usergroup_db_perm[ $fieldname ]) ?
							htmlspecialchars($usergroup_db_perm[ $fieldname ]) : $field[ 'default' ]);
						$size  = (!empty($field[ 'width' ]) ? $field[ 'width' ] : 60);


						$form_code .= <<<EOF

                            <fieldset{$rel} id="{$key}-{$fieldname}">
                                <label>{$label}</label>
                                <input type="text" name="{$html_fieldname}" size="{$size}" value="{$value}" class="form-control"/>
                                {$description}
                            </fieldset>
EOF;
						break;

					case 'textarea':
						$value = (!empty($usergroup_db_perm[ $fieldname ]) ?
							htmlspecialchars($usergroup_db_perm[ $fieldname ]) : $field[ 'default' ]);
						$cols  = (!empty($field[ 'cols' ]) ? $field[ 'cols' ] : 60);
						$rows  = (!empty($field[ 'rows' ]) ? $field[ 'rows' ] : 4);


						$form_code .= <<<EOF

                            <fieldset{$rel} id="{$key}-{$fieldname}">
                                <label>{$label}</label>
                                <textarea name="{$html_fieldname}" rows="{$rows}" cols="{$cols}" class="textarea-resize form-control">{$value}</textarea>
                                {$description}
                            </fieldset>
EOF;
						break;
				}
			}
			$form_code .= <<<EOF

        </div>
     </div><!-- close box -->
</div>



EOF;

			$x++;
		}

		$form_code .= <<<EOF

EOF;

		return array (
			'output' => $form_code,
			'cats'   => $tabs
		);
	}

}

?>