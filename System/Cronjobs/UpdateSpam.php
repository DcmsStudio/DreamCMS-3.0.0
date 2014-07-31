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
 * @file         UpdateSpam.php
 */

function getData($url, $toFile)
{
    $result = true;
    if ( $toFile )
    {
        $fp = fopen( $toFile, 'wb+' );
    }
    $ch   = curl_init();

    curl_setopt( $ch, CURLOPT_URL, $url  );
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "GET" );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $ch, CURLOPT_USERAGENT, "DreamCMS Spamprotection Updater 1.0" );

    if ( $toFile )
    {
        curl_setopt( $ch, CURLOPT_FILE, $fp );
        $result = curl_exec( $ch );
    }
    else
    {
        $result = curl_exec( $ch );
    }
    curl_close( $ch );

    if ( $toFile )
    {
        fclose( $fp );
        $c = @ob_get_clean();
        @ob_clean();
        $result = null;
    }

    return $result;
}




function zipFileErrMsg($errno)
{
    $zipFileFunctionsErrors = array(
        'ZIPARCHIVE::ER_MULTIDISK'   => 'Multi-disk zip archives not supported.',
        'ZIPARCHIVE::ER_RENAME'      => 'Renaming temporary file failed.',
        'ZIPARCHIVE::ER_CLOSE'       => 'Closing zip archive failed',
        'ZIPARCHIVE::ER_SEEK'        => 'Seek error',
        'ZIPARCHIVE::ER_READ'        => 'Read error',
        'ZIPARCHIVE::ER_WRITE'       => 'Write error',
        'ZIPARCHIVE::ER_CRC'         => 'CRC error',
        'ZIPARCHIVE::ER_ZIPCLOSED'   => 'Containing zip archive was closed',
        'ZIPARCHIVE::ER_NOENT'       => 'No such file.',
        'ZIPARCHIVE::ER_EXISTS'      => 'File already exists',
        'ZIPARCHIVE::ER_OPEN'        => 'Can\'t open file',
        'ZIPARCHIVE::ER_TMPOPEN'     => 'Failure to create temporary file.',
        'ZIPARCHIVE::ER_ZLIB'        => 'Zlib error',
        'ZIPARCHIVE::ER_MEMORY'      => 'Memory allocation failure',
        'ZIPARCHIVE::ER_CHANGED'     => 'Entry has been changed',
        'ZIPARCHIVE::ER_COMPNOTSUPP' => 'Compression method not supported.',
        'ZIPARCHIVE::ER_EOF'         => 'Premature EOF',
        'ZIPARCHIVE::ER_INVAL'       => 'Invalid argument',
        'ZIPARCHIVE::ER_NOZIP'       => 'Not a zip archive',
        'ZIPARCHIVE::ER_INTERNAL'    => 'Internal error',
        'ZIPARCHIVE::ER_INCONS'      => 'Zip archive inconsistent',
        'ZIPARCHIVE::ER_REMOVE'      => 'Can\'t remove file',
        'ZIPARCHIVE::ER_DELETED'     => 'Entry has been deleted',);

    foreach ( $zipFileFunctionsErrors as $constName => $errorMessage )
    {
        if ( defined( $constName ) and constant( $constName ) === $errno )
        {
            return $errorMessage;
        }
    }

    return 'unknown';
}

function extractZip($zipFile = '', $dirFromZip = '', $zipDir = null)
{
    static $x = 1;

    if (is_null($zipDir)) {
        Library::log( 'Spam Protection Updater can´t extract "'. $zipFile .'" to none directory', 'error');
        return false;
    }

    if ( !is_dir( $zipDir ) )
    {
        @mkdir( $zipDir );
    }

    $zip = zip_open( $zipFile );
    if ( $zip )
    {
        while ( $zip_entry = zip_read( $zip ) )
        {
            $entryName    = zip_entry_name( $zip_entry );
            $completePath = $zipDir . dirname( $entryName );
            $completeName = $zipDir . $entryName;
            $completePath = str_replace( '//', '/', $completePath );
            $completeName = str_replace( '//', '/', $completeName );

            $pos_last_slash = strrpos( $entryName, "/" );
            if ( $pos_last_slash !== false )
            {
                create_dirs( $zipDir . substr( $entryName, 0, $pos_last_slash + 1 ) );
            }


            if ( zip_entry_open( $zip, $zip_entry, "r" ) )
            {
                if ( !is_dir( $completeName ) )
                {
                    $fstream = zip_entry_read( $zip_entry, zip_entry_filesize( $zip_entry ) );
                    if ( substr( $completeName, -1 ) !== '/' && substr( $completeName, -1 ) !== '~' )
                    {
                        file_put_contents( $completeName, $fstream );
                    }
                }

                if ( is_file( $completeName ) )
                {
                    @chmod( $completeName, 0755 );
                }
                zip_entry_close( $zip_entry );
            }
        }
        zip_close( $zip );
    }
    else {
        return false;
    }

    return true;
}

$db = null;
$application = Registry::getObject( 'Application' );
if ( ($application instanceof Application ) )
{
    $db = $application->db;
}

function getIpAndCountryData($ip) {
    global $db;

    return $db->query( 'SELECT c.* FROM %tp%countries AS c
								LEFT JOIN %tp%ip2nation AS n ON(n.countryid = c.countryid)
								WHERE n.ip < INET_ATON(?)
								ORDER BY n.ip DESC LIMIT 1', $ip )->fetch();
}


if ( ($application instanceof Application ) )
{
    Library::makeDirectory(DATA_PATH .'cache/spamprotection/');
    Library::makeDirectory(DATA_PATH .'system/spamprotection/');


    if ( getData('http://www.stopforumspam.com/downloads/listed_ip_1.zip', DATA_PATH .'cache/iplist.zip' ) || file_exists(DATA_PATH .'cache/iplist.zip') )
    {
        if ( extractZip(DATA_PATH .'cache/iplist.zip', '', DATA_PATH .'cache/spamprotection/') ) {
            if ( is_file(DATA_PATH .'cache/spamprotection/listed_ip_1.txt'))
            {
                foreach( explode("\n", file_get_contents(DATA_PATH .'cache/spamprotection/listed_ip_1.txt')) as $ip) {
                    if ( trim($ip) ) {

                        $long_ip = ip2long($ip);

                        $r = $this->db->query( 'SELECT c.* FROM %tp%countries AS c
								LEFT JOIN %tp%ip2nation AS n ON(n.countryid = c.countryid)
								WHERE n.ip < INET_ATON(?)
								ORDER BY n.ip DESC LIMIT 1', $ip )->fetch();

                        $this->db->query( 'REPLACE INTO %tp%spammers (spammer_name,spammer_ip,spammer_iplong,spammer_mail,added,lastvisit,spammer_count,countryid,ispid)
						  VALUES (?,?,?,?,?,?,?,?,?)', '', $ip, $long_ip, '', TIMESTAMP, 0, 0, intval($r['countryid']), 0 );
                    }
                }

                @unlink(DATA_PATH .'cache/spamprotection/listed_ip_1.txt');

                Library::log('Spam Protection Updater has update the Spammer Database');
            }
            else {
                Library::log('Spam Protection Updater listed_ip_1.txt not found');
            }
        }

    }
    else {
        Library::log('Spam Protection Updater http://www.stopforumspam.com/downloads/listed_ip_1.zip not found', 'warn');
    }


    // load bad emails
    @unlink(DATA_PATH .'cache/spamprotection/listed_email.txt');

    if ( getData('http://www.stopforumspam.com/downloads/listed_email_1.zip', DATA_PATH .'cache/bademails.zip' ) || file_exists(DATA_PATH .'cache/bademails.zip') )
    {
        if ( extractZip(DATA_PATH .'cache/bademails.zip', '', DATA_PATH .'cache/spamprotection/') ) {
            if ( is_file(DATA_PATH .'cache/spamprotection/listed_email_1.txt'))
            {
                copy(DATA_PATH .'cache/spamprotection/listed_email_1.txt', DATA_PATH .'system/spamprotection/bademails.txt');
                @unlink(DATA_PATH .'cache/spamprotection/listed_email_1.txt');
                @unlink(DATA_PATH .'cache/bademails.zip');
                Library::log('Spam Protection Updater has update the Blacklisted Emails');
            }
            else {
                Library::log('Spam Protection Updater listed_email_1.txt not found');
            }
        }
    }
    else {
        Library::log('Spam Protection Updater http://www.stopforumspam.com/downloads/listed_email_1.zip not found', 'warn');
    }

    // load bad usernames
    @unlink(DATA_PATH .'cache/spamprotection/listed_username_1.txt');


    if ( getData('http://www.stopforumspam.com/downloads/listed_username_1.zip', DATA_PATH .'cache/badusernames.zip' ) || file_exists(DATA_PATH .'cache/badusernames.zip') )
    {
        if ( extractZip(DATA_PATH .'cache/badusernames.zip', '', DATA_PATH .'cache/spamprotection/') ) {
            if ( is_file(DATA_PATH .'cache/spamprotection/listed_username_1.txt'))
            {
                copy(DATA_PATH .'cache/spamprotection/listed_username_1.txt', DATA_PATH .'system/spamprotection/badusername.txt');
                @unlink(DATA_PATH .'cache/spamprotection/listed_username_1.txt');
                @unlink(DATA_PATH .'cache/badusernames.zip');

                Library::log('Spam Protection Updater has update the Blacklisted Usernames');
            }
            else {
                Library::log('Spam Protection Updater listed_username_1_all.txt not found');
            }
        }
    }
    else {
        Library::log('Spam Protection Updater http://www.stopforumspam.com/downloads/listed_username_1.zip not found', 'warn');
    }

}
else
{
    Library::log( 'Spam Updater could not update! The Controller is not in the Registry', 'warn' );
}