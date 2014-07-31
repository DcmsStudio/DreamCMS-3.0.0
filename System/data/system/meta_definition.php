<?php

/**
 * DreamCMS 2.0.1
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE Version 2
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-2.0.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@dcms-studio.de so we can send you a copy immediately.
 *
 * PHP Version 5.3.6
 * @copyright    Copyright (c) 2008-2011 Marcel Domke (http://www.dcms-studio.de)
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @package
 * @filesource
 */

// table name -> rest in array
$metaTables = array(
    'page'                     => array('notrans' => true, 'primarykey' => 'id'),
    'news'                     => array('controller' => 'news', 'action' => 'item', 'primarykey' => 'id'),
    'news_trans'               => array('controller' => 'news', 'action' => 'item', 'primarykey' => 'id'),
    'news_categories_trans'    => array('controller' => 'news', 'action' => 'index', 'primarykey' => 'id'),
    #'applications_items' => array('controller' => 'apps', 'action' => 'item', 'primarykey' => 'itemid'),
    'applications_items_trans' => array('controller' => 'apps', 'action' => 'item', 'primarykey' => 'itemid'),
    'applications_categories'  => array('controller' => 'apps', 'action' => 'category', 'primarykey' => 'catid'),
    'doc_pages_settings'       => array('controller' => 'page', 'action' => 'index', 'primarykey' => 'id'),
    'board'                    => array('controller' => 'news', 'action' => 'index', 'primarykey' => 'id'),
    'board_posts'              => array('controller' => 'news', 'action' => 'index', 'primarykey' => 'id'),
    'board_threads'            => array('controller' => 'news', 'action' => 'index', 'primarykey' => 'id'),
);


$metaTranslationTables = array(
    'news_trans'                    => array('controller' => 'news', 'action' => 'item', 'primarykey' => 'id'),
    'news_categories_trans'         => array('controller' => 'news', 'action' => 'index', 'primarykey' => 'catid'),
    'applications_items_trans'      => array('controller' => 'apps', 'action' => 'item', 'primarykey' => 'itemid'),
    'applications_categories_trans' => array('controller' => 'apps', 'action' => 'category', 'primarykey' => 'catid'),
);


/**
 * (table page) Meta
 * @todo remove it see "Basic Tables Meta"
 */
$tableMetaFieldDefinition = array(
    'pageid'           => array('type' => 'int', 'length' => 10, 'default' => 0, 'index' => true),
    'clickanalyse'     => array('type' => 'tinyint', 'length' => 1, 'default' => 0),
    'language'         => array('type' => 'char', 'length' => 6, 'default' => ''), // the base Language
    'languagefallback' => array('type' => 'tinyint', 'length' => 1, 'default' => 1),
    'searchable'       => array('type' => 'tinyint', 'length' => 1, 'default' => 1),
    'pagetitle'        => array('type' => 'varchar', 'length' => 250, 'default' => ''),
    'metadescription'  => array('type' => 'text'),
    'metakeywords'     => array('type' => 'text'),
    'activemenuitemid' => array('type' => 'int', 'length' => 10, 'default' => 0),
    'published'        => array('type' => 'tinyint', 'length' => 2, 'default' => 1, 'index' => true),
    'publishon'        => array('type' => 'int', 'length' => 11, 'default' => 0, 'index' => true),
    'publishoff'       => array('type' => 'int', 'length' => 11, 'default' => 0, 'index' => true),
    'alias'            => array('type' => 'varchar', 'length' => 150, 'default' => '', 'index' => true),
    'suffix'           => array('type' => 'varchar', 'length' => 6, 'default' => ''),
    'indexfollow'      => array('type' => 'varchar', 'length' => 15, 'default' => 1),
    'target'           => array('type' => 'varchar', 'length' => 10, 'default' => ''),
    'cacheable'        => array('type' => 'tinyint', 'length' => 1, 'default' => 0),
    'cachetime'        => array('type' => 'int', 'length' => 8, 'default' => 0),
    'cachegroups'      => array('type' => 'varchar', 'length' => 250, 'default' => '', 'datatype' => 'split'),
    'goto'             => array('type' => 'int', 'length' => 10, 'default' => 0),
    'draft'            => array('type' => 'tinyint', 'length' => 1, 'default' => 0),
    'rollback'         => array('type' => 'tinyint', 'length' => 1, 'default' => 0),
);


/**
 * Basic Tables Meta
 *
 */
$tableCoreMetaFieldDefinition = array(
    'pageid'           => array('type' => 'int', 'length' => 10, 'default' => 0, 'index' => true),
    'clickanalyse'     => array('type' => 'tinyint', 'length' => 1, 'default' => 0),
    'searchable'       => array('type' => 'tinyint', 'length' => 1, 'default' => 1),
    'language'         => array('type' => 'char', 'length' => 6, 'default' => ''), // the base Language
    'languagefallback' => array('type' => 'tinyint', 'length' => 1, 'default' => 1),
    'activemenuitemid' => array('type' => 'int', 'length' => 10, 'default' => 0),
    'published'        => array('type' => 'tinyint', 'length' => 2, 'default' => 1, 'index' => true),
    'publishon'        => array('type' => 'int', 'length' => 11, 'default' => 0, 'index' => true),
    'publishoff'       => array('type' => 'int', 'length' => 11, 'default' => 0, 'index' => true),
    'indexfollow'      => array('type' => 'varchar', 'length' => 15, 'default' => 1),
    'target'           => array('type' => 'varchar', 'length' => 10, 'default' => ''),
    'cacheable'        => array('type' => 'tinyint', 'length' => 1, 'default' => 0),
    'cachetime'        => array('type' => 'int', 'length' => 8, 'default' => 0),
    'cachegroups'      => array('type' => 'varchar', 'length' => 250, 'default' => '', 'datatype' => 'split'),
    'goto'             => array('type' => 'int', 'length' => 10, 'default' => 0),

    /* since version 2.0 */
    'draft'            => array('type' => 'tinyint', 'length' => 1, 'default' => 0),
    'rollback'         => array('type' => 'tinyint', 'length' => 1, 'default' => 0),
);


/**
 * Translation Tables Meta
 * since version 2.0
 */
$tableTranslationMetaDefinition = array(
    'lang'            => array('type' => 'char', 'length' => 6, 'default' => '', 'index' => true),
    'iscorelang'      => array('type' => 'tinyint', 'length' => 1, 'default' => 0),
    'alias'           => array('type' => 'varchar', 'length' => 150, 'default' => '', 'index' => true), // @since version 2.0.1 moved to the alias registry
    'suffix'          => array('type' => 'varchar', 'length' => 6, 'default' => ''), // @since version 2.0.1 moved to the alias registry

    // @since version 2.0.1 added the alias registry id
    // 'rewriteid' => array('type' => 'int', 'length' => 10, 'default' => 0),


    'pagetitle'       => array('type' => 'varchar', 'length' => 250, 'default' => ''),
    'metadescription' => array('type' => 'text'),
    'metakeywords'    => array('type' => 'text'),
    'tags'            => array('type' => 'varchar', 'length' => 250, 'default' => ''),
    'draft'           => array('type' => 'tinyint', 'length' => 1, 'default' => 0),
    'rollback'        => array('type' => 'tinyint', 'length' => 1, 'default' => 0),
);

?>