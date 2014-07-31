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
 * @file         Provider.php
 */

class Compiler_Tag_Custom_Provider extends Compiler_Tag_Abstract {
    private static $cn = 0;
    /**
     *
     */
    public function configure()
    {
        $this->tag->setAttributeConfig(
            array(
                'pname'     => array(
                    Compiler_Attribute::REQUIRED,
                    Compiler_Attribute::HARD_STRING )
            )
        );
    }

    /**
     * @param $variable
     * @param bool $return
     * @return mixed|null|string
     */
    private function improved_var_export ($variable, $return = false) {
        if ($variable instanceof stdClass) {
            $result = '(object) '.$this->improved_var_export(get_object_vars($variable), true);
        } else if (is_array($variable)) {
            $array = array ();
            foreach ($variable as $key => $value) {
                $array[] = var_export($key, true).' => '. $this->improved_var_export($value, true);
            }
            $result = 'array ('.implode(', ', $array).')';
        } else {
            $result = var_export($variable, true);
        }

        if (!$return) {
            print $result;
            return null;
        } else {
            return $result;
        }
    }
    public function process()
    {
        if ( $this->tag->isEndTag() )
        {
            return;
        }


        $name = $this->tag->getAttribute('pname');
        $attr = $this->tag->getAttributesArray();
        unset($attr['pname']);



        $newattr = array();
        $attr = $this->tag->getAttributes();


        $gencode = 'array(';

        foreach ($attr as $att)
        {
            if ( $att instanceof Compiler_Attribute )
            {
                $v = $att->getValue(true);
                $k = $att->getXmlName();
                if ($k === 'pname')
                {
                    continue;
                }



                // detect type
                $use = Compiler_Attribute::STRING;

                if ($v === 'false' || $v === 'true' || $v === 'on' || $v === 'off')
                {
                    $use = Compiler_Attribute::BOOL;
                }
                elseif (ctype_digit($v))
                {
                    $use = Compiler_Attribute::NUMBER;
                }

                $result = $att->getValue(false, $use );

                if (is_array($result) )
                {
                    $result = $result[0];
                }

                if (isset($result) && $result !== $v)
                {
                    // little patch
                    if ($use === Compiler_Attribute::BOOL) {
                        if ( $result ) {
                            $result = 'true';
                        }
                        else {
                            $result = 'false';
                        }
                    }

                    if (strpos($result, '\'') === false && substr($result, 0, 1) === '"' && substr($result, -1) === '"')
                    {
                        $result = '\''. substr($result, 1, -1) . '\'';
                    }

                    if ( strpos( $result, 'constant(\'' ) === false && strpos( $result, 'User::get' ) === false && strpos( $result, 'HTTP::' ) === false && strpos( $result, 'Session::get' ) === false && strpos( $result, 'Cookie::get' ) === false
                    )
                    {
                        $gencode .= '\''. $k .'\' => ';
                        $gencode .= $result.',';

                    }
                    else {
                        $gencode .= '\''. $k .'\' => ';
                        if (strpos($v, '"') !== false) {
                            $gencode .= $result.',';
                        }
                        else {
                            $gencode .= $result.',';
                        }
                    }
                }
                else {

                    if ($use === Compiler_Attribute::BOOL)
                    {
                        if ( $result ) {
                            $result = 'true';
                        }
                        else {
                            $result = 'false';
                        }
                    }

                    if (strpos($result, '\'') === false && substr($result, 0, 1) === '"' && substr($result, -1) === '"')
                    {
                        $result = '\''. substr($result, 1, -1) . '\'';
                    }

                    $gencode .= '\''. $k .'\' => ';
                    $gencode .= $result.',';

                }
            }
        }

        if (substr($gencode, -1) == ',') {
            $gencode = substr($gencode,0, -1);
        }

        $gencode .= ')';

        self::$cn++;


        $code = '
$attr'.self::$cn.' = '.$gencode.';
echo Provider::process(\''. $name .'\', $attr'.self::$cn.');
$attr'.self::$cn.' = null;';
        $this->setStartTag($code);
    }



}



/*
        foreach ( $attr as $idx => $att )
        {
            if ( $att instanceof Compiler_Attribute )
            {
                $v = $att->getValue( true );
                $k = $att->getXmlName();
                if ( $k === 'pname' )
                {
                    continue;
                }

                // detect type
                $use = Compiler_Attribute::STRING;

                if ( $v === 'false' || $v === 'true' || $v === 'on' || $v === 'off' )
                {
                    $use = Compiler_Attribute::BOOL;
                }
                elseif ( ctype_digit( $v ) )
                {
                    $use = Compiler_Attribute::NUMBER;
                }


                #$gencode .= '\'' . $k . '\' => ';

                $value = $att->getValue( true, $use );


                                if ( $value )
                                {
                                    $value = $templateInstance->postCompiler( $value );

                                    if ( strpos( $value, Compiler::PHP_OPEN ) !== false && strpos( $value, Compiler::PHP_CLOSE ) !== false )
                                    {
                                        $list = explode( Compiler::PHP_CLOSE, $value );

                                        $tmp    = array();
                                        $len    = count( $list );
                                        $endPhp = false;

                                        for ( $i = 0; $i < $len; ++$i )
                                        {
                                            $r = explode( Compiler::PHP_OPEN, $list[ $i ] );


                                            if ( trim( $r[ 0 ] ) && trim( $r[ 1 ] ) )
                                            {

                                                if ( isset( $tmp[ $i - 1 ] ) && $endPhp )
                                                {
                                                    $tmp[ ] = '.';
                                                }

                                                $tmp[ ] = $templateInstance->compileString( $r[ 0 ] );

                                                $tmp[ ] = '.(' . preg_replace( '#'. preg_quote(Compiler::PHP_OPEN, '#').'#', ' ', preg_replace( '#;\s*$#', ')', preg_replace( '#\s*echo\s*#', '', $r[ 1 ] ) ));
                                                $endPhp = true;
                                            }
                                            elseif ( trim( $r[ 0 ] ) )
                                            {
                                                if ( isset( $tmp[ $i - 1 ] ) && $endPhp )
                                                {
                                                    $tmp[ ] = '.';
                                                }

                                                $tmp[ ] = $templateInstance->compileString( $r[ 0 ] );

                                                $endPhp = false;


                                                //$tmp[ ] = '2(' . preg_replace( '#'. preg_quote(Compiler::PHP_OPEN, '#').'#', ' b ', preg_replace( '#;\s*$#', ')', preg_replace( '#\s*echo\s*#', '', $r[ 1 ] ) ))."\n";

                                            }
                                            elseif (trim( $list[ $i ] ))
                                            {
                                                if ( isset( $tmp[ $i - 1 ] ) && substr( $tmp[ $i - 1 ], 0, 2 ) == '(' )
                                                {
                                                    $tmp[ ] = '.';
                                                }

                                                $tmp[ ] = '(' .  preg_replace( '#;\s*$#', ') ', preg_replace( '#\s*echo\s*#', '', preg_replace( '#\s*'. preg_quote(Compiler::PHP_OPEN, '#').'\s*#', ' ',$list[ $i ] ) ) );
                                                $endPhp = true;
                                            }
                                        }

                                        $gencode .= implode( '', $tmp );
                                    }
                                    else {


                                        if (stripos($value, 'true') !== false || stripos($value, 'false') !== false )
                                        {
                                            $gencode .= $value;
                                        }
                                        elseif (is_numeric($value) || is_float($value)) {
                                            $gencode .= $value;
                                        }
                                        else {

                                            if (!preg_match( $templateInstance->_rExpressionTag, $value) &&
                                                !preg_match( $templateInstance->_rFunctions, $value) &&
                                                !preg_match('/^([a-zA-Z0-9_]+)\s*\((.+?)\)$/', $value) &&
                                                !preg_match( $templateInstance->_rConstante, $value) &&
                                                !preg_match( $templateInstance->_rVariable, $value)
                                            )
                                            {
                                                $gencode .= $templateInstance->compileString( $value ) ;
                                            }
                                            else
                                            {
                                                $result = $templateInstance->compileExpression($value);

                                                if (isset($result[0])) {
                                                    $gencode .= $result[0] ;
                                                }
                                                else {
                                                    $gencode .= $templateInstance->compileString( $value ) ;
                                                }
                                            }
                                        }
                                    }


                                    if (isset($attr[$idx]) && !empty($attr[$idx]))
                                    {
                                        $gencode .= ",\n";
                                    }

                                    $this->tag->clearAttribute($k);

                                }
                                else
                                {
                                    $gencode .= '\'\',';
                                }



                $result = $att->getValue(false, $use );

                if ( isset( $result[ 0 ] ) && $result[ 0 ] !== $v )
                {
                    // little patch
                    if ( $use === Compiler_Attribute::BOOL )
                    {
                        if ( $result[ 0 ] )
                        {
                            $result[ 0 ] = 'true';
                        }
                        else
                        {
                            $result[ 0 ] = 'false';
                        }
                    }

                    if ( strpos( $result[ 0 ], '\'' ) === false && substr( $result[ 0 ], 0, 1 ) === '"' && substr( $result[ 0 ], -1 ) === '"' )
                    {
                        $result[ 0 ] = '\'' . substr( $result[ 0 ], 1, -1 ) . '\'';
                    }


                    if ( strpos( $result, 'constant(\'' ) === false && strpos( $result, 'User::get' ) === false && strpos( $result, 'HTTP::' ) === false && strpos( $result, 'Session::get' ) === false && strpos( $result, 'Cookie::get' ) === false
                    )
                    {
                        $gencode .= '\'' . $k . '\' => ';
                        $gencode .= $result[ 0 ] . ',';

                    }
                    else
                    {
                        $gencode .= '\'' . $k . '\' => ';
                        if ( strpos( $v, '"' ) !== false )
                        {
                            $gencode .= $result[ 0 ] . ',';
                        }
                        else
                        {
                            $gencode .= $result[ 0 ] . ',';
                        }
                    }
                }
                else
                {

                    if ( $use === Compiler_Attribute::BOOL )
                    {
                        if ( $result )
                        {
                            $result = 'true';
                        }
                        else
                        {
                            $result = 'false';
                        }
                    }

                    if ( strpos( $result, '\'' ) === false && substr( $result, 0, 1 ) === '"' && substr( $result, -1 ) === '"' )
                    {
                        $result = '\'' . substr( $result, 1, -1 ) . '\'';
                    }


                    $gencode .= '\'' . $k . '\' => ';
                    $gencode .= $result . ',';

                }
            }
        }

        */
