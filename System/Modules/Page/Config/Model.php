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
 * @package      Page
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Model.php
 */
class Page_Config_Model
{

    /**
     * @return array
     */
    public static function getConfig()
    {

        return array(
            'pages'            => array(
                'trans'        => true,
                'transpk'      => 'id',
                'relationkey'  => 'id',
                'sourcemode'   => 'page/index',
                'useMetadata'  => true,
                // fields in translation table
                'fields'       => array(
                    'id'            => array(
                        'type'      => 'int',
                        'length'    => 10,
                        'default'   => 0,
                        'index'     => true,
                        'isprimary' => true
                    ),
                    'title'            => array(
                        'trans'  => true,
                        'type'   => 'varchar',
                        'length' => 200
                    ),
                    'teaser'           => array(
                        'trans' => true,
                        'type'  => 'text'
                    ),
                    'teaserimagetitle' => array(
                        'trans'  => true,
                        'type'   => 'varchar',
                        'length' => 255
                    ),
                    'content'          => array(

                        'trans' => true,
                        'type'  => 'text'
                    )
                ),


                /**
                 * fields for searchindexer
                 * this fields will use to update or create the searchindex
                 */
                'searchFields' => array()
            ),
            'pages_categories' => array(
                'trans'       => true,
                'pk'          => 'catid',
                'transpk'     => 'catid',
                'relationkey' => 'id',
                'sourcemode'  => 'page/index',
                'useMetadata' => true,
                // fields in translation table
                'fields'      => array(
                    'catid'       => array(
                        'type'      => 'int',
                        'length'    => 10,
                        'default'   => 0,
                        'index'     => true,
                        'isprimary' => true
                    ),
                    'title'       => array(
                        'trans'  => true,
                        'type'   => 'varchar',
                        'length' => 200
                    ),
                    'description' => array(
                        'trans' => true,
                        'type'  => 'text'
                    ),
                )
            )
        );

        return array(
            'TranslationTables' => array(
                'pages'            => array(
                    'id'               => array(
                        'type'      => 'int',
                        'length'    => 10,
                        'default'   => 0,
                        'index'     => true,
                        'isprimary' => true
                    ),
                    'title'            => array(
                        'type'   => 'varchar',
                        'length' => 200
                    ),
                    'teaser'           => array(
                        'type' => 'text'
                    ),
                    'teaserimagetitle' => array(
                        'type'   => 'varchar',
                        'length' => 255
                    ),
                    'content'          => array(
                        'type' => 'text'
                    )
                ),
                'pages_categories' => array(
                    'catid'       => array(
                        'type'      => 'int',
                        'length'    => 10,
                        'default'   => 0,
                        'index'     => true,
                        'isprimary' => true
                    ),
                    'title'       => array(
                        'type'   => 'varchar',
                        'length' => 200
                    ),
                    'description' => array(
                        'type' => 'text'
                    ),
                )
            ),
            'tables'            => array(
                'pages'            => array(
                    'useTranslation'  => true,
                    'transPK'         => 'id',
                    'mainPK'          => 'id',
                    'useMetadata'     => true,
                    'translateFields' => array(
                        'title',
                        'content',
                        'teaser',
                        'teaserimagetitle'
                    ),
                    'sourcemode'      => 'page/item'
                ),
                'pages_categories' => array(
                    'useTranslation'  => true,
                    'transPK'         => 'catid',
                    'mainPK'          => 'catid',
                    'useMetadata'     => true,
                    'translateFields' => array(
                        'title'
                    ),
                    'sourcemode'      => 'page/index'
                )
            )
        );
    }

    /**
     * @return array
     */
    public static function getConfiguraton()
    {

        return array(
            'tables' => array(
                'pages'            => array(
                    'useTranslation' => true,
                    'transPK'        => 'id',
                    'mainPK'         => 'id',
                    'useMetadata'    => true
                ),
                'pages_categories' => array(
                    'useTranslation' => true,
                    'transPK'        => 'catid',
                    'mainPK'         => 'catid',
                    'useMetadata'    => true
                )
            )
        );
    }

}
