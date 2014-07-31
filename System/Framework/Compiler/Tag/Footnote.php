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
 * @file         Footnote.php
 */

class Compiler_Tag_Footnote extends Compiler_Tag_Abstract
{
	private static $_footnoteIdx = 0;
	private static $_templates = null;
	/**
	 *
	 */
	public function configure()
	{
		$this->tag->setAttributeConfig(
			array(
			     'name'     => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::HARD_STRING,
				     null ),
			     'auto'     => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::BOOL ),
			     'var'      => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::HARD_STRING,
				     null ),
			     'value'    => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::EXPRESSION,
				     null ),
			     // 'set' => array(TemplateCompiler_Attribute::OPTIONAL, TemplateCompiler_Attribute::HARD_STRING),
			     'template' => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::HARD_STRING,
				     null ),
			     'enable'   => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::EXPRESSION,
				     null ),
			)
		);
	}

	public function process()
	{
		$this->set( 'nophp', false );


		$name = $this->getAttributeValue( 'name' );
		$var = $this->getAttributeValue( 'var' );
		$value = $this->getAttributeValue( 'value' ); //
		$auto = $this->getAttributeValue( 'auto' );
		//    $set = $this->getAttributeValue('set');
		$template = $this->getAttributeValue( 'template' );
		$enable = $this->getAttributeValue( 'enable' );


		if ( !$this->tag->isEmptyTag() )
		{
			throw new Compiler_Exception( sprintf( 'The Tag &lt;%s&gt; must have the attribute "template"!', $this->tag->getXmlName() ) );
		}


		$code = '';

		Compiler::$cnt++;
		self::$_footnoteIdx = Compiler::$cnt;


		if ( $name && $template && $value && $var )
		{
			self::$_templates[ $name ] = $template;



			if ( $enable[ 0 ] )
			{
				$code .= '
$currentState = Content::getOpt(\'footnotes\');

if (' . $enable[ 0 ] . ') {
    Content::enableFootnotes();
}
else {
    Content::disableFootnotes();
}';
			}

			// extract first
			$code .= '

$footnotes' . self::$_footnoteIdx . ' = Content::extractFootnotes(' . $value[ 0 ] . ',' . ($auto ? 'true' : 'false') . ');
if ( is_array($footnotes' . self::$_footnoteIdx . ') )
{
    $this->_data[\'' . $var . '\'] = $footnotes' . self::$_footnoteIdx . ';
}
';
			if ( $enable[ 0 ] )
			{
				$code .= '

if ( $currentState ) {
    Content::enableFootnotes();
}
else {
    Content::disableFootnotes();
}
';
			}

			$code .= 'echo ' . $value[ 0 ] . ';'; // the content
		}
		elseif ( $name && !$template && !$value[ 0 ] && !$var )
		{

			if ( !isset( self::$_templates[ $name ] ) )
			{
				throw new Compiler_Exception( sprintf( 'The Tag &lt;%s&gt; has no footnote template! ', $this->tag->getXmlName() ) );
			}

			$template = self::$_templates[ $name ];

			if ( substr( $template, -5 ) != '.html' )
			{
				$template .= '.html';
			}

			$path = $this->compiler->getTemplateDir();

			if ( !file_exists( $template ) )
			{
				$template = Compiler_Library::formatPath( $path . $template );
			}


			$templateInstance = $this->compiler->getTemplate();
			$compileDir = $this->compiler->getCompileDir();

			// now compile
			$factory = $templateInstance->factoryTemplate($this->compiler,  $template );
			$factory->enableFileCheck();
			$factory->getCompiledCode();

			$includePath = str_replace( $compileDir, '', $factory->getCompiledFilename());
			if ( $includePath )
			{
				$includePath = str_replace( $compileDir, '', $includePath );
			}


			$code .= 'echo $this->loadTemplate(\'' . self::$_templates[ $name ] . '\', \'' . $includePath . '\');';

			unset( self::$_templates[ $name ] );
		}
		else
		{
			throw new Compiler_Exception( sprintf( 'Hmm the Tag &lt;%s&gt; has an error!', $this->_tagObject->getXmlName() ) );
		}


		#  $this->set('nophp', true);
		$this->setStartTag( $code );
	}

}