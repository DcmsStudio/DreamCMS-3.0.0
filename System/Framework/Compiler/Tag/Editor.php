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
 * @file         Editor.php
 */

class Compiler_Tag_Editor extends Compiler_Tag_Abstract
{
	/**
	 *
	 */
	public function configure()
	{
		$this->tag->setAttributeConfig(
			array(
			     'baseurl'    => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::STRING ),
			     'lang'       => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::HARD_STRING ),
			     'name'       => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::STRING ),
			     'value'      => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::STRING ),
			     'width'      => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::HARD_STRING ),
			     'height'     => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::HARD_STRING ),
			     'toolbar'    => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::STRING ),
			     'toolbarpos' => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::STRING ),
                 'class' => array(
                     Compiler_Attribute::OPTIONAL,
                     Compiler_Attribute::STRING )
			)
		);
	}

	public function process()
	{
		$this->set( 'nophp', false );

		$lang = $this->getAttributeValue( 'lang' );
		if ( empty( $lang ) )
		{
			$lang = 'de';
		}

		$baseurl = $this->getAttributeValue( 'baseurl' );
		if ( empty( $baseurl ) )
		{
			return;
		}

		$name = $this->getAttributeValue( 'name' );
		$width = $this->getAttributeValue( 'width' );
		$height = $this->getAttributeValue( 'height' );
		$value = $this->getAttributeValue( 'value' );
        $_class = $this->getAttributeValue( 'class' );

        if ( !is_array($_class)) {
            $_class[0] = "''";
        }

		$toolbar = $this->getAttributeValue( 'toolbar' );
		$toolbarpos = $this->getAttributeValue( 'toolbarpos' );

		$global = "
        \$personal = new Personal();
        \$wysiwyg = \$personal->get('personal', 'settings');
        \$wysiwyg = \$wysiwyg['wysiwyg'];

        \$toolbar = " . (isset( $toolbar[ 0 ] ) ? $toolbar[ 0 ] : "''") . ";
        \$toolbarPos = " . (isset( $toolbarpos[ 0 ] ) ? $toolbarpos[ 0 ] : "''") . ";
";


		$this->setStartTag( $global . "
        if (\$wysiwyg)
        {
            echo Tinymce::getTextarea(" . $value[ 0 ] . ", " . $name[ 0 ] . ", '" . ($width ? $width : '100%') . "', '" . ($height ? $height : "300px") . "', 60, 50, \$toolbar, \$toolbarPos, ".$_class[0].");
        }
        else
        {
            echo \"<textarea name=\\\"" . str_replace( array(
		                                                    '"',
		                                                    '\'' ), '', $name[ 0 ] ) . "\\\" rows=\\\"15\\\" cols=\\\"80\\\" style=\\\"height:" . ($height ? $height : "300px") . "\\\">\";

            echo htmlspecialchars(" . $value[ 0 ] . ").\"</textarea>\";
        }" );
	}

}