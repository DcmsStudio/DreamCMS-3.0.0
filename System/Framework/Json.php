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
 * @category    Framework
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Json.php
 *
 */
class Json
{

    /**
     * How objects should be encoded -- arrays or as StdClass. TYPE_ARRAY is 1
     * so that it is a boolean true value, allowing it to be used with
     * ext/json's functions.
     */
    const TYPE_ARRAY = 1;

    const TYPE_OBJECT = 0;

    /**
     * To check the allowed nesting depth of the XML tree during xml2json conversion.
     *
     * @var int
     */
    public static $maxRecursionDepthAllowed = 25;

    /**
     * @var bool
     */
    public static $useBuiltinEncoderDecoder = true;

	public static $prettyOutput = false;

    /**
     * Decodes the given $encodedValue string which is
     * encoded in the JSON format
     *
     * Uses ext/json's json_decode if available.
     *
     * @param string $encodedValue Encoded in JSON format
     * @param int $objectDecodeType Optional; flag indicating how to decode objects.
     * @throws BaseException
     * @return mixed
     */
    public static function decode( $encodedValue, $objectDecodeType = Json::TYPE_ARRAY )
    {
        $encodedValue = (string) $encodedValue;
        if ( function_exists( 'json_decode' ) && self::$useBuiltinEncoderDecoder !== true )
        {
            $decode = json_decode( $encodedValue, $objectDecodeType );

            // php < 5.3
            if ( !function_exists( 'json_last_error' ) )
            {
                if ( $decode === $encodedValue )
                {
                    throw new BaseException( 'Decoding failed' );
                }
                // php >= 5.3
            }
            elseif ( ($jsonLastErr = json_last_error()) != JSON_ERROR_NONE )
            {
                switch ( $jsonLastErr )
                {
                    case JSON_ERROR_DEPTH:
                        throw new BaseException( 'Decoding failed: Maximum stack depth exceeded' );

                    case JSON_ERROR_CTRL_CHAR:
                        throw new BaseException( 'Decoding failed: Unexpected control character found' );

                    case JSON_ERROR_SYNTAX:
                        throw new BaseException( 'Decoding failed: Syntax error' );

                    default:
                        throw new BaseException( 'Decoding failed' );
                }
            }

            return $decode;
        }

        return Json_Decoder::decode( $encodedValue, $objectDecodeType );
    }

    /**
     * @param $m
     * @return string
     */
    public static function _futf8($m) {
        return chr( ord($m[1])<<6&0xC0 | ord($m[2])&0x3F );
    }


    /**
     * @param $dat
     * @return array|mixed|string
     */
    protected static function array_utf8_encode_recursive( $dat )
    {
        if ( is_string( $dat ) )
        {
            if ( trim($dat)) {
                $dat = Strings::fixLatin( $dat );

                $dat = preg_replace_callback("/([\xC2\xC3])([\x80-\xBF])/", create_function('$m', 'return chr( ord($m[1])<<6&0xC0 | ord($m[2])&0x3F );'), $dat);

                $dat = utf8_encode( $dat );
            }
            return $dat; // str_replace( array("\u00a0", "\x{00A0}"), " ", $dat ); // patch for space char
        }

        if ( is_object( $dat ) )
        {
            $ovs = get_object_vars( $dat );
            $new = $dat;
            foreach ( $ovs as $k => $v )
            {
                $new->$k = self::array_utf8_encode_recursive( $new->$k );
            }
            return $new;
        }

        if ( !is_array( $dat ) )
            return $dat;

        $ret = array();
        foreach ( $dat as $i => $d )
            $ret[ $i ] = self::array_utf8_encode_recursive( $d );

        return $ret;
    }

	// original code: http://www.daveperrett.com/articles/2008/03/11/format-json-with-php/
	// adapted to allow native functionality in php version >= 5.4.0

	/**
	 * Format a flat JSON string to make it more human-readable
	 *
	 * @param string $json The original JSON string to process
	 *        When the input is not a string it is assumed the input is RAW
	 *        and should be converted to JSON first of all.
	 * @return string Indented version of the original JSON string
	 */
	private static function json_format($json) {
		if (!is_string($json)) {
			if (phpversion() && phpversion() >= 5.4) {
				return json_encode($json, JSON_PRETTY_PRINT);
			}
			$json = json_encode($json);
		}
		$result      = '';
		$pos         = 0;               // indentation level
		$strLen      = strlen($json);
		$indentStr   = "\t";
		$newLine     = "\n";
		$prevChar    = '';
		$outOfQuotes = true;

		for ($i = 0; $i < $strLen; $i++) {
			// Grab the next character in the string
			$char = substr($json, $i, 1);

			// Are we inside a quoted string?
			if ($char === '"' && $prevChar !== '\\') {
				$outOfQuotes = !$outOfQuotes;
			}
			// If this character is the end of an element,
			// output a new line and indent the next line
			else if (($char === '}' || $char === ']') && $outOfQuotes) {
				$result .= $newLine;
				$pos--;
				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}
			// eat all non-essential whitespace in the input as we do our own here and it would only mess up our process
			else if ($outOfQuotes && false !== strpos(" \t\r\n", $char)) {
				continue;
			}

			// Add the character to the result string
			$result .= $char;
			// always add a space after a field colon:
			if ($char === ':' && $outOfQuotes) {
				$result .= ' ';
			}

			// If the last character was the beginning of an element,
			// output a new line and indent the next line
			if (($char === ',' || $char === '{' || $char === '[') && $outOfQuotes) {
				$result .= $newLine;
				if ($char === '{' || $char === '[') {
					$pos++;
				}
				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}
			$prevChar = $char;
		}

		return $result;
	}

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * Encodes using ext/json's json_encode() if available.
     *
     * NOTE: Object should not contain cycles; the JSON format
     * does not allow object reference.
     *
     * NOTE: Only public variables will be encoded
     *
     * NOTE: Encoding native javascript expressions are possible using Zend_Json_Expr.
     *       You can enable this by setting $options['enableJsonExprFinder'] = true
     *
     * @param  mixed $valueToEncode
     * @param  boolean $cycleCheck Optional; whether or not to check
     *         for object recursion; off by default
     * @param  array $options Additional options used during encoding
     * @throws BaseException
     * @return string JSON encoded object
     */
    public static function encode( $valueToEncode, $cycleCheck = false, $options = array() )
    {

	    // prepare json function strings
	    $value_arr = array();
	    $replace_keys = array();
	    foreach($valueToEncode as $key => &$value)
	    {
		    // Look for values starting with 'function('
		    if(is_string($value) && strpos($value, 'function(') === 0 )
		    {
			    // Store function string.
			    $value_arr[] = $value;

			    // Replace function string in $foo with a 'unique' special key.
			    $value = '%' . $key . '%';

			    // Later on, we'll look for the value, and replace it.
			    $replace_keys[] = '"' . $value . '"';
		    }
	    }

        if ( is_object( $valueToEncode ) && method_exists( $valueToEncode, 'toJson' ) )
        {
            $json = $valueToEncode->toJson();
	        return str_replace($replace_keys, $value_arr, $json );
        }

        // Pre-encoding look for Zend_Json_Expr objects and replacing by tmp ids
        $javascriptExpressions = array();
        if ( isset( $options[ 'enableJsonExprFinder' ] ) && ($options[ 'enableJsonExprFinder' ] === true) )
        {
            $valueToEncode = self::_recursiveJsonExprFinder( $valueToEncode, $javascriptExpressions );
        }

        $valueToEncode = self::array_utf8_encode_recursive( $valueToEncode );

        // Encoding
        if ( function_exists( 'json_encode' ) && self::$useBuiltinEncoderDecoder !== true )
        {
            if ( function_exists( 'json_last_error' ) )
            {
                $encodedResult = json_encode( $valueToEncode, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP );

                if ( ($jsonLastErr = json_last_error()) != JSON_ERROR_NONE )
                {
                    switch ( $jsonLastErr )
                    {
                        case JSON_ERROR_DEPTH:
                            throw new BaseException( 'Encoding failed: Maximum stack depth exceeded' );

                        case JSON_ERROR_CTRL_CHAR:
                            throw new BaseException( 'Encoding failed: Unexpected control character found' );

                        case JSON_ERROR_SYNTAX:
                            throw new BaseException( 'Encoding failed: Syntax error' );

                        case JSON_ERROR_UTF8:
                            throw new BaseException( 'Encoding failed: Invalid UTF-8' );

                        default:
                            throw new BaseException( 'Encoding failed! ' . $jsonLastErr );
                    }


                    //exit;
                }
            }
            else
            {

                $encodedResult = json_encode( $valueToEncode );

            }
        }
        else
        {
            $encodedResult = Json_Encoder::encode( $valueToEncode, $cycleCheck, $options );
        }

        //only do post-proccessing to revert back the Json_Expr if any.
        if ( count( $javascriptExpressions ) > 0 )
        {
            $count = count( $javascriptExpressions );
            for ( $i = 0; $i < $count; $i++ )
            {
                $magicKey = $javascriptExpressions[ $i ][ 'magicKey' ];
                $value = $javascriptExpressions[ $i ][ 'value' ];

                $encodedResult = str_replace(
                        //instead of replacing "key:magicKey", we replace directly
                        //magicKey by value because "key" never changes.
                        '"' . $magicKey . '"', $value, $encodedResult
                );
            }
        }


	    if (self::$prettyOutput) {
		    $encodedResult = self::json_format($encodedResult);
	    }

	    return str_replace($replace_keys, $value_arr, $encodedResult );
    }

    /**
     * Check & Replace DCMS_Json_Expr for tmp ids in the valueToEncode
     *
     * Check if the value is a DCMS_Json_Expr, and if replace its value
     * with a magic key and save the javascript expression in an array.
     *
     * NOTE this method is recursive.
     * NOTE: This method is used internally by the encode method.
     *
     * @param $value
     * @param array $javascriptExpressions
     * @param null $currentKey
     * @internal param mixed $valueToCheck a string - object property to be encoded
     * @return void
     */
    protected static function _recursiveJsonExprFinder( &$value, array &$javascriptExpressions, $currentKey = null )
    {
        if ( $value instanceof Json_Expr )
        {
            // TODO: Optimize with ascii keys, if performance is bad
            $magicKey = "____" . $currentKey . "_" . (count( $javascriptExpressions ));
            $javascriptExpressions[] = array(
                //if currentKey is integer, encodeUnicodeString call is not required.
                "magicKey" => (is_int( $currentKey )) ? $magicKey : DCMS_Json_Encoder::encodeUnicodeString( $magicKey ),
                "value"    => $value->__toString(),
            );
            $value = $magicKey;
        }
        elseif ( is_array( $value ) )
        {
            foreach ( $value as $k => $v )
            {
                $value[ $k ] = self::_recursiveJsonExprFinder( $value[ $k ], $javascriptExpressions, $k );
            }
        }
        elseif ( is_object( $value ) )
        {
            foreach ( $value as $k => $v )
            {
                $value->$k = self::_recursiveJsonExprFinder( $value->$k, $javascriptExpressions, $k );
            }
        }
        return $value;
    }

    /**
     * Return the value of an XML attribute text or the text between
     * the XML tags
     *
     * In order to allow DCMS_Json_Expr from xml, we check if the node
     * matchs the pattern that try to detect if it is a new Zend_Json_Expr
     * if it matches, we return a new Json_Expr instead of a text node
     *
     * @param SimpleXMLElement $simpleXmlElementObject
     * @return DCMS_Json_Expr|string
     */
    protected static function _getXmlValue( $simpleXmlElementObject )
    {
        $pattern = '/^[\s]*new Json_Expr[\s]*\([\s]*[\"\']{1}(.*)[\"\']{1}[\s]*\)[\s]*$/';
        $matchings = array();
        $match = preg_match( $pattern, $simpleXmlElementObject, $matchings );

        if ( $match )
        {
            return new Json_Expr( $matchings[ 1 ] );
        }
        else
        {
            return (trim( strval( $simpleXmlElementObject ) ));
        }
    }

    /**
     * _processXml - Contains the logic for xml2json
     *
     * The logic in this function is a recursive one.
     *
     * The main caller of this function (i.e. fromXml) needs to provide
     * only the first two parameters i.e. the SimpleXMLElement object and
     * the flag for ignoring or not ignoring XML attributes. The third parameter
     * will be used internally within this function during the recursive calls.
     *
     * This function converts the SimpleXMLElement object into a PHP array by
     * calling a recursive (protected static) function in this class. Once all
     * the XML elements are stored in the PHP array, it is returned to the caller.
     *
     * Throws a Json_Exception if the XML tree is deeper than the allowed limit.
     *
     * @param SimpleXMLElement $simpleXmlElementObject
     * @param boolean $ignoreXmlAttributes
     * @param integer $recursionDepth
     * @throws BaseException
     * @return array
     */
    protected static function _processXml( &$simpleXmlElementObject, $ignoreXmlAttributes, $recursionDepth = 0 )
    {
        // Keep an eye on how deeply we are involved in recursion.
        if ( $recursionDepth > self::$maxRecursionDepthAllowed )
        {
            // XML tree is too deep. Exit now by throwing an exception.
            throw new BaseException(
            "Function _processXml exceeded the allowed recursion depth of " .
            self::$maxRecursionDepthAllowed );
        }

        $childrens = $simpleXmlElementObject->children();
        $name = $simpleXmlElementObject->getName();
        $value = self::_getXmlValue( $simpleXmlElementObject );
        $attributes = (array) $simpleXmlElementObject->attributes();

        if ( count( $childrens ) === 0 )
        {
            if ( !empty( $attributes ) && !$ignoreXmlAttributes )
            {
                foreach ( $attributes[ '@attributes' ] as $k => $v )
                {
                    $attributes[ '@attributes' ][ $k ] = self::_getXmlValue( $v );
                }
                if ( !empty( $value ) )
                {
                    $attributes[ '@text' ] = $value;
                }
                return array(
                    $name => $attributes );
            }
            else
            {
                return array(
                    $name => $value );
            }
        }
        else
        {
            $childArray = array();
            foreach ( $childrens as $child )
            {
                $childname = $child->getName();
                $element = self::_processXml( $child, $ignoreXmlAttributes, $recursionDepth + 1 );
                if ( isset($childArray[$childname])  )
                {
                    if ( empty( $subChild[ $childname ] ) )
                    {
                        $childArray[ $childname ] = array(
                            $childArray[ $childname ] );
                        $subChild[ $childname ] = true;
                    }
                    $childArray[ $childname ][] = $element[ $childname ];
                }
                else
                {
                    $childArray[ $childname ] = $element[ $childname ];
                }
            }
            if ( !empty( $attributes ) && !$ignoreXmlAttributes )
            {
                foreach ( $attributes[ '@attributes' ] as $k => $v )
                {
                    $attributes[ '@attributes' ][ $k ] = self::_getXmlValue( $v );
                }
                $childArray[ '@attributes' ] = $attributes[ '@attributes' ];
            }
            if ( !empty( $value ) )
            {
                $childArray[ '@text' ] = $value;
            }
            return array(
                $name => $childArray );
        }
    }

    /**
     * fromXml - Converts XML to JSON
     *
     * Converts a XML formatted string into a JSON formatted string.
     * The value returned will be a string in JSON format.
     *
     * The caller of this function needs to provide only the first parameter,
     * which is an XML formatted String. The second parameter is optional, which
     * lets the user to select if the XML attributes in the input XML string
     * should be included or ignored in xml2json conversion.
     *
     * This function converts the XML formatted string into a PHP array by
     * calling a recursive (protected static) function in this class. Then, it
     * converts that PHP array into JSON by calling the "encode" static funcion.
     *
     * Throws a Json_Exception if the input not a XML formatted string.
     * NOTE: Encoding native javascript expressions via Zend_Json_Expr is not possible.
     *
     * @static
     * @access public
     * @param string $xmlStringContents XML String to be converted
     * @param boolean $ignoreXmlAttributes Include or exclude XML attributes in
     *        the xml2json conversion process.
     * @return mixed - JSON formatted string on success
     * @throws BaseException
     */
    public static function fromXml( &$xmlStringContents, $ignoreXmlAttributes = true )
    {
        // Load the XML formatted string into a Simple XML Element object.
        $simpleXmlElementObject = simplexml_load_string( $xmlStringContents );

        // If it is not a valid XML content, throw an exception.
        if ( $simpleXmlElementObject === null )
        {
            throw new BaseException( 'Function fromXml was called with an invalid XML formatted string.' );
        }

        $resultArray = null;

        // Call the recursive function to convert the XML into a PHP array.
        $resultArray = self::_processXml( $simpleXmlElementObject, $ignoreXmlAttributes );

        // Convert the PHP array to JSON using Zend_Json encode method.
        // It is just that simple.
        $jsonStringOutput = self::encode( $resultArray );
        return ($jsonStringOutput);
    }

}

?>