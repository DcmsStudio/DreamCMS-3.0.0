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
 * @package      Forms
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Editform.php
 */
class Forms_Action_Editform extends Controller_Abstract
{

    public function execute()
    {

        if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
        {
            $this->_edit( true );
        }
    }

    /**
     * @param bool $isForm
     */
    public function _edit($isForm = false)
    {

        $formid = (int)HTTP::input( 'formid' );

        if ( $isForm )
        {

        }
        else
        {
            $id = (int)HTTP::input( 'field_id' );
        }


        $model = Model::getModelInstance( 'forms' );


        if ( HTTP::post( 'send' ) )
        {
            demoadm();

            $data   = $this->_post();
            $errors = array();
            $errors = $this->validate( $data, $isForm, ($id ? $id : $formid) );

            if ( !count( $errors ) )
            {
                $newid = $model->saveData( $data, $isForm );

                if ( $isForm )
                {
                    if ( $newid )
                    {
                        echo Library::json( array(
                            'success' => true,
                            'msg'     => trans( 'Formular wurde erfolgreich hinzugefügt' ),
                            'newid'   => $newid
                        ) );
                        exit;
                    }
                    Library::sendJson( true, sprintf( trans( 'Formular `%s` wurde erfolgreich aktualisiert' ), HTTP::input( 'title' ) ) );
                }
                else
                {
                    if ( $newid )
                    {
                        echo Library::json( array(
                            'success' => true,
                            'msg'     => trans( 'Formularfeld wurde erfolgreich hinzugefügt' ),
                            'newid'   => $newid
                        ) );
                        exit;
                    }
                    Library::sendJson( true, sprintf( trans( 'Formularfeld `%s` wurde erfolgreich aktualisiert' ), HTTP::input( 'name' ) ) );
                }
            }
            else
            {
                $str = '';
                foreach ( $errors as $field => $e )
                {
                    if (is_numeric($field)) {
                        $str .= ( $str ? ' <br/>' : '' ) .$e;
                    }
                    else {
                        $str = ( $str ? ', ' : '' ) . 'Error: ' . $field . ' ';
                        foreach ( $e as $err )
                        {
                            $str .= $err;
                        }
                    }
                }

                Library::sendJson( false, $str );
            }
        }


        if ( $isForm )
        {

            $data[ 'form' ]      = $model->getForm( $formid );
            $data[ 'available' ] = $this->db->query( 'SELECT * FROM %tp%form_fields WHERE formid = ?', $formid )->fetchAll();

            $fields = !empty( $data[ 'form' ][ 'fields' ] ) ? explode( ',', $data[ 'form' ][ 'fields' ] ) : array();

            $tmp      = array();
            $assigned = array();
            foreach ( $data[ 'available' ] as $idx => $r )
            {
                $r[ 'options' ]              = ( !is_array( $r[ 'options' ] ) && $r[ 'options' ] ?
                    unserialize( $r[ 'options' ] ) : array() );
                $data[ 'available' ][ $idx ] = $r;
            }


            foreach ( $fields as $id )
            {
                foreach ( $data[ 'available' ] as $idx => $r )
                {
                    if ( $id == $r[ 'field_id' ] )
                    {
                        $assigned[ ] = $r;
                        unset( $data[ 'available' ][ $idx ] );
                    }
                }
            }


            $data[ 'assigned' ] = $assigned;


            Library::addNavi( trans( 'Formular Übersicht' ) );
            Library::addNavi( ( $data[ 'form' ][ 'formid' ] ?
                sprintf( trans( 'Formular `%s` bearbeiten' ), '' . $data[ 'form' ][ 'title' ] ) :
                trans( 'Formular erstellen' ) ) );

            $this->Template->process( 'forms/formedit', $data, true );
            exit;
        }
        else
        {


            $data[ 'field' ] = $model->getField( $id );
            if ( !$id )
            {
                $data[ 'field' ][ 'formid' ] = $formid;
            }

            $data[ 'form' ] = $model->getForm( $formid );

            Library::addNavi( trans( 'Formularfelder Übersicht' ) );
            Library::addNavi( ( $data[ 'field' ][ 'field_id' ] ?
                sprintf( trans( 'Formularfeld `%s` bearbeiten' ), '' . $data[ 'field' ][ 'name' ] ) :
                trans( 'Formularfeld erstellen' ) ) );

            $this->Template->process( 'forms/edit', $data, true );
            exit;
        }
    }

    /**
     * Validating the input for Forms and Fields
     *
     * @param array $data
     * @param bool $isForm
     * @return type
     */
    public function validate($data, $isForm = false, $formid = 0 )
    {

        $rules = array();
        if ( !$isForm )
        {
            $rules[ 'name' ][ 'required' ]   = array(
                'message' => trans( 'Feldname ist erforderlich' ),
                'stop'    => true
            );
            $rules[ 'name' ][ 'min_length' ] = array(
                'message' => trans( 'Der Feldname muss mind. 4 Zeichen lang sein' ),
                'test'    => 3
            );
            $rules[ 'name' ][ 'max_length' ] = array(
                'message' => trans( 'Der Feldname darf nicht länger als mind. 50 Zeichen lang sein' ),
                'test'    => 50
            );

            if (!$formid) {
                $rules[ 'name' ][ 'unique' ]     = array(
                    'message'  => trans( 'Der Feldname existiert schon. Es muss ein eindeutiger Name sein.' ),
                    'table'    => 'form_fields',
                    'id_field' => 'name'
                );
            }
        }
        else
        {
            $rules[ 'title' ][ 'required' ]      = array(
                'message' => trans( 'Der Titel des Formular ist erforderlich' ),
                'stop'    => true
            );
            $rules[ 'formaction' ][ 'required' ] = array(
                'message' => trans( 'Die Sende Adresse des Formulars ist erforderlich' ),
                'stop'    => true
            );
            $rules[ 'name' ][ 'required' ]       = array(
                'message' => trans( 'Name ist erforderlich' ),
                'stop'    => true
            );
            $rules[ 'name' ][ 'min_length' ]     = array(
                'message' => trans( 'Der Name muss mind. 4 Zeichen lang sein' ),
                'test'    => 3
            );
            $rules[ 'name' ][ 'max_length' ]     = array(
                'message' => trans( 'Der Name darf nicht länger als mind. 50 Zeichen lang sein' ),
                'test'    => 50
            );

            if (!$formid) {
                $rules[ 'name' ][ 'unique' ]         = array(
                    'message'  => trans( 'Der Name des Formulars existiert schon. Es muss ein eindeutiger Name sein.' ),
                    'table'    => 'forms',
                    'id_field' => 'name'
                );
            }
        }

        $validator = new Validation( $data, $rules );
        $errors    = $validator->validate();

        return $errors;
    }

}

?>