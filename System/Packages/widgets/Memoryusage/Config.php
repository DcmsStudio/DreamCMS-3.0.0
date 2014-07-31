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
 * @file         Config.php
 */

class Widget_Memoryusage_Config extends Widget
{

    public function getData()
    {
        $limit = ( intval( $this->getConfig( 'limit' ) ) > 0 ? intval( $this->getConfig( 'limit' ) ) : 10);
        $result = $this->db->query('SELECT l.*, u.username FROM %tp%logs AS l
									LEFT JOIN %tp%users AS u ON(u.userid=l.userid)
									ORDER BY time DESC LIMIT '.$limit)->fetchAll();

        foreach ( $result as &$r) {
            $r['message'] = htmlspecialchars($r['message']);
        }


        return $this->setWidgetData( array('mem' => $result) );
    }

}