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
 * @package      News
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Model.php
 */
class News_Config_Model
{

    /**
     * @return array
     */
    public static function getConfig()
    {


        return array(
            'news'            => array(
                'trans'        => true,
                'transpk'      => 'id',
                'relationkey'  => 'id',
                'sourcemode'   => 'news/item',
                'useMetadata'  => true,
                // fields in translation table
                'fields'       => array(
                    'id'               => array(
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
                    'text'             => array(
                        'trans' => true,
                        'type'  => 'text'
                    )
                ),


                /**
                 * fields for searchindexer
                 * this fields will use to update or create the searchindex
                 */
                'searchFields' => array(
                    'titlefield'   => 'title',
                    'contentfield' => 'text'
                )
            ),
            'news_categories' => array(
                'trans'       => true,
                'transpk'     => 'id',
                'relationkey' => 'id',
                'sourcemode'  => 'news/index',
                'useMetadata' => true,
                // fields in translation table
                'fields'      => array(
                    'id'           => array(
                        'type'      => 'int',
                        'length'    => 10,
                        'default'   => 0,
                        'index'     => true,
                        'isprimary' => true
                    ),
                    'title'        => array(
                        'trans'  => true,
                        'type'   => 'varchar',
                        'length' => 200
                    ),
                    'description'  => array(
                        'trans' => true,
                        'type'  => 'text'
                    ),
                    'teaserheader' => array(
                        'trans'  => true,
                        'type'   => 'varchar',
                        'length' => 250
                    ),
                )
            )
        );


        return array(
            'TranslationTables' => array(
                'news'            => array(
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
                    'text'             => array(
                        'type' => 'text'
                    )
                ),
                'news_categories' => array(
                    'id'           => array(
                        'type'      => 'int',
                        'length'    => 10,
                        'default'   => 0,
                        'index'     => true,
                        'isprimary' => true
                    ),
                    'title'        => array(
                        'type'   => 'varchar',
                        'length' => 200
                    ),
                    'description'  => array(
                        'type' => 'text'
                    ),
                    'teaserheader' => array(
                        'type'   => 'varchar',
                        'length' => 250
                    ),
                )
            ),
            'tables'            => array(
                'news'            => array(
                    'useTranslation'  => true,
                    'transPK'         => 'itemid',
                    'mainPK'          => 'id',
                    'useMetadata'     => true,
                    'translateFields' => array(
                        'title',
                        'teaser',
                        'text',
                        'teaserimagetitle'
                    ),
                    'sourcemode'      => 'news/item'
                ),
                'news_categories' => array(
                    'useTranslation'  => true,
                    'transPK'         => 'id',
                    'mainPK'          => 'id',
                    'useMetadata'     => true,
                    'translateFields' => array(
                        'title'
                    ),
                    'sourcemode'      => 'news/index'
                )
            ),
            /**
             * fields for searchindexer
             * this fields will use to update or create the searchindex
             */
            'searchFields'      => array(
                // table news
                // @see this arrayindex tables
                'news' => array(
                    'titlefield'   => 'title',
                    'contentfield' => 'text'
                ) // database fields
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
                'news'            => array(
                    'useTranslation' => true,
                    'transPK'        => '',
                    'mainPK'         => '',
                    'useMetadata'    => true
                ),
                'news_categories' => array(
                    'useTranslation' => true,
                    'transPK'        => '',
                    'mainPK'         => '',
                    'useMetadata'    => true
                )
            )
        );
    }

}
