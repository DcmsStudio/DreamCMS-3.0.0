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
 * @package      Fileman
 * @version      3.0.0 Beta
 * @category     Helper
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Fileman_Helper_Base extends Controller_Abstract
{

	/**
	 * @var
	 */
	protected $time;

	/**
	 * Driver id
	 * Must be started from letter and contains [a-z0-9]
	 * Used as part of volume id
	 *
	 * @var string
	 * */
	protected $driverId = 'a';

	/**
	 * Volume id - used as prefix for files hashes
	 *
	 * @var string
	 * */
	protected $id = '';

	/**
	 * Flag - volume "mounted" and available
	 *
	 * @var bool
	 * */
	protected $mounted = false;

	/**
	 * Root directory path
	 *
	 * @var string
	 */
	protected $root = '';

	/**
	 * Root basename | alias
	 *
	 * @var string
	 */
	protected $rootName = '';

	/**
	 * Default directory to open
	 *
	 * @var string
	 */
	protected $startPath = '';

	/**
	 * Base URL
	 *
	 * @var string
	 * */
	protected $URL = '';

	/**
	 * Thumbnails dir path
	 *
	 * @var string
	 * */
	protected $tmbPath = '';

	/**
	 * Is thumbnails dir writable
	 *
	 * @var bool
	 * */
	protected $tmbPathWritable = false;

	/**
	 * Thumbnails base URL
	 *
	 * @var string
	 * */
	protected $tmbURL = '';

	/**
	 * Thumbnails size in px
	 *
	 * @var int
	 * */
	protected $tmbSize = 48;

	/**
	 * Image manipulation lib name
	 * auto|imagick|mogtify|gd
	 *
	 * @var string
	 * */
	protected $imgLib = 'auto';

	/**
	 * Library to crypt files name
	 *
	 * @var string
	 * */
	protected $cryptLib = '';

	/**
	 * How many subdirs levels return for tree
	 *
	 * @var int
	 * */
	protected $treeDeep = 1;

	/**
	 * Errors from last failed action
	 *
	 * @var array
	 * */
	protected $error = array ();

	/**
	 * Today 24:00 timestamp
	 *
	 * @var int
	 * */
	protected $today = 0;

	/**
	 * Yesterday 24:00 timestamp
	 *
	 * @var int
	 * */
	protected $yesterday = 0;

	/**
	 * Defaults permissions
	 *
	 * @var array
	 * */
	protected $defaults = array (
		'read'   => true,
		'write'  => true,
		'locked' => false,
		'hidden' => false
	);

	/**
	 * Access control function/class
	 *
	 * @var mixed
	 * */
	protected $attributes = array ();

	/**
	 * Access control function/class
	 *
	 * @var mixed
	 * */
	protected $access = null;

	/**
	 * Mime types allowed to upload
	 *
	 * @var array
	 * */
	protected $uploadAllow = array ();

	/**
	 * Mime types denied to upload
	 *
	 * @var array
	 * */
	protected $uploadDeny = array ();

	/**
	 * Order to validate uploadAllow and uploadDeny
	 *
	 * @var array
	 * */
	protected $uploadOrder = array ();

	/**
	 * Mimetype detect method
	 *
	 * @var string
	 * */
	protected $mimeDetect = 'auto';

	/**
	 * Flag - mimetypes from externail file was loaded
	 *
	 * @var bool
	 */
	private static $mimetypesLoaded = false;

	/**
	 * Finfo object for mimeDetect == 'finfo'
	 *
	 * @var object
	 */
	protected $finfo = null;

	/**
	 * List of disabled client's commands
	 *
	 * @var array
	 */
	protected $diabled = array ();

	/**
	 * Directory separator - required by client
	 *
	 * @var string
	 * */
	protected $separator = '/';

	/**
	 * Mimetypes allowed to display
	 *
	 * @var array
	 * */
	protected $onlyMimes = array ();

	/**
	 * Store files moved or overwrited files info
	 *
	 * @var array
	 * */
	protected $removed = array ();

	/**
	 * Cache storage
	 *
	 * @var array
	 * */
	protected $cache = array ();

	/**
	 * Cache by folders
	 *
	 * @var array
	 * */
	protected $dirsCache = array ();

	/**
	 * mapping $_GET['cmd]/$_POST['cmd] to class methods
	 *
	 * @var array
	 * */
	protected $_commands = array (
		'open'      => '_open',
		'reload'    => '_reload',
		'mkdir'     => '_mkdir',
		'mkfile'    => '_mkfile',
		'rename'    => '_rename',
		'upload'    => '_upload',
		'paste'     => '_paste',
		'rm'        => '_rm',
		'delete'    => '_rm',
		'duplicate' => '_duplicate',
		'read'      => '_fread',
		'edit'      => '_edit',
		'archive'   => '_archive',
		'extract'   => '_extract',
		'resize'    => '_resize',
		'tmb'       => '_thumbnails',
		'ping'      => '_ping'
	);

	/**
	 * default extensions/mimetypes for mimeDetect == 'internal'
	 *
	 * @var array
	 */
	protected static $mimetypes = array (
		// applications
		'ai'      => 'application/postscript',
		'eps'     => 'application/postscript',
		'exe'     => 'application/x-executable',
		'doc'     => 'application/vnd.ms-word',
		'xls'     => 'application/vnd.ms-excel',
		'ppt'     => 'application/vnd.ms-powerpoint',
		'pps'     => 'application/vnd.ms-powerpoint',
		'pdf'     => 'application/pdf',
		'xml'     => 'application/xml',
		'swf'     => 'application/x-shockwave-flash',
		'torrent' => 'application/x-bittorrent',
		'jar'     => 'application/x-jar', // open office (finfo detect as application/zip)
		'odt'     => 'application/vnd.oasis.opendocument.text',
		'ott'     => 'application/vnd.oasis.opendocument.text-template',
		'oth'     => 'application/vnd.oasis.opendocument.text-web',
		'odm'     => 'application/vnd.oasis.opendocument.text-master',
		'odg'     => 'application/vnd.oasis.opendocument.graphics',
		'otg'     => 'application/vnd.oasis.opendocument.graphics-template',
		'odp'     => 'application/vnd.oasis.opendocument.presentation',
		'otp'     => 'application/vnd.oasis.opendocument.presentation-template',
		'ods'     => 'application/vnd.oasis.opendocument.spreadsheet',
		'ots'     => 'application/vnd.oasis.opendocument.spreadsheet-template',
		'odc'     => 'application/vnd.oasis.opendocument.chart',
		'odf'     => 'application/vnd.oasis.opendocument.formula',
		'odb'     => 'application/vnd.oasis.opendocument.database',
		'odi'     => 'application/vnd.oasis.opendocument.image',
		'oxt'     => 'application/vnd.openofficeorg.extension', // MS office 2007 (finfo detect as application/zip)
		'docx'    => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'docm'    => 'application/vnd.ms-word.document.macroEnabled.12',
		'dotx'    => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
		'dotm'    => 'application/vnd.ms-word.template.macroEnabled.12',
		'xlsx'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'xlsm'    => 'application/vnd.ms-excel.sheet.macroEnabled.12',
		'xltx'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
		'xltm'    => 'application/vnd.ms-excel.template.macroEnabled.12',
		'xlsb'    => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
		'xlam'    => 'application/vnd.ms-excel.addin.macroEnabled.12',
		'pptx'    => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'pptm'    => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
		'ppsx'    => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
		'ppsm'    => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
		'potx'    => 'application/vnd.openxmlformats-officedocument.presentationml.template',
		'potm'    => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
		'ppam'    => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
		'sldx'    => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
		'sldm'    => 'application/vnd.ms-powerpoint.slide.macroEnabled.12', // archives
		'gz'      => 'application/x-gzip',
		'tgz'     => 'application/x-gzip',
		'bz'      => 'application/x-bzip2',
		'bz2'     => 'application/x-bzip2',
		'tbz'     => 'application/x-bzip2',
		'zip'     => 'application/zip',
		'rar'     => 'application/x-rar',
		'tar'     => 'application/x-tar',
		'7z'      => 'application/x-7z-compressed', // texts
		'txt'     => 'text/plain',
		'php'     => 'text/x-php',
		'html'    => 'text/html',
		'htm'     => 'text/html',
		'js'      => 'text/javascript',
		'css'     => 'text/css',
		'rtf'     => 'text/rtf',
		'rtfd'    => 'text/rtfd',
		'py'      => 'text/x-python',
		'java'    => 'text/x-java-source',
		'rb'      => 'text/x-ruby',
		'sh'      => 'text/x-shellscript',
		'pl'      => 'text/x-perl',
		'xml'     => 'text/xml',
		'sql'     => 'text/x-sql',
		'c'       => 'text/x-csrc',
		'h'       => 'text/x-chdr',
		'cpp'     => 'text/x-c++src',
		'hh'      => 'text/x-c++hdr',
		'log'     => 'text/plain',
		'csv'     => 'text/x-comma-separated-values', // images
		'bmp'     => 'image/x-ms-bmp',
		'jpg'     => 'image/jpeg',
		'jpeg'    => 'image/jpeg',
		'gif'     => 'image/gif',
		'png'     => 'image/png',
		'tif'     => 'image/tiff',
		'tiff'    => 'image/tiff',
		'tga'     => 'image/x-targa',
		'psd'     => 'image/vnd.adobe.photoshop',
		'ai'      => 'image/vnd.adobe.photoshop',
		'xbm'     => 'image/xbm',
		'pxm'     => 'image/pxm', //audio
		'mp3'     => 'audio/mpeg',
		'mid'     => 'audio/midi',
		'ogg'     => 'audio/ogg',
		'oga'     => 'audio/ogg',
		'm4a'     => 'audio/x-m4a',
		'wav'     => 'audio/wav',
		'wma'     => 'audio/x-ms-wma', // video
		'avi'     => 'video/x-msvideo',
		'dv'      => 'video/x-dv',
		'mp4'     => 'video/mp4',
		'mpeg'    => 'video/mpeg',
		'mpg'     => 'video/mpeg',
		'mov'     => 'video/quicktime',
		'wm'      => 'video/x-ms-wmv',
		'flv'     => 'video/x-flv',
		'mkv'     => 'video/x-matroska',
		'webm'    => 'video/webm',
		'ogv'     => 'video/ogg',
		'ogm'     => 'video/ogg'
	);

	/**
	 * Object configuration
	 *
	 * @var array
	 * */
	protected $options = array (
		'fileMode'          => 0777,
		// new files mode
		'dirMode'           => 0755,
		// new folders mode
		'id'                => '',
		// root directory path
		'path'              => '',
		// open this path on initial request instead of root path
		'startPath'         => '',
		// how many subdirs levels return per request
		'treeDeep'          => 1,
		// root url, not set to disable sending URL to client (replacement for old "fileURL" option)
		'URL'               => PAGE_URL_PATH,
		// directory separator. required by client to show paths correctly
		'separator'         => DIRECTORY_SEPARATOR,
		// library to crypt/uncrypt files names (not implemented)
		'cryptLib'          => '',
		// how to detect files mimetypes. (auto/internal/finfo/mime_content_type)
		'mimeDetect'        => 'auto',
		// mime.types file path (for mimeDetect==internal)
		'mimefile'          => '',
		'tmbAtOnce'         => 10,
		// directory for thumbnails
		'tmbPath'           => '.tmb',
		// mode to create thumbnails dir
		'tmbPathMode'       => 0777,
		// thumbnails dir URL. Set it if store thumbnails outside root directory
		'tmbURL'            => PAGE_URL_PATH,
		// thumbnails size (px)
		'tmbSize'           => 48,
		'coverflowSize'     => 128,
		// thumbnails crop (true - crop, false - scale image to fit thumbnail size)
		'tmbCrop'           => false,
		// thumbnails background color (hex #rrggbb or 'transparent')
		'tmbBgColor'        => 'transparent',
		// image manipulations library
		'imgLib'            => 'auto',
		// on paste file - if true - old file will be replaced with new one, if false new file get name - original_name-number.ext
		'copyOverwrite'     => true,
		// if true - join new and old directories content on paste
		'copyJoin'          => true,
		// on upload - if true - old file will be replaced with new one, if false new file get name - original_name-number.ext
		'uploadOverwrite'   => true,
		// mimetypes allowed to upload
		'uploadAllow'       => array (
			'all'
		),
		// mimetypes not allowed to upload
		'uploadDeny'        => array (
			'application/x-bittorrent',
			'application/x-executable',
			'application/x-jar'
		),
		// order to proccess uploadAllow and uploadDeny options
		'uploadOrder'       => array (
			'deny',
			'allow'
		),
		// maximum upload file size. NOTE - this is size for every uploaded files
		'uploadMaxSize'     => 0,
		// files dates format
		'dateFormat'        => 'j M Y H:i',
		// files time format
		'timeFormat'        => 'H:i',
		// if true - every folder will be check for children folders, otherwise all folders will be marked as having subfolders
		'checkSubfolders'   => true,
		// allow to copy from this volume to other ones?
		'copyFrom'          => true,
		// allow to copy from other volumes to this one?
		'copyTo'            => true,
		// list of commands disabled on this root
		'disabled'          => array (),
		// regexp or function name to validate new file name
		'acceptedName'      => '/^[^\.].*/',
		//<-- DONT touch this! Use constructor options to overwrite it!
		// function/class method to control files permissions
		'accessControl'     => null,
		// some data required by access control
		'accessControlData' => null,
		// default permissions. not set hidden/locked here - take no effect
		'defaults'          => array (
			'read'  => true,
			'rm'    => true,
			'write' => true
		),
		'perms'             => array (),
		// files attributes
		'attributes'        => array (),
		// Allowed archive's mimetypes to create. Leave empty for all available types.
		'archiveMimes'      => array (),
		// Manual config for archivers. See example below. Leave empty for auto detect
		'archivers'         => array (),
		// required to fix bug on macos
		'utf8fix'           => false,
		// й ё Й Ё Ø Å
		'utf8patterns'      => array (
			"\u0438\u0306",
			"\u0435\u0308",
			"\u0418\u0306",
			"\u0415\u0308",
			"\u00d8A",
			"\u030a"
		),
		'utf8replace'       => array (
			"\u0439",
			"\u0451",
			"\u0419",
			"\u0401",
			"\u00d8",
			"\u00c5"
		)
	);

	/**
	 *
	 * @param $options
	 * @return Fileman_Helper_Base
	 */
	public function configure ( $options )
	{

		$options = array (
			'root' => array (
				'alias' => 'Home',
				'path'  => str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, substr(PAGE_PATH, 0, -1))
			)
		);

		if ( is_array($options) )
		{
			$this->options = array_merge($this->options, $options);
		}

		$this->root      = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, substr(PAGE_PATH, 0, -1));
		$this->aroot     = realpath($this->root);
		$this->rootAlias = 'Home';
		$this->rootAlias = empty( $this->options[ 'alias' ] ) ? ( $this->rootAlias ? $this->rootAlias : basename($this->root) ) : $this->options[ 'alias' ];


		$this->time      = $this->utime();
		$this->today     = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$this->yesterday = $this->today - 86400;


		$this->options = array_merge($this->options, $options);

		// default file attribute
		$this->defaults = array (
			'read'   => isset( $this->options[ 'defaults' ][ 'read' ] ) ? !!$this->options[ 'defaults' ][ 'read' ] : true,
			'write'  => isset( $this->options[ 'defaults' ][ 'write' ] ) ? !!$this->options[ 'defaults' ][ 'write' ] : true,
			'locked' => false,
			'hidden' => false
		);

		// root attributes
		$this->attributes[ ] = array (
			'pattern' => '~^' . preg_quote(DIRECTORY_SEPARATOR) . '$~',
			'locked'  => true,
			'hidden'  => false
		);

		// check some options is arrays
		$this->uploadAllow = isset( $this->options[ 'uploadAllow' ] ) && is_array($this->options[ 'uploadAllow' ]) ? $this->options[ 'uploadAllow' ] : array ();
		$this->uploadDeny  = isset( $this->options[ 'uploadDeny' ] ) && is_array($this->options[ 'uploadDeny' ]) ? $this->options[ 'uploadDeny' ] : array ();

		if ( is_string($this->options[ 'uploadOrder' ]) )
		{
			$parts             = explode(',', isset( $this->options[ 'uploadOrder' ] ) ? $this->options[ 'uploadOrder' ] : 'deny,allow');
			$this->uploadOrder = array (
				trim($parts[ 0 ]),
				trim($parts[ 1 ])
			);
		}
		else
		{
			// telephat_mode off
			$this->uploadOrder = $this->options[ 'uploadOrder' ];
		}


		if ( !empty( $this->options[ 'uploadMaxSize' ] ) )
		{
			$size = '' . $this->options[ 'uploadMaxSize' ];
			$unit = strtolower(substr($size, strlen($size) - 1));
			$n    = 1;
			switch ( $unit )
			{
				case 'k':
					$n = 1024;
					break;
				case 'm':
					$n = 1048576;
					break;
				case 'g':
					$n = 1073741824;
			}
			$this->uploadMaxSize = (int)$size * $n;
		}

		$this->disabled = isset( $this->options[ 'disabled' ] ) && is_array($this->options[ 'disabled' ]) ? $this->options[ 'disabled' ] : array ();


		$this->cryptLib   = $this->options[ 'cryptLib' ];
		$this->mimeDetect = $this->options[ 'mimeDetect' ];


		// find available mimetype detect method
		$type   = strtolower($this->options[ 'mimeDetect' ]);
		$type   = preg_match('/^(finfo|mime_content_type|internal|auto)$/i', $type) ? $type : 'auto';
		$regexp = '/text\/x\-(php|c\+\+)/';

		if ( ( $type == 'finfo' || $type == 'auto' ) && class_exists('finfo') )
		{
			$tmpFileInfo = @explode(';', @finfo_file(finfo_open(FILEINFO_MIME), __FILE__));
		}
		else
		{
			$tmpFileInfo = false;
		}

		if ( $tmpFileInfo && preg_match($regexp, array_shift($tmpFileInfo)) )
		{
			$type        = 'finfo';
			$this->finfo = finfo_open(FILEINFO_MIME);
		}
		elseif ( ( $type == 'mime_content_type' || $type == 'auto' ) && function_exists('mime_content_type') && preg_match($regexp, array_shift(explode(';', mime_content_type(__FILE__)))) )
		{
			$type = 'mime_content_type';
		}
		else
		{
			$type = 'internal';
		}

		$this->mimeDetect = $type;


		// load mimes from external file for mimeDetect == 'internal'
		// based on Alexey Sukhotin idea and patch: http://elrte.org/redmine/issues/163
		// file must be in file directory or in parent one
		if ( $this->mimeDetect == 'internal' && !self::$mimetypesLoaded )
		{
			self::$mimetypesLoaded = true;
			$this->mimeDetect      = 'internal';
			$file                  = false;

			if ( !empty( $this->options[ 'mimefile' ] ) && file_exists($this->options[ 'mimefile' ]) )
			{
				$file = $this->options[ 'mimefile' ];
			}
			elseif ( file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'mime.types') )
			{
				$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'mime.types';
			}
			elseif ( file_exists(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'mime.types') )
			{
				$file = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'mime.types';
			}

			if ( $file && file_exists($file) )
			{
				$mimecf = file($file);

				foreach ( $mimecf as $line_num => $line )
				{
					if ( !preg_match('/^\s*#/', $line) )
					{
						$mime = preg_split('/\s+/', $line, -1, PREG_SPLIT_NO_EMPTY);
						for ( $i = 1, $size = count($mime); $i < $size; $i++ )
						{
							if ( !isset( self::$mimetypes[ $mime[ $i ] ] ) )
							{
								self::$mimetypes[ $mime[ $i ] ] = $mime[ 0 ];
							}
						}
					}
				}
			}
		}


		$root = $this->getStat(DIRECTORY_SEPARATOR . $this->root);

		if ( !$root )
		{
			return $this->setError('Root folder does not exists.');
		}

		if ( !$root[ 'read' ] && !$root[ 'write' ] )
		{
			return $this->setError('Root folder has not read and write permissions.');
		}


		if ( $root[ 'read' ] )
		{
			// check startPath - path to open by default instead of root
			if ( $this->options[ 'startPath' ] )
			{
				$start = $this->getStat($this->options[ 'startPath' ]);

				if ( !empty( $start ) && $start[ 'mime' ] == 'directory' && $start[ 'read' ] && empty( $start[ 'hidden' ] ) && $this->inpath($this->options[ 'startPath' ], $this->root) )
				{
					$this->startPath = $this->options[ 'startPath' ];
					if ( substr($this->startPath, -1, 1) == $this->options[ 'separator' ] )
					{
						$this->startPath = substr($this->startPath, 0, -1);
					}
				}
			}
		}
		else
		{
			$this->options[ 'URL' ]     = '';
			$this->options[ 'tmbURL' ]  = '';
			$this->options[ 'tmbPath' ] = '';

			// read only volume
			array_unshift($this->attributes, array (
			                                       'pattern' => '/.*/',
			                                       'read'    => false
			                                 ));
		}


		if ( !empty( $this->options[ 'disabled' ] ) )
		{
			$no = array (
				'open',
				'reload',
				'tmb',
				'ping'
			);
			foreach ( $this->options[ 'disabled' ] as $k => $c )
			{
				if ( !isset( $this->commands[ $c ] ) || in_array($c, $no) )
				{
					unset( $this->options[ 'disabled' ][ $k ] );
				}
				else
				{
					unset( $this->commands[ $c ] );
				}
			}
		}

		if ( $this->options[ 'tmbPath' ] )
		{
			$tmbDir                     = $this->root . DIRECTORY_SEPARATOR . $this->options[ 'tmbPath' ];
			$this->options[ 'tmbPath' ] = is_dir($tmbDir) || @mkdir($tmbDir, $this->options[ 'dirMode' ]) ? $tmbDir : '';
		}

		if ( $this->options[ 'tmbPath' ] )
		{
			if ( !in_array($this->options[ 'imgLib' ], array (
			                                                 'imagick',
			                                                 'mogrify',
			                                                 'gd'
			                                           ))
			)
			{
				$this->options[ 'imgLib' ] = $this->_getImgLib();
			}
		}

		$this->treeDeep = $this->options[ 'treeDeep' ] > 0 ? (int)$this->options[ 'treeDeep' ] : 1;
		$this->tmbSize  = $this->options[ 'tmbSize' ] > 0 ? (int)$this->options[ 'tmbSize' ] : 48;
		$this->URL      = $this->options[ 'URL' ];

		if ( $this->URL && preg_match("|[^/?&=]$|", $this->URL) )
		{
			$this->URL .= '/';
		}

		$this->tmbURL = !empty( $this->options[ 'tmbURL' ] ) ? $this->options[ 'tmbURL' ] : '';
		if ( $this->tmbURL && preg_match("|[^/?&=]$|", $this->tmbURL) )
		{
			$this->tmbURL .= '/';
		}

		$this->nameValidator = is_string($this->options[ 'acceptedName' ]) && !empty( $this->options[ 'acceptedName' ] ) ? $this->options[ 'acceptedName' ] : '';

		// detect archivers
		$this->detectArchivers();

		// manual control archive types to create
		if ( !empty( $this->options[ 'archiveMimes' ] ) && is_array($this->options[ 'archiveMimes' ]) )
		{
			foreach ( $this->archivers[ 'create' ] as $mime => $v )
			{
				if ( !in_array($mime, $this->options[ 'archiveMimes' ]) )
				{
					unset( $this->archivers[ 'create' ][ $mime ] );
				}
			}
		}

		// manualy add archivers
		if ( !empty( $this->options[ 'archivers' ][ 'create' ] ) && is_array($this->options[ 'archivers' ][ 'create' ]) )
		{
			foreach ( $this->options[ 'archivers' ][ 'create' ] as $mime => $conf )
			{
				if ( strpos($mime, 'application/') === 0 && !empty( $conf[ 'cmd' ] ) && isset( $conf[ 'argc' ] ) && !empty( $conf[ 'ext' ] ) && !isset( $this->archivers[ 'create' ][ $mime ] ) )
				{
					$this->archivers[ 'create' ][ $mime ] = $conf;
				}
			}
		}

		if ( !empty( $this->options[ 'archivers' ][ 'extract' ] ) && is_array($this->options[ 'archivers' ][ 'extract' ]) )
		{
			foreach ( $this->options[ 'archivers' ][ 'extract' ] as $mime => $conf )
			{
				if ( substr($mime, 'application/') === 0 && !empty( $cons[ 'cmd' ] ) && isset( $conf[ 'argc' ] ) && !empty( $conf[ 'ext' ] ) && !isset( $this->archivers[ 'extract' ][ $mime ] ) )
				{
					$this->archivers[ 'extract' ][ $mime ] = $conf;
				}
			}
		}

		return $this;
	}

	public function prepareData() {

		if (isset($this->_result['error']))
		{
			$this->_result['msg'] = $this->_result['error'];
			$this->_result['success'] = false;
		}

	}

	/**
	 * Return image manipalation library name
	 *
	 * @return string
	 */
	public function _getImgLib ()
	{

		if ( extension_loaded('imagick') )
		{
			return 'imagick';
		}
		elseif ( function_exists('exec') )
		{
			exec('mogrify --version', $o, $c);
			if ( $c == 0 )
			{
				return 'mogrify';
			}
		}

		return function_exists('gd_info') ? 'gd' : '';
	}

	/**
	 *
	 */
	public function checkCommand ()
	{

		$cmd = $this->input('action', false);

		if ( $cmd && empty( $this->_commands[ $cmd ] ) )
		{

			Ajax::Send( false, array (
			                         'error' => 'Unknown command', 'msg' => 'Unknown command'
			                   ) );
			exit;

		}
	}

	/**
	 *
	 * @param string $message
	 */
	public function setError ( $message )
	{

		if ( IS_AJAX )
		{
			Library::sendJson(false, $message);
			exit;
		}

		die( $message );
	}

	/**
	 * Detect available archivers
	 *
	 * @return void
	 * */
	public function detectArchivers ()
	{

		if ( !function_exists('exec') )
		{
			$this->options[ 'archivers' ] = $this->options[ 'archive' ] = array ();

			return;
		}
		$arcs = array (
			'create'  => array (),
			'extract' => array ()
		);

		//exec('tar --version', $o, $ctar);
		Tools::processExec('tar --version', $o, $ctar);

		if ( $ctar == 0 )
		{
			$arcs[ 'create' ][ 'application/x-tar' ]  = array (
				'cmd'  => 'tar',
				'argc' => '-cf',
				'ext'  => 'tar'
			);
			$arcs[ 'extract' ][ 'application/x-tar' ] = array (
				'cmd'  => 'tar',
				'argc' => '-xf',
				'ext'  => 'tar'
			);
			//$test = exec('gzip --version', $o, $c);
			unset( $o );
			$test = Tools::processExec('gzip --version', $o, $c);

			if ( $c == 0 )
			{
				$arcs[ 'create' ][ 'application/x-gzip' ]  = array (
					'cmd'  => 'tar',
					'argc' => '-czf',
					'ext'  => 'tgz'
				);
				$arcs[ 'extract' ][ 'application/x-gzip' ] = array (
					'cmd'  => 'tar',
					'argc' => '-xzf',
					'ext'  => 'tgz'
				);
			}
			unset( $o );
			//$test = exec('bzip2 --version', $o, $c);
			$test = Tools::processExec('bzip2 --version', $o, $c);
			if ( $c == 0 )
			{
				$arcs[ 'create' ][ 'application/x-bzip2' ]  = array (
					'cmd'  => 'tar',
					'argc' => '-cjf',
					'ext'  => 'tbz'
				);
				$arcs[ 'extract' ][ 'application/x-bzip2' ] = array (
					'cmd'  => 'tar',
					'argc' => '-xjf',
					'ext'  => 'tbz'
				);
			}
		}
		unset( $o );
		//exec('zip --version', $o, $c);
		Tools::processExec('zip -v', $o, $c);
		if ( $c == 0 )
		{
			$arcs[ 'create' ][ 'application/zip' ] = array (
				'cmd'  => 'zip',
				'argc' => '-r9',
				'ext'  => 'zip'
			);
		}
		unset( $o );
		Tools::processExec('unzip --help', $o, $c);
		if ( $c == 0 )
		{
			$arcs[ 'extract' ][ 'application/zip' ] = array (
				'cmd'  => 'unzip',
				'argc' => '',
				'ext'  => 'zip'
			);
		}
		unset( $o );
		//exec('rar --version', $o, $c);
		Tools::processExec('rar --version', $o, $c);
		if ( $c == 0 || $c == 7 )
		{
			$arcs[ 'create' ][ 'application/x-rar' ]  = array (
				'cmd'  => 'rar',
				'argc' => 'a -inul',
				'ext'  => 'rar'
			);
			$arcs[ 'extract' ][ 'application/x-rar' ] = array (
				'cmd'  => 'rar',
				'argc' => 'x -y',
				'ext'  => 'rar'
			);
		}
		else
		{
			unset( $o );
			//$test = exec('unrar', $o, $c);
			$test = Tools::processExec('unrar', $o, $c);
			if ( $c == 0 || $c == 7 )
			{
				$arcs[ 'extract' ][ 'application/x-rar' ] = array (
					'cmd'  => 'unrar',
					'argc' => 'x -y',
					'ext'  => 'rar'
				);
			}
		}
		unset( $o );
		//exec('7za --help', $o, $c);
		Tools::processExec('7za --help', $o, $c);
		if ( $c == 0 )
		{
			$arcs[ 'create' ][ 'application/x-7z-compressed' ]  = array (
				'cmd'  => '7za',
				'argc' => 'a',
				'ext'  => '7z'
			);
			$arcs[ 'extract' ][ 'application/x-7z-compressed' ] = array (
				'cmd'  => '7za',
				'argc' => 'e -y',
				'ext'  => '7z'
			);

			if ( empty( $arcs[ 'create' ][ 'application/x-gzip' ] ) )
			{
				$arcs[ 'create' ][ 'application/x-gzip' ] = array (
					'cmd'  => '7za',
					'argc' => 'a -tgzip',
					'ext'  => 'tar.gz'
				);
			}
			if ( empty( $arcs[ 'extract' ][ 'application/x-gzip' ] ) )
			{
				$arcs[ 'extract' ][ 'application/x-gzip' ] = array (
					'cmd'  => '7za',
					'argc' => 'e -tgzip -y',
					'ext'  => 'tar.gz'
				);
			}
			if ( empty( $arcs[ 'create' ][ 'application/x-bzip2' ] ) )
			{
				$arcs[ 'create' ][ 'application/x-bzip2' ] = array (
					'cmd'  => '7za',
					'argc' => 'a -tbzip2',
					'ext'  => 'tar.bz'
				);
			}
			if ( empty( $arcs[ 'extract' ][ 'application/x-bzip2' ] ) )
			{
				$arcs[ 'extract' ][ 'application/x-bzip2' ] = array (
					'cmd'  => '7za',
					'argc' => 'a -tbzip2 -y',
					'ext'  => 'tar.bz'
				);
			}
			if ( empty( $arcs[ 'create' ][ 'application/zip' ] ) )
			{
				$arcs[ 'create' ][ 'application/zip' ] = array (
					'cmd'  => '7za',
					'argc' => 'a -tzip -l',
					'ext'  => 'zip'
				);
			}
			if ( empty( $arcs[ 'extract' ][ 'application/zip' ] ) )
			{
				$arcs[ 'extract' ][ 'application/zip' ] = array (
					'cmd'  => '7za',
					'argc' => 'e -tzip -y',
					'ext'  => 'zip'
				);
			}
			if ( empty( $arcs[ 'create' ][ 'application/x-tar' ] ) )
			{
				$arcs[ 'create' ][ 'application/x-tar' ] = array (
					'cmd'  => '7za',
					'argc' => 'a -ttar -l',
					'ext'  => 'tar'
				);
			}
			if ( empty( $arcs[ 'extract' ][ 'application/x-tar' ] ) )
			{
				$arcs[ 'extract' ][ 'application/x-tar' ] = array (
					'cmd'  => '7za',
					'argc' => 'e -ttar -y',
					'ext'  => 'tar'
				);
			}
		}

		$this->archivers = $arcs;
	}

	/**
	 * Return true if thumnbnail for required file can be created
	 *
	 * @param string $path thumnbnail path
	 * @param array  $stat file stat
	 * @return string|bool
	 * */
	public function canCreateTmb ( $path, $stat )
	{

		return $this->tmbPathWritable && strpos($path, $this->tmbPath) === false // do not create thumnbnail for thumnbnail
		&& $this->imgLib && strpos($stat[ 'mime' ], 'image') === 0 && ( $this->imgLib == 'gd' ? $stat[ 'mime' ] == 'image/jpeg' || $stat[ 'mime' ] == 'image/png' || $stat[ 'mime' ] == 'image/gif' : true );
	}

	/**
	 * Return true if required file can be resized.
	 * By default - the same as canCreateTmb
	 *
	 * @param string $path thumnbnail path
	 * @param array  $stat file stat
	 * @return string|bool
	 * */
	public function canResize ( $path, $stat )
	{

		return $this->canCreateTmb($path, $stat);
	}



	/**
	 * Return file URL
	 *
	 * @param string $path
	 * @return string
	 * */
	public function _path2url ( $path )
	{

		$dir  = substr(dirname($path), strlen($this->root) + 1);
		$file = rawurlencode(basename($path));

		return $this->options[ 'URL' ] . ( $dir ? str_replace(DIRECTORY_SEPARATOR, '/', $dir) . '/' : '' ) . $file;
	}

	/**
	 * Return normalized path, this works the same as os.path.normpath() in Python
	 *
	 * @param string $path path
	 * @return string
	 * */
	public function _normpath ( $path )
	{

		if ( empty( $path ) )
		{
			return '.';
		}

		if ( strpos($path, '/') === 0 )
		{
			$initial_slashes = true;
		}
		else
		{
			$initial_slashes = false;
		}
		if ( ( $initial_slashes ) && ( strpos($path, '//') === 0 ) && ( strpos($path, '///') === false )
		)
		{
			$initial_slashes = 2;
		}
		$initial_slashes = (int)$initial_slashes;

		$comps     = explode('/', $path);
		$new_comps = array ();
		foreach ( $comps as $comp )
		{
			if ( in_array($comp, array (
			                           '',
			                           '.'
			                     ))
			)
			{
				continue;
			}
			if ( ( $comp != '..' ) || ( !$initial_slashes && !$new_comps ) || ( $new_comps && ( end($new_comps) == '..' ) )
			)
			{
				array_push($new_comps, $comp);
			}
			elseif ( $new_comps )
			{
				array_pop($new_comps);
			}
		}
		$comps = $new_comps;
		$path  = implode('/', $comps);
		if ( $initial_slashes )
		{
			$path = str_repeat('/', $initial_slashes) . $path;
		}
		if ( $path )
		{
			return $path;
		}
		else
		{
			return '.';
		}
	}

	/**
	 * Return file path related to root dir
	 *
	 * @param  string $path file path
	 * @return string
	 * */
	protected function _relpath ( $path )
	{

		return $path == $this->root ? '' : substr($path, strlen($this->root) + 1);
	}

	/**
	 * Convert path related to root dir into real path
	 *
	 * @param  string $path file path
	 * @return string
	 * */
	protected function _abspath ( $path )
	{

		return $path == DIRECTORY_SEPARATOR ? $this->root : $this->root . DIRECTORY_SEPARATOR . $path;
	}

	/**
	 * Return fake path started from root dir
	 *
	 * @param  string $path file path
	 * @return string
	 * */
	protected function _path ( $path )
	{

		return $this->rootAlias . ( $path == $this->root ? '' : DIRECTORY_SEPARATOR . $this->_relpath($path) );
	}

	/**
	 * Return true if $path is children of $parent
	 *
	 * @param  string $path   path to check
	 * @param  string $parent parent path
	 * @return bool
	 * */
	protected function _inpath ( $path, $parent )
	{

		return $path == $parent || strpos($path, $parent . DIRECTORY_SEPARATOR) === 0;
	}

	/**
	 * @param $filename
	 * @return array
	 */
	private function askapacheStat ( $filename )
	{

		clearstatcache();
		$ss = @stat($filename);
		if ( !$ss )
		{
			die( "Couldnt stat {$filename}" );
		}


		$file_convert = array (
			0140000 => 'ssocket',
			0120000 => 'llink',
			0100000 => '-file',
			0060000 => 'bblock',
			0040000 => 'ddir',
			0020000 => 'cchar',
			0010000 => 'pfifo'
		);


		$p   = $ss[ 'mode' ];
		$t   = decoct($ss[ 'mode' ] & 0170000);
		$str = ( array_key_exists(octdec($t), $file_convert) ) ? $file_convert[ octdec($t) ]{0} : 'u';
		$str .= ( ( $p & 0x0100 ) ? 'r' : '-' ) . ( ( $p & 0x0080 ) ? 'w' : '-' ) . ( ( $p & 0x0040 ) ? ( ( $p & 0x0800 ) ? 's' : 'x' ) : ( ( $p & 0x0800 ) ? 'S' : '-' ) );
		$str .= ( ( $p & 0x0020 ) ? 'r' : '-' ) . ( ( $p & 0x0010 ) ? 'w' : '-' ) . ( ( $p & 0x0008 ) ? ( ( $p & 0x0400 ) ? 's' : 'x' ) : ( ( $p & 0x0400 ) ? 'S' : '-' ) );
		$str .= ( ( $p & 0x0004 ) ? 'r' : '-' ) . ( ( $p & 0x0002 ) ? 'w' : '-' ) . ( ( $p & 0x0001 ) ? ( ( $p & 0x0200 ) ? 't' : 'x' ) : ( ( $p & 0x0200 ) ? 'T' : '-' ) );

		$s = array (
			'perms' => array (
				'umask'     => sprintf("%04o", umask()),
				'human'     => $str,
				'octal1'    => sprintf("%o", ( $ss[ 'mode' ] & 000777 )),
				'octal2'    => sprintf("0%o", 0777 & $p),
				'decimal'   => sprintf("%04o", $p),
				'fileperms' => @fileperms($filename),
				'mode1'     => $p,
				'mode2'     => $ss[ 'mode' ]
			),
			'time'  => array (
				'mtime'    => $ss[ 'mtime' ],
				//Time of last modification
				'atime'    => $ss[ 'atime' ],
				//Time of last access.
				'ctime'    => $ss[ 'ctime' ],
				//Time of last status change
				'accessed' => @date('d.m.Y H:i:s', $ss[ 'atime' ]),
				'modified' => @date('d.m.Y H:i:s', $ss[ 'mtime' ]),
				'created'  => @date('d.m.Y H:i:s', $ss[ 'ctime' ])
			)
		);

		clearstatcache();

		return $s;
	}

	/**
	 * Return stat for given path.
	 * Stat contains following fields:
	 * - (int) size file size in b. required
	 * - (int) ts file modification time in unix time. required
	 * - (string) mime mimetype. required for folders, others - optionally
	 * - (bool) read read permissions. required
	 * - (bool) write write permissions. required
	 * - (bool) locked is object locked. optionally
	 * - (bool) hidden is object hidden. optionally
	 * - (string) alias for symlinks - link target path relative to root path. optionally
	 * - (string) target for symlinks - link target path. optionally
	 *
	 * If file does not exists - returns empty array or false.
	 *
	 * @param string $path file path
	 * @return array|false
	 */
	public function getStat ( $path )
	{

		$stat = array ();

		if ( !file_exists($path) )
		{
			return $stat;
		}


		if ( $path != $this->root && is_link($path) )
		{
			if ( ( $target = $this->readlink($path) ) == false || $target == $path )
			{
				$stat[ 'mime' ]  = 'symlink-broken';
				$stat[ 'read' ]  = false;
				$stat[ 'write' ] = false;
				$stat[ 'size' ]  = 0;
				$stat[ 'rm' ]    = $this->_isAllowed($path, 'rm');

				$options = $this->askapacheStat($path);

				$stat[ 'modified' ] = $options[ 'time' ][ 'modified' ];
				$stat[ 'accessed' ] = $options[ 'time' ][ 'accessed' ];
				$stat[ 'created' ]  = $options[ 'time' ][ 'created' ];

				$stat[ 'mtime' ] = $options[ 'time' ][ 'mtime' ];
				$stat[ 'atime' ] = $options[ 'time' ][ 'atime' ];
				$stat[ 'ctime' ] = $options[ 'time' ][ 'ctime' ];

				$stat[ 'perms_human' ]  = $options[ 'perms' ][ 'human' ];
				$stat[ 'perms_octal1' ] = $options[ 'perms' ][ 'octal1' ];
				$stat[ 'perms_octal2' ] = $options[ 'perms' ][ 'octal2' ];

				return $stat;
			}


			$symPath = $path;


			$stat[ 'alias' ]  = $this->_path($target);
			$stat[ 'target' ] = $target;
			$stat[ 'rm' ]     = $this->_isAllowed($path, 'rm');

			$path  = $target;
			$lstat = lstat($path);
			$size  = $lstat[ 'size' ];


			$pinfo = pathinfo($path);
			$ext   = isset( $pinfo[ 'extension' ] ) ? strtolower($pinfo[ 'extension' ]) : '';

			$stat[ 'ext' ]  = $ext;
			$stat[ 'mime' ] = $dir ? 'directory' : $this->getFileMime($ext);


			$options = $this->askapacheStat($path);

			$stat[ 'modified' ] = $options[ 'time' ][ 'modified' ];
			$stat[ 'accessed' ] = $options[ 'time' ][ 'accessed' ];
			$stat[ 'created' ]  = $options[ 'time' ][ 'created' ];

			$stat[ 'mtime' ] = $options[ 'time' ][ 'mtime' ];
			$stat[ 'atime' ] = $options[ 'time' ][ 'atime' ];
			$stat[ 'ctime' ] = $options[ 'time' ][ 'ctime' ];

			$stat[ 'perms_human' ]  = $options[ 'perms' ][ 'human' ];
			$stat[ 'perms_octal1' ] = $options[ 'perms' ][ 'octal1' ];
			$stat[ 'perms_octal2' ] = $options[ 'perms' ][ 'octal2' ];

			$stat[ 'date' ]  = Locales::formatDateTime($options[ 'time' ][ 'mtime' ]); //date($this->options['dateFormat'], filemtime($path));
			$stat[ 'ts' ]    = $options[ 'time' ][ 'mtime' ];
			$stat[ 'read' ]  = is_readable($path);
			$stat[ 'write' ] = is_writable($path);

			$stat[ 'parentDir' ] = str_replace($this->root, '', $path);
			$stat[ 'path' ]      = str_replace($this->root, '', $path);
			$stat[ 'name' ]      = basename($path);


			$stat[ 'hash' ] = 'link_' . $this->_hash($path);

			$dir            = is_dir($path);
			if ( $dir )
			{
				$stat[ 'mime' ] = 'directory';
			}

			if ( $stat[ 'read' ] )
			{
				$stat[ 'size' ] = $dir ? 0 : $size;
			}
			else
			{
				$stat[ 'size' ] = 0;
			}


			if ( $stat[ 'mime' ] != 'directory' )
			{
				if ( $this->options[ 'URL' ] && $stat[ 'read' ] )
				{
					$stat[ 'url' ] = $this->_path2url($lpath ? $lpath : $path);
				}

				$p = stripos($stat[ 'mime' ], 'image');

				if ( $p !== false )
				{

					$stat[ 'dimensions' ] = $this->getDimensions($path, $stat[ 'mime' ]);

					if ( $stat[ 'read' ] )
					{
						$stat[ 'resize' ] = isset( $stat[ 'dimensions' ] ) && $this->_canCreateTmb($stat[ 'mime' ]);
						$tmb              = $this->_tmbPath($path);
						$coverflow        = $this->_tmbPath($path, true);

						if ( file_exists($tmb) && file_exists($coverflow) )
						{
							$stat[ 'coverflow' ] = $this->_path2url($coverflow);
							$stat[ 'tmb' ]       = $this->_path2url($tmb);
						}
						elseif ( $stat[ 'resize' ] )
						{
							$this->_result[ 'tmb' ] = true;
						}
					}
				}
			}


			return $stat;
		}
		else
		{
			$size         = @filesize($path);
			$stat[ 'rm' ] = $this->_isAllowed($path, 'rm');
		}


		$stat[ 'hash' ] = $this->_hash($path);
		$dir            = is_dir($path);


		$pinfo = pathinfo($path);
		$ext   = isset( $pinfo[ 'extension' ] ) ? strtolower($pinfo[ 'extension' ]) : '';

		$stat[ 'ext' ]  = $ext;
		$stat[ 'mime' ] = $dir ? 'directory' : $this->getFileMime($ext);




		$options = $this->askapacheStat($path);

		$stat[ 'modified' ] = $options[ 'time' ][ 'modified' ];
		$stat[ 'accessed' ] = $options[ 'time' ][ 'accessed' ];
		$stat[ 'created' ]  = $options[ 'time' ][ 'created' ];

		$stat[ 'mtime' ] = $options[ 'time' ][ 'mtime' ];
		$stat[ 'atime' ] = $options[ 'time' ][ 'atime' ];
		$stat[ 'ctime' ] = $options[ 'time' ][ 'ctime' ];

		$stat[ 'perms_human' ]  = $options[ 'perms' ][ 'human' ];
		$stat[ 'perms_octal1' ] = $options[ 'perms' ][ 'octal1' ];
		$stat[ 'perms_octal2' ] = $options[ 'perms' ][ 'octal2' ];

		$stat[ 'date' ] = Locales::formatDateTime($options[ 'time' ][ 'mtime' ]); //date($this->options['dateFormat'], filemtime($path));


		$stat[ 'ts' ] = $options[ 'time' ][ 'mtime' ];


		$stat[ 'read' ]  = is_readable($path);
		$stat[ 'write' ] = is_writable($path);


		if ( $stat[ 'read' ] )
		{
			$stat[ 'size' ] = $dir ? 0 : $size;
		}
		else
		{
			$stat[ 'size' ] = 0;
		}


		$stat[ 'parentDir' ] = str_replace($this->root, '', $path);
		$stat[ 'path' ]      = $path;
		$stat[ 'name' ]      = basename($path);
		$stat[ 'coverflow' ] = false;

		if ($stat[ 'mime' ] != 'directory')
		{
			unset($stat[ 'parentDir' ]);
		}

		//$stat['dimensions'] = $this->getDimensions($path, $stat['mime']);

		if ( $stat[ 'mime' ] != 'directory' )
		{
			if ( $this->options[ 'URL' ] && $stat[ 'read' ] )
			{
				$stat[ 'url' ] = $this->_path2url($lpath ? $lpath : $path);
			}

			$p                   = stripos($stat[ 'mime' ], 'image');
			$stat[ 'coverflow' ] = '-';

			if ( $p !== false )
			{

				$stat[ 'dimensions' ] = $this->getDimensions($path, $stat[ 'mime' ]);


				if ( $stat[ 'read' ] )
				{
					$stat[ 'resize' ] = isset( $stat[ 'dimensions' ] ) && $this->_canCreateTmb($stat[ 'mime' ]);

					$coverflow = $this->_tmbPath($path);

					if ( file_exists($coverflow) )
					{
						$stat[ 'coverflow' ] = $this->_path2url($coverflow);
					}
					elseif ( $stat[ 'resize' ] )
					{
						$this->_result[ 'tmb' ] = true;
					}


					$tmb       = $this->_tmbPath($path);
					$coverflow = $this->_tmbPath($path, true);

					if ( file_exists($tmb) && file_exists($coverflow) )
					{
						$stat[ 'coverflow' ] = $this->_path2url($coverflow);
						$stat[ 'tmb' ]       = $this->_path2url($tmb);
					}
					elseif ( $stat[ 'resize' ] )
					{
						$this->_result[ 'tmb' ] = true;
					}

					if ( !$stat[ 'coverflow' ] )
					{
						$stat[ 'coverflow' ] = '-';
					}
				}
			}
		}

		return $stat;
	}

	/**
	 *
	 * @param string $ext
	 * @return string/false
	 */
	public function getFileMime ( $ext )
	{

		$ext = strtolower($ext);

		if ( isset( self::$mimetypes[ $ext ] ) )
		{
			return self::$mimetypes[ $ext ];
		}

		return 'unknown';
	}

	/**
	 * Return true if mime is required mimes list
	 *
	 * @param string    $mime  mime type to check
	 * @param array     $mimes allowed mime types list or not set to use client mimes list
	 * @param bool|null $empty what to return on empty list
	 * @return bool|null
	 * */
	public function mimeAccepted ( $mime, $mimes = array (), $empty = true )
	{

		$mimes = !empty( $mimes ) ? $mimes : $this->onlyMimes;
		if ( empty( $mimes ) )
		{
			return $empty;
		}

		return $mime == 'directory' || in_array('all', $mimes) || in_array('All', $mimes) || in_array($mime, $mimes) || in_array(substr($mime, 0, strpos($mime, '/')), $mimes);
	}

	/**
	 * Return object width and height
	 * Usualy used for images, but can be realize for video etc...
	 *
	 * @param string $path file path
	 * @param string $mime file mime type
	 * @return string/false
	 */
	public function getDimensions ( $path, $mime )
	{

		if ( stripos($mime, 'image') !== false )
		{
			if ( ( $s = @getimagesize($path) ) !== false )
			{
				return $s[ 0 ] . 'x' . $s[ 1 ];
			}
		}

		return;
	}

	/**
	 * Return dir files names list
	 *
	 * @param string $hash file hash
	 * @return array/false
	 * */
	public function ls ( $hash )
	{

		if ( ( $dir = $this->dir($hash) ) == false || !$dir[ 'read' ] )
		{
			return false;
		}

		$list = array ();
		$path = $this->decode($hash);

		foreach ( $this->getScandir($path) as $stat )
		{
			if ( empty( $stat[ 'hidden' ] ) && $this->mimeAccepted($stat[ 'mime' ]) )
			{
				$list[ ] = $stat[ 'name' ];
			}
		}

		return $list;
	}

	/**
	 * Return files list in directory.
	 *
	 * @param string $path dir path
	 * @return array
	 */
	public function scanDir ( $path )
	{

		$files = array ();

		foreach ( scandir($path) as $name )
		{
			if ( $name != '.' && $name != '..' )
			{
				$files[ ] = $path . DIRECTORY_SEPARATOR . $name;
			}
		}

		return $files;
	}

	/**
	 * Open file and return file pointer
	 *
	 * @param string $path file path
	 * @param string $mode
	 * @internal param bool $write open file for writing
	 * @return resource|false
	 */
	public function fOpen ( $path, $mode = 'rb' )
	{

		return @fopen($path, 'r');
	}

	/**
	 * Close opened file
	 *
	 * @param resource $fp file pointer
	 * @param string   $path
	 * @return bool
	 */
	public function fClose ( $fp, $path = '' )
	{

		return @fclose($fp);
	}

	/**
	 * @return float
	 */
	public function utime ()
	{

		$time = explode(" ", microtime());

		return (double)$time[ 1 ] + (double)$time[ 0 ];
	}

	/*


















     */

	/**
	 * Send header Connection: close. Required by safari to fix bug http://www.webmasterworld.com/macintosh_webmaster/3300569.htm
	 *
	 * @return void
	 * */
	public function _ping ()
	{
		header("Connection: close");
		exit;
	}

	/**
	 * @param bool $page
	 */
	public function _open ( $page = false )
	{

		if ( $this->input('type') === 'file' )
		{

			$target  = $this->input('fpathHash');
			$current = $this->input('cwd'); // the cwd hash

			$this->_cwd(Session::get('cwd'));

			$rel = $this->rootAlias ? $this->rootAlias : basename($this->root);


			// remove the root alias
			$current = str_replace('%2C', '/', $current);
			$current = str_replace(',', '/', $current);
			$current = $this->root . '/' . str_replace(array (
			                                                 '../',
			                                                 './'
			                                           ), '___', $current);

			# die($current);

			if ( empty( $target ) || false == ( $dir = $this->_findDir(trim($current)) ) || false == ( $file = $this->_find(trim($target), $dir) ) || is_dir($file)
			)
			{
				header('HTTP/1.x 404 Not Found');
				exit( 'File not found 1' . $current );
			}

			if ( !$this->_isAllowed($dir, 'read') || !$this->_isAllowed($file, 'read') )
			{
				header('HTTP/1.x 403 Access Denied');
				exit( 'Access denied 1' . $current );
			}


			if ( filetype($file) == 'link' )
			{
				$file = $this->_readlink($file);
				if ( !$file || is_dir($file) )
				{
					header('HTTP/1.x 404 Not Found');
					exit( 'File not found 2' );
				}
				if ( !$this->_isAllowed(dirname($file), 'read') || !$this->_isAllowed($file, 'read') )
				{
					header('HTTP/1.x 403 Access Denied');
					exit( 'Access denied 2' );
				}
			}


			/**
			 *
			 */
			if ( $this->_get('filesection') )
			{
				$w = 0;
				$h = 0;

				if (is_file($file)) {
					$pinfo = pathinfo($file);
					$ext   = isset( $pinfo[ 'extension' ] ) ? strtolower($pinfo[ 'extension' ]) : '';
					$mime  = $this->getFileMime($ext);

					if ( stripos($mime, 'image') !== false )
					{
						if ( ( $s = @getimagesize($file) ) !== false )
						{
							$w = $s[ 0 ];
							$h = $s[ 1 ];
						}
					}
				}

				echo Library::json(array (
				                         'success'  => true,
				                         'filepath' => str_replace(PUBLIC_PATH, '', $file),
				                         'width'    => $w,
				                         'height'   => $h
				                   ));
				exit;
			}

			error_reporting(0);
			Library::disableErrorHandling();


			$pinfo = pathinfo($file);
			$ext   = isset( $pinfo[ 'extension' ] ) ? strtolower($pinfo[ 'extension' ]) : '';


			$mime = $this->getFileMime($ext);

			$parts = explode('/', $mime);
			$disp  = $parts[ 0 ] === 'image' || $parts[ 0 ] === 'text' ? 'attachment' : 'attachments';


			#  header( "Content-Location: " . str_replace( $this->root, '', $file ) );
			#  header( 'Content-Transfer-Encoding: binary' );

			$filesize = filesize($file);

			header("Content-Type: " . $mime);

			// It's not a range request, output the file anyway
			header('Content-Length: ' . $filesize);


			if ( $this->_get('mode') == 'iframe' )
			{

			}
			else
			{
				header("Content-Transfer-Encoding: binary");
				header("Content-Disposition: " . $disp . "; filename=" . basename($file));
			}


			// Read the file
			readfile_chunked($file);

			exit;

		}
		else
		{

			$target = $this->input('pathHash'); // the cwd hash
			$tree   = $this->input('tree');
			$path   = $this->root;

			if ( $target != null && !empty( $target ) )
			{
				if ( false == ( $p = $this->_findDir(trim($target)) ) )
				{
					if ( $this->input('init') === null )
					{
						$this->_result[ 'error' ] = 'Invalid parameters';
					}
				}

				if ( !$this->_isAllowed($p, 'read') )
				{
					if ( $this->input('init') === null )
					{
						$this->_result[ 'error' ] = 'Access denied';
					}
				}
				else
				{
					$path = ( $p ? $p : $path );
				}

				$personal = new Personal;
				$personal->set('filemanager', 'path', array (
				                                            'path' => $path
				                                      ));
			}
			else
			{
				$personal = new Personal;
				$_path    = $personal->get('filemanager', 'path', array (
				                                                        'path' => $path
				                                                  ));

				if ( is_array($_path) && !empty( $_path[ 'path' ] ) )
				{
					$path = $_path[ 'path' ];
				}
			}

			Session::save('cwd', $path);

			$this->_content($path, !empty( $tree ), $page);
		}
	}

	/**
	 * Set current dir info, content and [dirs tree]
	 *
	 * @param string $path current dir path
	 * @param bool   $tree set dirs tree?
	 * @param bool   $page
	 * @return void
	 */
	public function _content ( $path, $tree = false, $page = false )
	{

		if ( $page === false )
		{
			$page = 1;

			if ( Session::get('fm-page') )
			{
				$page = Session::get('fm-page');
			}
		}


		$this->_cwd($path);
		$this->_cdc($path, $page);

		Session::save('fm-page', $page);


		if ( $tree )
		{
			$this->_result[ 'tree' ] = $this->_tree($this->root);
		}
	}

	/**
	 * Copy file into another file
	 *
	 * @param  string $source    source file path
	 * @param  string $targetDir target directory path
	 * @param  string $name      new file name
	 * @return bool
	 * @author Dmitry (dio) Levashov
	 * */
	protected function _copy ( $source, $targetDir, $name )
	{

		return copy($source, $targetDir . DIRECTORY_SEPARATOR . $name);
	}
	/**
	 * Set current dir info
	 *
	 * @param string $path current dir path
	 * @return void
	 * */
	public function _cwd ( $path )
	{
		$rel = $this->rootAlias ? $this->rootAlias : basename($this->root);
		if ( $path == $this->root )
		{
			$name = $rel;
		}
		else
		{
			$name = basename($path);
			$rel .= DIRECTORY_SEPARATOR . substr($path, strlen($this->root) + 1);
		}

		if ( !is_dir($path) )
		{
			return;
		}

		$mtime = @filemtime($path);


		$this->_result[ 'cwd' ] = array (
			'hash'  => $this->_hash($path),
			'name'  => $name,
			'mime'  => 'directory',
			'rel'   => $rel,
			'size'  => 0,
			'date'  => date($this->options[ 'dateFormat' ], $mtime),
			'read'  => true,
			'write' => $this->_isAllowed($path, 'write'),
			'rm'    => $path == $this->root ? false : $this->_isAllowed($path, 'rm')
		);
	}
	/**
	 * Set current dir content
	 *
	 * @param string $path current dir path
	 * @param int    $page
	 * @return void
	 */
	public function _cdc ( $path, $page = 1 )
	{

		$dirs = $files = array ();


		$ls = scandir($path);
		natcasesort($ls);
		$fcount = count($ls);
		$limit  = 50;

		$totalpages = ( $fcount > $limit ? ( ceil($fcount / $limit) ) : 1 );
		$page       = ( $page < 1 ? 1 : $page );
		$start      = ( $limit * ( $page - 1 ) );
		$end        = ( $start + $limit );

		$newstart = 1;
		// All files
		$x = 0;


		for ( $i = $start; $i < $end; $i++ )
		{
			if ( isset( $ls[ $i ] ) && $this->_isAccepted($ls[ $i ]) )
			{
				$info = $this->getStat($path . DIRECTORY_SEPARATOR . $ls[ $i ]);

				// $hash = ( $stat['mime'] == 'symlink-broken' || isset($info['alias']) ? '' : $info['hash'] );

				if ( $info[ 'mime' ] == 'directory' )
				{
					$dirs[ $info[ 'hash' ] ] = $info;
				}
				else
				{
					$files[ $info[ 'hash' ] ] = $info;
				}
			}
		}

		$this->_result[ 'dircontent_page' ]  = $page;
		$this->_result[ 'dircontent_pages' ] = $totalpages;
		$this->_result[ 'filestotal' ]       = $fcount;
		$this->_result[ 'dircontent' ]       = array_merge($dirs, $files);
	}

	/**
	 * Return directory tree (multidimensional array)
	 *
	 * @param string $path directory path
	 * @return array
	 * */
	public function _tree ( $path )
	{

		$dir = array (
			'hash'    => $this->_hash($path),
			'name'    => $path == $this->root && $this->rootAlias ? $this->rootAlias : basename($path),
			'read'    => $this->_isAllowed($path, 'read'),
			'write'   => $this->_isAllowed($path, 'write'),
			'date'    => date($this->options[ 'dateFormat' ], filemtime($path)),
			'subdirs' => array ()
		);

		if ( $dir[ 'read' ] && ( false != ( $ls = scandir($path) ) ) )
		{
			$max = count($ls);
			for ( $i = 0; $i < $max; $i++ )
			{
				$p = $path . DIRECTORY_SEPARATOR . $ls[ $i ];
				if ( $this->_isAccepted($ls[ $i ]) && is_dir($p) && !is_link($p) )
				{
					$dir[ 'subdirs' ][ ] = $this->_tree($p);
				}
			}
		}

		return $dir;
	}

	/**
	 *
	 * @param string $name
	 * @return string
	 */
	public function _hash ( $name )
	{
		return md5($name);
	}

	/**
	 * Return true if file's mimetype is allowed for upload
	 *
	 * @param string $name    file name
	 * @param string $tmpName uploaded file tmp name
	 * @return bool
	 * */
	public function _isUploadAllow ( $name, $tmpName )
	{

		$allow = false;
		$deny  = false;
		$mime  = $this->_mimetype($this->options[ 'mimeDetect' ] != 'internal' ? $tmpName : $name);

		if ( in_array('all', $this->options[ 'uploadAllow' ]) )
		{
			$allow = true;
		}
		else
		{
			foreach ( $this->options[ 'uploadAllow' ] as $type )
			{
				if ( 0 === strpos($mime, $type) )
				{
					$allow = true;
				}
			}
		}

		if ( in_array('all', $this->options[ 'uploadDeny' ]) )
		{
			$deny = true;
		}
		else
		{
			foreach ( $this->options[ 'uploadDeny' ] as $type )
			{
				if ( 0 === strpos($mime, $type) )
				{
					$deny = true;
				}
			}
		}

		$this->_result[ 'debug' ][ '_isUploadAllow' ][ $name ] = $mime;

		if ( ( is_string($this->options[ 'uploadOrder' ]) && 0 === strpos($this->options[ 'uploadOrder' ], 'allow') ) || is_array($this->options[ 'uploadOrder' ]) && in_array('allow', $this->options[ 'uploadOrder' ]) )
		{
			// ,deny
			if ( $deny === true )
			{
				return false;
			}
			elseif ( $allow === true )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			// deny,allow
			if ( $allow === true )
			{
				return true;
			}
			elseif ( $deny === true )
			{
				return false;
			}
			else
			{
				return true;
			}
		}
	}

	/**
	 * Check new file name for invalid simbols. Return name if valid
	 *
	 * @param $n
	 * @return string $n file name
	 * @return string
	 */
	public function _checkName ( $n )
	{

		$n = strip_tags(trim($n));
		if ( (!isset($this->options[ 'dotFiles' ]) || empty($this->options[ 'dotFiles' ])) && '.' === substr($n, 0, 1) )
		{
			return false;
		}

		return preg_match('|^[^\\/\<\>:]+$|', $n) ? $n : false;
	}

	/**
	 * Return true if file name is not . or ..
	 * If file name begins with . return value according to $this->_options['dotFiles']
	 *
	 * @param string $file file name
	 * @return bool
	 * */
	public function _isAccepted ( $file )
	{

		if ( '.' == $file || '..' == $file )
		{
			return false;
		}

		if ( (!isset($this->options[ 'dotFiles' ]) || empty($this->options[ 'dotFiles' ])) && '.' === substr($file, 0, 1) )
		{
			return false;
		}

		return true;
	}

	/**
	 * Return mimetype detect method name
	 *
	 * @return string
	 * */
	protected function _getMimeDetect ()
	{

		if ( class_exists('finfo') )
		{
			return 'finfo';
		}
		elseif ( function_exists('mime_content_type') && ( mime_content_type(__FILE__) == 'text/x-php' || mime_content_type(__FILE__) == 'text/x-c++' ) )
		{
			return 'mime_content_type';
		}
		elseif ( function_exists('exec') )
		{
			$type = exec('file -ib ' . escapeshellarg(__FILE__));
			if ( 0 === strpos($type, 'text/x-php') || 0 === strpos($type, 'text/x-c++') )
			{
				return 'linux';
			}
			$type = exec('file -Ib ' . escapeshellarg(__FILE__));
			if ( 0 === strpos($type, 'text/x-php') || 0 === strpos($type, 'text/x-c++') )
			{
				return 'bsd';
			}
		}

		return 'internal';
	}

	/**
	 * Return file mimetype
	 *
	 * @param  string $path file path
	 * @return string
	 * */
	protected function _mimetype ( $path )
	{

		if ( empty( $this->options[ 'mimeDetect' ] ) || $this->options[ 'mimeDetect' ] == 'auto' )
		{
			$this->options[ 'mimeDetect' ] = $this->_getMimeDetect();
		}

		switch ( $this->options[ 'mimeDetect' ] )
		{
			case 'finfo':
				if ( empty( $this->_finfo ) )
				{
					$this->_finfo = finfo_open(FILEINFO_MIME);
				}
				$type = @finfo_file($this->_finfo, $path);
				break;
			case 'php':
				$type = mime_content_type($path);
				break;
			case 'linux':
				$type = exec('file -ib ' . escapeshellarg($path));
				break;
			case 'bsd':
				$type = exec('file -Ib ' . escapeshellarg($path));
				break;
			default:
				$pinfo = pathinfo($path);
				$ext   = isset( $pinfo[ 'extension' ] ) ? strtolower($pinfo[ 'extension' ]) : '';
				$type  = isset( $this->_mimeTypes[ $ext ] ) ? $this->_mimeTypes[ $ext ] : 'unknown;';
		}
		$type = explode(';', $type);

		if ( $this->options[ 'mimeDetect' ] != 'internal' && $type[ 0 ] == 'application/octet-stream' )
		{
			$pinfo = pathinfo($path);
			$ext   = isset( $pinfo[ 'extension' ] ) ? strtolower($pinfo[ 'extension' ]) : '';
			if ( !empty( $ext ) && !empty( $this->_mimeTypes[ $ext ] ) )
			{
				$type[ 0 ] = $this->_mimeTypes[ $ext ];
			}
		}

		return $type[ 0 ];
	}

	/**
	 * Return true if requeired action allowed to file/folder
	 *
	 * @param string $path   file/folder path
	 * @param string $action action name (read/write/rm)
	 * @return void
	 * */
	public function _isAllowed ( $path, $action )
	{

		switch ( $action )
		{
			case 'read':
				if ( !is_readable($path) )
				{
					return false;
				}
				break;

			case 'write':
				if ( !is_writable($path) )
				{
					return false;
				}
				break;

			case 'rm':
				if ( !is_writable($path) )
				{
					return false;
				}
				break;
		}

		$path = substr($path, strlen($this->root) + 1);

		foreach ( $this->options[ 'perms' ] as $regex => $rules )
		{
			if ( preg_match($regex, $path) )
			{
				if ( isset( $rules[ $action ] ) )
				{
					return $rules[ $action ];
				}
			}
		}

		return isset( $this->options[ 'defaults' ][ $action ] ) ? $this->options[ 'defaults' ][ $action ] : false;
	}

	/**
	 * Return list of available archivers
	 *
	 * @return array
	 * */
	public function _checkArchivers ()
	{

		if ( !function_exists('exec') )
		{
			$this->options[ 'archivers' ] = $this->options[ 'archive' ] = array ();
			return;
		}

		$arcs = array (
			'create'  => array (),
			'extract' => array ()
		);

		exec('tar --version', $o, $ctar);
		if ( $ctar == 0 )
		{
			$arcs[ 'create' ][ 'application/x-tar' ]  = array (
				'cmd'  => 'tar',
				'argc' => '-cf',
				'ext'  => 'tar'
			);
			$arcs[ 'extract' ][ 'application/x-tar' ] = array (
				'cmd'  => 'tar',
				'argc' => '-xf',
				'ext'  => 'tar'
			);
			$test                                     = exec('gzip --version', $o, $c);
			if ( $c == 0 )
			{
				$arcs[ 'create' ][ 'application/x-gzip' ]  = array (
					'cmd'  => 'tar',
					'argc' => '-czf',
					'ext'  => 'tgz'
				);
				$arcs[ 'extract' ][ 'application/x-gzip' ] = array (
					'cmd'  => 'tar',
					'argc' => '-xzf',
					'ext'  => 'tgz'
				);
			}
			$test = exec('bzip2 --version', $o, $c);
			if ( $c == 0 )
			{
				$arcs[ 'create' ][ 'application/x-bzip2' ]  = array (
					'cmd'  => 'tar',
					'argc' => '-cjf',
					'ext'  => 'tbz'
				);
				$arcs[ 'extract' ][ 'application/x-bzip2' ] = array (
					'cmd'  => 'tar',
					'argc' => '-xjf',
					'ext'  => 'tbz'
				);
			}
		}

		exec('zip --version', $o, $c);
		if ( $c == 0 )
		{
			$arcs[ 'create' ][ 'application/zip' ] = array (
				'cmd'  => 'zip',
				'argc' => '-r9',
				'ext'  => 'zip'
			);
		}

		exec('unzip --help', $o, $c);
		if ( $c == 0 )
		{
			$arcs[ 'extract' ][ 'application/zip' ] = array (
				'cmd'  => 'unzip',
				'argc' => '',
				'ext'  => 'zip'
			);
		}

		exec('rar --version', $o, $c);
		if ( $c == 0 || $c == 7 )
		{
			$arcs[ 'create' ][ 'application/x-rar' ]  = array (
				'cmd'  => 'rar',
				'argc' => 'a -inul',
				'ext'  => 'rar'
			);
			$arcs[ 'extract' ][ 'application/x-rar' ] = array (
				'cmd'  => 'rar',
				'argc' => 'x -y',
				'ext'  => 'rar'
			);
		}
		else
		{
			$test = exec('unrar', $o, $c);
			if ( $c == 0 || $c == 7 )
			{
				$arcs[ 'extract' ][ 'application/x-rar' ] = array (
					'cmd'  => 'unrar',
					'argc' => 'x -y',
					'ext'  => 'rar'
				);
			}
		}

		exec('7za --help', $o, $c);
		if ( $c == 0 )
		{
			$arcs[ 'create' ][ 'application/x-7z-compressed' ]  = array (
				'cmd'  => '7za',
				'argc' => 'a',
				'ext'  => '7z'
			);
			$arcs[ 'extract' ][ 'application/x-7z-compressed' ] = array (
				'cmd'  => '7za',
				'argc' => 'e -y',
				'ext'  => '7z'
			);

			if ( empty( $arcs[ 'create' ][ 'application/x-gzip' ] ) )
			{
				$arcs[ 'create' ][ 'application/x-gzip' ] = array (
					'cmd'  => '7za',
					'argc' => 'a -tgzip',
					'ext'  => 'tar.gz'
				);
			}
			if ( empty( $arcs[ 'extract' ][ 'application/x-gzip' ] ) )
			{
				$arcs[ 'extract' ][ 'application/x-gzip' ] = array (
					'cmd'  => '7za',
					'argc' => 'e -tgzip -y',
					'ext'  => 'tar.gz'
				);
			}
			if ( empty( $arcs[ 'create' ][ 'application/x-bzip2' ] ) )
			{
				$arcs[ 'create' ][ 'application/x-bzip2' ] = array (
					'cmd'  => '7za',
					'argc' => 'a -tbzip2',
					'ext'  => 'tar.bz'
				);
			}
			if ( empty( $arcs[ 'extract' ][ 'application/x-bzip2' ] ) )
			{
				$arcs[ 'extract' ][ 'application/x-bzip2' ] = array (
					'cmd'  => '7za',
					'argc' => 'a -tbzip2 -y',
					'ext'  => 'tar.bz'
				);
			}
			if ( empty( $arcs[ 'create' ][ 'application/zip' ] ) )
			{
				$arcs[ 'create' ][ 'application/zip' ] = array (
					'cmd'  => '7za',
					'argc' => 'a -tzip -l',
					'ext'  => 'zip'
				);
			}
			if ( empty( $arcs[ 'extract' ][ 'application/zip' ] ) )
			{
				$arcs[ 'extract' ][ 'application/zip' ] = array (
					'cmd'  => '7za',
					'argc' => 'e -tzip -y',
					'ext'  => 'zip'
				);
			}
			if ( empty( $arcs[ 'create' ][ 'application/x-tar' ] ) )
			{
				$arcs[ 'create' ][ 'application/x-tar' ] = array (
					'cmd'  => '7za',
					'argc' => 'a -ttar -l',
					'ext'  => 'tar'
				);
			}
			if ( empty( $arcs[ 'extract' ][ 'application/x-tar' ] ) )
			{
				$arcs[ 'extract' ][ 'application/x-tar' ] = array (
					'cmd'  => '7za',
					'argc' => 'e -ttar -y',
					'ext'  => 'tar'
				);
			}
		}

		$this->options[ 'archivers' ] = $arcs;
		foreach ( $this->options[ 'archiveMimes' ] as $k => $mime )
		{
			if ( !isset( $this->options[ 'archivers' ][ 'create' ][ $mime ] ) )
			{
				unset( $this->options[ 'archiveMimes' ][ $k ] );
			}
		}

		if ( empty( $this->options[ 'archiveMimes' ] ) )
		{
			$this->options[ 'archiveMimes' ] = array_keys($this->options[ 'archivers' ][ 'create' ]);
		}
	}

	/**
	 * Return files list in directory.
	 *
	 * @param  string $path dir path
	 * @return array
	 * @author Dmitry (dio) Levashov
	 * */
	protected function _scandir ( $path )
	{
		return $this->scanDir($path);
	}

	/**
	 * Get stat for folder content and put in cache
	 *
	 * @param  string $path
	 * @return void
	 * @author Dmitry (dio) Levashov
	 * */
	protected function cacheDir ( $path )
	{

		$this->dirsCache[ $path ] = array ();

		foreach ( $this->scanDir($path) as $p )
		{
			if ( ( $stat = $this->getStat($p) ) && empty( $stat[ 'hidden' ] ) )
			{
				$this->dirsCache[ $path ][ ] = $p;
			}
		}
	}

	/**
	 * Return required dir's files info.
	 * If onlyMimes is set - return only dirs and files of required mimes
	 *
	 * @param  string $path dir path
	 * @return array
	 * @author Dmitry (dio) Levashov
	 * */
	protected function getScandir ( $path )
	{

		$files = array ();

		!isset( $this->dirsCache[ $path ] ) && $this->cacheDir($path);

		foreach ( $this->dirsCache[ $path ] as $p )
		{
			if ( ( $stat = $this->stat($p) ) && empty( $stat[ 'hidden' ] ) )
			{
				$files[ ] = $stat;
			}
		}

		return $files;
	}

	/**
	 * Find folder by hash in required folder and subfolders
	 *
	 * @param string      $hash folder hash
	 * @param bool|string $path folder path to search in
	 * @return string
	 */
	public function _findDir ( $hash, $path = false )
	{


		if ( !$path )
		{
			$path = $this->root;
			if ( $this->_hash($path) === $hash || $hash === '' )
			{
				return $path;
			}
		}


		if ( substr($hash, -1) == '/' )
		{
			$hash = substr($hash, 0, -1);
		}


		$inputHash = $this->_hash($hash);

		if ( $this->_hash($path) === $this->_hash($hash) || $hash === '' )
		{
			return $path;
		}


		if ( false !== ( $ls = scandir($path) ) )
		{
			$max = count($ls);
			for ( $i = 0; $i < $max; $i++ )
			{
				$p = $path . DIRECTORY_SEPARATOR . $ls[ $i ];


				if ( is_link($p) && is_readable($p) )
				{
					$link = $this->_readlink($p);
					//$this->_result['debug']['findDir_'.$p] = 'link to '.$link;
				}

				if ( $this->_isAccepted($ls[ $i ]) && is_dir($p) && ( !is_link($p) ) )
				{
					if ( $this->_hash($p) === $hash )
					{
						return $p;
					}

					if ( is_readable($p) )
					{
						if ( $this->_hash($p) === $hash || false != ( $p = $this->_findDir($hash, $p) ) )
						{
							return $p;
						}
					}
				}
			}
		}
	}

	/**
	 * Return name for duplicated file/folder or new archive
	 *
	 * @param  string $f      file/folder name
	 * @param  string $suffix file name suffix
	 * @return string
	 */
	public function _uniqueName ( $f, $suffix = ' copy' )
	{

		$dir  = dirname($f);
		$name = basename($f);
		$ext  = '';

		if ( !is_dir($f) )
		{
			if ( preg_match('/\.(tar\.gz|tar\.bz|tar\.bz2|[a-z0-9]{1,4})$/i', $name, $m) )
			{
				$ext  = '.' . $m[ 1 ];
				$name = substr($name, 0, strlen($name) - strlen($m[ 0 ]));
			}
		}

		if ( preg_match('/(' . $suffix . ')(\d*)$/i', $name, $m) )
		{
			$i    = (int)$m[ 2 ];
			$name = substr($name, 0, strlen($name) - strlen($m[ 2 ]));
		}
		else
		{
			$name .= $suffix;
			$i = 0;
			$n = $dir . DIRECTORY_SEPARATOR . $name . $ext;
			if ( !file_exists($n) )
			{
				return $n;
			}
		}

		while ( $i++ <= 10000 )
		{
			$n = $dir . DIRECTORY_SEPARATOR . $name . $i . $ext;
			if ( !file_exists($n) )
			{
				return $n;
			}
		}

		return $dir . DIRECTORY_SEPARATOR . $name . md5($f) . $ext;
	}

	/**
	 * Find file/folder by hash in required folder
	 *
	 * @param string $hash file/folder hash
	 * @param string $path folder path to search in
	 *
	 * @return string
	 */
	public function _find ( $hash, $path )
	{

		if ( false != ( $ls = scandir($path) ) )
		{
			$max = count($ls);

			for ( $i = 0; $i < $max; $i++ )
			{
				if ( $this->_isAccepted($ls[ $i ]) )
				{
					$p = $path . DIRECTORY_SEPARATOR . $ls[ $i ];

					// echo '_find ' . $p . ' ' . $this->_hash($p) . '===' . $hash . '<br/>';
					if ( $this->_hash($p) == $hash )
					{
						$p = $path . DIRECTORY_SEPARATOR . $ls[ $i ];

						return $p;
					}
				}
				// else echo 'SKIP _find ' . $path . DIRECTORY_SEPARATOR . $ls[$i] . ' ' . $this->_hash($p) . '===' . $hash . '<br/>';
			}
		}
	}

	/**
	 * Return path of file on which link point to, if exists in root directory
	 *
	 * @param string $path symlink path
	 * @return string
	 * */
	public function _readlink ( $path )
	{

		$target = readlink($path);
		if ( '/' != substr($target, 0, 1) )
		{
			$target = dirname($path) . DIRECTORY_SEPARATOR . $target;
		}
		$target = $this->_normpath($target);
		$root   = $this->_normpath($this->root);

		return $target && file_exists($target) && 0 === strpos($target, $root) ? $target : false;
	}

	/**
	 * Return symlink target file
	 *
	 * @param  string $path link path
	 * @return string
	 */
	protected function readlink ( $path )
	{

		if ( !( $target = @readlink($path) ) )
		{
			return false;
		}

		if ( substr($target, 0, 1) != DIRECTORY_SEPARATOR )
		{
			$target = dirname($path) . DIRECTORY_SEPARATOR . $target;
		}

		$atarget = realpath($target);

		if ( !$atarget )
		{
			return false;
		}

		$root  = $this->root;
		$aroot = $this->aroot;

		if ( $this->_inpath($atarget, $this->aroot) )
		{
			return $this->_normpath($this->root . DIRECTORY_SEPARATOR . substr($atarget, strlen($this->aroot) + 1));
		}

		return false;
	}

	/**
	 * Count total directory size if this allowed in options
	 *
	 * @param string $path directory path
	 * @return int
	 * */
	public function _dirSize ( $path )
	{

		$size = 0;
		if ( !$this->options[ 'dirSize' ] || !$this->_isAllowed($path, 'read') )
		{
			return filesize($path);
		}
		if ( !isset( $this->options[ 'du' ] ) )
		{
			$this->options[ 'du' ] = function_exists('exec') ? exec('du -h ' . escapeshellarg(__FILE__), $o, $s) > 0 && $s == 0 : false;
		}
		if ( $this->options[ 'du' ] )
		{
			$size = (int)exec('du -k ' . escapeshellarg($path)) * 1024;
		}
		else
		{
			$ls = scandir($path);
			for ( $i = 0; $i < count($ls); $i++ )
			{
				if ( $this->_isAccepted($ls[ $i ]) )
				{
					$p = $path . DIRECTORY_SEPARATOR . $ls[ $i ];
					$size += filetype($p) == 'dir' && $this->_isAllowed($p, 'read') ? $this->_dirSize($p) : filesize($p);
				}
			}
		}

		return $size;
	}

	/**
	 * Return x/y coord for crop image thumbnail
	 *
	 * @param int $w image width
	 * @param int $h image height
	 * @return array
	 */
	public function _cropPos ( $w, $h )
	{

		$x    = $y = 0;
		$size = min($w, $h);
		if ( $w > $h )
		{
			$x = ceil(( $w - $h ) / 2);
		}
		else
		{
			$y = ceil(( $h - $w ) / 2);
		}

		return array (
			$x,
			$y,
			$size
		);
	}

	/**
	 * Return true if we can create thumbnail for file with this mimetype
	 *
	 * @param string $mime file mimetype
	 * @return bool
	 */
	public function _canCreateTmb ( $mime = '' )
	{

		if ( $this->options[ 'tmbPath' ] && $this->options[ 'imgLib' ] && strpos($mime, 'image/') !== false )
		{
			//if ('gd' == $this->options['imgLib'] || 'imagick' == $this->options['imgLib'] )
			//{
			return $mime === 'image/jpeg' || $mime === 'image/png' || $mime === 'image/gif';
			//}
		}


		return false;
	}

	/**
	 * Return image thumbnail path. For thumbnail return itself
	 *
	 * @param string $path image path
	 * @param bool   $isCoverflow
	 * @return string
	 */
	public function _tmbPath ( $path, $isCoverflow = false )
	{

		$crop = '';
		if ( $this->options[ 'tmbCrop' ] != false )
		{
			$crop = '-crop';
		}


		$tmb = '';
		if ( $this->options[ 'tmbPath' ] )
		{
			$tmb = dirname($path) != $this->options[ 'tmbPath' ] ? $this->options[ 'tmbPath' ] . DIRECTORY_SEPARATOR . $this->_hash($path) . ( $isCoverflow ? '-coverflow' : '' ) . $crop . '.png' : $path;
		}

		return $tmb;
	}

	/**
	 * Remove image thumbnail
	 *
	 * @param string $img image file
	 * @return void
	 */
	public function _rmTmb ( $img )
	{

		if ( $this->options[ 'tmbPath' ] && false != ( $tmb = $this->_tmbPath($img) ) && file_exists($tmb) )
		{
			@unlink($tmb);
		}
	}

}
