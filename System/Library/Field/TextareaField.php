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
 * @file         TextareaField.php
 */
class Field_TextareaField extends Field_BaseField
{

	/**
	 * @return array
	 */
	static function getAttributes ()
	{

		return array ( 'label', 'cols', 'rows', 'style', 'class', 'controls', 'value', 'multiple' );
	}

	/**
	 * @param $field
	 * @return array
	 */
	static public function getFieldDefinition ( $field )
	{

		$value = !empty( $field[ 'value' ] ) ? $field[ 'value' ] : null;
		if ( !empty( $field[ 'options' ] ) )
		{
			$field = array_merge($field, unserialize($field[ 'options' ]));
		}
		$data = array (
			'data-inputtrigger' => !empty($field[ 'data-inputtrigger' ]) ? $field[ 'data-inputtrigger' ] : null,
			'type'              => !empty($field[ 'type' ]) ? $field[ 'type' ] : null,
			'label'             => ( !empty( $field[ 'label' ] ) ? $field[ 'label' ] : null ),
			'grouplabel'        => ( !empty( $field[ 'grouplabel' ] ) ? $field[ 'grouplabel' ] : null ),
            'description'       => ( !empty( $field[ 'description' ] ) ? $field[ 'description' ] : null ),
			'name'              => $field[ 'id' ],
			'id'                => $field[ 'id' ],
			'value'             => ( !empty( $field[ 'value' ] ) ? $field[ 'value' ] : '' ),
			'fieldid'           => $field[ 'fieldid' ],
			'maxlength'         => ( !empty( $field[ 'maxlength' ] ) ? $field[ 'maxlength' ] : null ),
			'cols'              => ( !empty( $field[ 'cols' ] ) ? $field[ 'cols' ] : null ),
			'rows'              => ( !empty( $field[ 'rows' ] ) ? $field[ 'rows' ] : null ),
			'style'             => ( !empty( $field[ 'style' ] ) ? $field[ 'style' ] : null ),
			'class'             => ( !empty( $field[ 'class' ] ) ? 'form-control ' . $field[ 'class' ] : 'form-control' ),
			'multiple'          => ( !empty( $field[ 'multiple' ] ) ? $field[ 'multiple' ] : null ),
			'controls'          => ( !empty( $field[ 'controls' ] ) && $field[ 'controls' ] == 1 ? true : false ),
		);
		if ( !empty( $field[ 'description' ] ) )
		{
			$data[ 'tip' ] = 'custom::' . $field[ 'id' ];
		}
		if ( !empty( $field[ 'tip' ] ) )
		{
			$data[ 'tip' ] = $field[ 'tip' ];
		}
		if ( !empty( $field[ 'style' ] ) )
		{
			$data[ 'style' ] = $field[ 'style' ];
		}
		if ( !is_null($value) )
		{
			$data[ 'value' ] = Library::encode($value);
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
	 * @param $field
	 * @return string
	 */
	public static function _renderField ( $field )
	{

		$data[ 'tagname' ]                     = 'textarea';
		$data[ 'attributes' ][ 'name' ]        = $field[ 'name' ];
		$data[ 'attributes' ][ 'id' ]          = $field[ 'id' ];
		$data[ 'attributes' ][ 'placeholder' ] = isset( $field[ 'grouplabel' ] ) && !empty( $field[ 'grouplabel' ] ) ? $field[ 'grouplabel' ] : ( isset( $field[ 'label' ] ) && !empty( $field[ 'label' ] ) ? $field[ 'label' ] : null );
		if ( !empty( $field[ 'rows' ] ) )
		{
			$data[ 'attributes' ][ 'rows' ] = $field[ 'rows' ];
		}
		else
		{
			$data[ 'attributes' ][ 'rows' ] = 4;
		}

		if ( !empty( $field[ 'cols' ] ) )
		{
			$data[ 'attributes' ][ 'cols' ] = $field[ 'cols' ];
		}
		else
		{
			$data[ 'attributes' ][ 'cols' ] = 60;
		}


		if ( !empty( $field[ 'style' ] ) )
		{
			$data[ 'attributes' ][ 'style' ] = $field[ 'style' ];
		}
		if ( !empty( $field[ 'class' ] ) )
		{
			$data[ 'attributes' ][ 'class' ] = $field[ 'class' ];
		}

		$data[ 'attributes' ][ 'class' ] .= ( $data[ 'attributes' ][ 'class' ] ? ' ' : '' ) . 'textarea-resize';

		if ( !empty( $field[ 'iscore' ] ) )
		{
			$data[ 'attributes' ][ 'class' ] .= ( $data[ 'attributes' ][ 'class' ] ? ' ' : '' ) . ' disabled';
		}


		if ( !empty( $field[ 'description' ] ) && Application::isBackend() )
		{
			$data[ 'attributes' ][ 'data-tooltip' ]  = strip_tags( $field[ 'description' ] );
			$data[ 'attributes' ][ 'data-position' ] = 'top-left';

		}
		if ( Application::isBackend() )
		{
			unset( $data[ 'attributes' ][ 'placeholder' ] );
		}

		if ( isset( $field[ 'data-inputtrigger' ] ) && !empty( $field[ 'data-inputtrigger' ] ) )
		{
			$data[ 'attributes' ][ 'data-inputtrigger' ] = $field[ 'data-inputtrigger' ];
		}

		$tag = Html::createTag($data);
		$tag .= htmlspecialchars($field[ 'value' ]);
		$tag .= '</textarea>';



		return $tag;
	}
}

?>