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
 * @file         Show.php
 */

class Widget_Memoryusage_Show extends Widget
{

    public function getData()
    {
        $options[ 'mem_limit' ] = (int)ini_get( 'memory_limit' );
        $options[ 'mem_usage' ] = function_exists( 'memory_get_usage' ) ? round( memory_get_usage() / 1024 / 1024, 2 ) : 0;

        if ( !empty( $options[ 'mem_usage' ] ) && !empty( $options[ 'mem_limit' ] ) )
        {
            $options[ 'mem_percent' ] = round( $options[ 'mem_usage' ] / $options[ 'mem_limit' ] * 100, 0 );
        }

        return $this->setWidgetData( $options );
    }

}