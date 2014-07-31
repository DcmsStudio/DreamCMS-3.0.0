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
 * @package     DreamCMS
 * @version     3.0.0 Beta
 * @category    Framework
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Metadata.php
 *
 */
class Document_Metadata extends Document_Abstract
{

    /**
     * @var null
     */
    protected $metadata = null;

	/**
	 * @var null
	 */
	protected $pagemeta = null;

    /**
     * @var null
     */
    protected $pagedata = null;

    /**
     * @var null
     */
    protected $type = null;

    /**
     *
     * @param boolean $isContent
     * @return Document_Metadata
     */
    public function setMetadataType( $isContent = true )
    {
        $this->type = ($isContent === true ? true : false);
        return $this;
    }

    /**
     *
     * @param array $data
     * @return Document_Metadata
     */
    public function initMetadata( array $data )
    {
        if ( $this->metadata === null )
        {
            $this->metadata = array();
        }

        foreach ( $this->tableCoreMetaFieldDefinition as $fieldname => $values )
        {
            if ( isset( $data[ $fieldname ] ) )
            {
                if ( isset( $values[ 'datatype' ] ) && $values[ 'datatype' ] === 'split' )
                {
                    $data[ $fieldname ] = explode( ',', $data[ $fieldname ] );
                }
                if ( !empty( $data[ $fieldname ] ) )
                    $this->metadata[ $fieldname ] = Library::maskContent( $data[ $fieldname ] );
            }
        }

        foreach ( $this->tableTranslationMetaDefinition as $fieldname => $values )
        {
            if ( isset( $data[ $fieldname ] ) )
            {
                if ( isset( $values[ 'datatype' ] ) && $values[ 'datatype' ] === 'split' )
                {
                    $data[ $fieldname ] = explode( ',', $data[ $fieldname ] );
                }

                if ( !empty( $data[ $fieldname ] ) )
                    $this->metadata[ $fieldname ] = Library::maskContent( $data[ $fieldname ] );
            }
        }

        if ( isset( $data[ $this->getPrimaryKey() ] ) )
        {
            $this->metadata[ 'contentid' ] = $data[ $this->getPrimaryKey() ];
        }


        return $this;
    }

    /**
     *
     * @return array metadata
     */
    public function getMetadata()
    {
        return ($this->type ? $this->metadata : $this->pagemeta);
    }

    /**
     * Set Metadata by Key
     * @param string $key
     * @param mixed $value default is null
     * @return Document_Metadata
     */
    public function set( $key, $value = null )
    {
        if ( $this->type )
        {
            $this->metadata[ $key ] = $value;
        }
        else
        {
            $this->pagemeta[ $key ] = $value;
        }

        return $this;
    }

    /**
     * Get Metadata by Key
     * @param string $key
     * @return
     */
    public function get( $key )
    {
        if ( $this->type )
        {
            return (isset( $this->metadata[ $key ] ) ? $this->metadata[ $key ] : null);
        }
        else
        {
            return (isset( $this->pagemeta[ $key ] ) ? $this->pagemeta[ $key ] : null);
        }
    }

}

?>