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
 * @copyright    2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Model.php
 */
class Addon_Forum_Config_Model
{

    public static function getConfig()
    {


        return array(
            'board'            => array(
                'trans'        => true,
                'transpk'      => 'forumid',
                'relationkey'  => 'forumid',
                'sourcemode'   => 'page/index',
                'useMetadata'  => true,
                // fields in translation table
                'fields'       => array(
                    'forumid'     => array(
                        'type'      => 'int',
                        'length'    => 10,
                        'default'   => 0,
                        'index'     => true,
                        'isprimary' => true),
                    'title'       => array(
                        'type'   => 'varchar',
                        'length' => 200
                    ),
                    'description' => array(
                        'type' => 'text'
                    ),
                    'note' => array(
                        'type' => 'text'
                    )
                ),


                /**
                 * fields for searchindexer
                 * this fields will use to update or create the searchindex
                 */
                'searchFields' => array()
            ),

        );










        return array(
            'TranslationTables' => array(
                'board' => array(
                    'forumid'     => array(
                        'type'      => 'int',
                        'length'    => 10,
                        'default'   => 0,
                        'index'     => true,
                        'isprimary' => true),
                    'title'       => array(
                        'type'   => 'varchar',
                        'length' => 200
                    ),
                    'description' => array(
                        'type' => 'text'
                    ),
                    'note' => array(
                        'type' => 'text'
                    )
                )
            ),
            'tables'            => array(
                'board' => array(
                    'useTranslation'  => true,
                    'transPK'         => 'forumid',
                    'mainPK'          => 'forumid',
                    'useMetadata'     => true,
                    'translateFields' => array(
                        'title',
                        'description',
                        'note'
                    ),
                    //'sourcemode'      => 'news/item'
                )
            ),
        );
    }

}

?>