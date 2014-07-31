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
 * @package     DreamCMS
 * @version     3.0.0 Beta
 * @category    Template Engine
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        For.php
 */
class Compiler_Tag_For extends Compiler_Tag_Abstract
{

    /**
     * @var int
     */
    protected static $cnt = 0;

    protected static $_scopes = array();


    /**
     *
     */
    public function configure()
    {
        $this->tag->setAttributeConfig(
                array(
                    'from'         => array(
                        Compiler_Attribute::REQUIRED,
                        Compiler_Attribute::EXPRESSION ),
                    'to'           => array(
	                    Compiler_Attribute::REQUIRED,
	                    Compiler_Attribute::EXPRESSION ),
                    'step'         => array(
	                    Compiler_Attribute::OPTIONAL,
	                    Compiler_Attribute::NUMBER ),
                    'math-iterate' => array(
	                    Compiler_Attribute::OPTIONAL,
	                    Compiler_Attribute::MATH_OPERATOR ),
                    'iterate'      => array(
	                    Compiler_Attribute::OPTIONAL,
	                    Compiler_Attribute::OPERATOR )
                )
        );
    }

    public function process()
    {
	    $this->set( 'nophp', false );
        //
        $math_iterate = $this->getAttributeValue( 'math-iterate' );
        $iterate = $this->getAttributeValue( 'iterate' );

        TemplateCompiler::$cnt++;
        $cnt = self::$cnt = TemplateCompiler::$cnt;


        $name = $this->getAttributeValue( 'name' );
        $scopename = ($name ? $name : 'scope' . $cnt);

        //
        $from = $this->getAttributeValue( 'from' );
        $from = is_array( $from ) ? $from[ 0 ] : $from;
        $from = (is_numeric( $from ) ? $from : 0);


        $to = $this->getAttributeValue( 'to' );
        $to = is_array( $to ) ? $to[ 0 ] : $to;

        $step = $this->getAttributeValue( 'step' );
        $step = ($step ? $step : 1);


        if ( $from > $to )
        {
            $condition = '>=';
            $incrementer = '-';

            // reverse from and to if needed
            $out = "
        \$_for{$cnt}_from = {$to};
        \$_for{$cnt}_to   = {$from};
        \$_for{$cnt}_step = abs({$step});
";
        }
        else
        {
            $condition = '<=';
            $incrementer = '+';

            $out = "
	\$_for{$cnt}_from = {$from};
	\$_for{$cnt}_to = {$to};
	\$_for{$cnt}_step = abs({$step});
";
        }


	    $this->compiler->addScope( $scopename, '__for_' . $scopename, $this->tag->getTagSource() );

        self::$_scopes[] = array('__for_' . $scopename, $scopename) ;

        $startCode = <<<EOF
    {$out}
    if ( is_numeric(\$_for{$cnt}_from) && is_numeric(\$_for{$cnt}_to) && (\$_for{$cnt}_to > \$_for{$cnt}_from || \$_for{$cnt}_from > \$_for{$cnt}_to) )
    {
        \$__for_{$scopename} = array();
        for ( \$__for{$scopename} = \$_for{$cnt}_from; \$__for{$scopename} {$condition} \$_for{$cnt}_to; \$__for{$scopename} {$incrementer}= \$_for{$cnt}_step)
        {
            \$__for_{$scopename}['index'] = \$__for{$scopename};
            \$this->d['{$scopename}']['index'] =  \$__for{$scopename};

EOF;


        $this->setStartTag( $startCode );
        $this->setEndTag( '
            }
        } unset($__for_' . $scopename . ', $this->d[\'' . $scopename . '\']);

' );
    }

    public function postProcess()
    {
	    $name = $this->getAttributeValue( 'name' );
        /*
	    $scopename = ($name ? $name : 'scope' . $cnt);
	    $this->compiler->removeScope( $scopename );
        */

        if (sizeof(self::$_scopes))
        {
            #$c = array_shift(self::$_scopes);
            #$this->compiler->removeScope( $c[1], $c[0] );
        }

	    $this->set( 'nophp', false );
    }

}
