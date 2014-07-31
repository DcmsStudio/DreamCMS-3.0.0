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
 * @file         Box.php
 */


class Compiler_Tag_Custom_Box extends Compiler_Tag_Abstract
{
	private static $layout;

	/**
	 *
	 */
	public function configure ()
	{

		$this->tag->setAttributeConfig(array (
		                                     'template' => array(
			                                     Compiler_Attribute::REQUIRED,
			                                     Compiler_Attribute::HARD_STRING
		                                     ),
		                                     'data' => array(
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::STRING
		                                     ),
		                                     'var' => array(
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::ID
		                                     ),
		                                     'content' => array(
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::HARD_STRING
		                                     ),
		                                     'label' => array(
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::STRING
		                                     ),
		                                     'class' => array(
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::STRING
		                                     ),
		                                     'showlabel'  => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::BOOL,
			                                     'true'
		                                     )
		                               ));
	}

	public function process ()
	{
		$template = $this->getAttributeValue( 'template' );


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

		// prepare template path for the included file
		if ( substr($template, 0,1) == '/'  )
		{
			$template = substr($template, 1);
		}
		elseif (substr( $template, 0, 2 ) == './')
		{
			$template = substr($template, 2);
		}


		if ( strpos($template, '../') !== false ) {

			$s = explode('/', $sourcePath);
			array_pop($s);


			$shifts = substr_count($template, '../');

			if ($shifts) {
				for ($x = 0; $x < $shifts; ++$x) {
					array_pop($s);

				}
			}

			$template = str_replace('../', '', $template);
			$sourcePath = implode('/', $s) .'/';
		}
		else {
			if (str_replace($templatesPath, '', $sourcePath) !== '')
			{
				$sourcePath = $templatesPath;
			}
		}


        $tt = new Compiler_Template($this->compiler, $sourcePath . $template);
        $_compiledname = $tt->getCompiledFilename();

        if (!is_file($_compiledname))
        {
            $tt->enableFileCheck();
            $tt->isProxyTemplate = true;
            $tt->scriptHeader = array();
            $tt->getCompiledCode(true);
        }
/*
		// now compile
		$factory = $templateInstance->factoryTemplate($this->compiler, $sourcePath . $template );
		$factory->enableFileCheck();
		$factory->getCompiledCode(true);
*/

		$start = '';
		$includePath = str_replace( $compileDir, '', $_compiledname);
		if ( $includePath )
		{
			$includePath = str_replace( $compileDir, '', $includePath );
		}

		$data = $this->getAttributeValue( 'data' );
		$label = $this->getAttributeValue( 'label' );
		$class = $this->getAttributeValue( 'class' );
		$showlabel = $this->getAttributeValue( 'showlabel' );
		$datavar = $this->getAttributeValue( 'var' );


		if ( $data[ 0 ] && empty($datavar) )
		{
			$start = ' $orgData = $this->dat; $this->dat = ' . $data[ 0 ] . '; ' ."\n";
		}
		elseif ($data[ 0 ] && !empty($datavar))
		{
			$start = ' $orgData = $this->dat; $this->dat';

			$keys = explode('.', $datavar);
			foreach ($keys as $k )
			{
				if ($k) {
					$start .= '[\'' . $k . '\']';
				}
			}

			$start .= ' = ' . $data[ 0 ] . '; ' ."\n";
		}


		if ( $showlabel && $label[0] )
		{
			$start .= ' $this->dat[\'boxlabel\'] = ' . $label[0] .';' ."\n";
		}

		if ($class[0])
		{
			$start .= ' $this->dat[\'boxclass\'] = \' \'.'. $class[0] .';' ."\n";
		}

		$content = $this->getAttributeValue( 'content' );
		if ($content)
		{
			$start .= ' $this->dat[\'boxcontent\'] = \''. addcslashes($content, "'") .'\';' ."\n";
		}
		else {
			if (!$this->tag->isEmptyTag()) {
				$tcontent = $this->tag->getTagContent();
				if (trim($tcontent))
				{
					$start .= '$out = ob_get_clean();ob_start();' ."\n";
					$end = '$this->dat[\'boxcontent\'] = ob_get_clean(); ob_start(); echo $out;' ."\n";
				}
			}
		}

		if ($this->tag->isEmptyTag()) {

			$start .= ' echo $this->loadTemplate(\'' .  $sourcePath . $template  . '\', \'' . $includePath . '\'); ' ."\n";

			if ( $data[ 0 ]  )
			{
				$start .= ' $this->dat = $orgData; $orgData = null; ' ."\n";
			}

			$this->setStartTag( $start  );
		}
		else {
			$end .= ' echo $this->loadTemplate(\'' .  $sourcePath . $template  . '\', \'' . $includePath . '\'); ' ."\n";

			if ( $data[ 0 ]  )
			{
				$end .= ' $this->dat = $orgData; $orgData = null; ' ."\n";
			}
			$this->setStartTag( $start  );
			$this->setEndTag( $end );
		}


	}
}