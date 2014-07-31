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
 * @package      Dashboard
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Diff.php
 */
class Dashboard_Action_Diff extends Controller_Abstract
{
    private $_type;

    public function execute()
    {

        if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
        {

            $modul         = strtolower( $this->_post( 'modul' ) );
            $sourceversion = (int)$this->_post( 'sourceversion' );
            $targetversion = (int)$this->_post( 'targetversion' );



            $documentID = (int)$this->_post( 'id' );

            if ( !$modul )
            {
                Library::sendJson( false, trans( 'Das Modul konnte nicht gefunden werden.' ) );
            }

            if ( !$documentID )
            {
                Library::sendJson( false, trans( 'Die Dokument ID ist 0? Bei neu zu erstellten Dokumenten, bitte erst das Dokument speichern.' ) );
            }


            $info = false;

            if ( !Plugin::isPlugin( $modul ) )
            {
                $modules = $this->getApplication()->loadFrontendModules();

                foreach ( $modules as $idx => $r )
                {
                    $r[ 'module' ] = strtolower( $r[ 'module' ] );

                    if ( $r[ 'module' ] == $modul )
                    {
                        $reg       = $this->getApplication()->getModulRegistry( $modul );
                        $info      = $reg[ 'definition' ];
                        $className = ucfirst( strtolower( $modul ) ) . '_Helper_Diff';
                        break;
                    }
                }
            }
            else
            {
                if ( Plugin::isExcecutable( $modul ) )
                {
                    $className = 'Addon_' . ucfirst( strtolower( $modul ) ) . '_Helper_Diff';
                    Plugin::initPlugin( Plugin::getConfig( $modul ), $modul );
                    $info = Plugin::getDefinition();
                }
                else
                {
                    Library::sendJson( false, trans( 'Das Plugin hat keine diff function.' ) );
                }
            }

            if (empty($className)) {
                Library::sendJson( false, trans( 'Das Modul hat keine diff function.' ) );
            }

            $this->_type = $modul;

            $data = array();


            if ( class_exists( $className ) )
            {
                $cls            = new $className;
                $data[ 'diff' ] = $cls->getDiff( $sourceversion, ( !$targetversion ? 1 : $targetversion ), $documentID );
            }
            else
            {
                Library::sendJson( false, ( !isset( $info[ 'modulelabel' ] ) ?
                    trans( 'Das Modul besitzt keine Diff funktion!' ) :
                    sprintf( trans( 'Das Modul `%s` besitzt keine Diff funktion!' ), $info[ 'modulelabel' ] ) ) );
            }

            $source = $data[ 'diff' ][ 'source' ];
            $target = $data[ 'diff' ][ 'target' ];

            $ts = 0;
            if ($target[ 'created' ]) {
                $ts = $target[ 'created' ];
            }
            if ($target[ 'modifed' ]) {
                $ts = $target[ 'modifed' ];
            }
            $r = $this->getDiff( $source, $target,
                array(
                    'show_split_view' => true,
                    'version'         => $targetversion,
                    'version_date'    => date( 'd.m.Y, H:i:s', $ts )
                )
            );


            if ( $this->_post( 'getdata' ) )
            {

                Ajax::Send(true, array('html_diff' => $r));

                exit;
            }

            $data[ 'diff' ][ 'html_diff' ] = $r;
            $data[ 'sourceVersion' ]       = $sourceversion;
            $data[ 'targetVersion' ]       = ( !$targetversion ? 1 : $targetversion );
            $data[ 'modul' ]               = $modul;
            $data[ 'id' ]                  = $documentID;

            $this->Template->process( 'generic/diff', $data, true );
        }
    }


    private function getDiff($source, $target, $args)
    {
        $r = "<table class='diff'>\n";
        if ( !empty( $args[ 'show_split_view' ] ) )
        {
            $r .= "<col class='content diffsplit left' /><col class='content diffsplit middle' /><col class='content diffsplit right' />";
        }
        else
        {
            $r .= "<col class='content' />";
        }
        $r .= "<thead>";
        $r .= "<tr class='diff-label-title'><th>" . trans( 'Aktuell' ) . "</th><th></th><th>" . sprintf(trans( 'Version %s - %s' ), $args['version'], $args['version_date'] ) . "</th></tr>\n";
        if ( isset( $source[ 'title' ] ) || isset( $target[ 'title' ] ) )
        {
            $r .= "<tr class='diff-title'><th colspan='3'>" . trans( 'Titel' ) . "</th></tr>\n";
            $r .= "<tr class='diff-sub-title'>\n";
            $r .= "\t<th>{$source['title']}</th>\n";
            $r .= "\t<th></th><th>{$target['title']}</th>\n";
            $r .= "</tr>\n";

        }
        $r .= "</thead>\n";
        $r .= "<tbody>";

        $diff = '';

        if ( isset( $source[ 'teaser' ] ) || isset( $target[ 'teaser' ] ) )
        {
            $left_lines  = explode( "\n", $source[ 'teaser' ] );
            $right_lines = explode( "\n", $target[ 'teaser' ] );
            $text_diff   = new Text_Diff( 'auto', array($left_lines, $right_lines) );
            $renderer    = new Text_Diff_Renderer_Table( $args );


            $diffTeaser = $renderer->render( $text_diff );
            if ( $diffTeaser ) {
                $diff .= "<tr class='diff-teaser'><td colspan='3'>" . trans( 'Teaser' ) . "</td></tr>\n";
                $diff .= $diffTeaser;
            }
        }

        $diff .= "<tr class='diff-content'><td colspan='3'>" . trans( 'Inhalt' ) . "</td></tr>\n";


        $left_lines  = explode( "\n", $source[ 'content' ] );
        $right_lines = explode( "\n", $target[ 'content' ] );
        $text_diff   = new Text_Diff( 'auto', array( $right_lines, $left_lines) );
        $renderer    = new Text_Diff_Renderer_Table( $args );
        $diff .= $renderer->render( $text_diff );

/*

        if (isset( $source[ 'customfields' ] ) || isset( $target[ 'customfields' ] )) {
            if (is_array($source[ 'customfields' ]) && is_array($target[ 'customfields' ]))
            {

                $model = Model::getModelInstance($this->_type);

                if ( $model && method_exists($model, 'getFieldById') ) {



                    foreach ($target[ 'customfields' ] as $rs) {
                        foreach ($rs as $key => $value) {
                            if (is_string($key)) {

                            }
                        }
                    }

                }

            }

        }


*/



        $r .= "\n$diff\n</tbody>\n";
        $r .= "</table>";

        return $r;
    }
}
