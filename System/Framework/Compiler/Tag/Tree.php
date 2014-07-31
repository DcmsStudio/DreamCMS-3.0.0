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
 * @file         Tree.php
 */

class Compiler_Tag_Tree extends Compiler_Tag_Abstract
{
	/**
	 * @var string
	 */
	protected static $mode = 'ul';

	/**
	 * @var int
	 */
	protected  $cnt = 0;



	/**
	 *
	 */
	public function configure()
	{
		$this->tag->setAttributeConfig(
			array(
			     'name'       => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::ID ),
			     'primarykey' => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::ID ),
			     'parentkey'  => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::ID ),
			     'key'        => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::ID ),
			     'mode'       => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::HARD_STRING ),
			     'class'      => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::HARD_STRING ),
			     'style'      => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::HARD_STRING ),
			     'id'         => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::HARD_STRING ),
			     'maxdeep'    => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::NUMBER ),
                 'nomapping'    => array(
                     Compiler_Attribute::OPTIONAL,
                     Compiler_Attribute::BOOL )
			)
		);
	}

	public function process()
	{
		$this->set( 'nophp', false );

		if ( $this->tag->isEndTag() )
		{
			return;
		}

		Compiler::$cnt++;
		$cnt = Compiler::$cnt;
		$this->cnt = $cnt;

		$name = $this->getAttributeValue( 'name' );
		$primarykey = $this->getAttributeValue( 'primarykey' );
		$parentkey = $this->getAttributeValue( 'parentkey' );
        $nomapping = $this->getAttributeValue( 'nomapping' );


		if ( substr( $name, 0, 1 ) !== '$' )
		{
			$name = $this->tag->getTemplateInstance()->compileVariable( '$' . $name );
		}


		$key = $this->getAttributeValue( 'key' );
		$scopenamebase = ($key !== '' ? $key . $cnt : 'scope' . $cnt);

		$maxdeep = $this->getAttributeValue( 'maxdeep' );
		$maxdeep = ((int)$maxdeep > 0 ? (int)$maxdeep : 20);


		$mode = self::$mode;
		$_mode = $this->getAttributeValue( 'mode' );
		if ( $_mode )
		{
			$mode = $_mode;
		}

		$_nolist = $this->getAttributeValue( 'nolist' );
		$_id = $this->getAttributeValue( 'id' );
		$_class = $this->getAttributeValue( 'class' );
		$_style = $this->getAttributeValue( 'style' );


		$modeExtra = '';
		if ( $_id )
		{
			$modeExtra .= ' id="' . $_id . '"';
		}

		if ( $_class )
		{
			$modeExtra .= ' class="' . $_class . '"';
		}

		if ( $_style )
		{
			$modeExtra .= ' style="' . $_style . '"';
		}


		$_dataLink = $name;

		$_cleanname = $this->getAttributeValue( 'name' );

		if (strpos($_cleanname, '$') !== false) {
			$_cleanname = substr($_cleanname, 1);
		}
		$_cleanname = str_replace('.', '_', $_cleanname);
		$scopename = $scopenamebase . '_'. $_cleanname;



		// the Tree template
		// find last end Tag from first block tag
		$element = trim( $this->tag->getTagContent() );


		$elementpos = strrpos( $element, '</' );
		$endtag = substr( $element, $elementpos );

        if ($this->compiler->getScope($key) !== null) {

            $v = $this->compiler->getScope($key);
#die($v);
            do {
                Compiler::$cnt++;
                $this->__index = Compiler::$cnt;
                $v = $this->compiler->getScope($key);
                $scopename = $scopenamebase . Compiler::$cnt . '_'. $_cleanname;
            }
            while ($v == '_tree_' . $scopename);
        }

		// remove last element from the first child
		$this->compiler->addScope( $key . 'tree', '_tree_' . $scopename, $this->tag->getTagSource() );

        // @todo rewrite function _Tree_{$cnt}_{$_cleanname}() to a static function??? problems with sub tree values!!! and rewrite Tree::mapTree() to Compiler::mapTree()



		$codeStart = <<<EOF
\$obCode = ob_get_clean();
\$compilerInstance = \$this;

    if ( !function_exists('_Tree_{$cnt}_{$_cleanname}') )
    {
        function _Tree_{$cnt}_{$_cleanname}(&\$nodes, \$indent = 0)
        {
            if (!is_array(\$nodes)) { return false; }

            global \$compilerInstance;

            \$levellimit = {$maxdeep};
            \$index = 1;
            foreach (\$nodes as \$tmp_key{$cnt} => \$_tree_{$scopename} )
            {
                \$_tree_{$scopename}['tree_index'] = \$index;
                Compiler::\$_staticData['{$key}'] = \$_tree_{$scopename};


EOF;


		$codeEnd = <<<EOF
                // Shows only X sub – level
                if(is_array(\$_tree_{$scopename}['_children']) && ({$maxdeep} > 0 && \$levellimit<={$maxdeep}) )
                {
                    if ( count(\$_tree_{$scopename}['_children'])) {
                        \$obContent = ob_get_clean();
                        ob_start();
                        echo preg_replace('#'. preg_quote('{$endtag}') .'$#', '', trim(\$obContent));

                        echo '<{$mode}>';
                        _Tree_{$cnt}_{$_cleanname}(\$_tree_{$scopename}['_children'], \$indent+1);
                        echo '</{$mode}>';
                        echo '{$endtag}';
                    }
                }

                ++\$index;

                //echo '{$endtag}';
            } // end foreach
        }//ENDTree
    }

    ob_start();
    echo \$obCode;

if (is_array({$_dataLink}) && count({$_dataLink})) {

EOF;


        if ($nomapping) {
            $codeEnd .= <<<EOF
            \$treeMap{$cnt}_{$_cleanname} =& {$_dataLink};
EOF;
        }
        else {
            $codeEnd .= <<<EOF
            \$treeMap{$cnt}_{$_cleanname} =& Tree::mapTree({$_dataLink}, '{$primarykey}', '{$parentkey}' );
EOF;
        }



		if ( empty( $_nolist ) )
		{
			$modeExtra = addcslashes( $modeExtra, "'" );
			$codeEnd .= "echo '<{$mode}{$modeExtra}>';";
		}

		$codeEnd .= "_Tree_{$cnt}_{$_cleanname}(\$treeMap{$cnt}_{$_cleanname}, 0);";

		if ( empty( $_nolist ) )
		{
			$codeEnd .= "echo '</{$mode}>';";
		}


		$codeEnd .= <<<EOF

    Compiler::\$_staticData['{$key}'] = \$treeMap{$cnt}_{$_cleanname} = null; // clear
}

EOF;

		$this->set( 'nothisvars', true );

		$this->setStartTag( $codeStart );
		$this->setEndTag( $codeEnd );
	}


	/**
	 * remove the used scope
	 */
	public function postProcess()
	{
		$this->set( 'nothisvars', false );
		$this->set( 'nophp', false );

		#$key = $this->getAttributeValue( 'key' );
		#$this->compiler->removeScope( $key . 'tree', $this->cnt );
	}

}