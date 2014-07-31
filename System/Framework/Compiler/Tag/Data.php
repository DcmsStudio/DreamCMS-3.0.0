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
 * @file         Data.php
 */

class Compiler_Tag_Data extends Compiler_Tag_Abstract
{


	/**
	 *
	 */
	public function configure()
	{
		$this->tag->setAttributeConfig(
			array(
			     'value'    => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::STRING ),
			     'template' => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::HARD_STRING ),
			     'var'      => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::STRING ),
			     'default'  => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::STRING )
			)
		);
	}

	public function process()
	{
		$templ = $this->getAttributeValue( 'template' );
		$var = $this->getAttributeValue( 'var' );

		$default = $this->getAttributeValue( 'default' );
		$ns = $this->isNamespacedAttribute( 'value' );

		if ( !$ns && $ns != 'str' )
		{
			$value = $this->getAttributeValue( 'value' );
		}
		else
		{
			$value = "'" . addcslashes( $this->getAttributeValue( 'value', false, true ), "'" ) . "'";
		}

		if ( !is_array( $value ) )
		{
			$value = array(
				0 => $value );
		}

		$_value = $this->getAttributeValue( 'value', false, true );

		if ( substr( $_value, 0, 1 ) === ' ' )
		{
			if ( substr( $value[ 0 ], 0, 1 ) === '\'' )
			{
				$value[ 0 ] = '\' ' . substr( $value[ 0 ], 1 );
			}
		}

		if ( substr( $_value, -1 ) === ' ' )
		{
			if ( substr( $value[ 0 ], -1 ) === '\'' )
			{
				$value[ 0 ] = substr( $value[ 0 ], 0, -1 ) . ' \'';
			}
		}

		$template = is_array( $templ ) ? $templ[ 0 ] : $templ;
		if ( substr( $template, -5 ) != '.html' )
		{
			$template .= '.html';
		}



        $templateInstance = $this->compiler->getTemplate();
        $compileDir = $this->compiler->getCompileDir();
        $_currentFile = $templateInstance->getCurrentTemplateFilename();

        $currentTemplatePath = explode( '/', $_currentFile );
        array_pop( $currentTemplatePath );



        // Comiled path
        $templatePath = str_replace( ROOT_PATH, '', $compileDir );
        $templatesPath = $this->compiler->getTemplateDir();

        if ( implode( '/', $currentTemplatePath ) . '/' == $compileDir )
        {
            if ( substr( $templatePath, 0, 1 ) == '/' )
            {
                $templatePath = substr( $templatePath, 1 );
            }
            if ( substr( $templatePath, -1 ) == '/' )
            {
                $templatePath = substr( $templatePath, -1 );
            }
        }
        else
        {
            if ( substr( $templatePath, 0, 1 ) == '/' )
            {
                $templatePath = substr( $templatePath, 1 );
            }
            if ( substr( $templatePath, -1 ) == '/' )
            {
                $templatePath = substr( $templatePath, -1 );
            }
        }


        if ( substr( $templatePath, 0, 1 ) == '/' )
        {
            $templatePath = substr( $templatePath, 1 );
        }

        if ( substr( $templatePath, -1 ) != '/' )
        {
            $templatePath .= '/';
        }

        //
        $sourcePath = implode('/', $currentTemplatePath) .'/';

		if ( !file_exists( $sourcePath . $template ) )
		{
			throw new Compiler_Exception('The Template "'. $sourcePath . $template .'" not exists!');
		}


        $baseScopes = $this->compiler->getScopes();
        $this->compiler->clearScopes();

        // now compile
        $this->compiler->getUsedBlocks();
        $tt = new Compiler_Template($this->compiler, $sourcePath . $template);
        $_compiledname = $tt->getCompiledFilename();

        if (!is_file($_compiledname))
        {
            $tt->enableFileCheck();
            $tt->isProxyTemplate = true;
            $tt->scriptHeader = array();
            $tt->getCompiledCode();
        }

        $this->compiler->setScopes($baseScopes);

        $includePath = str_replace( $compileDir, '', $_compiledname );
        if ( $includePath )
        {
            $includePath = str_replace( $compileDir, '', $includePath );
        }

		$start = '
				$this->dat[' . $var[ 0 ] . '] = ' . $value[ 0 ] . ';
                $this->dat[\'_defaultVal\'] = ' . ($default[ 0 ] ? $default[ 0 ] : 'null') . ';
                echo $this->loadTemplate(\'' . $sourcePath . $template . '\', \''.$includePath.'\');
                unset($this->dat[' . $var[ 0 ] . '],$this->dat[\'_defaultVal\']);';

		$this->setStartTag( $start );
	}
}