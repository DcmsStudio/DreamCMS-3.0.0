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
 * @package      User
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Access.php
 */
class User_Action_Access extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		if ( HTTP::input('send') )
		{
			demoadm();
			$user_list = HTTP::input('userid');
			$user_arr  = explode(',', $user_list);


			$users = $this->model->getUserPermission($user_list, false);

			$user_names = '';
			$user_perms = array ();

			foreach ( $users as $r )
			{
				$user_perms[ $r[ 'groupid' ] ] = ( isset( $r[ 'permissions' ] ) && trim($r[ 'permissions' ]) ? unserialize($r[ 'permissions' ]) : array () );
				$user_names .= $user_names ? ', ' . $r[ 'username' ] : $r[ 'username' ];
			}

			$this->model->saveUserAccess($users, $user_perms);

			Library::log("Change special permissions for Users: " . $user_names);

			Library::sendJson(true, ( count($user_arr) > 1 ? sprintf(trans('Spezielle Rechte für die Benutzer `%s` wurden gesetzt.'), $user_names) : sprintf(trans('Spezielle Rechte für den Benutzer `%s` wurden gesetzt.'), $user_names) ));

			exit;
		}


		if ( is_array(HTTP::input('ids')) )
		{
			$userids = HTTP::input('ids');
		}
		else
		{
			$userids = array (
				HTTP::input('userid')
			);
		}

		if ( is_array($userids) )
		{
			$users_arr = $userids;
			$users     = implode(',', $userids);

			if ( HTTP::input('removespecial') )
			{
				$this->model->removeUserAccess($users);

				Library::sendJson(true, trans('Die speziellen User Rechte wurden entfernt.<br/>Es werden nun die Benutzergruppen Rechte verwendet!'));
			}


			$user_names = '';
			$result     = $this->model->findUsersById($userids);
			foreach ( $result as $r )
			{
				$user_names .= $user_names ? ', ' . $r[ 'username' ] : $r[ 'username' ];
			}

			$permission = $this->model->getUserAccess($users);


			Library::addNavi(trans('Benutzer Übersicht'));
			Library::addNavi(( $user_names ? sprintf(trans('Berechtigungen der Benutzer `%s` bearbeiten'), $user_names) : trans('Berechtigungen des Benutzers bearbeiten') ));

			$data                 = array ();
			$data[ 'users' ]      = $users;
			$data[ 'multiusers' ] = ( count($users_arr) > 1 ? true : false );

			$users = $this->model->getUserPermission($users);


			$groupperms = array ();
			foreach ( $users as $r )
			{
				$groupperms = ( isset( $r[ 'permissions' ] ) && trim($r[ 'permissions' ]) ? unserialize($r[ 'permissions' ]) : array () );
			}

			if ( !empty( $permission[ 'userid' ] ) )
			{
				$data[ 'has_specialperms' ] = true;
			}

			// Add Group
			$res = $this->getEditHTML($permission, $groupperms);


			$tab_title   = array ();
			$default_tab = '';
			$cat_code    = '';

			foreach ( $res[ 'cats' ] as $k )
			{
				$cat_code .= $cat_code ? ' | <a href="#' . $k[ 'id' ] . '">' . $k[ 'title' ] . '</a>' : '<a href="#' . $k[ 'id' ] . '">' . $k[ 'title' ] . '</a>';

				array_push($tab_title, $k[ 'title' ]);

				if ( !$default_tab )
				{
					$default_tab = $k[ 'title' ];
				}
			}

			$data[ 'usernames' ]      = $user_names;
			$data[ 'defaulttab' ]     = $default_tab;
			$data[ 'tab_titles' ]     = $tab_title;
			$data[ 'tabdata' ]        = $res[ 'tabdata' ];
			$data[ 'tab_containers' ] = $res[ 'output' ];

#print_r($data);exit;

		}
		else
		{
			Error::raise(trans("Sie haben keinen Benutzer ausgewählt oder aber der Benutzer existiert nicht."));
		}

		$this->Template->addScript(BACKEND_JS_URL . 'dcms.perms.js');
		$this->Template->process('users/access', $data, true);
	}

	/**
	 * @param array $userperms
	 * @param array $groupperms
	 * @return array
	 */
	private function getEditHTML ( $userperms = array (), $groupperms = array () )
	{

		$user_groupperm = array ();

		// Register all Application Perms
		//       $apps = new Application();
		//       $apps->registerPermissions();
		// Register all Plugin Perms
		Plugin::loadPluginPermissions();


		$permKeys = Permission::initFrontendPermissions();


		if ( !empty( $userperms[ 'userid' ] ) && isset( $userperms[ 'permissions' ] ) )
		{
			$user_groupperm = ( trim($userperms[ 'permissions' ]) ? unserialize($userperms[ 'permissions' ]) : array () );
		}

		$form_code     = '';
		$tabs          = array ();
		$tabs_contents = array ();
		$x             = 0;
		foreach ( $permKeys[ 'usergroup' ] as $key => $rows )
		{

			$tablabel       = $rows[ 'tablabel' ];
			$tabdescription = !empty( $rows[ 'tabdescription' ] ) ? '<div class="p5 mb5">' . $rows[ 'tabdescription' ] . '</div>' : '';


			array_push($tabs, array (
			                        'id'    => $x,
			                        'title' => $tablabel
			                  ));
			array_shift($rows);

			$groupfieldname = 'perm[' . $key . ']';

			$form_code .= <<<EOF
<div id="tc{$x}" style="display:none">
    <div class="panel panel-default">
        <div class="panel-heading">{$tablabel}</div>
        <div class="panel-body" perm="{$key}">
            {$tabdescription}
EOF;


			//	if ( isset($groupperms[ $r[ 'groupid' ] ][ $key ][ $fieldname ]) )
			//	{
			//		$serialize_field[ $key ][ $fieldname ] = $groupperms[ $r[ 'groupid' ] ][ $key ][ $fieldname ];
			//	}


			//	$perm     = $group_perm[ $key ];
			$userperm = $user_groupperm[ $key ];


			$y     = 0;
			$total = count($rows);

			$tabsdata = array ();


			foreach ( $rows as $fieldname => $field )
			{
				$ofieldname = $fieldname;
				$fieldname  = 'perm[' . $key . '][' . $fieldname . ']';

				//if ( $group_perm[ $key ][ $ofieldname ] == -1 && $groupperms[ $key ][ $ofieldname ] )
				//{
				//	$group_perm[ $key ][ $ofieldname ] = $groupperms[ $key ][ $ofieldname ];
				//}

				$rel         = ( !empty( $field[ 'require' ] ) ? ' rel="' . $field[ 'require' ] . '"' : '' );
				$description = ( !empty( $field[ 'description' ] ) ? '<span class="note">' . $field[ 'description' ] . '</span>' : '' );


				$field_data[ 'require' ]     = ( !empty( $field[ 'require' ] ) ? $field[ 'require' ] : false );
				$field_data[ 'label' ]       = $field[ 'label' ];
				$field_data[ 'description' ] = $description;
				$field_data[ 'fieldname' ]   = $ofieldname;
				$field_data[ 'type' ]        = $field[ 'type' ];

				$yes     = trans('Ja');
				$no      = trans('Nein');
				$default = trans('Standart');


				$y++;


				switch ( $field[ 'type' ] )
				{
					case 'checkbox':
					case 'radio':


						$checked1 = isset( $userperm[ $ofieldname ] ) && $userperm[ $ofieldname ] == 1 ? ' checked="checked"' : '';
						$checked0 = isset( $userperm[ $ofieldname ] ) && $userperm[ $ofieldname ] == 0 ? ' checked="checked"' : '';
						$checked2 = ( $userperm[ $ofieldname ] == -1 || !isset( $userperm[ $ofieldname ] ) ? ' checked="checked"' : '' );

						$idName = '';
						$use = '';

							$idName = ' id="'.$key .'-'. $ofieldname .'"';


						$form_code .= <<<EOF
                        <fieldset{$rel}{$idName}>
                            <label>{$field['label']}</label>
                            {$description}
                            <label {$rel} for="{$key}-{$ofieldname}1"><input type="radio" use="{$ofieldname}" id="{$key}-{$ofieldname}1" name="{$fieldname}" value="1"{$checked1} /> {$yes}</label>
                            <label {$rel} for="{$key}-{$ofieldname}0"><input type="radio" use="{$ofieldname}" id="{$key}-{$ofieldname}0" name="{$fieldname}" value="0"{$checked0} /> {$no}</label>
                            <label {$rel} for="{$key}-{$ofieldname}2"><input type="radio" use="{$ofieldname}" id="{$key}-{$ofieldname}2" name="{$fieldname}" value="-1"{$checked2} /> {$default}</label>
                        </fieldset>
EOF;


						$field_data[ 'field' ] = <<<EOF
                        <label{$rel} for="{$key}-{$ofieldname}1"><input type="radio" id="{$key}-{$ofieldname}1" name="{$fieldname}" value="1"{$checked1} /> {$yes}</label>
                        <label{$rel} for="{$key}-{$ofieldname}0"><input type="radio" id="{$key}-{$ofieldname}0" name="{$fieldname}" value="0"{$checked0} /> {$no}</label>
                        <label{$rel} for="{$key}-{$ofieldname}2"><input type="radio" id="{$key}-{$ofieldname}2" name="{$fieldname}" value="-1"{$checked2} /> {$default}</label>
EOF;


						break;
					case 'text':


						$value = ( $perm[ $ofieldname ] ? $perm[ $ofieldname ] : $field[ 'default' ] );

						if ( $userperm[ $ofieldname ] && $userperm[ $ofieldname ] != $perm[ $ofieldname ] )
						{
							$value = $userperm[ $ofieldname ];
						}
						$value = htmlspecialchars($value);

						$size = ( !empty( $field[ 'width' ] ) ? $field[ 'width' ] : 60 );

						$idName = '';

							$idName = ' id="'.$key .'-'. $ofieldname .'"';


						$form_code .= <<<EOF

                            <fieldset{$rel}{$idName}>
                                <label>{$field['label']}</label>
                                <input type="text" name="{$fieldname}" size="{$size}" value="{$value}"/>{$description}
                            </fieldset>
EOF;

						$field_data[ 'field' ] = <<<EOF
                        <input class="form-control" type="text" name="{$fieldname}" size="{$size}" value="{$value}"/>{$description}
EOF;


						break;

					case 'textarea':
						$value = ( $perm[ $ofieldname ] ? $perm[ $ofieldname ] : $field[ 'default' ] );

						if ( $userperm[ $ofieldname ] && $userperm[ $ofieldname ] != $perm[ $ofieldname ] )
						{
							$value = $userperm[ $ofieldname ];
						}

						$value = htmlspecialchars($value);

						$cols = ( !empty( $field[ 'cols' ] ) ? $field[ 'cols' ] : 60 );
						$rows = ( !empty( $field[ 'rows' ] ) ? $field[ 'rows' ] : 4 );

						$idName = '';

							$idName = ' id="'.$key .'-'. $ofieldname .'"';

						$form_code .= <<<EOF

                            <fieldset {$rel}{$idName}>
                                <label>{$field['label']}</label>
                                <textarea name="{$fieldname}" rows="{$rows}" cols="{$cols}" class="textarea-resize form-control">{$value}</textarea>
                                {$description}
                            </fieldset>
EOF;

						$field_data[ 'field' ] = <<<EOF
                        <textarea name="{$fieldname}" rows="{$rows}" cols="{$cols}" class="textarea-resize">{$value}</textarea>

EOF;


						break;
				}





				if ( $y >= $total )
				{
					//$form_code .= '</div>';
				}


				$tabsdata[] = $field_data;
			}


			$tabs_contents[ ] = array('data' => $tabsdata, 'tablabel' => $tablabel );

			$form_code .= <<<EOF

        </div>
     </div><!-- close box -->
</div>



EOF;

			$x++;
		}


		return array (
			'tabdata' => $tabs_contents,
			'output'  => $form_code,
			'cats'    => $tabs
		);
	}

}

?>