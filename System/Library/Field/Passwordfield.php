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
 * @package      Field
 * @version      3.0.0 Beta
 * @category     Form Fields
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Passwordfield.php
 */
class Field_PasswordField extends Field_BaseField
{

	/**
	 * @return array
	 */
	static function getAttributes ()
	{

		return array ( 'label', 'maxlength', 'size', 'style', 'controls', 'class' );
	}

	/**
	 * @param $field
	 * @return array
	 */
	static public function getFieldDefinition ( $field )
	{

		if ( !empty( $field[ 'options' ] ) )
		{
			$field = array_merge($field, unserialize($field[ 'options' ]));
		}
		$data = array (
			'data-inputtrigger' => isset($field[ 'data-inputtrigger' ]) ? $field[ 'data-inputtrigger' ] : null,
			'type'              => $field[ 'type' ],
			'label'             => ( !empty( $field[ 'label' ] ) ? $field[ 'label' ] : '' ),
			'grouplabel'        => ( !empty( $field[ 'grouplabel' ] ) ? $field[ 'grouplabel' ] : null ),
			'name'              => $field[ 'id' ],
			'id'                => $field[ 'id' ],
			'fieldid'           => $field[ 'fieldid' ],
			'maxlength'         => ( !empty( $field[ 'maxlength' ] ) ? $field[ 'maxlength' ] : null ),
			'size'              => ( !empty( $field[ 'size' ] ) ? $field[ 'size' ] : null ),
			'style'             => ( !empty( $field[ 'style' ] ) ? $field[ 'style' ] : null ),
			'class'             => ( !empty( $field[ 'class' ] ) ? 'form-control ' . $field[ 'class' ] : 'form-control' ),
			'controls'          => ( !empty( $field[ 'controls' ] ) ? $field[ 'controls' ] : null ),
			'value'             => '' // passwords should never be pre-filled?
		);
		if ( !empty( $field[ 'description' ] ) )
		{
			$data[ 'tip' ] = 'custom::' . $field[ 'id' ];
		}
		if ( !empty( $field[ 'tip' ] ) )
		{
			$data[ 'tip' ] = $field[ 'tip' ];
		}

		return $data;
	}

	/**
	 * @param $field
	 */
	public static function renderField ( $field )
	{

	}

	/**
	 * @param $fields
	 * @return string
	 */
	public static function _renderField ( $fields )
	{

		$data[ 'tagname' ]                     = 'input';
		$data[ 'attributes' ][ 'name' ]        = $field[ 'name' ];
		$data[ 'attributes' ][ 'id' ]          = $field[ 'id' ];
		$data[ 'attributes' ][ 'type' ]        = 'password';
		$data[ 'attributes' ][ 'value' ]       = $field[ 'value' ];
		$data[ 'attributes' ][ 'placeholder' ] = isset( $field[ 'grouplabel' ] ) && !empty( $field[ 'grouplabel' ] ) ? $field[ 'grouplabel' ] : ( isset( $field[ 'label' ] ) && !empty( $field[ 'label' ] ) ? $field[ 'label' ] : null );
		if ( !empty( $field[ 'size' ] ) )
		{
			$data[ 'attributes' ][ 'size' ] = $field[ 'size' ];
		}
		if ( !empty( $field[ 'maxlength' ] ) )
		{
			$data[ 'attributes' ][ 'maxlength' ] = $field[ 'maxlength' ];
		}
		if ( !empty( $field[ 'style' ] ) )
		{
			$data[ 'attributes' ][ 'style' ] = $field[ 'style' ];
		}
		if ( !empty( $field[ 'class' ] ) )
		{
			$data[ 'attributes' ][ 'class' ] = $field[ 'class' ];
		}
		if ( !empty( $field[ 'iscore' ] ) )
		{
			$data[ 'attributes' ][ 'class' ] .= ( $data[ 'attributes' ][ 'class' ] ? ' ' : '' ) . ' disabled';
		}

		if ( !empty( $field[ 'description' ] ) && Application::isBackend() )
		{
			$data[ 'attributes' ][ 'data-tooltip' ]  = strip_tags( $field[ 'description' ] );
			$data[ 'attributes' ][ 'data-position' ] = 'top-left';

		}
		if ( isset( $field[ 'data-inputtrigger' ] ) && !empty( $field[ 'data-inputtrigger' ] ) )
		{
			$data[ 'attributes' ][ 'data-inputtrigger' ] = $field[ 'data-inputtrigger' ];
		}
		if ( Application::isBackend() )
		{
			unset( $data[ 'attributes' ][ 'placeholder' ] );
		}

		return Html::createTag($data);
	}
}

?>