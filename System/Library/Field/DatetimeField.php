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
 * @package     Field
 * @version     3.0.0 Beta
 * @category    Form Fields
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        DatetimeField.php
 */
class Field_DatetimeField extends Field_BaseField
{

    /**
     * @return array
     */
    static function getAttributes()
    {
        return array( 'label', 'value', 'time', 'multiple', 'controls' );
    }

    /**
     * @param $field
     * @return array
     */
    static public function getFieldDefinition( $field )
    {
        $value = !empty( $field[ 'value' ] ) ? $field[ 'value' ] : NULL;
        if ( !empty( $field[ 'options' ] ) )
        {
            $field = array_merge( $field, unserialize( $field[ 'options' ] ) );
        }
        $data = array(
                'type'       => $field[ 'type' ],
                'label'      => (!empty( $field[ 'label' ] ) ? $field[ 'label' ] : ''),
                'grouplabel' => (!empty( $field[ 'grouplabel' ] ) ? $field[ 'grouplabel' ] : null),
                'name'       => $field[ 'id' ],
                'id'         => $field[ 'id' ],
                'fieldid'    => $field[ 'fieldid' ],
                'value'      => (!empty( $field[ 'value' ] ) ? $field[ 'value' ] : '' ),
                'time'       => (!empty( $field[ 'time' ] ) && $field[ 'time' ] == 1 ? true : false),
                'multiple'   => (!empty( $field[ 'multiple' ] ) ? $field[ 'multiple' ] : null),
                'controls'   => (!empty( $field[ 'controls' ] ) && $field[ 'controls' ] == 1 ? true : false),
        );
        if ( !empty( $field[ 'description' ] ) )
        {
            $data[ 'tip' ] = 'custom::' . $field[ 'id' ];
        }
        if ( !empty( $field[ 'tip' ] ) )
        {
            $data[ 'tip' ] = $field[ 'tip' ];
        }
        if ( !is_null( $value ) )
        {
            $data[ 'value' ] = $value;
        }
        return $data;
    }

    /**
     * @param $field
     */
    public static function renderField( $field )
    {
        
    }

    /**
     * @param $field
     * @return string
     */
    public static function _renderField( $field )
    {

        $data[ 'tagname' ]               = 'input';
        $data[ 'attributes' ][ 'name' ]  = $field[ 'name' ];
        $data[ 'attributes' ][ 'id' ]    = 'cal_' . $field[ 'id' ];
        $data[ 'attributes' ][ 'type' ]  = 'text';
        $data[ 'attributes' ][ 'value' ] = $field[ 'value' ];
	    $data[ 'attributes' ][ 'placeholder' ] = isset($field[ 'grouplabel' ]) && !empty($field[ 'grouplabel' ]) ? $field[ 'grouplabel' ] : (isset($field[ 'label' ]) && !empty($field[ 'label' ]) ? $field[ 'id' ] : null) ;
        $name = $field[ 'id' ];

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
            $data[ 'attributes' ][ 'class' ] .= ( $data[ 'attributes' ][ 'class' ] ? ' ' : '') . ' disabled';
        }
        $tag = Html::createTag( $data );


        $date = date( 'd.m.Y, H:i' );

        $im = BACKEND_IMAGE_PATH;

        $tag .= <<<EOF
		
<script type="text/javascript">
//<![CDATA[

	var caloptions = {
		firstDay: 1,
		changeMonth: true,
		changeYear: true,
		dateFormat: 'dd.mm.yy',
		showButtonPanel: true,
		regional: 'de',
		beforeShow: function () {
			$(this ).addClass('popup-cal');
			$(this).find('button:first').addClass('action-button');
			$(this).find('.ui-datepicker-close').remove();
			$(this).removeClass('ui-corner-all').find('.ui-corner-all' ).removeClass('ui-corner-all');
			setTimeout(function () {
				$(this).find('.ui-datepicker-current').addClass('action-button');
			}, 50);
		}
	};

	$("#cal_{$name}").datepicker(caloptions);

//]]>
</script>
		
		
EOF;

        return $tag;
    }
}

?>