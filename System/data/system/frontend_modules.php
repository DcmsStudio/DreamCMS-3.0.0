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
/**
 * Frontend core modules
 *
 */
$modtranslation[ 'sitemap' ]            = array(
        trans( 'Navigation' ) );
$modtranslation[ 'sitemap' ][ 'index' ] = array(
        trans( 'Sitemap' ),
        trans( 'Erzeugt eine Liste aller Seiten aus der Seitenstruktur.' ) );



$modtranslation[ 'user' ]                   = array( trans( 'Benutzer' ) );
$modtranslation[ 'user' ][ 'closeaccount' ] = array(
        trans( 'Konto schließen' ),
        trans( 'Erzeugt ein Formular zur Löschung eines Benutzerkontos.' ) );
$modtranslation[ 'user' ][ 'controlpanel' ] = array(
        trans( 'Kontroll Zentrum' ),
        trans( 'Kontroll Zentrum des Benutzers' ) );


$modtranslation[ 'user' ][ 'password' ] = array(
        trans( 'Passwort ändern' ),
        trans( 'Erzeugt ein Formular zum ändern des Passwortes eines Benutzerkontos.' ) );
$modtranslation[ 'user' ][ 'settings' ] = array(
        trans( 'Konto Einstellungen' ),
        trans( 'Erzeugt ein Formular mit den Einstellungen eines Benutzerkontos.' ) );
$modtranslation[ 'user' ][ 'avatar' ]   = array(
        trans( 'Benutzer Avatar/Profilbild' ),
        trans( 'Erzeugt ein Formular zur Änderung des Benutzer Bildes eines Benutzerkontos.' ) );
$modtranslation[ 'user' ][ 'signatur' ] = array(
        trans( 'Signatur' ),
        trans( 'Erzeugt ein Formular zum bearbeiten der Signatur eines Benutzerkontos.' ) );
$modtranslation[ 'user' ][ 'other' ]    = array(
        trans( 'Sonstige Benutzer-Einstellungen' ),
        trans( 'Erzeugt ein Formular zum bearbeiten sonstiger Einstellungen eines Benutzerkontos.' ) );

$modtranslation[ 'profile' ]            = array( trans( 'Benutzerprofil' ) );
$modtranslation[ 'profile' ][ 'index' ] = array(
        trans( 'Benutzerprofil' ),
        trans( 'Profilansicht des Benutzers eines Benutzerkontos.' ) );




$modtranslation[ 'auth' ]                   = array(
        trans( 'Anmeldung' ) );
$modtranslation[ 'auth' ][ 'index' ]        = array(
        trans( 'Login-Formular' ),
        trans( 'Erzeugt ein Anmeldeformular (Login).' ) );
$modtranslation[ 'auth' ][ 'logout' ]       = array(
        trans( 'Automatischer Logout' ),
        trans( 'Meldet einen Benutzer automatisch ab (Logout).' ) );
$modtranslation[ 'auth' ][ 'lostpassword' ] = array(
        trans( 'Passwort vergessen' ),
        trans( 'Erzeugt ein Formular zur Passwort-Anforderung.' ) );


$modtranslation[ 'register' ]             = array(
        trans( 'Registrierung' ) );
$modtranslation[ 'register' ][ 'index' ]  = array(
        trans( 'Registrierungs-Formular' ),
        trans( 'Erzeugt ein Formular zur Benutzerregistrierung.' ) );
$modtranslation[ 'register' ][ 'verify' ] = array(
        trans( 'Verifizierungs-Formular' ),
        trans( 'Erzeugt ein Formular zur Verifizierung eines Benutzerkontos.' ) );

$modtranslation[ 'messenger' ]            = array(
        trans( 'Private Nachrichten' ) );
$modtranslation[ 'messenger' ][ 'index' ] = array(
        trans( 'Übersicht der Privaten Nachrichten' ),
        trans( 'Erzeugt eine Übersicht der Privaten Nachrichten.' ) );

$modtranslation[ 'guestbook' ]            = array(
        trans( 'Gästebuch' ) );
// $modtranslation['guestbook']['usergbook'] = array(trans('Benutzer Gästebuch'), trans('Erzeugt ein Gästebuch welches den registrierten Benutzern zur verfügung gestellt wird.'));
$modtranslation[ 'guestbook' ][ 'index' ] = array(
        trans( 'Gästebuch' ),
        trans( 'Erzeugt ein Gästebuch.' ) );



$modtranslation[ 'forum' ]             = array(
        trans( 'Forum' ) );
$modtranslation[ 'forum' ][ 'index' ]  = array(
        trans( 'Forum' ),
        trans( 'Fügt der Seite ein Forum hinzu.' ) );
$modtranslation[ 'forum' ][ 'search' ] = array(
        trans( 'Forum Suche' ),
        trans( 'Fügt dem Forum ein Suchformular hinzu.' ) );


$modtranslation[ 'news' ]              = array(
        trans( 'Nachrichten' ) );
$modtranslation[ 'news' ][ 'index' ]   = array(
        trans( 'Nachrichtenliste' ),
        trans( 'Fügt der Seite eine Nachrichtenliste hinzu.' ) );
$modtranslation[ 'news' ][ 'show' ]    = array(
        trans( 'Nachrichtenleser' ),
        trans( 'Stellt einen einzelnen Nachrichtenbeitrag dar.' ) );
$modtranslation[ 'news' ][ 'archive' ] = array(
        trans( 'Nachrichtenarchiv' ),
        trans( 'Fügt der Seite ein Nachrichtenarchiv hinzu.' ) );


$modtranslation[ '_other' ]                = array(
        trans( 'Verschiedenes' ) );
$modtranslation[ '_other' ][ 'members' ]   = array(
        'index' => array(
                trans( 'Benutzer Liste' ),
                trans( 'Erzeugt eine Liste aller registrierten Benutzer.' ) ) );
$modtranslation[ '_other' ][ 'page' ]      = array(
        'index' => array(
                trans( 'Einfache Inhalte' ),
                trans( 'Ermöglicht es einfache Seiten zu verwalten.' ) ) );
$modtranslation[ '_other' ][ 'search' ]    = array(
        'index' => array(
                trans( 'Suchmaschine' ),
                trans( 'Fügt der Seite ein Suchformular hinzu.' ) ) );
$modtranslation[ '_other' ][ 'tracker' ]   = array(
        '' => array(
                trans( 'Statistik' ),
                trans( 'Dient zur Statistik auswertung.' ) ) );
$modtranslation[ '_other' ][ 'plugins' ]   = array(
        '' => array(
                trans( 'Plugins' ),
                trans( 'Dient zur Statistik auswertung.' ) ) );
$modtranslation[ '_other' ][ 'main' ]      = array(
        'index' => array(
                trans( 'Startseite' ),
                '' ) );
$modtranslation[ '_other' ][ 'printpage' ] = array( '' => array( trans( 'Druckansicht' ), '' ) );
?>