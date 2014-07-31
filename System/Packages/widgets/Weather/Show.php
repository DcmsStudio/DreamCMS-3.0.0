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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Widget s
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Show.php
 */
class Widget_Weather_Show extends Widget
{

	private $skin = 'tango-reloaded';

	const ACCUWEATHER_LOCATE_CITY_URL = 'http://forecastfox.accuweather.com/adcbin/forecastfox/locate_city.asp?partner=forecastfox&location=';

	const ACCUWEATHER_WEATHER_DATA_URL = 'http://forecastfox.accuweather.com/adcbin/forecastfox/weather_data.asp?partner=forecastfox&location=';

	public function getData ()
	{

		if ( $this->input('q') )
		{
			$this->findLocationByName();
			exit;
		}


		if ( !$this->getConfig('location') )
		{
			return 'This widget has not been configured yet, please click on the configure icon to do so now.';
		}
		/*
		  setlocale( LC_ALL, 'de_DE.utf-8' );
		  //$loc_de = setlocale (LC_ALL, 'de_DE@euro', 'de_DE.utf-8', 'de_DE', 'de', 'ge');

		  return;
		  $weather = cache::get( 'widget_data_weather_' . $this->getID() );
		  if ( is_null( $weather ) || $weather[ 'expires' ] < time() )
		  {
		  $requestAddress = "http://www.google.de/ig/api?weather=" . urlencode( $this->getConfig( 'location' ) ) . "&hl=" . substr( GUI_LANGUAGE, 0, 2 );
		  $xml_str        = Library::getRemoteFile( $requestAddress );

		  //ISO 8859-1 to UTF-8
		  //$xml_str = preg_replace("/([\xC2\xC3])([\x80-\xBF])/e", "chr(ord('\\1')<<6&0xC0|ord('\\2')&0x3F)", $xml_str);
		  //$xml_str = preg_replace("/([\x80-\xFF])/e","chr(0xC0|ord('\\1')>>6).chr(0x80|ord('\\1')&0x3F)", $xml_str);

		  if ( $xml_str === false )
		  {
		  return 'The remote server could not be reached while trying to fetch data for the widget.';
		  }
		  $expires = time() + 1800;
		  cache::write( 'widget_data_weather_' . $this->getID(), array( 'expires' => $expires, 'xml_str' => $xml_str ) );
		  $src     = 'google';
		  }
		  else
		  {
		  $xml_str = $weather[ 'xml_str' ];
		  $src     = 'cache';
		  }


		  //ISO 8859-1 to UTF-8
		  $xml_str = preg_replace( "/([\xC2\xC3])([\x80-\xBF])/e", "chr(ord('\\1')<<6&0xC0|ord('\\2')&0x3F)", $xml_str );
		  $xml_str = preg_replace( "/([\x80-\xFF])/e", "chr(0xC0|ord('\\1')>>6).chr(0x80|ord('\\1')&0x3F)", $xml_str );


		  die( $requestAddress );

		  $xml = simplexml_load_string( $xml_str, 'SimpleXMLElement' ) or die( $xml_str );

		  $count  = 0;
		  $output = '';
		  if ( !is_object( $xml ) || isset( $xml->weather[ 0 ]->problem_cause ) )
		  {
		  return '<div>' . sprintf( "<p>There is no weather information for <strong>`%s`</strong>.</p><p>Please click on the configure icon and enter a different location.</p>", $this->getConfig( 'location' ) ) . '</div>';
		  }
		  else
		  {
		  // work out which units to use
		  $units_from = @$xml->weather[ 0 ]->forecast_information[ 0 ]->unit_system[ 'data' ] == 'US' ? 'f' : 'c';
		  $units_to   = $this->getConfig( 'units' );


		  $data[ 'src' ] = $src;

		  // what location are we showing the weather for?
		  $data[ 'location' ] = @$xml->weather[ 0 ]->forecast_information[ 0 ]->city[ 'data' ];

		  $_icon = str_replace( '/ig/images/weather/', WIDGET_URL_PATH . "weather/icons/{$this->skin}/", !empty( $current->icon[ 'data' ] ) ? $current->icon[ 'data' ] : @$xml->weather[ 0 ]->forecast_conditions[ 0 ]->icon[ 'data' ]  );
		  $_icon = str_replace( '.gif', '.png', $_icon );




		  // show the current weather
		  $current                               = @$xml->weather[ 0 ]->current_conditions[ 0 ];
		  $data[ 'current' ][ 'icon' ]           = $_icon;
		  $data[ 'current' ][ 'condition' ]      = $current->condition[ 'data' ];
		  $data[ 'current' ][ 'wind_condition' ] = $current->wind_condition[ 'data' ];
		  $data[ 'current' ][ 'humidity' ]       = $current->humidity[ 'data' ];
		  $data[ 'current' ][ 'temp' ]           = $units_to == 'c' ? $current->temp_c[ 'data' ] : $current->temp_f[ 'data' ];

		  // show the forecast
		  foreach ( $xml->weather as $item )
		  {
		  foreach ( $item->forecast_conditions as $count => $snew )
		  {
		  if ( !$snew->day_of_week[ 'data' ] )
		  continue;

		  $_[ 'icon' ] = str_replace( '/ig/images/weather/', WIDGET_URL_PATH . "weather/icons/{$this->skin}/", $snew->icon[ 'data' ] );
		  $_[ 'icon' ] = str_replace( '.gif', '.png', $_[ 'icon' ] );



		  $forecast                = array( );
		  $forecast[ 'day' ]       = ( string ) $snew->day_of_week[ 'data' ];
		  $forecast[ 'icon' ]      = ( string ) $_[ 'icon' ];
		  $forecast[ 'low' ]       = self::convert( $snew->low[ 'data' ], $units_from, $units_to );
		  $forecast[ 'high' ]      = self::convert( $snew->high[ 'data' ], $units_from, $units_to );
		  $forecast[ 'condition' ] = ( string ) $snew->condition[ 'data' ];
		  $data[ 'forecast' ][ ]   = $forecast;
		  }
		  }
		  }
		 */

		$data = $this->getWeatherData($this->getConfig('location'), ($this->getConfig('units') == 'c' ? 1 : 0));
		$this->setWidgetData($data);
	}

	public function findLocationByName ()
	{

		$q = $this->Input->input('q');


		$q      = str_replace(' ', '%20', $q);
		$result = $this->db->query('SELECT * FROM %tp%weather_locations WHERE city LIKE ?', '%'.$q .'%')->fetchAll();

		if ( count($result) )
		{

			echo Library::json(array (
			                         'success' => true,
			                         'result'  => $result
			                   ));
			exit;

		}
		else
		{
			$request_url = self::ACCUWEATHER_LOCATE_CITY_URL . $q;


			$ch      = curl_init();
			$timeout = 5;
			curl_setopt($ch, CURLOPT_URL, $request_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$data = curl_exec($ch);
			curl_close($ch);

			$tmp = simplexml_load_string($data);
			$xml = json_decode(json_encode(simplexml_load_string($data)));


			$xml = new Xml();
			$arr = array_shift($xml->createArray($data));


			$result = array ();

			if ( ($found = count($arr[ 'citylist' ][ 'location' ])) > 0 )
			{
				//$tmp->children();
				if ( $found > 1 )
				{
					foreach ( $arr[ 'citylist' ][ 'location' ] as $row )
					{
						$result[ ] = array (
							'country'  => $row[ 'attributes' ][ 'city' ],
							'location' => $row[ 'attributes' ][ 'location' ],
							'city'     => $row[ 'attributes' ][ 'state' ]
						);

						$this->db->query('REPLACE INTO %tp%weather_locations (city,location,country) VALUES (?,?,?)', $row[ 'attributes' ][ 'city' ], $row[ 'attributes' ][ 'location' ], $row[ 'attributes' ][ 'state' ]);
					}
				}
				else
				{

					$attr = $arr[ 'citylist' ][ 'location' ][ 'attributes' ];

					$result[ ] = array (
						'country'  => $attr[ 'city' ],
						'location' => $attr[ 'location' ],
						'city'     => $attr[ 'state' ]
					);

					$this->db->query('REPLACE INTO %tp%weather_locations (city,location,country) VALUES (?,?,?)', $attr[ 'city' ], $attr[ 'location' ], $attr[ 'state' ]);
				}

				echo Library::json(array (
				                         'success' => true,
				                         'result'  => $result
				                   ));
				exit;
			}
			else
			{
				echo Library::json(array (
				                         'success' => true,
				                         'result'  => array ()
				                   ));
				exit;
			}

		}


		$result   = array ();
		$fcontent = file_get_contents(dirname(__FILE__) . '/WeatherLocationDatabase.txt');
		foreach ( explode("\n", $fcontent) as $line )
		{
			if ( preg_match('#(' . preg_quote($q, '#') . ')#is', $line) )
			{
				preg_match_all('#"([^"]*)"#is', $line, $match);

				$cityName = '';
				$location = '';
				$country  = '';

				if ( $match[ 0 ][ 0 ] )
				{
					$cityName = preg_replace('#,.*$#', '', $match[ 0 ][ 0 ]);
				}

				if ( $match[ 0 ][ 1 ] )
				{
					$location = $match[ 0 ][ 1 ];
				}

				if ( $match[ 0 ][ 2 ] )
				{
					$country = $match[ 0 ][ 2 ];
				}

				$result[ ] = array (
					'country'  => str_replace('"', '', $country),
					'location' => str_replace('"', '', $location),
					'city'     => str_replace('"', '', $cityName)
				);
			}
		}


		echo Library::json(array (
		                         'success' => true,
		                         'result'  => $result
		                   ));
		exit;
	}

	/**
	 *
	 * @param string $location
	 * @param int    $metric
	 * @return type
	 */
	private function getWeatherData ( $location, $metric )
	{

		#	$url = 'http://wwwa.accuweather.com/adcbin/forecastfox/weather_data.asp?location=' . $location . '&metric=' . $metric;
		$url     = self::ACCUWEATHER_WEATHER_DATA_URL . $location . '&metric=' . $metric;
		$ch      = curl_init();
		$timeout = 0;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$file_contents = curl_exec($ch);
		curl_close($ch);



		$xml = simplexml_load_string($file_contents);

		$weather[ 'weather_city' ]     = (string)$xml->local->city;
		$weather[ 'weather_country' ]  = (string)$xml->local->state;
		$weather[ 'weather_temp' ]     = $this->formatTemp((int)$xml->currentconditions->temperature, (string)$xml->units->temp);
		$weather[ 'weather_realfeel' ] = $this->formatTemp((int)$xml->currentconditions->realfeel, (string)$xml->units->temp);
		$weather[ 'weather_text' ]     = $this->convertAccuTrans((string)$xml->currentconditions->weathericon);
		$weather[ 'weather_icon' ]     = $this->getIcon((int)$xml->currentconditions->weathericon);

		$weather[ 'weather_windspeed' ]     = (int)$xml->currentconditions->windspeed . ' ' . (string)$xml->units->speed;
		$weather[ 'weather_winddirection' ] = (string)$xml->currentconditions->winddirection;

		$weather[ 'weather_humidity' ] = (string)$xml->currentconditions->humidity;


		// forecast
		//$day = count($xml->forecast->day);
		$day = 5;
		for ( $i = 0; $i < $day; $i++ )
		{
			$weather[ 'weather_forecast' ][ $i ][ 'date' ]    = $this->convertDate((string)$xml->forecast->day[ $i ]->obsdate, true);
			$weather[ 'weather_forecast' ][ $i ][ 'sunrise' ] = (string)$xml->forecast->day[ $i ]->sunrise;
			$weather[ 'weather_forecast' ][ $i ][ 'sunset' ]  = (string)$xml->forecast->day[ $i ]->sunset;


			$weather[ 'weather_forecast' ][ $i ][ 'day_text' ] = $this->convertAccuTrans((string)$xml->forecast->day[ $i ]->daytime->weathericon);
			$weather[ 'weather_forecast' ][ $i ][ 'day_icon' ] = $this->getIcon((int)$xml->forecast->day[ $i ]->daytime->weathericon);

			$weather[ 'weather_forecast' ][ $i ][ 'day_heightemp' ] = $this->formatTemp((int)$xml->forecast->day[ $i ]->daytime->hightemperature, (string)$xml->units->temp);
			$weather[ 'weather_forecast' ][ $i ][ 'day_lowtemp' ]   = $this->formatTemp((int)$xml->forecast->day[ $i ]->daytime->lowtemperature, (string)$xml->units->temp);

			$weather[ 'weather_forecast' ][ $i ][ 'day_windspeed' ]     = (int)$xml->forecast->day[ $i ]->daytime->windspeed . ' ' . (string)$xml->units->speed;
			$weather[ 'weather_forecast' ][ $i ][ 'day_winddirection' ] = (string)$xml->forecast->day[ $i ]->daytime->winddirection;

			$weather[ 'weather_forecast' ][ $i ][ 'day_realfeellow' ]  = $this->formatTemp((int)$xml->forecast->day[ $i ]->daytime->realfeellow, (string)$xml->units->temp);
			$weather[ 'weather_forecast' ][ $i ][ 'day_realfeelhigh' ] = $this->formatTemp((int)$xml->forecast->day[ $i ]->daytime->realfeelhigh, (string)$xml->units->temp);
		}

		return $weather;
	}

	private function getIcon ( $iconcode )
	{

		if ( file_exists(dirname(__FILE__) . '/icons/' . $iconcode . '.png') )
		{
			return 'Packages/widgets/Weather/icons/' . $iconcode . '.png';
		}
		else
		{
			return 'Packages/widgets/Weather/icons/na.png';
		}
	}

	private function formatTemp ( $temp, $currentMetricUnit )
	{

		$units_to = $this->getConfig('units');

		if ( $units_to == 'c' && $currentMetricUnit == 'C' )
		{
			return $temp . '°C';
		}
		elseif ( $units_to == 'f' && $currentMetricUnit == 'F' )
		{
			return $temp . '°F';
		}
	}

	private function convertDate ( $input, $daynameOnly = false )
	{

		$timestamp = strtotime($input);

		if ( !$daynameOnly )
		{
			return Locales::formatFullDate($timestamp);
		}
		else
		{
			$day = date('w', $timestamp);

			return Locales::getDayName($day, false);
		}
	}

	private function convertAccuTrans ( $iconcode = '' )
	{

		if ( $iconcode == '01' )
		{
			$data = trans('Wolkenlos');
		}
		elseif ( $iconcode == '02' )
		{
			$data = trans('Heiter');
		}
		elseif ( $iconcode == '03' )
		{
			$data = trans('Heiter bis wolkig');
		}
		elseif ( $iconcode == '04' )
		{
			$data = trans('Wolkig');
		}
		elseif ( $iconcode == '05' )
		{
			$data = trans('Dunstiger Sonnenschein');
		}
		elseif ( $iconcode == '06' )
		{
			$data = trans('Bewölkt');
		}
		elseif ( $iconcode == '07' )
		{
			$data = trans('Stark bewölkt');
		}
		elseif ( $iconcode == '08' )
		{
			$data = trans('Bedeckt');
		}
		elseif ( $iconcode == '11' )
		{
			$data = trans('Nebel');
		}
		elseif ( $iconcode == '12' )
		{
			$data = trans('Regenschauer');
		}
		elseif ( $iconcode == '13' )
		{
			$data = trans('Bewölkt mit Regenschauern');
		}
		elseif ( $iconcode == '14' )
		{
			$data = trans('Wolkig mit Regenschauern');
		}
		elseif ( $iconcode == '15' )
		{
			$data = trans('Gewitter');
		}
		elseif ( $iconcode == '16' )
		{
			$data = trans('Bewölkt mit Gewitterschauern');
		}
		elseif ( $iconcode == '17' )
		{
			$data = trans('Wolkig mit Gewitterschauern');
		}
		elseif ( $iconcode == '18' )
		{
			$data = trans('Regen');
		}
		elseif ( $iconcode == '19' )
		{
			$data = trans('Schneeschauer');
		}
		elseif ( $iconcode == '20' )
		{
			$data = trans('Bewölkt mit Schneeschauern');
		}
		elseif ( $iconcode == '21' )
		{
			$data = trans('Wolkig mit Schneeschauern');
		}
		elseif ( $iconcode == '22' )
		{
			$data = trans('Schneefall');
		}
		elseif ( $iconcode == '23' )
		{
			$data = trans('Bewölkt mit Schneefall');
		}
		elseif ( $iconcode == '24' )
		{
			$data = trans('Glatteis');
		}
		elseif ( $iconcode == '25' )
		{
			$data = trans('Graupelschauer');
		}
		elseif ( $iconcode == '26' )
		{
			$data = trans('Eisregen');
		}
		elseif ( $iconcode == '29' )
		{
			$data = trans('Schneeregen');
		}
		elseif ( $iconcode == '30' )
		{
			$data = trans('Heiß');
		}
		elseif ( $iconcode == '31' )
		{
			$data = trans('Kalt');
		}
		elseif ( $iconcode == '32' )
		{
			$data = trans('Windig');
		}
		elseif ( $iconcode == '33' )
		{
			$data = trans('Klare Nacht');
		}
		elseif ( $iconcode == '34' )
		{
			$data = trans('Überwiegend klar');
		}
		elseif ( $iconcode == '35' )
		{
			$data = trans('Wolkig');
		}
		elseif ( $iconcode == '36' )
		{
			$data = trans('Zeitweise wolkig');
		}
		elseif ( $iconcode == '37' )
		{
			$data = trans('Dunstig');
		}
		elseif ( $iconcode == '38' )
		{
			$data = trans('Dunstig');
		}
		elseif ( $iconcode == '39' )
		{
			$data = trans('Wolkig mit Regenschauern');
		}
		elseif ( $iconcode == '40' )
		{
			$data = trans('Bewölkt mit Regenschauern');
		}
		elseif ( $iconcode == '41' )
		{
			$data = trans('Wolkig mit Gewitterschauern');
		}
		elseif ( $iconcode == '42' )
		{
			$data = trans('Bewölkt mit Gewitterschauern');
		}
		elseif ( $iconcode == '43' )
		{
			$data = trans('Bewölkt mit Schneeschauern');
		}
		elseif ( $iconcode == '44' )
		{
			$data = trans('Bewölkt mit Schneefall');
		}
		else
		{
			$data = $iconcode . ' untranslated';
		}

		return $data;
	}

	private function getWindDirection ( $degree = 0 )
	{

		$direction = array (
			'N',
			'NNO',
			'NO',
			'ONO',
			'O',
			'OSO',
			'SE',
			'SSO',
			'S',
			'SSW',
			'SW',
			'WSW',
			'W',
			'WNW',
			'NW',
			'NNW'
		);

		$step = 360 / (count($direction));
		$b    = floor(($degree + ($step / 2)) / $step);

		return $direction[ $b % count($direction) ];
	}

	private function convertConditions ( $input )
	{

		if ( $input == 'AM Clouds/PM Sun' )
		{
			$data = trans('vormittags bewölkt/nachmittags sonnig');
		}
		elseif ( $input == 'AM Drizzle' )
		{
			$data = trans('vormittags Nieselregen');
		}
		elseif ( $input == 'AM Drizzle/Wind' )
		{
			$data = trans('vorm. Nieselregen / Wind');
		}
		elseif ( $input == 'AM Fog/PM Clouds' )
		{
			$data = trans('vormittags Nebel/nachmittags bewölkt');
		}
		elseif ( $input == 'AM Fog/PM Sun' )
		{
			$data = trans('vormittags Nebel, nachmittags sonnig');
		}
		elseif ( $input == 'AM Ice' )
		{
			$data = trans('vorm. Eis');
		}
		elseif ( $input == 'AM Light Rain' )
		{
			$data = trans('vormittags leichter Regen');
		}
		elseif ( $input == 'AM Light Rain/Wind' )
		{
			$data = trans('vorm. leichter Regen / Wind');
		}
		elseif ( $input == 'AM Light Snow' )
		{
			$data = trans('vormittags leichter Schneefall');
		}
		elseif ( $input == 'AM Rain' )
		{
			$data = trans('vormittags Regen');
		}
		elseif ( $input == 'AM Rain/Snow Showers' )
		{
			$data = trans('vorm. Regen-/Schneeschauer');
		}
		elseif ( $input == 'AM Rain/Snow' )
		{
			$data = trans('vormittags Regen / Schnee');
		}
		elseif ( $input == 'AM Rain/Snow/Wind' )
		{
			$data = trans('vorm. Regen / Schnee / Wind');
		}
		elseif ( $input == 'AM Rain/Wind' )
		{
			$data = trans('vorm. Regen / Wind');
		}
		elseif ( $input == 'AM Showers' )
		{
			$data = trans('vormittags Schauer');
		}
		elseif ( $input == 'AM Showers/Wind' )
		{
			$data = trans('vormittags Schauer / Wind');
		}
		elseif ( $input == 'AM Snow Showers' )
		{
			$data = trans('vormittags Schneeschauer');
		}
		elseif ( $input == 'AM Snow' )
		{
			$data = trans('vormittags Schnee');
		}
		elseif ( $input == 'AM Thundershowers' )
		{
			$data = trans('vorm. Gewitterschauer');
		}
		elseif ( $input == 'Blowing Snow' )
		{
			$data = trans('Schneetreiben');
		}
		elseif ( $input == 'Clear' )
		{
			$data = trans('Klar');
		}
		elseif ( $input == 'Clear/Windy' )
		{
			$data = trans('Klar/Windig');
		}
		elseif ( $input == 'Clouds Early/Clearing Late' )
		{
			$data = trans('früh Wolken/später klar');
		}
		elseif ( $input == 'Cloudy' )
		{
			$data = trans('Bewölkt');
		}
		elseif ( $input == 'Cloudy/Wind' )
		{
			$data = trans('Bewölkt/Wind');
		}
		elseif ( $input == 'Cloudy/Windy' )
		{
			$data = trans('Wolkig/Windig');
		}
		elseif ( $input == 'Drifting Snow' )
		{
			$data = trans('Schneetreiben');
		}
		elseif ( $input == 'Drifting Snow/Windy' )
		{
			$data = trans('Schneetreiben/Windig');
		}
		elseif ( $input == 'Drizzle Early' )
		{
			$data = trans('früh Nieselregen');
		}
		elseif ( $input == 'Drizzle Late' )
		{
			$data = trans('später Nieselregen');
		}
		elseif ( $input == 'Drizzle' )
		{
			$data = trans('Nieselregen');
		}
		elseif ( $input == 'Drizzle/Fog' )
		{
			$data = trans('Nieselregen/Nebel');
		}
		elseif ( $input == 'Drizzle/Wind' )
		{
			$data = trans('Nieselregen/Wind');
		}
		elseif ( $input == 'Drizzle/Windy' )
		{
			$data = trans('Nieselregen/Windig');
		}
		elseif ( $input == 'Fair' )
		{
			$data = trans('Heiter');
		}
		elseif ( $input == 'Fair/Windy' )
		{
			$data = trans('Heiter/Windig');
		}
		elseif ( $input == 'Few Showers' )
		{
			$data = trans('vereinzelte Schauer');
		}
		elseif ( $input == 'Few Showers/Wind' )
		{
			$data = trans('vereinzelte Schauer / Wind');
		}
		elseif ( $input == 'Few Snow Showers' )
		{
			$data = trans('vereinzelt Schneeschauer');
		}
		elseif ( $input == 'Fog Early/Clouds Late' )
		{
			$data = trans('früh Nebel, später Wolken');
		}
		elseif ( $input == 'Fog Late' )
		{
			$data = trans('später neblig');
		}
		elseif ( $input == 'Fog' )
		{
			$data = trans('Nebel');
		}
		elseif ( $input == 'Fog/Windy' )
		{
			$data = trans('Nebel/Windig');
		}
		elseif ( $input == 'Foggy' )
		{
			$data = trans('neblig');
		}
		elseif ( $input == 'Freezing Drizzle' )
		{
			$data = trans('gefrierender Nieselregen');
		}
		elseif ( $input == 'Freezing Drizzle/Windy' )
		{
			$data = trans('gefrierender Nieselregen / Windig');
		}
		elseif ( $input == 'Freezing Rain' )
		{
			$data = trans('gefrierender Regen');
		}
		elseif ( $input == 'Haze' )
		{
			$data = trans('Dunst');
		}
		elseif ( $input == 'Heavy Drizzle' )
		{
			$data = trans('starker Nieselregen');
		}
		elseif ( $input == 'Heavy Rain Shower' )
		{
			$data = trans('Starker Regenschauer');
		}
		elseif ( $input == 'Heavy Rain' )
		{
			$data = trans('Starker Regen');
		}
		elseif ( $input == 'Heavy Rain/Wind' )
		{
			$data = trans('starker Regen / Wind');
		}
		elseif ( $input == 'Heavy Rain/Windy' )
		{
			$data = trans('Starker Regen / Windig');
		}
		elseif ( $input == 'Heavy Snow Shower' )
		{
			$data = trans('Starker Schneeschauer');
		}
		elseif ( $input == 'Heavy Snow' )
		{
			$data = trans('Starker Schneefall');
		}
		elseif ( $input == 'Heavy Snow/Wind' )
		{
			$data = trans('Starker Schneefall / Wind');
		}
		elseif ( $input == 'Heavy Thunderstorm' )
		{
			$data = trans('Schweres Gewitter');
		}
		elseif ( $input == 'Heavy Thunderstorm/Windy' )
		{
			$data = trans('Schweres Gewitter / Windig');
		}
		elseif ( $input == 'Ice Crystals' )
		{
			$data = trans('Eiskristalle');
		}
		elseif ( $input == 'Ice Late' )
		{
			$data = trans('später Eis');
		}
		elseif ( $input == 'Isolated T-storms' )
		{
			$data = trans('Vereinzelte Gewitter');
		}
		elseif ( $input == 'Isolated Thunderstorms' )
		{
			$data = trans('Vereinzelte Gewitter');
		}
		elseif ( $input == 'Light Drizzle' )
		{
			$data = trans('Leichter Nieselregen');
		}
		elseif ( $input == 'Light Freezing Drizzle' )
		{
			$data = trans('Leichter gefrierender Nieselregen');
		}
		elseif ( $input == 'Light Freezing Rain' )
		{
			$data = trans('Leichter gefrierender Regen');
		}
		elseif ( $input == 'Light Freezing Rain/Fog' )
		{
			$data = trans('Leichter gefrierender Regen / Nebel');
		}
		elseif ( $input == 'Light Rain Early' )
		{
			$data = trans('anfangs leichter Regen');
		}
		elseif ( $input == 'Light Rain' )
		{
			$data = trans('Leichter Regen');
		}
		elseif ( $input == 'Light Rain Late' )
		{
			$data = trans('später leichter Regen');
		}
		elseif ( $input == 'Light Rain Shower' )
		{
			$data = trans('Leichter Regenschauer');
		}
		elseif ( $input == 'Light Rain Shower/Fog' )
		{
			$data = trans('Leichter Regenschauer / Nebel');
		}
		elseif ( $input == 'Light Rain Shower/Windy' )
		{
			$data = trans('Leichter Regenschauer / windig');
		}
		elseif ( $input == 'Light Rain with Thunder' )
		{
			$data = trans('Leichter Regen mit Gewitter');
		}
		elseif ( $input == 'Light Rain/Fog' )
		{
			$data = trans('Leichter Regen / Nebel');
		}
		elseif ( $input == 'Light Rain/Freezing Rain' )
		{
			$data = trans('Leichter Regen/Gefrierender Regen');
		}
		elseif ( $input == 'Light Rain/Wind Early' )
		{
			$data = trans('früh leichter Regen / Wind');
		}
		elseif ( $input == 'Light Rain/Wind Late' )
		{
			$data = trans('später leichter Regen / Wind');
		}
		elseif ( $input == 'Light Rain/Wind' )
		{
			$data = trans('leichter Regen / Wind');
		}
		elseif ( $input == 'Light Rain/Windy' )
		{
			$data = trans('Leichter Regen / Windig');
		}
		elseif ( $input == 'Light Sleet' )
		{
			$data = trans('Leichter Schneeregen');
		}
		elseif ( $input == 'Light Snow Early' )
		{
			$data = trans('früher leichter Schneefall');
		}
		elseif ( $input == 'Light Snow Grains' )
		{
			$data = trans('Leichter Schneegriesel');
		}
		elseif ( $input == 'Light Snow Late' )
		{
			$data = trans('später leichter Schneefall');
		}
		elseif ( $input == 'Light Snow Shower' )
		{
			$data = trans('Leichter Schneeschauer');
		}
		elseif ( $input == 'Light Snow Shower/Fog' )
		{
			$data = trans('Leichter Schneeschauer / Nebel');
		}
		elseif ( $input == 'Light Snow with Thunder' )
		{
			$data = trans('Leichter Schneefall mit Gewitter');
		}
		elseif ( $input == 'Light Snow' )
		{
			$data = trans('Leichter Schneefall');
		}
		elseif ( $input == 'Light Snow/Fog' )
		{
			$data = trans('Leichter Schneefall / Nebel');
		}
		elseif ( $input == 'Light Snow/Freezing Rain' )
		{
			$data = trans('Leichter Schneefall/Gefrierender Regen');
		}
		elseif ( $input == 'Light Snow/Wind' )
		{
			$data = trans('Leichter Schneefall / Wind');
		}
		elseif ( $input == 'Light Snow/Windy' )
		{
			$data = trans('Leichter Schneeschauer / Windig');
		}
		elseif ( $input == 'Light Snow/Windy/Fog' )
		{
			$data = trans('Leichter Schneefall / Windig / Nebel');
		}
		elseif ( $input == 'Mist' )
		{
			$data = trans('Nebel');
		}
		elseif ( $input == 'Mostly Clear' )
		{
			$data = trans('überwiegend Klar');
		}
		elseif ( $input == 'Mostly Cloudy' )
		{
			$data = trans('Überwiegend bewölkt');
		}
		elseif ( $input == 'Mostly Cloudy/Wind' )
		{
			$data = trans('meist bewölkt / Wind');
		}
		elseif ( $input == 'Mostly sunny' )
		{
			$data = trans('Überwiegend sonnig');
		}
		elseif ( $input == 'Partial Fog' )
		{
			$data = trans('teilweise Nebel');
		}
		elseif ( $input == 'Partly Cloudy' )
		{
			$data = trans('Teilweise bewölkt');
		}
		elseif ( $input == 'Partly Cloudy/Wind' )
		{
			$data = trans('teilweise bewölkt / Wind');
		}
		elseif ( $input == 'Patches of Fog' )
		{
			$data = trans('Nebelfelder');
		}
		elseif ( $input == 'Patches of Fog/Windy' )
		{
			$data = trans('Nebelfelder/Windig');
		}
		elseif ( $input == 'PM Drizzle' )
		{
			$data = trans('nachm. Nieselregen');
		}
		elseif ( $input == 'PM Fog' )
		{
			$data = trans('nachmittags Nebel');
		}
		elseif ( $input == 'PM Light Snow' )
		{
			$data = trans('nachmittags leichter Schneefall');
		}
		elseif ( $input == 'PM Light Rain' )
		{
			$data = trans('nachmittags leichter Regen');
		}
		elseif ( $input == 'PM Light Rain/Wind' )
		{
			$data = trans('nachm. leichter Regen / Wind');
		}
		elseif ( $input == 'PM Light Snow/Wind' )
		{
			$data = trans('nachm. leichter Schneefall / Wind');
		}
		elseif ( $input == 'PM Rain' )
		{
			$data = trans('nachmittags Regen');
		}
		elseif ( $input == 'PM Rain/Snow Showers' )
		{
			$data = trans('nachmittags Regen / Schneeschauer');
		}
		elseif ( $input == 'PM Rain/Snow' )
		{
			$data = trans('nachmittags Regen / Schnee');
		}
		elseif ( $input == 'PM Rain/Wind' )
		{
			$data = trans('nachm. Regen / Wind');
		}
		elseif ( $input == 'PM Showers' )
		{
			$data = trans('nachmittags Schauer');
		}
		elseif ( $input == 'PM Showers/Wind' )
		{
			$data = trans('nachmittags Schauer / Wind');
		}
		elseif ( $input == 'PM Snow Showers' )
		{
			$data = trans('nachmittags Schneeschauer');
		}
		elseif ( $input == 'PM Snow Showers/Wind' )
		{
			$data = trans('nachm. Schneeschauer / Wind');
		}
		elseif ( $input == 'PM Snow' )
		{
			$data = trans('nachm. Schnee');
		}
		elseif ( $input == 'PM T-storms' )
		{
			$data = trans('nachmittags Gewitter');
		}
		elseif ( $input == 'PM Thundershowers' )
		{
			$data = trans('nachmittags Gewitterschauer');
		}
		elseif ( $input == 'PM Thunderstorms' )
		{
			$data = trans('nachm. Gewitter');
		}
		elseif ( $input == 'Rain and Snow' )
		{
			$data = trans('Schneeregen');
		}
		elseif ( $input == 'Rain and Snow/Windy' )
		{
			$data = trans('Regen und Schnee / Windig');
		}
		elseif ( $input == 'Rain/Snow Showers/Wind' )
		{
			$data = trans('Regen/Schneeschauer/Wind');
		}
		elseif ( $input == 'Rain Early' )
		{
			$data = trans('früh Regen');
		}
		elseif ( $input == 'Rain Late' )
		{
			$data = trans('später Regen');
		}
		elseif ( $input == 'Rain Shower' )
		{
			$data = trans('Regenschauer');
		}
		elseif ( $input == 'Rain Shower/Windy' )
		{
			$data = trans('Regenschauer/Windig');
		}
		elseif ( $input == 'Rain to Snow' )
		{
			$data = trans('Regen, in Schnee übergehend');
		}
		elseif ( $input == 'Rain' )
		{
			$data = trans('Regen');
		}
		elseif ( $input == 'Rain/Snow Early' )
		{
			$data = trans('früh Regen / Schnee');
		}
		elseif ( $input == 'Rain/Snow Late' )
		{
			$data = trans('später Regen / Schnee');
		}
		elseif ( $input == 'Rain/Snow Showers Early' )
		{
			$data = trans('früh Regen-/Schneeschauer');
		}
		elseif ( $input == 'Rain/Snow Showers Late' )
		{
			$data = trans('später Regen-/Schneeschnauer');
		}
		elseif ( $input == 'Rain/Snow Showers' )
		{
			$data = trans('Regen/Schneeschauer');
		}
		elseif ( $input == 'Rain/Snow' )
		{
			$data = trans('Regen/Schnee');
		}
		elseif ( $input == 'Rain/Snow/Wind' )
		{
			$data = trans('Regen/Schnee/Wind');
		}
		elseif ( $input == 'Rain/Thunder' )
		{
			$data = trans('Regen/Gewitter');
		}
		elseif ( $input == 'Rain/Wind Early' )
		{
			$data = trans('früh Regen / Wind');
		}
		elseif ( $input == 'Rain/Wind Late' )
		{
			$data = trans('später Regen / Wind');
		}
		elseif ( $input == 'Rain/Wind' )
		{
			$data = trans('Regen/Wind');
		}
		elseif ( $input == 'Rain/Windy' )
		{
			$data = trans('Regen/Windig');
		}
		elseif ( $input == 'Scattered Showers' )
		{
			$data = trans('vereinzelte Schauer');
		}
		elseif ( $input == 'Scattered Showers/Wind' )
		{
			$data = trans('vereinzelte Schauer / Wind');
		}
		elseif ( $input == 'Scattered Snow Showers' )
		{
			$data = trans('vereinzelte Schneeschauer');
		}
		elseif ( $input == 'Scattered Snow Showers/Wind' )
		{
			$data = trans('vereinzelte Schneeschauer / Wind');
		}
		elseif ( $input == 'Scattered T-storms' )
		{
			$data = trans('vereinzelte Gewitter');
		}
		elseif ( $input == 'Scattered Thunderstorms' )
		{
			$data = trans('vereinzelte Gewitter');
		}
		elseif ( $input == 'Shallow Fog' )
		{
			$data = trans('flacher Nebel');
		}
		elseif ( $input == 'Showers' )
		{
			$data = trans('Schauer');
		}
		elseif ( $input == 'Showers Early' )
		{
			$data = trans('früh Schauer');
		}
		elseif ( $input == 'Showers Late' )
		{
			$data = trans('später Schauer');
		}
		elseif ( $input == 'Showers in the Vicinity' )
		{
			$data = trans('Regenfälle in der Nähe');
		}
		elseif ( $input == 'Showers/Wind' )
		{
			$data = trans('Schauer/Wind');
		}
		elseif ( $input == 'Sleet and Freezing Rain' )
		{
			$data = trans('Schneeregen und gefrierender Regen');
		}
		elseif ( $input == 'Sleet/Windy' )
		{
			$data = trans('Schneeregen/Windig');
		}
		elseif ( $input == 'Snow Grains' )
		{
			$data = trans('Schneegriesel');
		}
		elseif ( $input == 'Snow Late' )
		{
			$data = trans('später Schnee');
		}
		elseif ( $input == 'Snow Shower' )
		{
			$data = trans('Schneeschauer');
		}
		elseif ( $input == 'Snow Showers Early' )
		{
			$data = trans('früh Schneeschauer');
		}
		elseif ( $input == 'Snow Showers Late' )
		{
			$data = trans('später Schneeschauer');
		}
		elseif ( $input == 'Snow Showers' )
		{
			$data = trans('Schneeschauer');
		}
		elseif ( $input == 'Snow Showers/Wind' )
		{
			$data = trans('Schneeschauer/Wind');
		}
		elseif ( $input == 'Snow to Rain' )
		{
			$data = trans('Schneeregen');
		}
		elseif ( $input == 'Snow' )
		{
			$data = trans('Schneefall');
		}
		elseif ( $input == 'Snow/Wind' )
		{
			$data = trans('Schneefall/Wind');
		}
		elseif ( $input == 'Snow/Windy' )
		{
			$data = trans('Schnee/Windig');
		}
		elseif ( $input == 'Squalls' )
		{
			$data = trans('Böen');
		}
		elseif ( $input == 'Sunny' )
		{
			$data = trans('Sonnig');
		}
		elseif ( $input == 'Sunny/Wind' )
		{
			$data = trans('Sonnig/Wind');
		}
		elseif ( $input == 'Sunny/Windy' )
		{
			$data = trans('Sonnig/Windig');
		}
		elseif ( $input == 'T-showers' )
		{
			$data = trans('Gewitterschauer');
		}
		elseif ( $input == 'Thunder in the Vicinity' )
		{
			$data = trans('Gewitter in der Umgebung');
		}
		elseif ( $input == 'Thunder' )
		{
			$data = trans('Gewitter');
		}
		elseif ( $input == 'Thundershowers Early' )
		{
			$data = trans('früh Gewitterschauer');
		}
		elseif ( $input == 'Thundershowers' )
		{
			$data = trans('Gewitterschauer');
		}
		elseif ( $input == 'Thunderstorm' )
		{
			$data = trans('Gewitter');
		}
		elseif ( $input == 'Thunderstorm/Windy' )
		{
			$data = trans('Gewitter/Windig');
		}
		elseif ( $input == 'Thunderstorms Early' )
		{
			$data = trans('früh Gewitter');
		}
		elseif ( $input == 'Thunderstorms Late' )
		{
			$data = trans('später Gewitter');
		}
		elseif ( $input == 'Thunderstorms' )
		{
			$data = trans('Gewitter');
		}
		elseif ( $input == 'Unknown Precipitation' )
		{
			$data = trans('Niederschlag');
		}
		elseif ( $input == 'Unknown' )
		{
			$data = trans('unbekannt');
		}
		elseif ( $input == 'Wintry Mix' )
		{
			$data = trans('Winterlicher Mix');
		}
		else
		{
			$data = $input;
		}

		return $data;
	}

	private static function convert ( $val, $from, $to )
	{

		if ( $from == $to )
		{
			return $val;
		}
		if ( $to == 'c' )
		{
			return round(($val - 32) * (5 / 9));
		}
		if ( $to == 'f' )
		{
			return round(($val * (5 / 9)) + 32);
		}
	}

}

?>