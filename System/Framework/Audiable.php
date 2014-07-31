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
 * @package
 * @version      3.0.0 Beta
 * @category
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Audiable.php
 */

class Audiable
{

	private $text;

	private $maxStrLen = 100;

	private $texts = array ();

	private $lang;

	private $audioHeader = array ();


	private $binaryTable = array();

    /**
     * @param $text
     * @param null $lang
     */
    public function speak ( &$text, $lang = null )
	{

		if ( $lang === null )
		{
			$lang = CONTENT_TRANS;
		}

		$this->binaryTable = array();
		for($i = 0; $i < 256; $i++)
		{
			$this->binaryTable[chr($i)] = sprintf('%08b', $i);
		}

		$this->lang = $lang;
		$text       = str_replace(array ( "\n", "\r", "\t" ), ' ', str_replace('</', ' </', $text));
		$text       = preg_replace('#\s{1,}#', ' ', trim(strip_tags($text)));


		if ( strlen($text) > $this->maxStrLen )
		{
			$this->text = $text;


			// Get get separated files contents and marge them into one
			$audio = '';
			$texts = $this->strSplitWordFriendly($this->text, $this->maxStrLen);


			#print_r($texts); exit;


			foreach ( $texts as $txt )
			{
				if ( $txt )
				{
					$_audio = $this->getAudio($txt);


					$audio .= $this->stripTags($_audio);
				}
			}
			unset( $words, $texts );


			if ( is_array($this->audioHeader) )
			{
				foreach ( $this->audioHeader as $k => $h )
				{

					if ( $k )
					{
						@header($k . ':' . $h);
					}
				}
			}


			echo $audio;
			exit;


		}
		else
		{
			if ( trim($text) )
			{
				$this->text = trim($text);
				$this->getSpeak();
			}
		}
	}

	/**
	 * Smart str_split, does not split if in a middle of a word... Goes to next file then.
	 * If words are bigger then $size it will use generic str_split.
	 *
	 * Created because google has 100chars limit. This makes many files, and does nice splitting when it comes to words and limit of chars.
	 */
	private function strSplitWordFriendly ( $str, $size )
	{

		$str    = trim(strip_tags($str));
		$length = strlen($str);
		$ex     = explode(' ', $str);
		$op     = array ();

		$newarr = '';
		foreach ( $ex as $word )
		{
			if ( strlen(' ' . $word) > $size )
			{
				$op[ ]  = $newarr;
				$newarr = '';
				$splitA = str_split($word, $size);
				$op     = array_merge($op, $splitA);

			}
			elseif ( strlen($newarr . ' ' . $word) <= $size && !( ( $size - strlen($newarr) ) > $size * 0.15 && strstr($newarr, '.') ) )
			{
				$newarr .= ' ' . $word;
			}
			else
			{
				$op[ ]  = $newarr;
				$newarr = '';
				$newarr = $word;
			}

		}
		if ( $newarr )
		{
			$op[ ] = $newarr;
		}

		return $op;
	}

    /**
     * @param $txt
     * @return mixed
     */
    private function getAudio ( $txt )
	{

		$remote = new Request();
		$remote->__set('useragent', $_SERVER[ 'HTTP_USER_AGENT' ]);

		if ( $remote->getUrlEncoded('http://translate.google.com/translate_tts', array (
		                                                                               'tl' => $this->lang,
		                                                                               'q'  => $txt
		                                                                         ))
		)
		{

			$this->audioHeader = $remote->__get('headers');

			return $remote->__get('response');
		}
		else
		{
			die( 'Invalid Audiable! ' . $remote->__get('error') );
			exit;
		}


	}

	/** Function to remove the ID3 tags from mp3 files
	 *
	 * @param     String $contents - File contents
	 * @return     String
	 */
	private function stripTags ( $contents )
	{

		// Remove start
		$start = $this->getStart($contents);

		if ( $start === false )
		{
			return false;
		}
		else
		{
			return substr($contents, $start);
		}

		// Remove end tag
		if ( $this->getEnd($contents) !== false )
		{
			return substr($contents, 0, ( strlen($contents) - 129 ));
		}
	}

	/** Function to find the beginning of the mp3 file
	 *
	 * @param     String $contents - File contents
	 * @return     Integer
	 */
	private function getStart ( $contents )
	{

		$currentStrPos = -1;
		while ( true )
		{
			$currentStrPos = strpos($contents, chr(255), $currentStrPos + 1);
			if ( $currentStrPos === false )
			{
				return 0;
			}

			$str    = substr($contents, $currentStrPos, 4);
			$strlen = strlen($str);
			$parts  = array ();
			for ( $i = 0; $i < $strlen; $i++ )
			{
				$parts[ ] = $this->decbinFill(ord($str[ $i ]), 8);
			}

			if ( $this->doFrameStuff($parts) === false )
			{
				continue;
			}

			return $currentStrPos;
		}

        return 0;
	}

    /**
     * @param string $dec
     * @param int $length
     * @return string
     */
    private function decbinFill ( $dec, $length = 0 )
	{

		$str   = decbin($dec);
		$nulls = $length - strlen($str);
		if ( $nulls > 0 )
		{
			for ( $i = 0; $i < $nulls; $i++ )
			{
				$str = '0' . $str;
			}
		}

		return $str;
	}


    /**
     * @param array $parts
     * @return array|bool
     */
    private function doFrameStuff ( $parts )
	{

		//Get Audio Version
		$seconds = 0;
		$errors  = array ();
		switch ( substr($parts[ 1 ], 3, 2) )
		{
			case '01':
				$errors[ ] = 'Reserved audio version';
				break;
			case '00':
				$audio = 2.5;
				break;
			case '10':
				$audio = 2;
				break;
			case '11':
				$audio = 1;
				break;
		}
		//Get Layer
		switch ( substr($parts[ 1 ], 5, 2) )
		{
			case '01':
				$layer = 3;
				break;
			case '00':
				$errors[ ] = 'Reserved layer';
				break;
			case '10':
				$layer = 2;
				break;
			case '11':
				$layer = 1;
				break;
		}
		//Get Bitrate
		$bitFlag  = substr($parts[ 2 ], 0, 4);
		$bitArray = array (
			'0000' => array ( 0, 0, 0, 0, 0 ),
			'0001' => array ( 32, 32, 32, 32, 8 ),
			'0010' => array ( 64, 48, 40, 48, 16 ),
			'0011' => array ( 96, 56, 48, 56, 24 ),
			'0100' => array ( 128, 64, 56, 64, 32 ),
			'0101' => array ( 160, 80, 64, 80, 40 ),
			'0110' => array ( 192, 96, 80, 96, 48 ),
			'0111' => array ( 224, 112, 96, 112, 56 ),
			'1000' => array ( 256, 128, 112, 128, 64 ),
			'1001' => array ( 288, 160, 128, 144, 80 ),
			'1010' => array ( 320, 192, 160, 160, 96 ),
			'1011' => array ( 352, 224, 192, 176, 112 ),
			'1100' => array ( 384, 256, 224, 192, 128 ),
			'1101' => array ( 416, 320, 256, 224, 144 ),
			'1110' => array ( 448, 384, 320, 256, 160 ),
			'1111' => array ( -1, -1, -1, -1, -1 )
		);
		$bitPart  = $bitArray[ $bitFlag ];
		$bitArrayNumber = 0;
		if ( $audio == 1 )
		{
			switch ( $layer )
			{
				case 1:
					$bitArrayNumber = 0;
					break;
				case 2:
					$bitArrayNumber = 1;
					break;
				case 3:
					$bitArrayNumber = 2;
					break;
			}
		}
		else
		{
			switch ( $layer )
			{
				case 1:
					$bitArrayNumber = 3;
					break;
				case 2:
					$bitArrayNumber = 4;
					break;
				case 3:
					$bitArrayNumber = 4;
					break;
			}
		}

		$bitRate = $bitPart[ $bitArrayNumber ];

		if ( $bitRate <= 0 )
		{
			return false;
		}
		//Get Frequency
		$frequencies = array (
			1   => array (
				'00' => 44100,
				'01' => 48000,
				'10' => 32000,
				'11' => 'reserved'
			),
			2   => array (
				'00' => 44100,
				'01' => 48000,
				'10' => 32000,
				'11' => 'reserved'
			),
			'2.5' => array (
				'00' => 44100,
				'01' => 48000,
				'10' => 32000,
				'11' => 'reserved'
			)
		);
		$freq        = $frequencies[ $audio ][ substr($parts[ 2 ], 4, 2) ];
		//IsPadded?
		$padding = substr($parts[ 2 ], 6, 1);
		if ( $layer == 3 || $layer == 2 )
		{
			//FrameLengthInBytes = 144 * BitRate / SampleRate + Padding
			$frameLength = (144 * $bitRate * 1000) / $freq + $padding;
		}
		$frameLength = floor($frameLength);
		if ( $frameLength == 0 )
		{
			return false;
		}
		$seconds += ($frameLength * 8) / ( $bitRate * 1000 );

		return array ( $frameLength, $seconds );
		//Calculate next when next frame starts.
		//Capture next frame.
	}

	/** Function to find the end of the mp3 file
	 *
	 * @param     String $contents - File contents
	 * @return     Integer
	 */
	private function getEnd ( $contents )
	{

		$c = substr($contents, ( strlen($contents) - 128 ));
		if ( strtoupper(substr($c, 0, 3)) === 'TAG' )
		{
			return $c;
		}
		else
		{
			return false;
		}
	}

    /**
     *
     */
    private function getSpeak ()
	{

		$remote = new Request();
		$remote->__set('useragent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.4410) Gecko/20110902 Firefox/3.6');


		$textlength = utf8_strlen($this->text);
		$wordCount  = str_word_count($this->text);

		usleep(25000);


		if ( $remote->getUrlEncoded('http://translate.google.com/translate_tts/', array (
		                                                                                'tl'      => $this->lang,
		                                                                                'q'       => $this->text,
		                                                                                'total'   => $wordCount,
		                                                                                'idx'     => 0,
		                                                                                'textlen' => $textlength
		                                                                          ))
		)
		{


			$headers = $remote->__get('headers');

			//	print_r($headers);exit;

			if ( is_array($headers) )
			{
				foreach ( $headers as $k => $h )
				{

					if ( $k )
					{
						@header($k . ':' . $h);
					}
				}
			}

			echo $remote->__get('response');

			exit;
		}
		else
		{
			die( 'Invalid Audiable! ' . $remote->__get('error') );

			exit;
		}

	}
}