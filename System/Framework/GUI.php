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
 * @category    Framework
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        GUI.php
 *
 */
class GUI
{

    /**
     * @var
     */
    protected static $objInstance;

    /**
     * @var
     */
    public $languages;

    /**
     * @var null
     */
    private $availableLanguages = null;

    /**
     * Prevent cloning of the object (Singleton)
     */
    private function __clone()
    {
        
    }

    /**
     * Return the current object instance (Singleton)
     *
     * @return GUI
     */
    public static function getInstance()
    {
        if ( !is_object( self::$objInstance ) )
        {
            self::$objInstance = new GUI();
            self::$objInstance->languages = array();
            self::$objInstance->getAvailableLanguages();
        }

        return self::$objInstance;
    }

    /**
     * get all available GUI Languages
     *
     * @return array
     */
    public function getAvailableLanguages()
    {
        if ( $this->availableLanguages !== null )
        {
            return $this->availableLanguages;
        }


        $languages = Cache::get( 'gui_languages' );
        if ( !$languages )
        {
            $result = Database::getInstance()->query( 'SELECT code FROM %tp%locale WHERE guilanguage = 1' )->fetchAll();

            $languages = array();
            foreach ( $result as $r )
            {
                $languages[ $r[ 'code' ] ] = true;
            }

            Cache::write( 'gui_languages', $languages );
        }
        $this->availableLanguages = $languages;
        return $languages;
    }

    /**
     *
     * @return string
     */
    public function getAutoDetectedLanguage()
    {
        $this->parseLanguageList();
        $lang = array_shift( $this->languages );

        $locale = Locales::getLocaleFromLang( Locales::getShortLocaleFromCode( $lang[ 0 ] ) );

        return $locale;
    }

    /**
     *
     * @param string $languageList
     * @return array/null
     */
    private function parseLanguageList( $languageList = null )
    {
        if ( is_null( $languageList ) )
        {
            if ( !isset( $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] ) )
            {
                $this->languages = array();
                return null;
            }
            $languageList = $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ];
        }

        $this->languages = array();
        $languageRanges = explode( ',', trim( $languageList ) );

        foreach ( $languageRanges as $languageRange )
        {
            if ( preg_match( '/(\*|[a-zA-Z0-9]{1,8}(?:-[a-zA-Z0-9]{1,8})*)(?:\s*;\s*q\s*=\s*(0(?:\.\d{0,3})|1(?:\.0{0,3})))?/S', trim( $languageRange ), $match ) )
            {
                if ( !isset( $match[ 2 ] ) )
                {
                    $match[ 2 ] = '1.0';
                }
                else
                {
                    $match[ 2 ] = (string) floatval( $match[ 2 ] );
                }

                if ( !isset( $this->languages[ $match[ 2 ] ] ) )
                {
                    $this->languages[ $match[ 2 ] ] = array();
                }

                $this->languages[ $match[ 2 ] ][] = strtolower( $match[ 1 ] );
            }
        }

        krsort( $this->languages );
        return $this->languages;
    }

    /**
     * compare two parsed arrays of language tags and find the matches
     *
     * @param array $accepted
     * @param array $available
     * @return array
     */
    private function findMatches( $accepted, $available )
    {
        $matches = array();
        $any = false;

        foreach ( $accepted as $acceptedQuality => $acceptedValues )
        {
            $acceptedQuality = floatval( $acceptedQuality );
            if ( $acceptedQuality === 0.0 )
            {
                continue;
            }

            foreach ( $available as $availableQuality => $availableValues )
            {
                $availableQuality = floatval( $availableQuality );
                if ( $availableQuality === 0.0 )
                {
                    continue;
                }

                foreach ( $acceptedValues as $acceptedValue )
                {
                    if ( $acceptedValue === '*' )
                    {
                        $any = true;
                    }

                    foreach ( $availableValues as $availableValue )
                    {
                        $matchingGrade = matchLanguage( $acceptedValue, $availableValue );
                        if ( $matchingGrade > 0 )
                        {
                            $q = (string) ($acceptedQuality * $availableQuality * $matchingGrade);
                            if ( !isset( $matches[ $q ] ) )
                            {
                                $matches[ $q ] = array();
                            }

                            if ( !in_array( $availableValue, $matches[ $q ] ) )
                            {
                                $matches[ $q ][] = $availableValue;
                            }
                        }
                    }
                }
            }
        }

        if ( count( $matches ) === 0 && $any )
        {
            $matches = $available;
        }

        krsort( $matches );
        return $matches;
    }

    /**
     * compare two language tags and distinguish the degree of matching
     *
     * @param array $a
     * @param array $b
     * @return float
     */
    private function matchLanguage( $a, $b )
    {
        $a = explode( '-', $a );
        $b = explode( '-', $b );
        for ( $i = 0, $n = min( count( $a ), count( $b ) ); $i < $n; $i++ )
        {
            if ( $a[ $i ] !== $b[ $i ] )
                break;
        }
        return $i === 0 ? 0 : (float) $i / count( $a );
    }

}
