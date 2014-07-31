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
 * @file         PingService.php
 */

class PingService {

    /**
     * @var PingService
     */
    protected static $_instanceObj = null;

    private $_dataURL = null;
    private $_dataTitle = null;

    private $_Server = null;
    private $_ServerPath = null;

    /**
     * @return null|PingService
     */
    public static function getInstance ()
    {

        if ( self::$_instanceObj === null )
        {
            self::$_instanceObj = new PingService();
        }

        return self::$_instanceObj;
    }

    /**
     * @param $url
     * @param $title
     * @return PingService
     */
    public function setData($url, $title)
    {
        $this->_dataURL = $url;
        $this->_dataTitle = $title;

        return $this;
    }

    /**
     * @param string $server
     * @param string $path
     * @return PingService
     */
    public function setServer($server, $path = '/') {

        $this->_Server = $server;
        $this->_ServerPath = $path;

        return $this;
    }

    /**
     * Sends pings to all of the ping site services.
     */
    public function genericPing()
    {
        $services = Settings::get('pingservices', '');
        $services = explode("\n", $services);
        foreach ( (array) $services as $service ) {
            $service = trim($service);
            if ( '' != $service )
            {
                $serviceServer = $service;
                $servicePath = '/';

                $test = @parse_url($service);
                if ( isset($test['path']) && !empty($test['path']) )
                {
                    $servicePath = $test['path'];
                    $serviceServer = ($test['path'] != '' && $test['path'] != '/' ? str_replace($test['path'], '', $service) : $service);
                }

                $valid = $this->setServer($serviceServer, $servicePath)->send();


                // add log if has error
                if (!$valid) {
                    // Library::log('Ping Updater could not send Ping!<br/>\nServer:'. $serviceServer.'<br/>\nPath: '.$servicePath .'<br/>\nData Url: '. $this->_dataURL .'<br/>\nTitle: '. $this->_dataTitle, 'warn');
                }

            }
        }
    }


    /**
     * @return bool
     * @throws BaseException
     */
    public function send()
    {

        if ( !$this->_Server || !$this->_ServerPath )
        {
            throw new BaseException('Could not send Ping. Server not set!');
        }

        if ( !$this->_dataURL || !$this->_dataTitle )
        {
            throw new BaseException('Could not send Ping. Data not set!');
        }

        if (!class_exists('IXR_Client', false))
        {
            include_once VENDOR_PATH .'ixr/class-IXR.php';
        }

        // using a timeout of 3 seconds should be enough to cover slow servers
        $client = new IXR_Client($this->_Server, ((!strlen(trim($this->_ServerPath)) || ('/' == $this->_ServerPath)) ? false : $this->_ServerPath));
        $client->timeout = 2;
        $client->useragent .= ' -- DreamCMS/'. VERSION;

        // when set to true, this outputs debug messages by itself
        $client->debug = false;

        $url = Settings::get('portalurl');
        $url = rtrim($url, '/\\') .'/';


        // remove first slash
        if (substr($this->_dataURL, 0,1) == '/')
        {
            $this->_dataURL = substr($this->_dataURL, 1);
        }

        // add website url if not exists
        if ( substr($this->_dataURL, 0, strlen($url) ) != $url ) {
            $this->_dataURL = $url . $this->_dataURL;
        }

        // add website title if not exists
        if ( !preg_match('#'.preg_quote(Settings::get('pagename'), '#').'#is', $this->_dataTitle) )
        {
            $this->_dataTitle = Settings::get('pagename') .' - '. $this->_dataTitle;
        }

        if ( !$client->query('weblogUpdates.extendedPing', $this->_dataTitle, $url, $this->_dataURL ) ) // then try a normal ping
        {
            Library::log('Send Ping to '.$this->_Server . (trim($this->_ServerPath) ? (substr($this->_ServerPath, 0, 1) !== '/' ? '/'.$this->_ServerPath : $this->_ServerPath) : '') .' Faild! (weblogUpdates.extendedPing)', 'warn' );

            if (!$client->query('weblogUpdates.ping', $this->_dataTitle, $this->_dataURL )) {
                Library::log('Send Ping to '.$this->_Server . (trim($this->_ServerPath) ? (substr($this->_ServerPath, 0, 1) !== '/' ? '/'.$this->_ServerPath : $this->_ServerPath) : '') .' Faild! (weblogUpdates.ping)', 'warn' );
                return false;
            }
            else {
                Library::log('Send Ping to '.$this->_Server . (trim($this->_ServerPath) ? (substr($this->_ServerPath, 0, 1) !== '/' ? '/'.$this->_ServerPath : $this->_ServerPath) : '') .' Done. (weblogUpdates.ping)' );
            }
        }
        else {
            Library::log('Send Ping to '.$this->_Server . (trim($this->_ServerPath) ? (substr($this->_ServerPath, 0, 1) !== '/' ? '/'.$this->_ServerPath : $this->_ServerPath) : '') .' Done. (weblogUpdates.extendedPing)' );
        }

        return true;
    }
}