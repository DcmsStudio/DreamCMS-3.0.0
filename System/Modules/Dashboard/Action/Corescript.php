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
 * @package
 * @version      3.0.0 Beta
 * @category
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Corescript.php
 */
class Dashboard_Action_Corescript extends Controller_Abstract
{

    public function execute()
    {
        $modul = strtolower( $this->_post( 'script' ) );

        switch ($modul) {
            case 'ace':
                $files = array(
                    'Vendor/ace/htmlhint.js',
                    'Vendor/ace/ace.js',
                    'Vendor/ace/ext-chromevox.js',
                    'Vendor/ace/ext-elastic_tabstops_lite.js',
                    'Vendor/ace/ext-emmet.js',
                    'Vendor/ace/emmet.js',
                    'Vendor/ace/jshint.js',
                    'Vendor/ace/csslint.js',
                    'Vendor/ace/ext-keybinding_menu.js',
                    'Vendor/ace/ext-language_tools.js',
                    'Vendor/ace/ext-modelist.js',
                    'Vendor/ace/ext-settings_menu.js',
                    'Vendor/ace/ext-static_highlight.js',
                    //   'Vendor/ace/ext-statusbar',
                    //   'Vendor/ace/ext-textarea',
                    'Vendor/ace/ext-themelist.js',
                    'Vendor/ace/ext-whitespace.js',
                    'Vendor/ace/worker-javascript.js',
                    'Vendor/ace/worker-php.js',
                    'Vendor/ace/worker-css.js',
                    'Vendor/ace/keybinding-emacs.js',
                    'Vendor/ace/keybinding-vim.js',
                    'Vendor/ace/theme-netbeans.js',
                    'Vendor/ace/mode-html.js',
                    'public/html/js/backend/tpleditor/dcms.ace.token_tooltip.js',
                    'public/html/js/backend/tpleditor/dcms.ace.intellisense.js',
                    'html/js/backend/tpleditor/beautifier/lib/beautify.js'
                );
                break;

            case 'base':

                break;

            case 'tinymce':

                break;

            default:
                $files = false;
                break;
        }

        if (!$files) {
            exit;
        }

        $comp = Settings::get('compress_js') ? '-min' : '';
        $etag = md5(serialize($files));

        if ( isset($_SERVER[ 'HTTP_IF_NONE_MATCH' ]) && str_replace( '"', '', stripslashes( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) ) == $etag )
        {
            header('HTTP/1.0 304 Not Modified'); // entsprechenden Header senden => Datei wird nicht geladen

            exit();
        }

        if ( file_exists(PAGE_CACHE_PATH . 'data/assets/' . $modul .'-'.$etag . $comp . '.js') )
        {
            $assetcache = file_get_contents(PAGE_CACHE_PATH . 'data/assets/' . $modul .'-'.$etag . $comp . '.js');
        }
        else
        {
            $path = ROOT_PATH . 'public/html/js/';
            $assetcache = null;
            $tmp = array ();

            $cache = false;

            foreach ( $files as $file )
            {
                if ( trim($file) == '' )
                {
                    continue;
                }

                if ( substr($file, -3) === '.js' )
                {
                    $file = substr($file, 0, -3);
                }


                if ( substr($file, 0, 8) === 'html/js/' )
                {
                    $file = substr($file, 8);
                }



                if ( substr($file, 0, 1) === '/' )
                {
                    $file = substr($file, 1);
                }


                if ( substr($file, 0, 8) === 'Modules/' )
                {
                    $file = MODULES_PATH . substr($file, 8);
                }
                elseif ( substr($file, 0, 9) === 'Packages/' )
                {
                    $file = PACKAGES_PATH . substr($file, 9);
                }
                else if ( substr($file, 0, 12) === 'public/simg/')
                {
                    $file = ROOT_PATH . $file;
                }
                else if ( substr($file, 0, 5) === 'simg/')
                {
                    $file = PUBLIC_PATH . $file;
                }
                else if ( substr($file, 0, 7) === 'public/' )
                {
                    $file = ROOT_PATH . $file;
                }
                else if ( substr($file, 0, 5) === 'html/' )
                {
                    $file = PUBLIC_PATH . $file;
                }
                elseif ( substr($file, 0, 7) === 'Vendor/' || substr($file, 0, strlen('public/html/js/')) === 'public/html/js/' )
                {
                    $file = ROOT_PATH . $file;
                }
                else
                {
                    $file = $path . $file;
                }



                if ( file_exists($file . '.js') )
                {
                    if (strpos($file, '.min') === false && $comp) {
                        $tmp[ ] = Minifier::minifyJs( file_get_contents($file . '.js') );
                    }
                    else {
                        $tmp[ ] = file_get_contents($file . '.js');
                    }

                    #$cache = (!$cache ? preg_match('/(jquery\.|dcms[\.-])(.*?)/', $file) : $cache);
                }
                else {
                    $tmp[ ] = '/* FILE: '. $file . '.js not exists! */';
                }
            }


            if ( count($tmp) )
            {
                $assetcache = implode("\n", $tmp);
                Library::makeDirectory(PAGE_CACHE_PATH . 'data/assets/');
                file_put_contents(PAGE_CACHE_PATH . 'data/assets/' . $modul .'-'.$etag . $comp . '.js', $assetcache);
                unset($tmp);
            }
        }


        $cacheStamp = time();
        if ( date('Z') >= 0 ) {
            $cacheStamp += date('Z');
        }
        else {
            $cacheStamp -= date('Z');
        }


        $output = new Output();
        $output->setMode( Output::AJAX );
        $output->addHeader( 'Content-Type', 'application/javascript' );

        if ( $assetcache === null )
        {
            $assetcache = '/* Javascript Files not found! */';
        }
        else
        {
            $output->addHeader( 'Cache-Control', "public, max-age=5184000" );
            $output->addHeader( 'Etag', '"'.$etag .'"');
            $output->addHeader( 'Expires', gmdate("D, d M Y H:i:s", $cacheStamp + 96000) . " GMT");
        }

        // Add body
        $output->appendOutput( Strings::fixUtf8($assetcache) );

        // Send
        $output->sendOutput();
        exit;


    }
}