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
 * @package      Dashboard
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Dashboard_Config_Base
{

	/**
	 * @var array
	 */
	private static $controllerpermFrontend = array ();

	/**
	 * @var array
	 */
	private static $controllerpermBackend = array (
		'sessionhistory'  => array (
			true,
			true
		),
		'menu'            => array (
			true,
			false
		),
		'tree'            => array (
			true,
			true
		),
		'switchfirewall'  => array (
			true,
			true
		),
		'switchdebug'     => array (
			true,
			true
		),
		'checkversion'    => array (
			true,
			false
		),
		'checkalias'      => array (
			true,
			false
		),
		'savetoolbartabs' => array (
			true,
			false
		),
		'setpage'         => array (
			true,
			false
		),
		'index'           => array (
			false,
			false
		),
	);

	/**
	 *
	 *
	 *
	 * @param bool $getBackend default false
	 * @return array
	 */
	public static function getControllerPermissions ( $getBackend = false )
	{

		if ( !$getBackend )
		{
			return self::$controllerpermFrontend;
		}
		else
		{
			return self::$controllerpermBackend;
		}
	}

	/**
	 *
	 * @param boolean $getBackend
	 * @return array
	 */
	public static function getPermissions ( $getBackend = false )
	{

		if ( !$getBackend )
		{
			return null;
		}
		else
		{
			return array (
				'title'        => trans('Dashboard'),
				'access-items' => array (
					'sessionhistory' => array (
						trans('Sitzungshistorie sehen'),
						0
					),
					'tree'           => array (
						trans('Darf den Inhalts-Tree benutzen'),
						0
					),
					'switchdebug'    => array (
						trans('Darf den Debugger aktivieren/deaktivieren'),
						0
					),
					'switchfirewall' => array (
						trans('Darf die Firewall aktivieren/deaktivieren'),
						0
					),
				)
			);
		}
	}

	/**
	 * Used in Class SystemManager
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{

		return array (
			'modulelabel'       => trans('Dashboard'),
			'moduledescription' => null,
			'version'           => '0.1',
			'allowmetadata'     => false,
			'license'           => 'GPL v2',
			'copyright'         => '(c) 2013 by Marcel Domke'
		);
	}

	/**
	 *
	 */
	public static function registerBackedMenu ()
	{

		$menu = array (
			'label' => trans('Sicherheit'),
			'items' => array (
				array (
					'label'       => trans('Debugger an/abschalten'),
					'description' => null,
					'icon'        => null,
					'action'      => 'switchdebug',
					'extraparams' => '',
					'ajax'        => true
				),
				array (
					'label'       => trans('Firewall an/abschalten'),
					'description' => null,
					'icon'        => null,
					'action'      => 'switchfirewall',
					'extraparams' => '',
					'ajax'        => true
				),
			)
		);

		Menu::addMenuItem('system', 'dashboard', $menu);


		$menu = array (
			'label'       => trans('Hilfe'),
			'description' => null,
			'icon'        => null,
			'action'      => '',
			'extraparams' => '',
			#   'ajax'        => true
		);
		Menu::addMenuItem('help', 'help', $menu);
		Menu::addMenuItem('help', 'help', array (
		                                        'type' => 'separator'
		                                  ));
		$menu = array (
			'label'       => trans('About'),
			'description' => null,
			'icon'        => null,
			'action'      => 'credits',
			'extraparams' => '',
			#    'ajax'        => true
		);

		Menu::addMenuItem('help', 'help', $menu);
	}

	/**
	 * get all system config options
	 * find all modul settings by function getConfigItems in all modul model classes
	 *
	 * @return array
	 */
	public static function loadConfigOptions ()
	{

		$opts                        = array ();
		$opts[ 'basic' ][ 'global' ] = array (
			'label'       => trans('Allgemeine Einstellungen'),
			'description' => '',
			'icon'        => BACKEND_IMAGE_PATH . 'cfgitems/64x64/' . 'general.png',
			'published'   => true,
			'minwidth'    => 400,
			'minheight'   => 500,
			'items'       => array (
				'pagename'           => array (
					'label'       => trans('Titel der Website'),
					'type'        => 'text',
					'value'       => '',
					'maxlength'   => 200,
					'size'        => 70,
					'controls'    => true,
					'description' => trans('Geben Sie hier den Titel Ihrer Websize an.'),
				),
				'portalurl'          => array (
					'label'       => trans('URL zur Website'),
					'type'        => 'text',
					'value'       => '',
					'maxlength'   => 200,
					'size'        => 70,
					'controls'    => true,
					'rgxp'        => array (
						'url',
						trans('Dieses Url-Format ist nicht korrekt')
					),
					'description' => trans('Geben Sie hier die URL der Seite an. Angaben bitte ohne / am ende!!!'),
				),
				'frontpage'          => array (
					'label'       => trans('Eigene Startseite'),
					'type'        => 'text',
					'value'       => '',
					'maxlength'   => 200,
					'size'        => 70,
					'controls'    => false,
					'rgxp'        => array (
						'url',
						trans('Dieses Url-Format ist nicht korrekt')
					),
					'description' => trans('Geben Sie hier die URL der Seite an. Diese Angabe ist optional.'),
				),
				'websiteoffline'     => array (
					'label'       => trans('Website Offline schalten'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|\n0|" . trans('Nein') . '|checked',
					'description' => trans('Schaltet die Website Offline. Der Admin und die Benutzergruppen denen gestattet wurde, das diese die Website auch im Offline-Modus sehen dürfen, werden die Website weiterhin sehen.'),
				),
				'locale'             => array (
					'label'       => trans('Standart System Locale'),
					'type'        => 'select',
                    'controls'    => true,
					'value'       => 'de_DE',
					'values'      => self::getLocales(),
					'description' => '',
				),

				'mod_rewrite'        => array (
					'label'       => trans('URLs umschreiben'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|\n0|" . trans('Nein') . '|checked',
					'description' => trans('Statische URLs ohne das index.php-Fragment erzeugen. Für diese Funktion muss `mod_rewrite` verfügbar sein sowie die Datei `.htaccess.default` in `.htaccess` umbenannt und gegebenenfalls die RewriteBase angepasst werden.'),
				),
                'mod_rewrite_addpublic'        => array (
                    'label'       => trans('Public path hinzufügen'),
                    'type'        => 'radio',
                    'values'      => '1|' . trans('Ja') . "|\n0|" . trans('Nein') . '|checked',
                    'description' => trans('Fügt public/ zu URLs hinzu, wenn URLs Umschreibung (mod_rewrite) deaktiviert ist.'),
                ),

				'mod_rewrite_suffix' => array (
					'label'       => trans('Standart Seiten Suffix'),
					'type'        => 'select',
					'values'      => "|" . trans('Standart verwenden') . "|\nhtml|.html|checked\nxhtml|.xhtml|\ndcms|.dcms|\nphp|.php|",
					'description' => trans('Diese Angabe ist Optional. Wenn sie nichts wählen wird der Suffix automatisch auf `.html` gesetzt.'),
				),
				'twittername'        => array (
					'label'       => trans('Twitter Name'),
					'type'        => 'text',
					'value'       => '',
					'maxlength'   => 200,
					'size'        => 70,
					'controls'    => false,
					//'rgxp' => array('url', trans('Dieses Url-Format ist nicht korrekt')),
					'description' => trans('Geben Sie hier den Twitternamen für Ihre Seite an'),
				),
				'googleapikey'       => array (
					'label'       => trans('Google Api Key'),
					'type'        => 'text',
					'value'       => '',
					'maxlength'   => 200,
					'size'        => 70,
					'controls'    => false,
					//'rgxp' => array('url', trans('Dieses Url-Format ist nicht korrekt')),
					'description' => trans('Geben Sie hier den Google Api Schlüssel an'),
				),

                'pingservices' => array(
                    'label'       => trans('Ping Service'),
                    'type'        => 'textarea',
                    'value'       => '',
                    'controls'    => false,
                    //'rgxp' => array('url', trans('Dieses Url-Format ist nicht korrekt')),
                    'description' => trans('Wenn Sie einen Beitrag veröffentlichst, kann das DreamCMS verschiedene Dienste darüber informieren. Bitte trennen Sie mehrere URLs jeweils durch einen Zeilenumbruch.'),
                )
			)
		);

		$opts[ 'basic' ][ 'email' ] = array (
			'label'       => trans('Email Einstellungen'),
			'description' => '',
			'icon'        => BACKEND_IMAGE_PATH . 'cfgitems/64x64/' . 'mail.png',
			'published'   => true,
			'minwidth'    => 400,
			'minheight'   => 600,
			'items'       => array (
				'frommail'           => array (
					'label'       => trans('Absender eMail Adresse'),
					'rgxp'        => array (
						'email',
						trans('Dieses Emailformat ist nicht korrekt')
					),
					'type'        => 'text',
					'value'       => '',
					'maxlength'   => 200,
					'size'        => 70,
					'controls'    => true,
					'description' => trans('Geben Sie hier die eMail Adresse ein, die als Absender bei allen vom System automatisch verschickten eMails als Absender benutzt werden soll.'),
				),
				'webmastermail'      => array (
					'label'       => trans('Kontakt eMail Adresse'),
					'rgxp'        => array (
						'email',
						trans('Dieses Emailformat ist nicht korrekt')
					),
					'type'        => 'text',
					'value'       => '',
					'maxlength'   => 200,
					'size'        => 70,
					'controls'    => true,
					'description' => trans('Geben Sie hier die Kontakt eMail Adresse Ihrer Seite bzw. des Administrators an.'),
				),
				'use_email_debugger' => array (
					'label'  => trans('Email Debugger an?'),
					'type'   => 'radio',
					'values' => '1|' . trans('Ja') . "|\n0|" . trans('Nein') . '|checked',
				),
				'mailtype'           => array (
					'label'       => trans('Email Funktion'),
					'type'        => 'radio',
					'values'      => '0|' . trans('Standart PHP Mail Funktion') . "|checked\n1|" . trans('SMTP') . '|',
					'description' => trans('PHP Mail funktion oder die erweiterte Mail funktion?'),
				),
				'smtp_server'        => array (
					'label'        => trans('SMTP Server'),
					'type'         => 'text',
					'value'        => '',
					'maxlength'    => 200,
					'size'         => 70,
					'controls'     => false,
					'description'  => trans('Geben Sie hier die Adresse zu einem SMTP Server an.'),
					'fieldrequire' => 'mailtype'
				),
				'smtp_encryption'    => array (
					'label'        => trans('SMTP Verschlüsselung'),
					'type'         => 'radio',
					'values'       => 'none|' . trans('keine') . "|checked\nssl|" . trans('SSL') . "|\ntls|" . trans('TLS') . '|',
					'description'  => trans('Geben Sie hier den SMTP Server Port an.'),
					'fieldrequire' => 'mailtype'
				),
				'smtp_port'          => array (
					'label'        => trans('SMTP Server Port'),
					'type'         => 'text',
					'value'        => '25',
					'maxlength'    => 5,
					'size'         => 5,
					'controls'     => false,
					'description'  => trans('Geben Sie hier den SMTP Server Port an.'),
					'fieldrequire' => 'mailtype'
				),
				'smtp_user'          => array (
					'label'        => trans('SMTP Benutzer'),
					'type'         => 'text',
					'value'        => '',
					'maxlength'    => 80,
					'size'         => 70,
					'controls'     => false,
					'description'  => trans('Geben Sie hier den Benutzer zum SMTP Server an.'),
					'fieldrequire' => 'mailtype'
				),
				'smtp_password'      => array (
					'label'        => trans('SMTP Passwort'),
					'type'         => 'text',
					'value'        => '',
					'maxlength'    => 80,
					'size'         => 70,
					'controls'     => false,
					'description'  => trans('Geben Sie hier das Passwort zum SMTP Server an.'),
					'fieldrequire' => 'mailtype'
				)
			)
		);


		$opts[ 'security' ][ 'cookie' ] = array (
			'label'       => trans('Cookies'),
			'description' => '',
			'icon'        => BACKEND_IMAGE_PATH . 'cfgitems/64x64/' . 'cookies.png',
			'published'   => true,
			'minwidth'    => 400,
			'minheight'   => 300,
			'items'       => array (
				'cookiepath'   => array (
					'label'       => trans('Pfadangabe für Cookie'),
					'type'        => 'text',
					'value'       => '/',
					'maxlength'   => 200,
					'size'        => 70,
					'controls'    => true,
					'description' => trans('Geben Sie hier einen relativen Pfad zur eigentlichen Domain, auf der sich Ihr Portal befindet, an.'),
				),
				'cookiedomain' => array (
					'label'       => trans('Domainangabe für Cookie'),
					'type'        => 'text',
					'value'       => '',
					'maxlength'   => 200,
					'size'        => 70,
					'controls'    => false,
					'description' => trans('Hier können Sie angeben, unter welcher Domain die Cookies gespeichert werden sollen.'),
				),
				'cookie_timer' => array (
					'label'             => trans('Cookie Timeout'),
					'rgxp'              => array (
						'integer',
						trans('Der Wert darf nur aus Zahlen bestehen')
					),
					'type'              => 'text',
					'value'             => '3600',
					'maxlength'         => 11,
					'size'              => 30,
					'controls'          => false,
					'data-inputtrigger' => 'calctime',
			//		'onkeyup'           => 'Form.trigger($(this), \'calctime\', \'after\')',
			//		'onfocus'           => 'Form.trigger($(this), \'calctime\', \'after\')',
					'description'       => trans('Tragen Sie hier ein, wie lange ein Cookie gültig sein soll. Angaben in Sekunden. Bsp: 60*60 = 3600 Sec. (1h)'),
				),
			)
		);


		$opts[ 'security' ][ 'security' ] = array (
			'label'       => trans('Sicherheit'),
			'description' => '',
			'icon'        => BACKEND_IMAGE_PATH . 'cfgitems/64x64/' . 'security.png',
			'published'   => true,
			'minwidth'    => 400,
			'minheight'   => 350,
			'items'       => array (
				'usersession_timeout'   => array (
					'label'       => trans('Session Timeout (Frontend)'),
					'rgxp'        => array (
						'integer',
						trans('Der Wert darf nur aus Zahlen bestehen')
					),
					'type'        => 'text',
					'value'       => 3600,
					'maxlength'   => 10,
					'size'        => 30,
					'controls'    => true,
					'data-inputtrigger' => 'calctime',
				//	'onkeyup'     => 'Form.trigger($(this), \'calctime\', \'after\')',
				//	'onfocus'     => 'Form.trigger($(this), \'calctime\', \'after\')',
					'description' => trans('Wielange sollen Benutzer Sitzungen aktiv sein.'),
				),
				'adminsession_timeout'  => array (
					'label'       => trans('Session Timeout (Backend)'),
					'rgxp'        => array (
						'integer',
						trans('Der Wert darf nur aus Zahlen bestehen')
					),
					'type'        => 'text',
					'value'       => 3600,
					'maxlength'   => 10,
					'size'        => 30,
					'controls'    => true,
					'data-inputtrigger' => 'calctime',
				//	'onkeyup'     => 'Form.trigger($(this), \'calctime\', \'after\')',
				//	'onfocus'     => 'Form.trigger($(this), \'calctime\', \'after\')',
					'description' => trans('Wielange sollen Admin Sitzungen aktiv sein.'),
				),
				'minuserpasswordlength' => array (
					'label'       => trans('Minimale Passwort länge'),
					'rgxp'        => array (
						'integer',
						trans('Der Wert darf nur aus Zahlen bestehen')
					),
					'type'        => 'text',
					'value'       => 6,
					'maxlength'   => 2,
					'size'        => 30,
					'controls'    => true,
					'description' => trans('Wie lang muss ein Passwort mindestens sein.'),
				),
				'crypt_key'             => array (
					'label'       => trans('Hashwert für Verschlüsselung'),
					'type'        => 'text',
					'value'       => md5(time()),
					'maxlength'   => 128,
					'size'        => 70,
					'description' => trans('<strong>Verschlüsselte Daten können nur mit demselben Hashwert wieder entschlüsselt werden!!!<br/>Bitte notieren Sie sich diesen Schlüssel.</strong>'),
				),
				'cli_key'               => array (
					'label'       => trans('Hashwert für Cronjobs'),
					'type'        => 'text',
					'value'       => md5(time()),
					'maxlength'   => 128,
					'size'        => 70,
					'controls'    => true,
					'description' => trans('Dieser Wert darf in keinem falle leer sein!!!'),
				),
				'block_failed_logins'   => array (
					'label'       => trans('User Sperren nach mehreren fehlerhafteb Logins'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|\n0|" . trans('Nein') . '|checked',
					'description' => '',
				),
				'max_failed_logins'     => array (
					'label'        => trans('Fehlgeschlagene Login Limit'),
					'type'         => 'text',
					'value'        => '5',
					'maxlength'    => 2,
					'size'         => 4,
					'description'  => '',
					'rgxp'         => array (
						'integer',
						trans('Der Wert darf nur aus Zahlen bestehen')
					),
					'fieldrequire' => 'block_failed_logins'
				),
				'failed_login_timeout'  => array (
					'label'        => trans('Dauer der Sperre'),
					'type'         => 'select',
					'value'        => '10',
					'values'       => "5|" . sprintf(trans('%s Minuten'), 5) . "|\n10|" . sprintf(trans('%s Minuten'), 10) . "|checked\n15|" . sprintf(trans('%s Minuten'), 15) . "|\n30|" . sprintf(trans('%s Minuten'), 30) . "|\n60|" . sprintf(trans('%s Minuten'), 60) . "|\n1440|" . sprintf(trans('%s Tag'), 1) . "|\n4320|" . sprintf(trans('%s Tage'), 3) . "|\n-1|" . trans('dauerhaft') . "|\n",
					'description'  => trans('Tragen Sie hier ein, wie lange ein Cookie gültig sein soll. Angaben in Sekunden. Bsp: 60*60 = 3600 Sec. (1h)'),
					'fieldrequire' => 'block_failed_logins'
				),
			)
		);


		$opts[ 'basic' ][ 'cachetimes' ] = array (
			'label'       => trans('Zwischenspeicher Zeiten'),
			'description' => '',
			'icon'        => BACKEND_IMAGE_PATH . 'cfgitems/64x64/' . 'hourglass.png',
			'published'   => true,
			'minwidth'    => 400,
			'minheight'   => 250,
			'items'       => array (
				'versioning_period'        => array (
					'label'       => trans('Speicherzeit für Versionen'),
					'rgxp'        => array (
						'integer',
						trans('Der Wert darf nur aus Zahlen bestehen')
					),
					'type'        => 'text',
					'value'       => '7776000',
					'maxlength'   => 11,
					'size'        => 40,
					'controls'    => true,
					'data-inputtrigger' => 'calctime',
			//		'onkeyup'     => 'Form.trigger($(this), \'calctime\', \'after\')',
			//		'onfocus'     => 'Form.trigger($(this), \'calctime\', \'after\')',
					'description' => trans('Hier können Sie die Speicherzeit für verschiedene Versionen eines Datensatzes in Sekunden eingeben (90 Tage = 7776000 Sekunden)'),
				),
				'system_cachetimeout'      => array (
					'label'       => trans('Speicherzeit für den System Cache'),
					'rgxp'        => array (
						'integer',
						trans('Der Wert darf nur aus Zahlen bestehen')
					),
					'type'        => 'text',
					'value'       => '99600',
					'maxlength'   => 11,
					'size'        => 40,
					'controls'    => true,
					'data-inputtrigger' => 'calctime',
			//		'onkeyup'     => 'Form.trigger($(this), \'calctime\', \'after\')',
			//		'onfocus'     => 'Form.trigger($(this), \'calctime\', \'after\')',
					'description' => trans('Hier können Sie die Speicherzeit für den System Cache in Sekunden eingeben (90 Tage = 7776000 Sekunden)'),
				),
				'sharcounterupdate'        => array (
					'label'       => trans('Speicherzeit für die Social Share Counter'),
					//'rgxp'        => array( 'integer', trans( 'Der Wert darf nur aus Zahlen bestehen' ) ),
					'type'        => 'text',
					'value'       => '3600',
					'maxlength'   => 6,
					'size'        => 40,
					'controls'    => false,
					'data-inputtrigger' => 'calctime',
			//		'onkeyup'     => 'Form.trigger($(this), \'calctime\', \'after\')',
			//		'onfocus'     => 'Form.trigger($(this), \'calctime\', \'after\')',
					'description' => trans('Hier können Sie die Speicherzeit für die Social Share Counter angeben. Achtung bei neuem Share wird der Cache so lange nicht gelöscht bis die Cachezeit erreicht wurde. Daher bei bedarf den Cache manuell leeren'),
				),
				'pagedefaultenablecaching' => array (
					'label'       => trans('Caching'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|\n0|" . trans('Nein') . '|checked',
					'description' => trans('Aktiviert den Seiten Cache.'),
				),
				'pagedefaultcachetime'     => array (
					'require'      => 'pagedefaultenablecaching',
					'label'        => trans('Cachezeit'),
					'type'         => 'text',
					'value'        => '3600',
					'maxlength'    => 10,
					'size'         => 40,
					'data-inputtrigger' => 'calctime',
			//		'onkeyup'      => 'Form.trigger($(this), \'calctime\', \'after\')',
			//		'onfocus'      => 'Form.trigger($(this), \'calctime\', \'after\')',
					'description'  => trans('Wie lange soll eine Seite im Cache behalten werden bis diese neu generiert wird?'),
					'fieldrequire' => 'pagedefaultenablecaching'
				),
			)
		);


		$opts[ 'output' ][ 'metatags' ] = array (
			'label'       => trans('Metatags'),
			'description' => trans('HIerbei handelt es sich um die Standartwerte die eingetragen werden, wenn zu einer Seite keine Metadaten hinterlegt wurden.'),
			'icon'        => BACKEND_IMAGE_PATH . 'cfgitems/64x64/' . 'seo_optimization.png',
			'published'   => true,
			'minwidth'    => 500,
			'minheight'   => 500,
			'items'       => array (
				'meta_copyright'    => array (
					'label'       => trans('Copyright'),
					'type'        => 'text',
					'value'       => '',
					'maxlength'   => 150,
					'size'        => 70,
					'description' => '',
				),
				'meta_author'       => array (
					'label'       => trans('Author'),
					'type'        => 'text',
					'value'       => '',
					'maxlength'   => 150,
					'size'        => 70,
					'description' => ''
				),
				'meta_revisitafter' => array (
					'label'       => trans('Revisit After'),
					'type'        => 'text',
					'value'       => '10 Days',
					'maxlength'   => 10,
					'size'        => 40,
					'description' => ''
				),
				'meta_robots'       => array (
					'label'       => trans('Suchmaschienen'),
					'type'        => 'select',
					'value'       => 'index,follow',
					'values'      => 'nofollow|' . trans('Links nicht folgen') . "|\nindex,nofollow|" . trans('Indizeieren aber Links nicht folgen') . "|\nindex,follow|" . trans('Indizeieren und Links folgen') . "|checked",
					'description' => ''
				),
				'meta_description'  => array (
					'label'       => trans('Beschreibung'),
					'type'        => 'textarea',
					'value'       => '',
					'maxlength'   => 500,
					'rows'        => 3,
					'description' => '',
				),
				'meta_keywords'     => array (
					'label'       => trans('Keywords'),
					'type'        => 'text',
					'value'       => '',
					'maxlength'   => 300,
					'size'        => 70,
					'description' => trans('Bitte mit komma trennen.'),
				),
			)
		);


		$opts[ 'basic' ][ 'texts' ] = array (
			'label'       => trans('Texte'),
			'description' => '',
			'icon'        => BACKEND_IMAGE_PATH . 'cfgitems/64x64/' . 'disclaimer.png',
			'published'   => true,
			'minwidth'    => 500,
			'minheight'   => 450,
			'height'      => 450,
			'items'       => array (
				'disclaimer_text' => array (
					'label'       => trans('Disclaimer'),
					'type'        => 'richtext',
					'toolbarpos'  => 'internal',
					'value'       => '',
					'description' => trans('Unsere Bedingungen.'),
				),
			)
		);


		$opts[ 'basic' ][ 'performance' ] = array (
			'label'       => trans('Performance'),
			'description' => '',
			'icon'        => BACKEND_IMAGE_PATH . 'cfgitems/64x64/' . 'chronometer.png',
			'published'   => true,
			'minwidth'    => 400,
			'minheight'   => 300,
			'items'       => array (
				'use_cache_system' => array (
					'label'       => trans('Cache System verwenden'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|checked\n0|" . trans('Nein') . '|',
					'description' => trans('Mit dieser Funktion spart man sich sehr viele Datenbank Abfragen.'),
				),
				'compress_js'      => array (
					'label'       => trans('Javascripts komprimieren'),
					'type'        => 'checkbox',
					'values'      => '1|' . trans('Ja') . '|',
					'description' => trans('Diese Funktion comprimiert alle Javascripts.'),
				),
				'gzip'             => array (
					'label'       => trans('GZip Komprimierung aktivieren'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|checked\n0|" . trans('Nein') . '|',
					'description' => trans('Geben Sie hier an, ob die Ausgabe der Seiten des mit GZip komprimiert werden soll. Dies kann den Trafficverbrauch erheblich verkleinern.'),
				),
				'gziplevel'        => array (
					'require'      => 'gzip',
					'label'        => trans('GZip Komprimierungslevel'),
					'type'         => 'select',
					'value'        => 3,
					'values'       => '1|1 ' . trans('schwach') . '|
2|2|
3|3 ' . trans('optimal') . '|
4|4|
5|5|
6|6|
7|7|
8|8|
9|9 ' . trans('sehr stark') . '|',
					'description'  => trans('Geben Sie hier an, ob die Ausgabe der Seiten des mit GZip komprimiert werden soll. Dies kann den Trafficverbrauch erheblich verkleinern.'),
					'fieldrequire' => 'gzip'
				),
			)
		);


		$opts[ 'output' ][ 'output' ] = array (
			'label'       => trans('HTML Ausgabe'),
			'description' => '',
			'icon'        => BACKEND_IMAGE_PATH . 'cfgitems/64x64/' . 'html.png',
			'published'   => true,
			'minwidth'    => 400,
			'minheight'   => 450,
			'items'       => array (
				'compress_html'      => array (
					'label'       => trans('Html Code Comprimiert ausgeben'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|\n0|" . trans('Nein') . '|checked',
					'description' => trans('Hiermit ist es möglich den komplettrn HTML Code zu komprimieren. Textareas, JavaScripts und Pre Elemente werden nicht kompimiert!'),
				),
				'pretty_html'        => array (
					'label'       => trans('HTML Code sauber formatieren'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|\n0|" . trans('Nein') . '|checked',
					'description' => trans('Hiermit ist es möglich den Ausgegeben HTML Code sauber und lesbasr zu formatiern. Diese Funktion hat keine Auswirkung, wenn Sie den HTML Code Comprimiert ausgeben. ACHTUNG: kleine Performance einbuße!'),
				),
                /*
				'sendheaders'        => array (
					'label'       => trans('Standard headers senden'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|checked\n0|" . trans('Nein') . '|',
					'description' => trans('Bei einigen Webservern führt diese Option zu Problemen - bei anderen wird sie benötigt. Hier gilt es auszuprobieren.'),
				),
                */
				'sendnocacheheaders' => array (
					'label'       => trans('Browser Caching deaktivieren'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|\n0|" . trans('Nein') . '|checked',
					'description' => trans('Mit dieser Option können Sie das Caching der Seite im Browser verhindern.'),
				),
			)
		);


		$opts[ 'output' ][ 'datetime' ] = array (
			'label'       => trans('Datum & Zeit Formate'),
			'description' => '',
			'icon'        => BACKEND_IMAGE_PATH . 'cfgitems/64x64/' . 'date-time.png',
			'published'   => true,
			'minwidth'    => 400,
			'minheight'   => 400,
			'items'       => array (
				'dateformat'             => array (
					'label'       => trans('Datumsformat'),
					'type'        => 'text',
					'value'       => 'd.m.Y',
					'maxlength'   => 10,
					'size'        => 40,
					'description' => trans('Geben Sie hier das Standard-Datumsformat an.'),
				),
				'timeformat'             => array (
					'label'       => trans('Zeitformat'),
					'type'        => 'text',
					'value'       => 'H:i:s',
					'maxlength'   => 10,
					'size'        => 40,
					'description' => trans('Geben Sie hier das Standard-Zeitformat an.'),
				),
				'default_timezoneoffset' => array (
					'label'       => trans('Zeitzone'),
					'type'        => 'select',
					'values'      => self::getTimezones(),
					'description' => trans('Geben Sie hier den Unterschied in Stunden zur GMT Zeitzone an.'),
				),
				'default_startweek'      => array (
					'label'       => trans('Start der Woche'),
					'type'        => 'radio',
					'value'       => '1',
					'values'      => self::getDayNames(),
					'description' => trans('Geben Sie hier an, mit welchem Tag die Woche standardmäßig startet.'),
				),
			)
		);


		$opts[ 'content' ][ 'content' ] = array (
			'label'       => trans('Inhalte'),
			'description' => trans('Legen Sie hier die Standart Einstellungen fest die beim erstellen eines Dokumentes schon aktiv sein sollen.'),
			'icon'        => BACKEND_IMAGE_PATH . 'cfgitems/64x64/' . 'document.png',
			'published'   => true,
			'minwidth'    => 400,
			'minheight'   => 430,
			'items'       => array (
				'autolock'                => array (
					'label'       => trans('Bei der Bearbeitung von Inhalten diese automatisch Offline schalten'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|\n0|" . trans('Nein') . '|checked',
					'description' => trans('Inhalte können hiermit automatisch offline geschalten werden.'),
				),
				'pagedefaultclickanalyse' => array (
					'label'       => trans('Klick-Analyse'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|\n0|" . trans('Nein') . '|checked',
					'description' => trans('Mit dieser Option können Sie Analysieren wo ein Nutzer klickt um so den Inhalt auf deren Wünschen anzupassen oder auch festzustellen wo die Seite noch optimiert werden kann.'),
				),
				'pagedefaultsearchable'   => array (
					'label'       => trans('Durchsuchbarkeit'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|\n0|" . trans('Nein') . '|checked',
					'description' => trans('Mit dieser Option können Sie festlegen ob ein Dokument durchsuchbar ist oder nicht. Gilt nur beim neu erstellen eines Dokumentes.'),
				)
			)
		);
        $opts[ 'content' ][ 'write' ] = array (
            'label'       => trans('Schreiben'),
            'description' => trans('Legen Sie hier die Standart Einstellungen fest die beim erstellen eines Dokumentes schon aktiv sein sollen.'),
            'icon'        => BACKEND_IMAGE_PATH . 'cfgitems/64x64/' . 'document-write.png',
            'published'   => true,
            'minwidth'    => 400,
            'minheight'   => 430,
            'items'       => array (
                'pingservices' => array (
                    'label'       => trans('Update'),
                    'type'        => 'textarea',
                    'value'       => 'http://rpc.pingomatic.com/',
                    'rows'        => 5,
                    'description' => trans('Wenn Sie einen Beitrag veröffentlichen, kann das DreamCMS verschiedene Dienste darüber informieren. Bitte trenne mehrere URLs jeweils durch einen Zeilenumbruch.')
                )
            )
        );
        $opts[ 'content' ][ 'comments' ] = array (
            'label'       => trans('Kommentare'),
            'description' => trans('Legen Sie hier die Standart Einstellungen für Kommentare fest.'),
            'icon'        => BACKEND_IMAGE_PATH . 'cfgitems/64x64/' . 'comments.png',
            'published'   => true,
            'minwidth'    => 400,
            'minheight'   => 430,
            'items'       => array (

                'commentmustmoderate'   => array (
                    'label'       => trans('Kommentare Allgemein'),
                    'type'        => 'checkbox',
                    'values'      => '1|' . trans('Der Kommentar muss manuell bestätigt werden') . '|',
                    'description' => ''//trans('Mit dieser Option können Sie festlegen ob ein Dokument durchsuchbar ist oder nicht. Gilt nur beim neu erstellen eines Dokumentes.'),
                ),

                'emailnotifieroncomment'   => array (
                   // 'label'       => trans('Mir eine E-Mail senden, wenn'),
                    'type'        => 'checkbox',
                    'values'      => '1|' . trans('Mir eine E-Mail senden, wenn jemand einen Kommentar schreibt.') . "|",
                    'description' => ''//trans('Mit dieser Option können Sie festlegen ob ein Dokument durchsuchbar ist oder nicht. Gilt nur beim neu erstellen eines Dokumentes.'),
                ),
                'emailnotifieroncommentwait'   => array (
                    // 'label'       => trans('Mir eine E-Mail senden, wenn'),
                    'type'        => 'checkbox',
                    'values'      => '1|' . trans('Mir eine E-Mail senden, ein Kommentar auf Freischaltung wartet.') . "|",
                    'description' => ''//trans('Mit dieser Option können Sie festlegen ob ein Dokument durchsuchbar ist oder nicht. Gilt nur beim neu erstellen eines Dokumentes.'),
                ),
                'moderatecommentsifmorelinks'   => array (
                    'label'       => trans('Einen Kommentar in die Warteschlange schieben, wenn er mehr als X Links enthält'),
                    'type'        => 'text',
                    'values'      => '',
                    'rgxp'         => array (
                        'integer',
                        trans('Der Wert darf nur aus Zahlen bestehen')
                    ),
                    'description' => trans('Eine hohe Anzahl von Links ist ein typisches Merkmal von Kommentar-Spam.'),
                ),
                'blockuserifhaspostspam'   => array (
                    // 'label'       => trans('Mir eine E-Mail senden, wenn'),
                    'type'        => 'checkbox',
                    'values'      => '1|' . trans('Wenn ein Registrierter Benutzer einen Kommentar geschrieben hat, dieser aber als Spam markiert wurde soll der Benutzer automatisch gesperrt werden.') . "|",
                    'description' => ''//trans('Mit dieser Option können Sie festlegen ob ein Dokument durchsuchbar ist oder nicht. Gilt nur beim neu erstellen eines Dokumentes.'),
                ),
                'emailnotifieronblockuser'   => array (
                    // 'label'       => trans('Mir eine E-Mail senden, wenn'),
                    'type'        => 'checkbox',
                    'values'      => '1|' . trans('Mir eine E-Mail senden, wenn ein Benutzer durch Kommentar-Spam blockiert wurde.') . "|",
                    'description' => ''//trans('Mit dieser Option können Sie festlegen ob ein Dokument durchsuchbar ist oder nicht. Gilt nur beim neu erstellen eines Dokumentes.'),
                ),
                'badcommentwords' => array (
                    'label'       => trans('Kommentar-Blacklist'),
                    'type'        => 'textarea',
                    'value'       => '',
                    'rows'        => 5,
                    'description' => trans('Wenn in einem Kommentar im Inhalt, Namen, URL, E-Mail-Adresse oder IP eines der unten aufgeführten Wörter oder Werte vorkommt, dann wird er als Spam markiert. Ein Wort oder IP-Adresse pro Zeile. Wortteile werden auch berücksichtigt, also wird durch "dream" auch "DreamCMS" gefiltert.')
                )
            )
        );
		/**
		 *  Read all other Modul Configurations :)
		 *
		 * @see News_Config_Base::getConfigItems
		 */
		$_applicationInst = Registry::getObject('Application');
		$modules          = $_applicationInst->getModuleNames();

		foreach ( $modules as $module )
		{
			$className = ucfirst(strtolower($module)) . '_Config_Base';

			if ( checkClassMethod($className . '/getConfigItems', 'static') )
			{
				$rs = $_applicationInst->getModulRegistry($module);
				$r  = $rs[ 'definition' ];


				// '../Modules/' . CONTROLLER
				$_mod = ucfirst(strtolower($module));
				if ( file_exists(MODULES_PATH . $_mod . '/Resources/' . $_mod . '_32x32.png') )
				{
					$r[ 'icon' ] = '../Modules/' . $_mod . '/Resources/' . $_mod . '_32x32.png';
				}


				$opts[ 'modules' ][ strtolower($module) ]                  = array ();
				$opts[ 'modules' ][ strtolower($module) ][ 'label' ]       = $r[ 'modulelabel' ];
				$opts[ 'modules' ][ strtolower($module) ][ 'description' ] = $r[ 'moduledescription' ];
				$opts[ 'modules' ][ strtolower($module) ][ 'icon' ]        = !empty( $r[ 'icon' ] ) ? $r[ 'icon' ] : '';

				$opts[ 'modules' ][ strtolower($module) ] = array_merge($opts[ 'modules' ][ strtolower($module) ], call_user_func($className . '::getConfigItems', true));
			}
		}

		/**
		 *  Read all Plugin Configurations :)
		 *
		 * @see Plugin_xyz_Config_Base::getConfigItems
		 */
		$plugins = Plugin::getInteractivePlugins();
		foreach ( $plugins as $plugin => $r )
		{
			if ( $r[ 'published' ] )
			{

				$className = 'Addon_' . ucfirst(strtolower($plugin)) . '_Config_Base';

				$definition = false;
				// Read modul definitions
				if ( checkClassMethod($className . '/getModulDefinition', 'static') )
				{
					$definition = call_user_func($className . '::getModulDefinition', true);
				}

				if ( $definition && checkClassMethod($className . '/getConfigItems', 'static') )
				{
					$r = $definition;


					$_mod = ucfirst(strtolower($plugin));
					if ( file_exists(PLUGIN_PATH . $_mod . '/Resources/' . $_mod . '_32x32.png') )
					{
						$r[ 'icon' ] = PLUGIN_URL_PATH . $_mod . '/Resources/' . $_mod . '_32x32.png';
					}

					$opts[ 'plugin' ][ strtolower($plugin) ]                  = array ();
					$opts[ 'plugin' ][ strtolower($plugin) ][ 'label' ]       = $r[ 'modulelabel' ];
					$opts[ 'plugin' ][ strtolower($plugin) ][ 'description' ] = $r[ 'moduledescription' ];
					$opts[ 'plugin' ][ strtolower($plugin) ][ 'icon' ]        = !empty( $r[ 'icon' ] ) ? $r[ 'icon' ] : '';

					$opts[ 'plugin' ][ strtolower($plugin) ] = array_merge($opts[ 'plugin' ][ strtolower($plugin) ], call_user_func($className . '::getConfigItems', true));
				}
			}
		}


		return $opts;
	}

	/**
	 *
	 * @return string
	 */
	private static function getLocales ()
	{


		$values = '|---------------|checked' . "\n";
		$sql    = "SELECT code, title FROM %tp%locale ORDER BY title";
		$locres = Database::getInstance()->query($sql)->fetchAll();
		if ( is_array($locres) )
		{
			foreach ( $locres as $idx => $locr )
			{
				$values .= $locr[ 'code' ] . '|' . str_replace('|', '', $locr[ 'title' ]) . "|\n";
			}
		}

		return $values;
	}

	/**
	 *
	 * @return string
	 */
	private static function getTimezones ()
	{

		$timezones = Locales::getTimezones(false);

		$values = '';
		foreach ( $timezones as $idx => $title )
		{
			$values .= $idx . '|' . str_replace('|', '', $title) . "|\n";
		}

		return $values;
	}

	/**
	 *
	 * @return string
	 */
	private static function getDayNames ()
	{

		$timezones = Locales::getTimezones();

		$values = '';
		for ( $i = 0; $i < 7; $i++ )
		{
			$values .= $i . '|' . Locales::getDayName($i, true) . "|\n";
		}

		return $values;
	}

}

?>