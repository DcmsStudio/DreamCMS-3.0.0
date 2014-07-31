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
 * @file         Mysql.php
 */
class Smilies_Model_Mysql extends Model
{
    public function getSmilieGroups()
    {
        return $this->db->query( 'SELECT * FROM %tp%smilies WHERE type = 1 ORDER BY smilietitle ASC' )->fetchAll();
    }

    public function getSmilies($groupid = 0)
    {
        if ( $groupid )
        {
            return $this->db->query( 'SELECT * FROM %tp%smilies WHERE type = 0 AND groupid = ? ORDER BY smilieorder ASC', $groupid )->fetchAll();
        }

        return $this->db->query( 'SELECT * FROM %tp%smilies WHERE type = 0 ORDER BY smilieorder ASC' )->fetchAll();
    }

    public function getSmilieById($id) {
        return $this->db->query( 'SELECT * FROM %tp%smilies WHERE smilieid = ?', $id)->fetch();
    }


    /**
     * @param array $data
     * @return int
     */
    public function insert($data) {
        $this->db->insert('smilies', $data)->execute();
        return $this->db->insert_id();
    }


    public function update($id, $smiliecode, $smilieorder)
    {
        $this->db->query( 'UPDATE %tp%smilies SET smiliecode = ?, smilieorder = ? WHERE smilieid = ?', $smiliecode, $smilieorder, $id );
    }


}