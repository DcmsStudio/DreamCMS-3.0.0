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
 * @file        Locales.php
 *
 */
class Locales extends Locales_Abstract
{

    /**
     * @param $locale
     */
    public static function _init( $locale = '' )
    {

        $loc = $locale !== '' ? $locale : Session::get( 'guilang' );
        $loc = ((is_string( $loc ) && trim( $loc ) !== '' && trim( $loc ) !== '_') ? $loc : 'de_DE');


        $locale = Cache::get( 'locale_' . $loc );
        if ( !is_array( $locale ) )
        {
            $db = Database::getInstance();
            $rs = $db->query( "SELECT * FROM %tp%locale WHERE code = ?", $loc );
            $locale = $rs->fetch();
            if ( !isset( $locale[ 'timezone' ] ) )
            {
                Error::raise( 'Unregistered locale: ' . $loc );
            }

            Cache::write( 'locale_' . $loc, $locale );
        }

        if ( !is_array( $locale ) )
        {

            $rs = $db->query( "SELECT * FROM %tp%locale WHERE code = ?", 'de_DE' );
            $locale = $rs->fetch();
            if ( !isset( $locale[ 'timezone' ] ) )
            {
                Error::raise( 'Unregistered locale: ' . 'de_DE' );
            }

            Cache::write( 'locale_' . $loc, $locale );

            if ( !is_array( $locale ) )
            {
                Error::raise( 'Unregistered locale: ' . $loc );
            }
        }

        foreach ( $locale as $key => $value )
        {
            self::$config[ $key ] = $value;
            $key = null;
        }

        $locale = null;

        if ( self::$config[ 'timezone' ] != '' )
        {
            // Settings::set('default_timezoneoffset', self::$config['timezone']);
        }

        self::getWeekAndMonthnames();
        self::setLocale();
        self::setTimezone();

        self::$loaded = true;
    }

    /**
     * Get all registred languages in the database-table
     * @return array
     */
    public static function getAllRegistredLocales()
    {
        return Database::getInstance()->query( "SELECT * FROM %tp%locale" )->fetchAll();
    }

    /**
     *
     * @param integer $id
     */
    public static function getLocaleById( $id )
    {
        static $cache;

        if (!is_array($cache)) {
            $cache = array();
        }

        if ( isset($cache[$id]))
        {
            return $cache[$id];
        }

        $db = Database::getInstance();
        $cache[$id] = $db->query( "SELECT * FROM %tp%locale WHERE id = ?", intval( $id ) )->fetch();
        return $cache[$id];
    }

    /**
     *
     * @param string $code
     */
    public static function getLocaleByLang( $code )
    {
        static $cache;

        if (!is_array($cache)) {
            $cache = array();
        }

        if ( isset($cache[$code]))
        {
            return $cache[$code];
        }

        $db = Database::getInstance();
        $cache[$code] = $db->query( "SELECT * FROM %tp%locale WHERE `lang` = ?", $code )->fetch();
        return $cache[$code];
    }

    /**
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function getConfig( $name, $default = null )
    {
        if ( !self::$loaded )
        {
            self::_init();
        }

        if ( !isset( self::$config[ $name ] ) && $default !== null )
        {
            self::$config[ $name ] = $default;
        }

        if ( isset( self::$config[ $name ] ) )
        {
            return self::$config[ $name ];
        }
        elseif ( $name === 'ALL' )
        {
            return self::$config;
        }
        else
        {
            return false;
        }
    }

    /**
     * Get a list of locales (code => language and country)
     *
     * @return list of languages in the form 'code' => 'name'
     */
    public static function getLocaleList()
    {
        return self::$all_locales;
    }

    /**
     *
     * @param string $locale
     * @return string
     */
    public static function getCodeFromLocale( $locale )
    {
        return preg_replace( '/.*(_|-)/', '', $locale );
    }

    /**
     * Get a name from a locale code (xx_YY).
     *
     * @see getLocaleList()
     *
     * @param mixed $code locale code
     * @return Name of the locale
     */
    public static function getLocaleNameFromCode( $code )
    {
        $langs = self::getLocaleList();

        return isset( $langs[ $code ] ) ? $langs[ $code ] : false;
    }

    /**
     * Provides you "likely locales"
     * for a given "short" language code. This is a guess,
     * as we can't disambiguate from e.g. "en" to "en_US" - it
     * could also mean "en_UK". Based on the Unicode CLDR
     * project.
     * @see http://www.unicode.org/cldr/data/charts/supplemental/likely_subtags.html
     *
     * @param string $lang Short language code, e.g. "en"
     * @return string Long locale, e.g. "en_US"
     */
    public static function getLocaleFromLang( $lang )
    {
        if ( preg_match( '/\-|_/', $lang ) )
        {
            return $lang;
        }
        else if ( isset( self::$likely_subtags[ $lang ] ) )
        {
            return self::$likely_subtags[ $lang ];
        }
        else
        {
            return $lang . '_' . strtoupper( $lang );
        }
    }

    /**
     * Gets a RFC 1766 compatible language code,
     * e.g. "en-US".
     *
     * @see http://www.ietf.org/rfc/rfc1766.txt
     * @see http://tools.ietf.org/html/rfc2616#section-3.10
     *
     * @param string $locale
     * @return string
     */
    public static function convert_rfc1766( $locale )
    {
        return str_replace( '_', '-', $locale );
    }

    /**
     *
     * @return integer
     */
    public static function getLocaleId()
    {
        if ( !self::$loaded )
        {
            self::_init();
        }
        return self::$config[ 'id' ];
    }

    /**
     *
     * @return string
     */
    public static function getLocaleIcon()
    {
        if ( !self::$loaded )
        {
            self::_init();
        }
        return self::$config[ 'flag' ];
    }

    /**
     *
     * @return string
     */
    public static function getLocaleName()
    {
        if ( !self::$loaded )
        {
            self::_init();
        }
        return self::$config[ 'title' ];
    }

    /**
     * Return a Array of all Locales, Sorted alphabtically by the language name
     * @return array of all Locales
     */
    public static function getAllLocales()
    {
        return self::$common_locales;
    }

    /**
     *
     * @return string
     */
    public static function getLocale()
    {
        if ( !self::$loaded )
        {
            self::_init();
        }
        return self::$config[ 'code' ];
    }

    /**
     *
     * @return string
     */
    public static function getShortLocale()
    {
        if ( !self::$loaded )
        {
            self::_init();
        }
        $locale = explode( '-', self::convert_rfc1766( self::$config[ 'code' ] ) );
        return $locale[ 0 ];
    }

    /**
     *
     * @param string $code
     * @return string
     */
    public static function getShortLocaleFromCode( $code )
    {
        $locale = explode( '-', self::convert_rfc1766( $code ) );
        return $locale[ 0 ];
    }


    /**
     * @param $offset
     * @return string
     */
    private static function formatOffset($offset) {
		$hours = $offset / 3600;
		$remainder = $offset % 3600;
		$sign = $hours > 0 ? '+' : '-';
		$hour = (int) abs($hours);
		$minutes = (int) abs($remainder / 60);

		if ($hour === 0 AND $minutes === 0) {
			$sign = ' ';
		}

		return 'GMT ' . $sign . str_pad($hour, 2, '0', STR_PAD_LEFT).':'. str_pad($minutes,2, '0');
	}


    /**
     *
     * @param bool $useKeys
     * @return array
     */
    public static function getTimezones($useKeys = true)
    {

	    $data = Cache::get('timezones');
		if (!$data) {


		    static $allRegions = array(
			    DateTimeZone::AFRICA,
			    DateTimeZone::AMERICA,
			    DateTimeZone::ANTARCTICA,
			    DateTimeZone::ASIA,
			    DateTimeZone::ATLANTIC,
			    DateTimeZone::AUSTRALIA,
			    DateTimeZone::EUROPE,
			    DateTimeZone::INDIAN,
			    DateTimeZone::PACIFIC,
		    );

		    $region = array();
		    foreach ($allRegions as $area){
			    array_push ($region,DateTimeZone::listIdentifiers( $area ));
		    }

		    $count = count ($region); $i = 0; $idx = 0;

		    $data = array();

		    while ($i < $count){
			    $chunck = $region[$i];

			    $timezone_offsets = array();
			    foreach( $chunck as $timezone ){
				    $tz = new DateTimeZone($timezone);
				    $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
			    }

			    asort ($timezone_offsets);
			    $timezone_list = array();
			    foreach ($timezone_offsets as $timezone => $offset){
				    $offset_prefix = $offset < 0 ? '-' : '+';
				    $offset_formatted = gmdate( 'H:i', abs($offset) );
				    $pretty_offset = "UTC ${offset_prefix}${offset_formatted}";
				    $timezone_list[$timezone] = "(${pretty_offset}) $timezone";
			    }


			    foreach ($timezone_list as $key => $val){
					if ($useKeys) {
						$data[$idx] = $key;
					}
				    else {
					    $data[$idx] = $val;
				    }
				    ++$idx;
			    }
			    ++$i;
		    }

			Cache::write('timezones', $data);
		}


		return $data;



	    $list = DateTimeZone::listAbbreviations();
	    $idents = DateTimeZone::listIdentifiers();

	    $data = $offset = $added = array();
	    foreach ($list as $abbr => $info) {
		    foreach ($info as $zone) {
			    if ( ! empty($zone['timezone_id'])
				    AND
				    ! in_array($zone['timezone_id'], $added)
				    AND
				    in_array($zone['timezone_id'], $idents)) {
				    $z = new DateTimeZone($zone['timezone_id']);
				    $c = new DateTime(null, $z);
				    $zone['time'] = $c->format('H:i');
				    $data[] = $zone;
				    $offset[] = $z->getOffset($c);
				    $added[] = $zone['timezone_id'];
			    }
		    }
	    }

	    unset($list, $idents, $added);

	    array_multisort($offset, SORT_ASC, $data);
	    $options = array();
	    foreach ($data as $idx => $row)
	    {
		    if ( $useKeys ) {
			    $options[$idx] = $row['timezone_id'];
		    }
		    else {
		        $options[$row['timezone_id']] = $row['time'] . ' - '. self::formatOffset($row['offset']). ' ' . $row['timezone_id'];
		    }
	    }

	    unset($data, $offset);


		return $options;





        $arrWords = array();

        if ( is_file( I18N_PATH . self::$config[ 'code' ] . '/timezones.txt' ) )
        {
            $arrWords = array_map( 'trim', file( I18N_PATH . self::$config[ 'code' ] . '/timezones.txt' ) );
        }
        else
        {
            if ( is_file( DATA_PATH . 'system/default_timezones.txt' ) )
            {
                $arrWords = array_map( 'trim', file( DATA_PATH . 'system/default_timezones.txt' ) );
            }
        }


        return $arrWords;
    }

    /**
     *
     * @param string $locale
     * @param boolean $loadAll
     * @return array
     *         an with all stopwords
     */
    public static function getStopwords( $locale = null, $loadAll = false )
    {
        if ( $locale !== null )
        {
            $locale = self::getLocaleFromLang( $locale );
        }
        else
        {
            $locale = self::$config[ 'code' ];
        }

        $stopwords = array();

        if ( !function_exists( 'utf8_strtolower' ) )
        {
            include_once(HELPER_PATH . 'mbstring.php');
        }


        if ( !$loadAll )
        {
            if ( is_file( I18N_PATH . $locale . '/stopwords.txt' ) )
            {
                $stops = file( I18N_PATH . $locale . '/stopwords.txt' );
                foreach ( $stops as $idx => $word )
                {
                    if ( !$word )
                    {
                        continue;
                    }
                    $stopwords[ trim( utf8_strtolower( $word ) ) ] = trim( $word );
                }

                $stops = array();
            }
        }
        else
        {

            $db = Database::getInstance();
            $result = $db->query( "SELECT `code` FROM %tp%locale WHERE contentlanguage = 1" )->fetchAll();
            foreach ( $result as $r )
            {
                $locale = $r[ 'code' ];


                if ( is_file( I18N_PATH . $locale . '/stopwords.txt' ) )
                {
                    $stops = file( I18N_PATH . $locale . '/stopwords.txt' );
                    foreach ( $stops as $idx => $word )
                    {
                        if ( !$word )
                        {
                            continue;
                        }
                        $stopwords[ trim( utf8_strtolower( $word ) ) ] = trim( $word );
                    }

                    $stops = array();
                }
            }

            $result = null;
        }

        return $stopwords;
    }

    /**
     *
     * @param string $format
     * @param integer/string $time (unix timestamp or string)
     * @return string
     */
    public static function formatedDate( $format, $time )
    {
        if ( !self::$loaded )
        {
            self::_init();
        }
        $time = !is_numeric( $time ) ? strtotime( $time ) : $time;

        return self::getTranslatedDate( $format, (int) $time );
    }

    /**
     *
     * @param integer/string $time (unix timestamp or string)
     * @return string
     */
    public static function formatDate( $time )
    {
        if ( !self::$loaded )
        {
            self::_init();
        }
        $time = !is_numeric( $time ) ? strtotime( $time ) : $time;

        return self::getTranslatedDate( self::$config[ 'dateformat' ], (int) $time );
    }

    /**
     *
     * @param integer/string $time (unix timestamp or string)
     * @return string
     */
    public static function formatDateTime( $time )
    {
        if ( !self::$loaded )
        {
            self::_init();
        }

        $time = !is_numeric( $time ) ? strtotime( $time ) : $time;

        return self::getTranslatedDate( self::$config[ 'datetime_format' ], (int) $time );
    }

    /**
     *
     * @param integer/string $time (unix timestamp or string)
     * @return string
     */
    public static function formatTime( $time )
    {
        if ( !self::$loaded )
        {
            self::_init();
        }
        $time = !is_numeric( $time ) ? strtotime( $time ) : $time;


        #self::getTranslatedDate(self::$config['timeformat'],  $time);

        return date( self::$config[ 'timeformat' ], (int) $time );
    }

    /**
     *
     * @param integer/string $time (unix timestamp or string)
     * @return string
     */
    public static function formatFullDate( $time )
    {
        if ( !self::$loaded )
        {
            self::_init();
        }
        $time = !is_numeric( $time ) ? strtotime( $time ) : $time;


        return self::getTranslatedDate( self::$config[ 'fulldate_format' ], (int) $time );
    }

    /**
     *
     * @param integer/string $time (unix timestamp or string)
     * @return string
     */
    public static function formatFullDateTime( $time )
    {
        if ( !self::$loaded )
        {
            self::_init();
        }
        $time = !is_numeric( $time ) ? strtotime( $time ) : $time;

        return self::getTranslatedDate( self::$config[ 'fulldatetime_format' ], (int) $time );
    }

    /**
     *
     * @param integer $number
     * @param integer $decimals
     * @return string
     */
    public static function formatNumber( $number, $decimals = 0 )
    {
        if ( !self::$loaded )
        {
            self::_init();
        }

        return number_format( $number, $decimals, self::$config[ 'decimal' ], self::$config[ 'thousands' ] );
    }

    /**
     *
     * @param integer $index 0 - 6
     * @param boolean $longFormat
     * @return string
     */
    public static function getDayName( $index = 0, $longFormat = false )
    {
        if ( $longFormat )
        {
            return self::$weekday_long_index[ $index ];
        }

        return self::$weekday_short_index[ $index ];
    }

    /**
     *
     * @param integer $index 1 - 12
     * @param boolean $longFormat
     * @return string
     */
    public static function getMonthName( $index = 1, $longFormat = false )
    {
        if ( $longFormat )
        {
            return self::$month_long_index[ $index ];
        }

        return self::$month_short_index[ $index ];
    }

    /**
     *
     * @param bool|\type $code
     * @param bool|\type $wincode
     */
    public static function setLocale( $code = false, $wincode = false )
    {
        $code = $code === false ? self::$config[ 'code' ] : $code;
        $wincode = $wincode === false ? self::$config[ 'wincode' ] : $wincode;

        if ( strpos( strtolower( PHP_OS ), 'win' ) === false )
        {
            T_setlocale( (defined( 'LC_MESSAGES' ) ? LC_MESSAGES : LC_ALL ), $code . '.utf-8' );
            //setlocale(LC_TIME, $code . '.utf-8');
        }
        else
        {
            T_setlocale( (defined( 'LC_MESSAGES' ) ? LC_MESSAGES : LC_ALL ), $wincode );
            //setlocale(LC_TIME, $wincode);
        }
    }

    /**
     * @var int timestamp
     * @var int or timestamp
     * @return string approximate time, for example: about 19 hours
     */
    static function getTimeDifferenceInWords( $firstTime, $secondTime = '' )
    {


        // if second time was not supplied, use current time
        $secondTime = ($secondTime) ? strtotime( $secondTime ) : time();

        // find out the difference in seconds
        $seconds = ($firstTime > $secondTime) ? $firstTime - $secondTime : $secondTime - $firstTime;

        $minutes = floor( $seconds / 60 );
        if ( $minutes === 0 )
        {
            return 'less than a minute';
        }
        if ( $minutes === 1 )
        {
            return trans( 'Minute' );
        }
        if ( $minutes < 59 )
        {
            return $minutes . ' ' . trans( 'Minuten' );
        }

        $hours = round( $minutes / 60 );
        if ( $hours === 1 )
        {
            return '1 ' . trans( 'Stunde' );
        }
        if ( $hours < 24 )
        {
            return $hours . ' ' . trans( 'Stunden' );
        }

        $days = round( $hours / 24 );
        if ( $days === 1 )
        {
            return '1 ' . trans( 'Tag' );
        }
        if ( $days < 30 )
        {
            return $days . ' ' . trans( 'Tage' );
        }

        $months = round( $days / 30 );
        if ( $months === 1 )
        {
            return '1 ' . trans( 'Monat' );
        }
        if ( $months < 12 )
        {
            return $months . ' ' . trans( 'Monate' );
        }

        $years = round( $months / 12 );
        if ( $years === 1 )
        {
            return '1 ' . trans( 'Jahr' );
        }
        return $years . ' ' . trans( 'Jahre' );
    }

}
