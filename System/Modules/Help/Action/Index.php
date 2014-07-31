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
 * @package      Help
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Help_Action_index extends Controller_Abstract
{

    private $_template = <<<E

<p>
    help for controller: {controller} {action}
</p>

E;






    public function execute()
    {


        if ( $this->_post( 'gethelp' ) )
        {
            $c = $this->_post('c');
            $a = $this->_post('a');

            if ( $c && $a )
            {
                if ( !is_file(DATA_PATH . 'help/' . $c . '/' . $a . '.html') )
                    Library::makeFile( DATA_PATH . 'help/' . $c . '/' . $a . '.html', 'w', str_replace('{action}', $a, str_replace('{controller}', $c, $this->_template) ) );
            }
            elseif ( $c && !$a )
            {
                if ( !is_file(DATA_PATH . 'help/' . $c . '/index.html') )
                    Library::makeFile( DATA_PATH . 'help/' . $c . '/index.html', 'w', str_replace('{action}', $a, str_replace('{controller}', $c, $this->_template) ) );
            }
            elseif ( !$c && !$a )
            {
                if ( !is_file(DATA_PATH . 'help/index.html') ) {
                    Library::makeFile( DATA_PATH . 'help/index.html', 'w', str_replace('{action}', $a, str_replace('{controller}', $c, $this->_template) ) );
                }
            }

            if ( $c && $a && file_exists( DATA_PATH . 'help/' . $c . '/' . $a . '.html' ) )
            {
                $path = dirname( DATA_PATH . 'help/' . $c . '/' . $a . '.html' );
                $code  = implode( '', file( DATA_PATH . 'help/' . $c . '/' . $a . '.html' ) );
            }
            elseif ( $c && !$a && file_exists( DATA_PATH . 'help/' . $c . '/index.html' ) )
            {
                $path = dirname( DATA_PATH . 'help/' . $c . '/index.html' );
                $code  = implode( '', file( DATA_PATH . 'help/' . $c . '/index.html' ) );
            }
            elseif ( !$c && !$a && file_exists( DATA_PATH . 'help/index.html' ) )
            {
                $path = dirname( DATA_PATH . 'help/index.html' );
                $code  = implode( '', file( DATA_PATH . 'help/index.html' ) );
            }
            else
            {

                $code = sprintf( trans( '<p>Unfortunately, there is no help-page available yet for `%s/%s`. (%s)</p>' ), $c, $a, DATA_PATH . 'help/' . $c . '/' . $a . '.html' );
            }


            if ($c) {
                $reg = $this->getApplication()->getModulRegistry($c);
            }


            $code = str_replace( 'src="', 'src="' . $fpath . '/', $code );
            echo Library::json( array(
                'success' => true,
                'title' => isset($reg['definition']['modulelabel']) ? $reg['definition']['modulelabel'] : '',
                'content' => $code
            ) );

            exit;
        }

        /*
                $menuid = 0; // Für das Javascript
                $me = Menu::getMenu();


                foreach ( $mtree->parent_menu_array[ 0 ] as $idx => $r )
                {

                    $icon     = BACKEND_IMAGE_PATH . 'spacer.gif';
                    $menuicon = $mtree->replace_icon_ext(($r[ 'icon' ] != '' ? $r[ 'icon' ] : ''), $mtree->_icon_type);

                    if ( file_exists(ROOT_PATH . $mtree->menu_item_dir . '/' . $menuicon) )
                    {
                        $icon = $mtree->menu_item_dir . '/' . $menuicon;
                    }


                    if ( trim((string)$r[ 'item_function' ]) && $r[ 'parentid' ] == 0 )
                    {
                        //=========================================================
                        // Interne CMS Function dieser Classe aufrufen
                        //=========================================================
                        //$this->$r['item_function']($r);
                    }
                    else
                    {
                        if ( isset($mtree->parent_menu_array[ $r[ 'parentid' ] ]) )
                        {
                            $_arrs                          = array ();
                            $mtree->_menu[ 'menu-' . $idx ] = array (
                                'name'  => 'menu-' . $idx,
                                'label' => $r[ 'title' ],
                                'icon'  => $icon,
                                'items' => $mtree->get_parent_items_arr($_arrs, $idx, $r[ 'id' ]),
                                'tip'   => $r[ 'description' ]
                            );
                        }
                    }
                }


                if ( HTTP::input('load') )
                {
                    $menu = json::encode($mtree->_menu);

                    //ISO 8859-1 to UTF-8
                    $menu = preg_replace("/([\xC2\xC3])([\x80-\xBF])/e", "chr(ord('\\1')<<6&0xC0|ord('\\2')&0x3F)", $menu);
                    // $this->html_code = preg_replace("/([\xC2\xC3])([\x80-\xBF])/e", "chr(ord('\\1')<<6&0xC0|ord('\\2')&0x3F)", $this->html_code);
                    $menu = preg_replace("/([\x80-\xFF])/e", "chr(0xC0|ord('\\1')>>6).chr(0x80|ord('\\1')&0x3F)", $menu);

                    header('Content-Type: application/javascript');
                    die('var helpItems = ' . $menu . ";");
                    exit();
                }
        */
        if ( HTTP::input( 'load' ) )
        {
            die( '' );
        }

        $data               = array();
        $data[ 'menu' ]     = Menu::getMenu();
        $data[ 'scrollable' ] = false;

        $this->Template->addScript( BACKEND_JS_URL . 'help' );
        $this->Template->process( 'help/help', $data, true );
        exit;
    }

}
