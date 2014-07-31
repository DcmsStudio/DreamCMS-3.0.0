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
 * @package      Skins
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edittemplate.php
 */
class Skins_Action_Edittemplate extends Controller_Abstract
{

    private $_fullclearOn = array('container', 'html-layout', 'layout');



	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$id     = (int)HTTP::input('id');
		$skinid = (int)HTTP::input('skinid');
		$group  = HTTP::input('group');

		$rs   = $this->model->getTemplateByID($id);
		$skin = $this->model->getSkinByID($skinid);


		$templatename     = trim(HTTP::input('templatename'));
		$templatesource   = HTTP::input('source');
		$templategroup    = trim(HTTP::input('group_name'));
		$iswidgettemplate = (int)HTTP::input('iswidgettemplate');


		/**
		 * Patch unicode
		 */
		$templatesource = str_replace(array (
		                                    '˂',
		                                    '˃'
		                              ), array (
		                                       '<',
		                                       '>'
		                                 ), $templatesource);

		if ( HTTP::input('send') )
		{


			if ( !trim($templatename) )
			{
				Library::sendJson(false, trans('Sie haben dem Template keinen namen gegeben'));
			}

			if ( !trim($templatesource) )
			{
				Library::sendJson(false, trans('Template darf nicht leer sein'));
			}

			demoadm();

			// convert back the input cleanup
			$templatesource = Strings::revertInputEntitiesClean($templatesource);



			if ( $id )
			{
				$add_arr = array (
					'templatename' => $templatename,
					'content'      => $templatesource,
					'updated'      => time(),
					'modifie_by'   => User::getUserId()
				);

                $this->db->update('skins_templates', $add_arr)->where('id', '=', $id)->execute();

				$path = TEMPLATES_PATH . $skin[ 'templates' ] . '/';
				if ( $rs[ 'group_name' ] != '' )
				{
					$path = TEMPLATES_PATH . $skin[ 'templates' ] . '/' . $rs[ 'group_name' ] . '/';
					Library::makeDirectory($path);
				}

                $this->clearCompilerCache($templatesource, $templatename, $skin[ 'templates' ], $rs[ 'group_name' ]);

                $file = new File($path . $templatename . '.html');
                $file->open('w')->writelock()->write($templatesource);
                $file->unlock()->close();

				$this->addLastEdit($id, 'Skin ' . trim((string)$rs[ 'skintitle' ]) . ' (Template: ' . $rs[ 'group_name' ] . '/' . $templatename . ')');

				Library::log(sprintf('Edit the Template `%s` (ID:%s) in Skin `%s`.', $rs[ 'templatename' ], $id, $rs[ 'skintitle' ]));
                echo Library::json(array('success' => true, 'msg' => trans('Template wurde erfolgreich aktualisiert'), 'newid' => $id ));
                exit;

			}
			else
			{
				$add_arr = array (
					'set_id'           => $skinid,
					'templatename'     => $templatename,
					'group_name'       => ($templategroup != 'ROOT' ? $templategroup : ''),
					'content'          => $templatesource,
					'iswidgettemplate' => $iswidgettemplate,
				);


				$this->db->insert('skins_templates')->values($add_arr)->execute();
                $id = $this->db->insert_id();

                $path = TEMPLATES_PATH . $skin[ 'templates' ] . '/';
				if ( $templategroup != '' )
				{
					$path = TEMPLATES_PATH . $skin[ 'templates' ] . '/' . $templategroup . '/';
					Library::makeDirectory($path);
				}

                $file = new File($path . $templatename . '.html');
                $file->open('w')->writelock()->write($templatesource);
                $file->unlock()->close();


				//@chmod($path . $templatename . '.html', 0777);

				$this->addLastEdit($id, 'Skin ' . trim((string)$rs[ 'skintitle' ]) . ' (Template: ' . $templategroup . '/' . $templatename . ')');


				Library::log(sprintf('Add the Template `%s` (ID:%s) in Skin `%s`.', $templatename, $id, $rs[ 'skintitle' ]));

                echo Library::json(array('success' => true, 'msg' => trans('Template wurde erfolgreich angelegt'), 'newid' => $id ));
				exit;
			}
		}

		$rs[ 'content' ] = htmlspecialchars($rs[ 'content' ]);

		$data             = array ();
		$data             = $rs;
		$data[ 'skinid' ] = $skinid;

		if ( empty($data[ 'group_name' ]) )
		{
			$data[ 'group_name' ] = $group;
		}

		$template_editor = $this->template_editor;


		Library::addNavi(trans('Frontend Skins Übersicht'));
		Library::addNavi(sprintf(trans('Frontend Skin `%s`'), $rs[ 'skintitle' ]));
		Library::addNavi(sprintf(trans('Skin `%s` Template `%s` bearbeiten'), $rs[ 'skintitle' ], ($data[ 'group_name' ] ? $data[ 'group_name' ] .'/' :'').$rs[ 'templatename' ]));

		$this->Template->process('skins/edit_template', $data, true);
	}


    /**
     *
     * @param string $templatesource
     * @param string $templatename
     * @param string $dirname
     * @param string $groupname
     */
    private function clearCompilerCache($templatesource, $templatename, $dirname, $groupname) {


        $path = PAGE_CACHE_PATH . 'templates/' . $dirname. '/compiled/';
        if ( $groupname )
        {
            $path = PAGE_CACHE_PATH . 'templates/' . $dirname . '/compiled/' . $groupname .'/';
        }



        if (!$groupname)
        {
            $_templatename = strtolower($templatename);
            if (in_array($_templatename, $this->_fullclearOn))
            {
                $file = new File(null, true);
                $file->deleteRescursiveDir($path);
                return;
            }
        }

        preg_match_all('#<'.(Compiler::TAGNAMESPACE ? Compiler::TAGNAMESPACE .':' : '').'block ([^>]*)>#is', $templatesource, $matches);
        $clearCompiled = null;

        if (is_array($matches[1]))
        {
            foreach ($matches[1] as $str) {
                if (!preg_match('#/\s*>$#', $str))
                {
                    preg_match('#name\s*=\s*(["\'])([^\1]*)\1#isU', $str, $m);
                    if ($m[2]) {
                        $clearCompiled[] = $path . 'module-blocks/'. $templatename .'-'. $m[2].'.php';
                    }
                }
            }
        }

        $clearCompiled[] = $path . $templatename.'_html.php';

        $file = new File(null, true);
        foreach ($clearCompiled as $path)
        {
            $file->delete($path);
        }
        $clearCompiled = null;
    }
}

?>