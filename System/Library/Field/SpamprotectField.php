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
 * @file         SpamprotectField.php
 */
class Field_SpamprotectField extends Field_BaseField
{

	/**
	 * @return array
	 */
	static public function getAttributes ()
	{

		return array ( 'label', 'description', 'maxlength', 'size', 'style', 'class' );
	}

	/**
	 * @param $field
	 * @return array
	 */
	static public function getFieldDefinition ( $field )
	{

		$field[ 'controls' ] = true;

		$value = !empty( $field[ 'value' ] ) ? $field[ 'value' ] : null;

		if ( !empty( $field[ 'options' ] ) )
		{
			$field = array_merge($field, unserialize($field[ 'options' ]));
		}

		$data = array (
			'type'        => $field[ 'type' ],
			'label'       => ( !empty( $field[ 'label' ] ) ? $field[ 'label' ] : '' ),
			'description' => ( !empty( $field[ 'description' ] ) ? $field[ 'description' ] : '' ),
			'grouplabel'  => ( !empty( $field[ 'grouplabel' ] ) ? $field[ 'grouplabel' ] : null ),
			'name'        => $field[ 'id' ],
			'id'          => $field[ 'id' ],
			'maxlength'   => ( !empty( $field[ 'maxlength' ] ) ? $field[ 'maxlength' ] : null ),
			'size'        => ( !empty( $field[ 'size' ] ) ? $field[ 'size' ] : 60 ),
			'style'       => ( !empty( $field[ 'style' ] ) ? $field[ 'style' ] : null ),
			'class'       => ( !empty( $field[ 'class' ] ) ? $field[ 'class' ] : null ),
			'controls'    => ( !empty( $field[ 'controls' ] ) ? $field[ 'controls' ] : null ),
			'multiple'    => ( !empty( $field[ 'multiple' ] ) ? $field[ 'multiple' ] : null ),
			'fieldid'     => $field[ 'fieldid' ],
			'value'       => ( !empty( $field[ 'value' ] ) ? $field[ 'value' ] : '' )
		);
		if ( !empty( $field[ 'description' ] ) )
		{
			$data[ 'tip' ] = 'custom::' . $field[ 'id' ];
		}
		if ( !empty( $field[ 'tip' ] ) )
		{
			$data[ 'tip' ] = $field[ 'tip' ];
		}
		if ( !is_null($value) )
		{
			$data[ 'value' ] = $value;
		}

		return $data;
	}

	/**
	 * @param $field
	 * @return null
	 */
	public static function renderField ( $field )
	{

		return !empty( $field[ 'value' ] ) ? $field[ 'value' ] : null;
	}

	/**
	 * @param $field
	 * @return string
	 */
	public static function _renderField ( $field )
	{

		$field[ 'controls' ] = true;

		$data[ 'tagname' ]               = 'input';
		$data[ 'attributes' ][ 'name' ]  = $field[ 'name' ];
		$data[ 'attributes' ][ 'id' ]    = $field[ 'id' ];
		$data[ 'attributes' ][ 'type' ]  = 'text';
		$data[ 'attributes' ][ 'value' ] = $field[ 'value' ];

		if ( !empty( $field[ 'size' ] ) )
		{
			$data[ 'attributes' ][ 'size' ] = $field[ 'size' ];
		}
		else
		{
			$data[ 'attributes' ][ 'size' ] = 60;
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

		if ( !empty( $field[ 'controls' ] ) )
		{
			if ( !isset( $data[ 'attributes' ][ 'class' ] ) )
			{
				$data[ 'attributes' ][ 'class' ] = '';
			}
			$data[ 'attributes' ][ 'class' ] .= ( isset( $data[ 'attributes' ][ 'class' ] ) && $data[ 'attributes' ][ 'class' ] ? ' ' : '' ) . 'required';
		}

		if ( !empty( $field[ 'iscore' ] ) )
		{
			if ( !isset( $data[ 'attributes' ][ 'class' ] ) )
			{
				$data[ 'attributes' ][ 'class' ] = '';
			}
			$data[ 'attributes' ][ 'class' ] .= ( isset( $data[ 'attributes' ][ 'class' ] ) && $data[ 'attributes' ][ 'class' ] ? ' ' : '' ) . ' disabled';
		}


		$p = array ();
		Captcha::regenerate($p);
		$html = Captcha::getCaptcha(array ( 'width' => 200, 'height' => 80, 'audio' => true, 'reload' => true ));

		$html .= Html::createTag($data);

		return $html;
	}
}

?>