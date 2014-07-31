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
 * @package     DreamCMS
 * @version     3.0.0 Beta
 * @category    Plugin s
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Permissions.php
 */
// Fields
$perms[ 'usergroup' ] = array(
    // Tab Label
    'tablabel'         => trans( 'Gallery Plugin' ),
    // global perms
    'run'              => array(
        'type'        => 'checkbox',
        'label'       => trans( 'Kann das Plugin ausführen' ),
        'default'     => 0,
        'isActionKey' => true ),
    // backend perms	
    'caneditgallery'   => array(
        'type'    => 'checkbox',
        'label'   => trans( 'Kann Gallerien bearbeiten/erstellen' ),
        'require' => 'canrun',
        'default' => 0 ),
    'canpublish'       => array(
        'type'    => 'checkbox',
        'label'   => trans( 'Kann Gallerien aktivieren/deaktivieren' ),
        'require' => 'caneditgallery',
        'default' => 0 ),
    'candelete'        => array(
        'type'    => 'checkbox',
        'label'   => trans( 'Kann Gallerien löschen' ),
        'require' => 'caneditgallery',
        'default' => 0 ),
    'canemptygallery'  => array(
        'type'    => 'checkbox',
        'label'   => trans( 'Kann Gallerien leeren' ),
        'require' => 'caneditgallery',
        'default' => 0 ),
    'caneditimage'     => array(
        'type'    => 'checkbox',
        'label'   => trans( 'Kann Gallerie Bilder bearbeiten' ),
        'require' => 'caneditgallery',
        'default' => 0 ),
    'canpublishimg'    => array(
        'type'    => 'checkbox',
        'label'   => trans( 'Kann Gallerie Bilder aktivieren/deaktivieren' ),
        'require' => 'caneditimage',
        'default' => 0 ),
    'candeleteimage'   => array(
        'type'    => 'checkbox',
        'label'   => trans( 'Kann Gallerie Bilder löschen' ),
        'require' => 'caneditimage',
        'default' => 0 ),
    'canupload'        => array(
        'type'    => 'checkbox',
        'label'   => trans( 'Kann Gallerie Bilder hochladen' ),
        'require' => 'caneditgallery',
        'default' => 0 ),
    // frontend perms
    'cancomment'       => array(
        'type'    => 'checkbox',
        'label'   => trans( 'Kommentare zu einem Bild hinzufügen' ),
        'default' => 0 ),
    'maxcommentlength' => array(
        'type'    => 'text',
        'width'   => 20,
        'label'   => trans( 'maximale länge des Kommentars' ),
        'require' => 'cancomment',
        'default' => 500 )
);
?>