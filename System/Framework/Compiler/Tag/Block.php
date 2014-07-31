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
 * @file         Block.php
 */

class Compiler_Tag_Block extends Compiler_Tag_Abstract
{


	/**
	 *
	 */
	public function configure ()
	{

		$this->tag->setAttributeConfig(array (
		                                     'name'      => array (
			                                     Compiler_Attribute::REQUIRED,
			                                     Compiler_Attribute::ID

		                                     ),
		                                     'appendto'  => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::HARD_STRING

		                                     ),
		                                     'prependto' => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::HARD_STRING

		                                     ),
		                                     'static'    => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::BOOL

		                                     ),
		                                     'notempty'  => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::BOOL

		                                     )
		                               ));
	}

	public function process ()
	{

		$name = $this->getAttributeValue('name');
		if ( empty( $name ) )
		{
			return;
		}


		if ( !$this->tag->isEmptyTag() )
		{
			$this->set('nophp', false);
			$this->_prepareBlock($name);

            $this->set('nophp', true);
           # $this->setStartTag('<!-- Block: '.$name.' -->');
           # $this->setEndTag('<!-- End Block: '.$name.' -->');

			//$this->tag->removeChild();
		}
		else
		{
			$_nameFix = str_replace('-', '_', $name);
			$notempty = $this->getAttributeValue('notempty');

            /*
            if ( $notempty )
            {
                $str = Compiler_Abstract::PHP_OPEN. 'ob_start();'. Compiler_Abstract::PHP_CLOSE;
                $str .= $this->compiler->getBlockCode($name);

                $str .= Compiler_Abstract::PHP_OPEN .'$_block' . $_nameFix . 'Code = ob_get_clean();if (trim($_block' . $_nameFix . 'Code)) { echo $_block' . $_nameFix . 'Code; }' . Compiler_Abstract::PHP_CLOSE;


            }
            else {
                $str = Compiler_Abstract::PHP_OPEN. 'ob_start();'. Compiler_Abstract::PHP_CLOSE;
                $str .= '<!-- Start Block: '.$name.' -->'.$this->compiler->getBlockCode($name) .'<!-- End Block: '.$name.' -->';
                $str .= Compiler_Abstract::PHP_OPEN .'$_block' . $_nameFix . 'Code = ob_get_clean(); }' . Compiler_Abstract::PHP_CLOSE;


            }
*/



			//$str = '<!-- Block ' . $name . ' from dcmsTag_block -->' . "\n";
			$str = "\n" . Compiler_Abstract::PHP_OPEN;
			if ( $notempty )
			{
				$str .= "\n" . ' $_block' . $_nameFix . 'Code = $this->useBlock(\'' . $name . '\'); if (trim($_block' . $_nameFix . 'Code)) { echo $_block' . $_nameFix . 'Code; }' . "\n";
			}
			else
			{
				$str .= "\n" . ' echo $this->useBlock(\'' . $name . '\');' . "\n";
			}
			$str .= Compiler_Abstract::PHP_CLOSE . "\n";

			$this->set('nophp', true);
			$this->setStartTag($str);
		}
	}


    /**
     *
     * @param $name
     * @throws Compiler_Exception
     * @return string path to the compiled blockfile
     */
	private function _prepareBlock ( $name )
	{

		if ( empty( $name ) || !$this->tag->hasChildren())
		{
			return;
		}

		$appendto  = $this->getAttributeValue('appendto');
		$prependto = $this->getAttributeValue('prependto');

		$compileDir = $this->compiler->getCompileDir();
		$template   = $this->compiler->getTemplate();

		$_currentFile = $template->getCurrentTemplateFilename();
		$currentFile  = str_replace($this->compiler->getTemplateDir(), '', $_currentFile);

		$currentTemplateFile = explode('/', $_currentFile);
		array_pop($currentTemplateFile);

		/**
		 * read the template filename
		 */
		$__tmp   = explode('/', $currentFile);
		$__tmp   = array_pop($__tmp);
		$tplName = explode('.', $__tmp);
		array_pop($tplName);
		$tplName = implode('', $tplName);

		$tplKeyName = '';
		if ( $tplName != '' )
		{
			$tplKeyName = $tplName . '-';
		}

		// Comiled path
		$templatePath = str_replace(ROOT_PATH, '', $compileDir);


		$templatesPath = $this->compiler->getTemplateDir();


		if ( implode('/', $currentTemplateFile) . '/' == $compileDir )
		{
			$currentTemplatePath = implode('/', $currentTemplateFile);
			$currentTemplatePath = str_replace(( $templatesPath ? $templatesPath : ROOT_PATH ), '', $currentTemplatePath);

			if ( substr($templatePath, 0, 1) == '/' )
			{
				$templatePath = substr($templatePath, 1);
			}
			if ( substr($templatePath, -1) == '/' )
			{
				$templatePath = substr($templatePath, -1);
			}
		}
		else
		{
			$currentTemplatePath = implode('/', $currentTemplateFile);
			$currentTemplatePath = str_replace(( $templatesPath ? $templatesPath : ROOT_PATH ), '', $currentTemplatePath);

			if ( substr($templatePath, 0, 1) == '/' )
			{
				$templatePath = substr($templatePath, 1);
			}
			if ( substr($templatePath, -1) == '/' )
			{
				$templatePath = substr($templatePath, -1);
			}
		}


		if ( substr($templatePath, 0, 1) == '/' )
		{
			$templatePath = substr($templatePath, 1);
		}
		if ( substr($templatePath, -1) != '/' )
		{
			$templatePath .= '/';
		}

		if ( substr($currentTemplatePath, -1) !== '/' )
		{
			$currentTemplatePath .= '/';
		}


		$currentTemplatePath = str_replace($this->compiler->getTemplateDir(), '', $currentTemplatePath);
		$currentTemplatePath = str_replace(ROOT_PATH, '', $currentTemplatePath);


		$compilefile = str_replace('//', '/', str_replace($compileDir, '', ROOT_PATH . $templatePath . $currentTemplatePath . 'module-blocks/' . $tplKeyName . $name));
		$compilefile = str_replace('/module-blocks/module-blocks/', '/module-blocks/', $compilefile);
		$compilefile = str_replace(ROOT_PATH, '', $compilefile);

		##############$content = $this->tag->getTagContent();


		$baseScopes = $this->compiler->getScopes();
		$this->compiler->clearScopes();


        /**
         * new code
         */

        $node = $this->tag->getNode();
        $code = '';
        //$this->tag->getTemplateInstance()->compile($node['children'],$code,0,false);


        if (!$node[ 'children' ] instanceof SplQueue)
        {
            throw new Compiler_Exception('The Block is not a SplQueue! '. htmlspecialchars($this->tag->getTagSource()) );
        }

        if (isset($node['children']) && !($node[ 'children' ] instanceof SplQueue))
        {
            die('invalid spl');
        }

        if ( !$node['children']->count() ) {
            return;
        }

        #print_r($node['children']);exit;

        $this->tag->getTemplateInstance()->_processCompile($node['children'],$code);

        $compilefilepath =  $compileDir . $compilefile .'.php';

        #$code = implode('', $code);
        $baseProxy = $this->tag->getCompiler()->getTemplate()->isProxyTemplate;
        $this->tag->getCompiler()->getTemplate()->isProxyTemplate = true;
        $this->tag->getTemplateInstance()->cleanCompiledCode( $compilefilepath, $code, true);
        $this->tag->getCompiler()->getTemplate()->isProxyTemplate = $baseProxy;

        $code = null;

        #$cs = $this->tag->countChildren();

        $this->tag->removeChildren();
        $this->set( 'remove_children', true );
        #$cs2 = $this->tag->countChildren();


        if (isset($node['children'])) {
            unset($node['children']);
        }

       // die($this->tag->);


       # $this->compiler->setBlockCode($name, $code );


        // reset compiler output dir
        $this->compiler->setCompileDir($compileDir);

        $checking = '$this->registerBlocks(\'' . $name . '\', \'' . str_replace($compileDir, '', $compilefilepath) . '\', ' . 'array(\'append\' => \'' . $appendto . '\', \'prepend\' => \'' . $prependto . '\' ), __FILE__);';


        $this->compiler->addUsedBlock($name, $checking);



        return;
	}

}

