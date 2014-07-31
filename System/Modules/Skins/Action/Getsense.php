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
 * @package      Skins
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Getsense.php
 */
class Skins_Action_Getsense extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$defines = $this->getAllTagDefines();
        $etag = md5( serialize($defines) ); // ETag generieren (md5-Hash)


        if ( isset( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) && str_replace( '"', '', stripslashes( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) ) == $etag )
        {
            // Datei/ETag im Browser Cache vorhanden?
            header('Not Modified',true,304); // entsprechenden Header senden => Datei wird nicht geladen

            exit();
        }









		$o = ob_get_clean();
        $mtimestr = gmdate( "D, d M Y H:i:s", TIMESTAMP ) . " GMT";
		header('Content-Type: application/javascript');
        header( 'Cache-Control: public, max-age=5184000' );
        header( 'ETag: "' . $etag . '"' );
        header( 'Pragma: public' );
        header( 'Last-Modified: '.$mtimestr );
        header( "Vary: Accept-Encoding" ); // Handle proxies
        header( 'Expires:'. gmdate( "D, d M Y H:i:s", TIMESTAMP + 5184000 ) . " GMT" );




		ob_start();
		echo '// --------------- All CP-Tags' . "\n";
		echo 'top.dcms_tags = new Array;' . "\n";

		$singletags = array ();
		$jsonarray  = array ();
		$allTags    = array ();


		foreach ( $defines as $def )
		{
			if ( isset($def[ 'tagname' ]) )
			{
				$allTags[ ] = $def[ 'tagname' ];

				echo sprintf("\n" . 'top.dcms_tags[\'cp:%s\'] = {', $def[ 'tagname' ]);

				echo sprintf('\'desc\': \'%s\',', addslashes((isset($def[ 'description' ]) ?
					htmlspecialchars(str_replace('&gt;', '>', str_replace('&lt;', '<', $def[ 'description' ]))) : '')));



				echo sprintf('\'singleTag\': %s,', (isset($def[ 'isSingleTag' ]) && $def[ 'isSingleTag' ] ? 'true' :
					'false'));


				if ( !empty($def[ 'isSingleTag' ]) )
				{
					$singletags[ ] = 'cp:' . $def[ 'tagname' ];
				}

				$attr    = array ();
				$reqattr = array ();

				if ( is_array($def[ 'attributes' ]) )
				{
					foreach ( $def[ 'attributes' ] as $attname => $values )
					{
						$vals = array ();
						if ( $attname == '__custom' )
						{
							continue;
						}

						if ( isset($values[ 'values' ]) && is_string($values[ 'values' ]) && !empty($values[ 'values' ]) )
						{
							$_v = explode("\n", $values[ 'values' ]);
							foreach ( $_v as $str )
							{
								$v = explode('|', $str);

								if ( !empty($v[ 0 ]) && $v[ 0 ] != '-' )
								{
									$vals[ $v[ 0 ] ] = 3;
								}
							}
						}
						elseif (isset($values[ 'values' ]) && is_array($values[ 'values' ])) {
							foreach ( $values[ 'values' ] as $str )
							{
								if ( !empty($str) && $str != '-' )
								{
									$vals[ $str ] = 3;
								}
							}
						}
						elseif ( isset($values[ 'default' ]) && !empty($values[ 'default' ]) && $values[ 'default' ] != '-' )
						{
							$vals[ $values[ 'default' ] ] = 3;
						}

						if ( !count($vals) )
						{
							$vals = 2;
						}

						$attr[ $attname ] = $vals;

						if ( isset($values[ 'required' ]) && $values[ 'required' ] )
						{
							$reqattr[ ] = $attname;
						}

						$vals = null;
					}
				}


				echo sprintf('\'requiredAttributes\': \'%s\',', implode(',', $reqattr));

				echo '\'attributes\':';

				if ( !count($attr) )
				{
					echo '1';
				}
				else
				{
					echo '{';

					$attributes = array ();

					foreach ( $attr as $key => $data )
					{
						$attributeString = sprintf('\'%s\':', $key);

						if ( is_array($data) )
						{
							$attributeString .= '{';

							$options = array ();
							foreach ( $data as $k => $v )
							{
								if ( $k == '__required' )
								{
									continue;
								}

								$options[ ] = sprintf('\'%s\': 3', $k);
							}
							$attributeString .= implode(',', $options);
							$attributeString .= '}';
						}
						else
						{
							$attributeString .= '2';
						}
						$attributes[ ] = $attributeString;
					}

					echo implode(',', $attributes);

					echo '}';
				}


				echo '};';
			}
		}


		// echo ' top.dcms_tags = dcmsTags;' . "\n";
		echo ' top.dcms_selfclosetags = \'' . implode(',', $singletags) . "';";


		echo "\n" . 'top.tagGroups = new Array;top.tagLabels = new Array;';
		echo "\n" . sprintf('top.tagGroups[\'alltags\'] = new Array(\'%s\');', implode('\',\'', $allTags));
		echo "\n" . sprintf('top.tagLabels[\'alltags\'] = \'%s\';', trans('Alle cp: Tags'));


		$grouped = $this->getTagGroups();
		if ( $grouped !== false )
		{
			foreach ( $grouped as $k => $v )
			{
				echo "\n" . sprintf('top.tagGroups[\'%s\'] = new Array(\'%s\');', $k, implode('\',\'', explode(',', $v[ 1 ])));
				echo "\n" . sprintf('top.tagLabels[\'%s\'] = \'%s\';', $k, addcslashes($v[ 0 ], "'"));
			}
		}
		ob_end_flush();
		exit;
	}

	/**
	 *
	 * @return array|bool
	 */
	private function getTagGroups ()
	{

		$tagGroups = null;
		include(DATA_PATH . 'system/tagwizard/grouptags.php');
		if ( is_array($tagGroups) )
		{
			return $tagGroups;
		}

		return false;
	}

	/**
	 * grab all CP:Tags
	 *
	 * @return array
	 */
	private function getAllTagDefines ()
	{

		$files = glob(DATA_PATH . 'system/tagwizard/tags/*.php');
		$data  = array ();
		foreach ( $files as $file )
		{
			$tagDefine = null;
			include($file);

			if ( is_array($tagDefine) )
			{
				$data[ ] = $tagDefine;
			}
		}

		return $data;
	}

	/**
	 * grab all functions
	 *
	 * @return array
	 */
	private function getAllFunctionDefines ()
	{

		$files = glob(DATA_PATH . 'system/tagwizard/function/*.php');
		$data  = array ();
		foreach ( $files as $file )
		{
			$tagDefine = null;
			include($file);

			if ( is_array($tagDefine) )
			{
				$data[ ] = $tagDefine;
			}
		}

		return $data;
	}

}
