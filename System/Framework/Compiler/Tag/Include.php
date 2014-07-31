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
 * @file         Include.php
 */

class Compiler_Tag_Include extends Compiler_Tag_Abstract
{
	private static $layout;
	/**
	 *
	 */
	public function configure()
	{
		$this->tag->setAttributeConfig(
			array(
			     'template'  => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::HARD_STRING ),
			     'group'     => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::ID ),
			     'data'      => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::STRING ),
			     'var'     => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::ID ),
			     'cachetime' => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::NUMBER ),
			     'cacheid'   => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::HARD_STRING ),
			     'compileid' => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::HARD_STRING ),
			)
		);
	}



	/**
	 * @return bool
	 */
	public function process()
	{

		$template = $this->getAttributeValue( 'template' );
		if ( $template == '' )
		{
			return;
		}

		self::$layout = $this->compiler->getData( 'layout' );
		$layoutTemplate = false;

		if ( $template == 'layout' )
		{
			$layoutTemplate = true;
		}

		if ( $layoutTemplate )
		{
			$template = '@@@_LAYOUT_@@@' . self::$layout[ 'template' ];
			if ( substr( $template, -5 ) != '.html' )
			{
				$template .= '.html';
			}

			$template = Compiler_Library::formatPath( DATA_PATH . 'layouts/' . $template );
		}

		if ( substr( $template, -5 ) != '.html' )
		{
			$template .= '.html';
		}


        $templateInstance = $this->compiler->getTemplate();

		#$templateInstance = $this->tag->getTemplateInstance();
		$compileDir = $this->compiler->getCompileDir();
        $rootTemplateDir = $this->compiler->getTemplateDir();

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

		// prepare template path for the included file
		if ( substr($template, 0,1) == '/'  )
		{
			$template = substr($template, 1);
		}
		elseif (substr( $template, 0, 2 ) == './')
		{
			$template = substr($template, 2);
		}


		$data = $this->getAttributeValue( 'data' );
		$datavar = $this->getAttributeValue( 'var' );
		$group = $this->getAttributeValue( 'group' );
		$cachetime = $this->getAttributeValue( 'cachetime' );
		$cacheid = $this->getAttributeValue( 'cacheid' );
		$compileid = $this->getAttributeValue( 'compileid' );


		if ( !is_numeric( $cachetime ) )
		{
			$cachetime = null;
		}

		if ( is_string( $group ) )
		{
			$result = $templateInstance->getCompilerProcess()->compileExpression( $group );
			$valueStr = $result[ 0 ];
		}

        if ($sourcePath !== $rootTemplateDir && strpos($template, '../') === false )
        {
            $sourcePath = $rootTemplateDir;
        }


        if (strpos($sourcePath, 'System/Packages/') !== false ) {
            $sourcePath = $rootTemplateDir;
        }


		if ( strpos($template, '../') !== false )
        {

            if ($sourcePath != $rootTemplateDir )
            {
                $s = explode('/', $sourcePath);

                if (substr($sourcePath, -1) === '/' ) {
                    array_pop($s);
                }


                $shifts = substr_count($template, '../');

                if ($shifts > 0)
                {
                    for ($x = 0; $x < $shifts; ++$x)
                    {
                        array_pop($s);
                    }
                }
                $sourcePath = implode('/', $s) .'/';

                $s1 = explode('/', $template);

                if ($shifts > 0) {
                    for ($x = 0; $x < $shifts; ++$x)
                    {
                        array_shift($s1);
                    }
                }

                $template = implode('/', $s1);

            }
            else
            {
                $s = explode('/', $template);
                $shifts = substr_count($template, '../');

                if ($shifts) {
                    for ($x = 0; $x < $shifts; ++$x) {
                        array_shift($s);
                    }
                }

                $template = implode('/', $s);
            }
		}
		else
        {

			if (str_replace($templatesPath, '', $sourcePath) !== '')
			{
				$sourcePath = $templatesPath;
			}
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


		$start = '';
		$includePath = str_replace( $compileDir, '', $_compiledname );
		if ( $includePath )
		{
			$includePath = str_replace( $compileDir, '', $includePath );
		}


		if ( $data[ 0 ] && empty($datavar) )
		{
			$start .= ' $orgData = $this->d; $this->d = ' . $data[ 0 ] . '; ';
		}
		elseif ($data[ 0 ] && !empty($datavar))
		{
			$start = ' $orgData = $this->d; $this->d';

			$keys = explode('.', $datavar);
			foreach ($keys as $k )
			{
				if ($k) {
					$start .= '[\'' . $k . '\']';
				}
			}

			$start .= ' = ' . $data[ 0 ] . '; ';
		}



		$start .= ' echo $this->loadTemplate(\'' .  $sourcePath . $template  . '\', \'' . $includePath . '\'); ';

		if ( $data[ 0 ]  )
		{
			$start .= ' $this->d = $orgData; $orgData = null; ';
		}

		$this->setStartTag( $start );

	}

}