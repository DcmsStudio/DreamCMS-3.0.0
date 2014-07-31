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
 * @copyright	Copyright (c) 2008-2011 Marcel Domke (http://www.dcms-studio.de)
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @package
 * @filesource
 */
$applicationTypes = array(
        // Blog Application
        'blog'    => array(
                'title'       => trans('Blog'),
                'description' => trans('Blog Application'),
                'itemtypes'   => array(
                        'article' => trans('Artikel'),
                        'author'  => trans('Autor')
                ),
                // required Fields
                'core-fields' => array(
                        'article' => array(
                                array(
                                        'label'     => trans('Artikel Titel'),
                                        'fieldname' => 'title',
                                        'fieldtype' => 'text',
                                ),
                                array(
                                        'label'     => trans('Artikel Inhalt'),
                                        'fieldname' => 'content',
                                        'fieldtype' => 'richtext',
                                ),
                                array(
                                        'label'     => trans('Artikel Kategorie'),
                                        'fieldname' => 'catid',
                                        'fieldtype' => 'categorie',
                                        'value'     => 0,
                                ),
                                array(
                                        'label'     => trans('Artikel Zugriffsrechte'),
                                        'fieldname' => 'access',
                                        'fieldtype' => 'usergroups',
                                        'value'     => 0,
                                ),
                                array(
                                        'grouplabel' => trans('Core Tags'),
                                        'label'      => trans('Core Tags für diesen Inhalt nicht Parsen'),
                                        'fieldname'  => 'escapecoretags',
                                        'fieldtype'  => 'checkbox',
                                        'value'      => 0,
                                )
                        ),
                        'author'  => array(
                                array(
                                        'label'     => trans('Autor Titel'),
                                        'fieldname' => 'title',
                                        'fieldtype' => 'text',
                                ),
                                array(
                                        'label'     => trans('Autor Inhalt'),
                                        'fieldname' => 'content',
                                        'fieldtype' => 'richtext',
                                ),
                                array(
                                        'label'     => trans('Autor Kategorie'),
                                        'fieldname' => 'catid',
                                        'fieldtype' => 'categorie',
                                        'value'     => 0,
                                ),
                                array(
                                        'label'     => trans('Autor Zugriffsrechte'),
                                        'fieldname' => 'access',
                                        'fieldtype' => 'usergroups',
                                        'value'     => 0,
                                ),
                                array(
                                        'grouplabel' => trans('Core Tags'),
                                        'label'      => trans('Core Tags für diesen Inhalt nicht Parsen'),
                                        'fieldname'  => 'escapecoretags',
                                        'fieldtype'  => 'checkbox',
                                        'value'      => 0,
                                )
                        ),
                )
        ),
        // END Blog Application
        // Product Application
        'product' => array(
                'title'       => trans('Produkt Katalog'),
                'description' => trans('-'),
                'itemtypes'   => array(
                        'automobile' => trans('Automobil'),
                        'book'       => trans('Buch'),
                        'camera'     => trans('Kamera'),
                        'cell-phone' => trans('Autor'),
                        'furniture'  => trans('Möbel'),
                        'product'    => trans('Produkt Allgemein'),
                        'shoe'       => trans('Schuhe'),
                        'test'       => trans('Test'),
                        'watch'      => trans('Uhr'),
                ),
                // required Fields
                'core-fields' => array(
                        'automobile' => array(
                                array(
                                        'label'       => trans('Hersteller'),
                                        'fieldname'   => 'publisher',
                                        'fieldtype'   => 'select',
                                        'fieldvalues' => array(
),
                                ),
                                array(
                                        'label'     => trans('Maximal Geschwindigkeit'),
                                        'fieldname' => 'isbn-10',
                                        'fieldtype' => 'text',
                                ),
                                array(
                                        'spanlabel' => trans('Core Tags'),
                                        'label'     => trans('Core Tags für diesen Inhalt nicht Parsen'),
                                        'fieldname' => 'escapecoretags',
                                        'fieldtype' => 'checkbox',
                                        'value'     => 1,
                                )
                        ),
                        'book'       => array(
                                array(
                                        'label'     => trans('Sprache'),
                                        'fieldname' => 'language',
                                        'fieldtype' => 'select',
                                ),
                                array(
                                        'label'       => trans('Hersteller'),
                                        'fieldname'   => 'publisher',
                                        'fieldtype'   => 'select',
                                        'fieldvalues' => array(
),
                                ),
                                array(
                                        'label'     => trans('ISBN-10'),
                                        'fieldname' => 'isbn-10',
                                        'fieldtype' => 'text',
                                ),
                                array(
                                        'label'     => trans('ISBN-13'),
                                        'fieldname' => 'isbn-13',
                                        'fieldtype' => 'text',
                                ),
                                array(
                                        'label'     => trans('Seiten Anzahl'),
                                        'fieldname' => 'pages',
                                        'fieldtype' => 'text',
                                ),
                                array(
                                        'spanlabel' => trans('Core Tags'),
                                        'label'     => trans('Core Tags für diesen Inhalt nicht Parsen'),
                                        'fieldname' => 'escapecoretags',
                                        'fieldtype' => 'checkbox',
                                        'value'     => 1,
                                )
                        ),
                        'camera'     => array(
                                array(
                                        'label'       => trans('Megapixel'),
                                        'fieldname'   => 'size',
                                        'fieldtype'   => 'select',
                                        'fieldvalues' => array(
                                                '121-megapixels' => '12.1 Megapixel',
                                                '10-megapixels'  => '10 Megapixel',
                                                '8-megapixels'   => '8 Megapixel',
                                                '71-megapixels'  => '7.1 Megapixel',
                                                '5-megapixels'   => '5 Megapixel',
                                        ),
                                ),
                                array(
                                        'label'       => trans('Farbe'),
                                        'fieldname'   => 'color',
                                        'fieldtype'   => 'select',
                                        'fieldvalues' => array(
),
                                ),
                                array(
                                        'label'       => trans('Optischer Zoom'),
                                        'fieldname'   => 'optical-zoom',
                                        'fieldtype'   => 'select',
                                        'fieldvalues' => array(
),
                                ),
                                array(
                                        'label'       => trans('Digital Zoom'),
                                        'fieldname'   => 'digital-zoom',
                                        'fieldtype'   => 'select',
                                        'fieldvalues' => array(
),
                                ),
                                array(
                                        'spanlabel' => trans('Core Tags'),
                                        'label'     => trans('Core Tags für diesen Inhalt nicht Parsen'),
                                        'fieldname' => 'escapecoretags',
                                        'fieldtype' => 'checkbox',
                                        'value'     => 1,
                                )
                        ),
                        'cell-phone' => array(
                                array(
                                        'label'       => trans('Wireless'),
                                        'fieldname'   => 'wireless',
                                        'fieldtype'   => 'multiplecheckbox',
                                        'fieldvalues' => array(
                                                'wi-fi'     => 'Wi-Fi',
                                                'bluetooth' => 'Bluetooth',
                                                'infrared'  => 'Infrared',
                                        ),
                                ),
                                array(
                                        'label'       => trans('Farbe'),
                                        'fieldname'   => 'color',
                                        'fieldtype'   => 'select',
                                        'fieldvalues' => array(
),
                                ),
                                array(
                                        'spanlabel' => trans('Core Tags'),
                                        'label'     => trans('Core Tags für diesen Inhalt nicht Parsen'),
                                        'fieldname' => 'escapecoretags',
                                        'fieldtype' => 'checkbox',
                                        'value'     => 1,
                                )
                        ),
                        'furniture'  => array(
                                array(
                                        'label'     => trans('Farbe'),
                                        'fieldname' => 'color',
                                        'fieldtype' => 'text',
                                ),
                                array(
                                        'label'     => trans('Abmessungen'),
                                        'fieldname' => 'dimensions',
                                        'fieldtype' => 'text',
                                ),
                                array(
                                        'spanlabel' => trans('Core Tags'),
                                        'label'     => trans('Core Tags für diesen Inhalt nicht Parsen'),
                                        'fieldname' => 'escapecoretags',
                                        'fieldtype' => 'checkbox',
                                        'value'     => 1,
                                )
                        ),
                        'product'    => array(
                        ),
                        'shoe'       => array(
                                array(
                                        'label'       => trans('Größe'),
                                        'fieldname'   => 'size',
                                        'fieldtype'   => 'select',
                                        'fieldvalues' => array(
),
                                ),
                                array(
                                        'label'       => trans('Farbe'),
                                        'fieldname'   => 'color',
                                        'fieldtype'   => 'select',
                                        'fieldvalues' => array(
),
                                ),
                                array(
                                        'spanlabel' => trans('Core Tags'),
                                        'label'     => trans('Core Tags für diesen Inhalt nicht Parsen'),
                                        'fieldname' => 'escapecoretags',
                                        'fieldtype' => 'checkbox',
                                        'value'     => 1,
                                )
                        ),
                        'test'       => array(
                        ),
                        'watch'      => array(
                                array(
                                        'label'       => trans('Gehäuse'),
                                        'fieldname'   => 'case',
                                        'fieldtype'   => 'select',
                                        'fieldvalues' => array(
                                                'stainless-steel' => trans('Edelstahl'),
                                                'plastic'         => trans('Plastik')
                                        ),
                                ),
                                array(
                                        'label'       => trans('Band'),
                                        'fieldname'   => 'band',
                                        'fieldtype'   => 'select',
                                        'fieldvalues' => array(
                                                'stainless-steel' => trans('Edelstahl'),
                                                'plastic'         => trans('Plastik'),
                                                'leather'         => trans('Leder'),
                                                'resin'           => trans('Harz'),
                                        ),
                                ),
                                array(
                                        'label'       => trans('Typ'),
                                        'fieldname'   => 'type',
                                        'fieldtype'   => 'select',
                                        'fieldvalues' => array(
                                                'analog'  => trans('Analog'),
                                                'digital' => trans('Digital'),
                                        ),
                                ),
                                array(
                                        'spanlabel' => trans('Core Tags'),
                                        'label'     => trans('Core Tags für diesen Inhalt nicht Parsen'),
                                        'fieldname' => 'escapecoretags',
                                        'fieldtype' => 'checkbox',
                                        'value'     => 0,
                                )
                        ),
                ),
        ),
        // END Product Application
        'movie'         => array(
                'title'       => trans('Movie Datenbank'),
                'description' => trans('-'),
                'itemtypes'   => array(
                        'person' => trans('Person'),
                        'movie'  => trans('Film')
                )
        ),
        'documentation' => array(
                'title'       => trans('Dokumentation'),
                'description' => trans('Erstellt eine Anwendung anhand einer Dokumentations Anwendung'),
                'itemtypes'   => array(
                        'page' => trans('Dokumentationsseite'),
                ),
                // required Fields
                'core-fields' => array(
                        'page' => array(
                                array(
                                        'label'     => trans('Titel'),
                                        'fieldname' => 'title',
                                        'fieldtype' => 'text',
                                ),
                                array(
                                        'label'     => trans('Content'),
                                        'fieldname' => 'content',
                                        'fieldtype' => 'richtext',
                                ),
                                array(
                                        'label'     => trans('Alias'),
                                        'fieldname' => 'alias',
                                        'fieldtype' => 'pageidentifier',
                                ),
                                array(
                                        'label'     => trans('Kategorie'),
                                        'fieldname' => 'catid',
                                        'fieldtype' => 'categorie',
                                        'value'     => 0,
                                ),
                                array(
                                        'label'     => trans('Zugriffsrechte'),
                                        'fieldname' => 'access',
                                        'fieldtype' => 'usergroups',
                                        'value'     => 0,
                                ),
                                array(
                                        'grouplabel' => trans('Core Tags'),
                                        'label'      => trans('Core Tags für diesen Inhalt nicht Parsen'),
                                        'fieldname'  => 'escapecoretags',
                                        'fieldtype'  => 'checkbox',
                                        'value'      => 0,
                                ),
                                array(
                                        'grouplabel' => trans('Publish'),
                                        'label'      => trans('Inhalt ist aktiv'),
                                        'fieldname'  => 'published',
                                        'fieldtype'  => 'checkbox',
                                        'value'      => 1,
                                        'checked'    => 1
                                )
                        ),
                ),
        ),
        'download'      => array(
                'title'       => trans('Download Archiv'),
                'description' => trans('-'),
                'itemtypes'   => array(
                        'file' => trans('Dateien'),
                ),
                // required Fields
                'core-fields' => array(
                        'file' => array(
                                array(
                                        'label'     => trans('Titel'),
                                        'fieldname' => 'title',
                                        'fieldtype' => 'text',
                                ),
                                array(
                                        'label'     => trans('Datei'),
                                        'fieldname' => 'file',
                                        'fieldtype' => 'mediafile',
                                ), /*
                                  array(
                                  'label' => trans('Alias'),
                                  'fieldname' => 'alias',
                                  'fieldtype' => 'pageidentifier',
                                  ), */
                                array(
                                        'label'     => trans('Kategorie'),
                                        'fieldname' => 'catid',
                                        'fieldtype' => 'categorie',
                                        'value'     => 0,
                                ),
                                array(
                                        'label'     => trans('Content'),
                                        'fieldname' => 'content',
                                        'fieldtype' => 'richtext',
                                ),
                                array(
                                        'label'     => trans('Zugriffsrechte'),
                                        'fieldname' => 'access',
                                        'fieldtype' => 'usergroups',
                                        'value'     => 0,
                                ),
                                array(
                                        'grouplabel' => trans('Core Tags'),
                                        'label'      => trans('Core Tags für diesen Inhalt nicht Parsen'),
                                        'fieldname'  => 'escapecoretags',
                                        'fieldtype'  => 'checkbox',
                                        'value'      => 0,
                                ), /*
                          array(
                          'grouplabel' => trans('Publish'),
                          'label' => trans('Inhalt ist aktiv'),
                          'fieldname' => 'published',
                          'fieldtype' => 'checkbox',
                          'value' => 1,
                          'checked' => 1
                          ) */
                        ),
                ),
        ),
        'page'          => array(
                'title'       => trans('Einfache Seite'),
                'description' => trans('-'),
                'itemtypes'   => array(
                        'page' => trans('Seite'),
                ),
                'core-fields' => array(
                        'page' => array(
                                array(
                                        'label'     => trans('Titel'),
                                        'fieldname' => 'title',
                                        'fieldtype' => 'text',
                                ),
                                array(
                                        'label'     => trans('Content'),
                                        'fieldname' => 'content',
                                        'fieldtype' => 'richtext',
                                ),
                                array(
                                        'label'     => trans('Zugriffsrechte'),
                                        'fieldname' => 'access',
                                        'fieldtype' => 'usergroups',
                                        'value'     => 0,
                                ),
                                array(
                                        'grouplabel' => trans('Core Tags'),
                                        'label'      => trans('Core Tags für diesen Inhalt nicht Parsen'),
                                        'fieldname'  => 'escapecoretags',
                                        'fieldtype'  => 'checkbox',
                                        'value'      => 0,
                                ),
                                array(
                                        'grouplabel' => trans('Publish'),
                                        'label'      => trans('Inhalt ist aktiv'),
                                        'fieldname'  => 'published',
                                        'fieldtype'  => 'checkbox',
                                        'value'      => 1,
                                        'checked'    => 1
                                )
                        )
                )
        ),
        'cookbook'      => array(
                'title'       => trans('Koch Rezepte'),
                'description' => trans('-'),
                'itemtypes'   => array(
                        'recipe' => trans('Rezept'),
                )
        ),
        'business'      => array(
                'title'       => trans('Business Katalog'),
                'description' => trans('-'),
                'itemtypes'   => array(
                        'company'  => trans('Hersteller'),
                        'employee' => trans('Mitarbeiter')
                )
        )
);
?>