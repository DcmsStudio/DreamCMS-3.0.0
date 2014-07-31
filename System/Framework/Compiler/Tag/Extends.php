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
 * @file         Extends.php
 */


/**
 * suche erst alle extends
 * dann suche alle include tags
 * danach compilieren
 */


class Compiler_Tag_Extends extends Compiler_Tag_Abstract
{

	private static $_layout = null;


	/**
	 *
	 */
	public function configure ()
	{

		$this->tag->setAttributeConfig(array (
		                                     'template' => array (
			                                     Compiler_Attribute::REQUIRED,
			                                     Compiler_Attribute::HARD_STRING
		                                     ),
		                                     'layout'   => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::HARD_STRING
		                                     ),
		                                     'file'     => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::HARD_STRING
		                                     ),
		                               ));
	}

	/**
	 *
	 * @throws Compiler_Exception
	 * @return void
	 */
	public function process ()
	{

		$sourceTag = $this->tag->getTagSource();
		$name      = $this->getAttributeValue('file');
		$template  = $this->getAttributeValue('template');
		$layout    = $this->getAttributeValue('layout');

		$namespace = $this->compiler->getTagNamespace();


		if ( empty( $name ) && empty( $template ) )
		{
			return;
		}

		$isLayoutFile = false;
		$isLayout     = false;
		$skinid       = 0;


		$_layout = $this->compiler->getData('layout');

		self::$_layout = ( !is_null($_layout) ? $_layout : array () );


		$file = ( $name != "" ? $name : $template );
		if ( ( $file === 'layout' || $file === 'errorpage' || $file === 'layout_html5' || $file === 'errorpage_html5' ) && self::$_layout[ 'template' ] != '' )
		{
			$file = self::$_layout[ 'template' ];
			if ( substr($file, -5) != '.html' )
			{
				if ( self::$_layout[ 'doctype' ] === 'html_5' && strpos($file, 'html5') === false )
				{
					$file = '_html5';
				}
				$file .= '.html';
			}
			else
			{
				if ( self::$_layout[ 'doctype' ] === 'html_5' && strpos($file, 'html5') === false )
				{
					$file = str_replace('.html', '_html5', $file);
					$file .= '.html';
				}
			}

			$file = Compiler_Library::formatPath($file);

			if ( !file_exists($file) )
			{
				throw new Compiler_Exception( sprintf('Layout File `%s` not exists', $file) );
			}

			$template     = $file = '@@@_LAYOUT_@@@:' . self::$_layout[ 'template' ];
			$skinid       = User::getSkinId();
			$isLayoutFile = true;
		}

		if ( substr($file, -5) != '.html' )
		{
			$file .= '.html';
		}

        $t = $this->compiler->getTemplate();
		$currentSourceTemplate = $t->getTemplateCode();
		$currentSourceTemplate = str_replace($sourceTag, '', $currentSourceTemplate);


        Compiler_Library::enableErrorHandling();
		$templateInstance = $t->getCompilerProcess();
		$inheritanceTree = array ();


		$inheritanceTree = array (
			array (
				'source'       => $currentSourceTemplate,
				'resource'     => $t->getCurrentTemplateFilename(),
				'compiledName' => $t->getCompiledFilename(),
			)
		);


		while ( !is_null($file) )
		{
			if ( $file === '' || $file === null )
			{
				trigger_error('Extends : The file name must be a non-empty string', E_USER_ERROR);

				return;
			}


			if ( preg_match('#^template:(.*)$#', $file, $m) )
			{
				$sub = true;

				// resource:identifier given, extract them
				$resource   = $m[ 1 ];
				$identifier = $m[ 1 ];
			}
			else
			{
				// get the current template's resource
				if ( substr($file, 0, 1) == '/' )
				{
					$file = substr($file, 1);
				}

				//   die($file);

				if ( $isLayout )
				{
					$identifier = Compiler_Library::formatPath($file);
					$isLayout   = false;
				}
				else
				{
					$identifier = Compiler_Library::formatPath(realpath($this->compiler->getTemplateDir()) . '/' . str_replace('../', '', $file));
				}

				$resource = $identifier;
			}

           # echo $resource." ---- \n\n";

			try
			{
				$parent = $t->factoryTemplate($this->compiler, $resource, null);
			}
			catch ( Exception $e )
			{
				trigger_error('Extends : ' . $e->getMessage(), E_USER_ERROR);
			}


			if ( $parent === null )
			{
				trigger_error('Extends : Resource "' . $resource . '" not found.', E_USER_ERROR);
			}
			elseif ( $parent === false )
			{
				trigger_error('Extends : Resource "' . $resource . '" does not support extends.', E_USER_ERROR);
			}

			$curTpl = $parent;

			$newParent = array (
				'source'         => $parent->getTemplateCode(),
				'resource'       => str_replace(SKIN_PATH . $skinid . '/html/' . ROOT_PATH, ROOT_PATH, str_replace('@@@_LAYOUT_@@@', '', $resource)),
				'compiledName'   => $parent->getCompiledFilename(),
				'layoutTemplate' => ( $isLayoutFile || $isLayout ? true : false )
			);

			if ( array_search($newParent, $inheritanceTree, true) !== false )
			{
				trigger_error('Extends : Recursive template inheritance detected', E_USER_ERROR);
			}

			$inheritanceTree[ ] = $newParent;


			// Sub templates
			if ( preg_match('/^\r*\n*\s*\t*<' . $namespace . 'extends\s+(?:(name|template)=)\s*(["\'])((.+?)\2|\S+?)\s*\/?\s*>/i', $parent->getTemplateCode(), $match) )
			{
				$curPath = dirname($identifier) . '/';

				if ( isset( $match[ 2 ] ) && $match[ 2 ] == '"' )
				{
					$file = $match[ 4 ];
				}
				elseif ( isset( $match[ 2 ] ) && $match[ 2 ] == "'" )
				{
					$file = $match[ 4 ];
				}
				else
				{
					$file = $match[ 1 ];
				}
				$isLayout = false;

				if ( $file === 'layout' || $file === 'layout_html5' || $file === 'errorpage' )
				{
					$isLayout = true;
					$file     = self::$_layout[ 'template' ];
				}

				if ( substr($file, -5) != '.html' )
				{
					$file .= '.html';
				}

				if ( !$isLayout )
				{
					$file = 'template:' . $curPath . $file;
				}
				else
				{
					$file = 'template:' . Compiler_Library::formatPath($file);
				}

				unset( $parent ); // free mem

			}
			else
			{
				$file = null;

				unset( $parent ); // free mem
			}
		}


		$newTemplateCode = '';
		//$sourceCheck = array();
        $checkFiles = array();


		foreach ($inheritanceTree as $r)
		{
			// remove all extends tags
			$code = preg_replace('#<'. preg_quote($namespace, '#') .'extends[^>]*>#isU', '', $r['source']);
			$file = str_replace( ROOT_PATH, '', $r['resource']);
            $mtime = filemtime( ROOT_PATH . $file );
		//	$sourceCheck[] = 'if( @filemtime(ROOT_PATH ."' . $file . '") !== ' . $mtime . ') { $this->clearCompilerCache( __FILE__ ); return false; }';
            $checkFiles[ $file ] = $mtime;
			$newTemplateCode .= trim( $code );
		}
        #print_r($inheritanceTree);

       # die($newTemplateCode);
        #exit;
		#unset($inheritanceTree);

        #$t->isRecompileFromExtends = true;

        $t->setCheckFiles($checkFiles);
       // $t->appendHeaderCode( "\n/* start extends */\n". implode( "\n", $sourceCheck ) . "\n/* end extends */\n" );
        $this->compiler->getTemplate()->setTemplateCode($newTemplateCode);

        $t->isProxyTemplate = false;

        return $t->getCompilerProcess()->compileIt(true, 9999);
	}


}