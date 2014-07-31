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
 * @file         Combine.php
 */
class Compiler_Tag_Combine extends Compiler_Tag_Abstract
{
    private $d = array();

    /**
     *
     */
    public function configure()
    {

        $this->tag->setAttributeConfig( array(
            'type'     => array(
                Compiler_Attribute::REQUIRED,
                Compiler_Attribute::HARD_STRING
            ),
            'compress' => array(
                Compiler_Attribute::OPTIONAL,
                Compiler_Attribute::BOOL
            )
        ) );
    }

    public function process()
    {

        $type      = $this->getAttributeValue( 'type' );
        $type      = strtolower( $type );
        $compress  = $this->getAttributeValue( 'compress' );
        $childHtml = $this->tag->getTagContent();

        if ( $childHtml )
        {
            $this->mulipleCombine( $childHtml, $type, $compress );
        }

    }


    /**
     * @param $childHtml
     * @param $type
     * @param $compress
     */
    private function mulipleCombine($childHtml, $type, $compress)
    {

        $baseUrl         = Settings::get( 'portalurl', '' );
        $lengthOfBaseUrl = strlen( Settings::get( 'portalurl', '' ) );

        $cacheFiles = array();
        $inlineCode = array();
        $cacheCode  = '';

        $_data = $this->tag->getCompiler()->getData();
        #print_r($_data); exit;

        if ( $type == 'js' || $type == 'script' )
        {
            $child = $this->tag->getChildren();



            if ( is_array( $child ) )
            {
                $_data = Compiler::$_staticData;

                foreach ( $child as $idx => $r )
                {
                    if ( $r[ 'type' ] === Compiler::TAG )
                    {
                        if ( isset( $r[ 'attributes' ] ) && is_array( $r[ 'attributes' ] ) )
                        {
                            $src = $this->getAttr( $r[ 'attributes' ], 'src' );


                            if ( $src )
                            {
                                if ( strpos( $src, '{$' ) !== false )
                                {

                                    $xsrc = $this->tag->getTemplateInstance()->postCompiler( $src );
                                    $xsrc = str_replace( '$this->dat[', '$_data[', $xsrc );

                                    $xsrc = preg_replace( '#^' . preg_quote( Compiler::PHP_OPEN ) . '#', '', $xsrc );
                                    $xsrc = preg_replace( '#' . preg_quote( Compiler::PHP_CLOSE ) . '$#', '', $xsrc );

                                    ob_start();

                                    eval( $xsrc );
                                    $src = trim( ob_get_contents() );
                                    ob_clean();
                                }

                                if ( strpos( $src, 'http:' ) === false && strpos( $src, 'https:' ) === false && strpos( $src, $baseUrl ) === false )
                                {
                                    if ( substr( $src, 0, $lengthOfBaseUrl ) === $baseUrl )
                                    {
                                        $src = substr( $src, $lengthOfBaseUrl );
                                    }

                                    if ( substr( $src, 0, 7 ) === 'Vendor/' )
                                    {
                                        $src = ROOT_PATH . $src;
                                    }
                                    else if ( substr( $src, 0, 9 ) === 'Packages/' )
                                    {
                                        $src = ROOT_PATH . 'System/' . $src;
                                    }
                                    else if ( substr( $src, 0, 7 ) === 'public/' )
                                    {
                                        $src = ROOT_PATH . $src;
                                    }
                                    else if ( substr( $src, 0, 5 ) === 'html/' || substr( $src, 0, 5 ) === 'simg/' )
                                    {
                                        $src = PUBLIC_PATH . $src;
                                    }
                                    else
                                    {
                                        $src = ROOT_PATH . $src;
                                    }

                                    if ( file_exists( $src ) )
                                    {
                                        $cacheFiles[ $idx ] = str_replace( ROOT_PATH, '', $src );
                                        $cacheCode .= file_get_contents( $src );
                                    }
                                }
                                else
                                {


                                    if ( strpos( $src, 'http:' ) === false && strpos( $src, 'https:' ) === false )
                                    {
                                        $remote = Library::getRemoteFile( $src );

                                        if ( is_string( $remote ) && stripos( $remote, '<html' ) && stripos( $remote, '</html>' ) )
                                        {
                                            $cacheFiles[ ] = $src;
                                            $cacheCode .= $remote;
                                        }
                                    }

                                }


                            }
                            else {

                                if ( isset( $r[ 'children' ] ) && ( $r[ 'children' ] instanceof SplQueue ) )
                                {
                                    if ( $r[ 'children' ]->count()  )
                                    {
                                        while ($r[ 'children' ]->valid() )
                                        {
                                            $node = $r[ 'children' ]->dequeue();
                                            if ($node['type'] == Compiler::TAG && $node['tagname'] === 'literal' && isset($node['children'])) {
                                                if ($node['children'] instanceof SplQueue) {
                                                    $rsx = $node[ 'children' ]->dequeue();

                                                    if (isset($rsx['value'])) {
                                                        $count1 = count($cacheFiles);
                                                        if ($count1-1 < $idx)
                                                        {
                                                            $cacheFiles = array_pad($cacheFiles, ($idx ), '');
                                                        }


                                                        $inlineCode[ $idx ] = $rsx[ 'value' ];
                                                        $cacheCode .= $rsx[ 'value' ];
                                                    }
                                                }
                                            }

                                            $r[ 'children' ]->next();
                                        }
                                    }
                                }
                            }
                        }
                        else {

                            if ( isset( $r[ 'children' ] ) && ( $r[ 'children' ] instanceof SplQueue ) )
                            {
                                if ( $r[ 'children' ]->count()  )
                                {
                                    while ($r[ 'children' ]->valid() )
                                    {
                                        $node = $r[ 'children' ]->dequeue();
                                        if ($node['type'] == Compiler::TAG && $node['tagname'] === 'literal' && isset($node['children'])) {
                                            if ($node['children'] instanceof SplQueue) {
                                                $rsx = $node[ 'children' ]->dequeue();

                                                if (isset($rsx['value'])) {
                                                    $count1 = count($cacheFiles);
                                                    if ($count1-1 < $idx)
                                                    {
                                                        $cacheFiles = array_pad($cacheFiles, ($idx ), '');
                                                    }


                                                    $inlineCode[ $idx ] = $rsx[ 'value' ];
                                                    $cacheCode .= $rsx[ 'value' ];
                                                }
                                            }
                                        }

                                        $r[ 'children' ]->next();
                                    }
                                }
                            }
                        }
                    }
                    else {
                        $count1 = count($cacheFiles);
                        if ($count1-1 < $idx)
                        {
                            $cacheFiles = array_pad($cacheFiles, ($idx ), '');
                        }
                    }
                }

                $_data = null;
            }

        }
        elseif ( $type == 'css' || $type == 'style' )
        {
            $child = $this->tag->getChildren();

            if ( is_array( $child ) )
            {
                $_data = Compiler::$_staticData;

                foreach ( $child as $idx => $r )
                {
                    if ( $r[ 'type' ] == Compiler::TAG )
                    {

                        if ( isset( $r[ 'attributes' ] ) && is_array( $r[ 'attributes' ] ) )
                        {
                            $src = $this->getAttr( $r[ 'attributes' ], 'href' );

                            if ( $src )
                            {
                                if ( strpos( $src, '{$' ) !== false )
                                {

                                    $xsrc = $this->tag->getTemplateInstance()->postCompiler( $src );
                                    $xsrc = str_replace( '$this->dat[', '$_data[', $xsrc );

                                    $xsrc = preg_replace( '#^' . preg_quote( Compiler::PHP_OPEN ) . '#', '', $xsrc );
                                    $xsrc = preg_replace( '#' . preg_quote( Compiler::PHP_CLOSE ) . '$#', '', $xsrc );

                                    ob_start();

                                    eval( $xsrc );
                                    $src = trim( ob_get_contents() );
                                    ob_clean();
                                }

                                if ( strpos( $src, 'http:' ) === false && strpos( $src, 'https:' ) === false && strpos( $src, $baseUrl ) === false )
                                {
                                    if ( substr( $src, 0, $lengthOfBaseUrl ) === $baseUrl )
                                    {
                                        $src = substr( $src, $lengthOfBaseUrl );
                                    }

                                    if ( substr( $src, 0, 1 ) === '/' )
                                    {
                                        $src = substr( $src, 1 );
                                    }

                                    if ( substr( $src, 0, 7 ) === 'Vendor/' )
                                    {
                                        $src = ROOT_PATH . $src;
                                    }
                                    else if ( substr( $src, 0, 9 ) === 'Packages/' )
                                    {
                                        $src = ROOT_PATH . 'System/' . $src;
                                    }
                                    else if ( substr( $src, 0, 7 ) === 'public/' )
                                    {
                                        $src = ROOT_PATH . $src;
                                    }
                                    else if ( substr( $src, 0, 5 ) === 'html/' || substr( $src, 0, 5 ) === 'simg/' )
                                    {
                                        $src = PUBLIC_PATH . $src;
                                    }
                                    else
                                    {
                                        $src = ROOT_PATH . $src;
                                    }

                                    if ( file_exists( $src ) )
                                    {
                                        $cacheFiles[ $idx ] = str_replace( ROOT_PATH, '', $src );
                                        $code          = file_get_contents( $src );

                                        $path = explode( '/', $src );
                                        array_pop( $path );
                                        $curentPath = implode( '/', $path ) . '/';

                                        $cacheCode .= self::getCssImports( $code, $curentPath, $compress, $src );
                                    }
                                    else
                                    {
                                        $cacheCode .= '/* File: ' . $src . ' not exists! */';
                                    }
                                }
                                else
                                {
                                    if ( strpos( $src, 'http:' ) !== false || strpos( $src, 'https:' ) !== false )
                                    {
                                        $cacheCode .= '@import url(\'' . $src . '\');';
                                    }
                                }
                            }
                            else
                            {
                                // implode all style tags
                                if ( $r[ 'tagname' ] === 'style' && isset( $r[ 'children' ] ) && ( $r[ 'children' ] instanceof SplQueue ) )
                                {
                                    if ( $r[ 'children' ]->count() === 1 )
                                    {
                                        if ( isset( $r[ 'children' ] ) && ( $r[ 'children' ] instanceof SplQueue ) )
                                        {
                                            if ( $r[ 'children' ]->count()  )
                                            {
                                                while ($r[ 'children' ]->valid() )
                                                {
                                                    $node = $r[ 'children' ]->dequeue();
                                                    if ($node['type'] !== Compiler::TAG && $node['type'] !== Compiler::CDATA && $node['type'] !== Compiler::COMMENT)
                                                    {
                                                        if (isset($node['value'])) {
                                                            $count1 = count($cacheFiles);
                                                            if ($count1-1 < $idx)
                                                            {
                                                                $cacheFiles = array_pad($cacheFiles, ($idx ), '');
                                                            }


                                                            $inlineCode[ $idx ] = $node[ 'value' ];
                                                            $cacheCode .= $node[ 'value' ];
                                                        }
                                                    }

                                                    $r[ 'children' ]->next();
                                                }
                                            }
                                        }
                                    }
                                    else {
                                        $count1 = count($cacheFiles);
                                        if ($count1-1 < $idx)
                                        {
                                            $cacheFiles = array_pad($cacheFiles, ($idx ), '');
                                        }
                                    }
                                }
                            }
                        }
                    }
                    else {
                        $count1 = count($cacheFiles);
                        if ($count1-1 < $idx)
                        {
                            $cacheFiles = array_pad($cacheFiles, ($idx ), '');
                        }
                    }
                }

                $_data = null;
            }
        }

        $hash = md5( $cacheCode );


        if ( $compress == true && !isset( $_codeHashes[ $hash ] ) )
        {
            $_codeHashes[ $hash ] = true;


            if ( $type == 'css' || $type == 'style' )
            {
                if ( $compress )
                {
                    $cacheCode = Compiler_Tag_Combine::compressCssCode( $cacheCode );
                }

                Compiler_Library::makeDirectory( PAGE_CACHE_PATH . 'data/assets' );
                file_put_contents( PAGE_CACHE_PATH . 'data/assets/' . $hash . '.css', $cacheCode );

                $cacheCode = null;

                //Cache::write( $hash . '.css', $cacheCode, 'data/assets' );

                $this->tag->removeChildren();

                $str = Compiler_Abstract::PHP_OPEN;


                $str .= ' $__hash = \'' . $hash . '\'; $__files = ' . Cache::var_export_min( $cacheFiles, true ) . ';$__cssinlineCode = ' . Cache::var_export_min( $inlineCode, true ) . '; $__compress = ' . ( $compress ? 'true' : 'false' ) . ';';
                $str .= '
if (is_file(PAGE_PATH .".cache/data/assets/".$__hash.".css")) { $__mtime = filemtime(PAGE_PATH.\'.cache/data/assets/\'.$__hash.\'.css\');
    $__reloadCache = false;
    foreach($__files as $_f) {
        if ($_f) {
            if (filemtime( ROOT_PATH . $_f) > $__mtime)
            {
                $__reloadCache = true;
                break;
            }
        }
    }
} // end file exists' . "\n";


                $str .= ' if (!is_file(PAGE_PATH .".cache/data/assets/".$__hash.".css") || $__reloadCache) { ';
                $str .= '   $__cacheCode = ""; ';
                $str .= '
foreach($__files as $idx => $__f) {
    if (isset($__cssinlineCode[$idx])) {
        $__cacheCode .= $__cssinlineCode[$idx];
        unset($__cssinlineCode[$idx]);
    }
    else {
        if ($__f && file_exists( ROOT_PATH . $__f) )
        {
            $__code = file_get_contents(  ROOT_PATH . $__f );
            $__path = explode(\'/\',  ROOT_PATH . $__f);
            array_pop($__path);
            $__curentPath = implode(\'/\', $__path) .\'/\';

            $__cacheCode .= Compiler_Tag_Combine::getCssImports($__code, $__curentPath);
        }
    }
}

foreach($__cssinlineCode as $code) {
    $__cacheCode .= $code;
}



if ($__cacheCode)
{
    $__code = null;
    if ($__compress) { Compiler_Tag_Combine::compressCssCode( $__cacheCode ); }
    Compiler_Library::makeDirectory(PAGE_CACHE_PATH . \'data/assets\');
    file_put_contents(PAGE_CACHE_PATH . \'data/assets/\' . $__hash . \'.css\' , $__cacheCode);
    $__cacheCode = null;
}
';

                $str .= ' } ';
                $str .= Compiler_Abstract::PHP_CLOSE;


                $str .= '<link type="text/css" rel="stylesheet" href="' . PAGE_URL_PATH . '.cache/data/assets/' . $hash . '.css" />';

                $this->set( 'nophp', true );
                $this->setStartTag( $str );
                $this->set( 'nophp', false );
            }
            else if ( $type == 'js' || $type == 'script' )
            {
                //  self::compressJsCode( $cacheCode );

                $cacheCode = Minifier::minifyJs( $cacheCode );

                Compiler_Library::makeDirectory( PAGE_CACHE_PATH . 'data/assets' );
                file_put_contents( PAGE_CACHE_PATH . 'data/assets/' . $hash . '.js', $cacheCode );

                $cacheCode = null;

                $this->tag->removeChildren();

                $str = Compiler_Abstract::PHP_OPEN;
                $str .= '
$__jshash = \'' . $hash . '\';
$__jsfiles = ' . Cache::var_export_min( $cacheFiles, true ) . ';
$__jscompress = ' . ( $compress ? 'true' : 'false' ) . ';
$__inlineCode = ' . Cache::var_export_min( $inlineCode, true ) . ';

if (is_file(PAGE_CACHE_PATH ."data/assets/".$__jshash.".js")) {
    $__jsmtime = filemtime(PAGE_CACHE_PATH.\'data/assets/\'.$__jshash.\'.js\');
    $__jsreloadCache = false;
    foreach($__jsfiles as $__f) {
        if ($__f) {
            if (filemtime( ROOT_PATH . $__f) > $__jsmtime)
            {
                $__jsreloadCache = true;
                break;
            }
        }
    }
} // end file exists

if (!is_file(PAGE_CACHE_PATH ."data/assets/".$__jshash.".js") || $__jsreloadCache) {
    $__jscacheCode = "";
    foreach($__jsfiles as $idx => $__f) {

        if (isset($__inlineCode[$idx])) {
            $__jscacheCode .= $__inlineCode[$idx];
            unset($__inlineCode[$idx]);
        }
        else {
            if ($__f && file_exists( ROOT_PATH . $__f) )
            {
                $__jscacheCode .= file_get_contents(  ROOT_PATH . $__f );
            }
        }
    }
    foreach($__inlineCode as $code) {
        $__jscacheCode .= $code;
    }
    if ($__jscacheCode)
    {
        if ($__jscompress) {
            $__jscacheCode = Minifier::minifyJs($__jscacheCode);
        }

        Compiler_Library::makeDirectory(PAGE_CACHE_PATH . \'data/assets\');
        file_put_contents(PAGE_CACHE_PATH . \'data/assets/\' . $__jshash . \'.js\' , $__jscacheCode);
        $__jscacheCode = null;
    }

}';

                $str .= Compiler_Abstract::PHP_CLOSE;

                $str .= '<script type="application/x-javascript" src="' . PAGE_URL_PATH . '.cache/data/assets/' . $hash . '.js"></script>';


                $this->set( 'nophp', true );
                $this->setStartTag( $str );
                $this->set( 'nophp', false );
            }

            $this->set( 'remove_children', true );
        }
        elseif ( $compress !== true && !isset( $_codeHashes[ $hash ] ) )
        {
            $_codeHashes[ $hash ] = true;

            if ( $type == 'css' || $type == 'style' )
            {
                if ( !$cacheCode )
                {
                    $cacheCode = '/* Invalid css Cache Code!
' . htmlspecialchars( $childHtml ) . '
 */';
                }

                Compiler_Library::makeDirectory( PAGE_CACHE_PATH . 'data/assets' );
                file_put_contents( PAGE_CACHE_PATH . 'data/assets/' . $hash . '.css', $cacheCode );

                $this->tag->removeChildren();

                $str = '';

                if ( count( $cacheFiles ) )
                {

                    $str = Compiler_Abstract::PHP_OPEN;
                    $str .= ' $__hash = \'' . $hash . '\'; $__files = ' . Cache::var_export_min( $cacheFiles, true ) . ';$__cssinlineCode = ' . Cache::var_export_min( $inlineCode, true ) . ';  $__compress = ' . ( $compress ? 'true' : 'false' ) . ';';
                    $str .= '
    if (is_file(PAGE_PATH .".cache/data/assets/".$__hash.".css")) {
        $__mtime = filemtime(PAGE_PATH.\'.cache/data/assets/\'.$__hash.\'.css\');
        $__reloadCache = false;
        foreach($__files as $_f) {
            if ($_f) {
                if (filemtime( ROOT_PATH . $_f) > $__mtime)
                {
                    $__reloadCache = true;
                    break;
                }
            }
        }
    } // end file exists' . "\n";

                    $str .= ' if (!is_file(PAGE_PATH .".cache/data/assets/".$__hash.".css") || $__reloadCache) { ';
                    $str .= '   $__cacheCode = ""; ';
                    $str .= '
foreach($__files as $idx => $__f) {
    if (isset($__inlineCode[$idx])) {
        $__jscacheCode .= $__inlineCode[$idx];
        unset($__inlineCode[$idx]);
    }
    else {
        if ($__f && file_exists( ROOT_PATH . $__f) )
        {
            $__code = file_get_contents(  ROOT_PATH . $__f );
            $__path = explode(\'/\',  ROOT_PATH . $__f);
            array_pop($__path);
            $__curentPath = implode(\'/\', $__path) .\'/\';
            $__cacheCode .= Compiler_Tag_Combine::getCssImports($__code, $__curentPath);
        }
    }
}
foreach ($__inlineCode as $c ) { $__cacheCode .= $c; }
if ($__cacheCode)
{
    $__code = null;
    if ($__compress) { Compiler_Tag_Combine::compressCssCode( $__cacheCode ); }
    Compiler_Library::makeDirectory(PAGE_CACHE_PATH . \'data/assets\');
    file_put_contents(PAGE_CACHE_PATH . \'data/assets/\' . $__hash . \'.css\' , $__cacheCode);

   # echo $__cacheCode;
    $__cacheCode = null;
}
';

                    $str .= ' } ';
                    $str .= Compiler_Abstract::PHP_CLOSE;
                }


                $str .= '<link type="text/css" rel="stylesheet" href="' . PAGE_URL_PATH . '.cache/data/assets/' . $hash . '.css" />';

                $this->set( 'nophp', true );
                $this->setStartTag( $str );
                $this->set( 'nophp', false );
            }
            else if ( $type == 'js' || $type == 'script' )
            {
                Compiler_Library::makeDirectory( PAGE_CACHE_PATH . 'data/assets' );
                file_put_contents( PAGE_CACHE_PATH . 'data/assets/' . $hash . '.js', $cacheCode );

                $this->tag->removeChildren();

                $str = '';

                if ( count( $cacheFiles ) )
                {

                    $str = Compiler_Abstract::PHP_OPEN;
                    $str .= ' $__hash = \'' . $hash . '\';
$__files = ' . Cache::var_export_min( $cacheFiles, true ) . ';
$__compress = ' . ( $compress ? 'true' : 'false' ) . ';
$__inlineCode = ' . Cache::var_export_min( $inlineCode, true ) . ';';
                    $str .= '
if (is_file(PAGE_PATH .".cache/data/assets/".$__hash.".js")) { $__mtime = filemtime(PAGE_PATH.\'.cache/data/assets/\'.$__hash.\'.js\');
    $__reloadCache = false;
    foreach($__files as $__f) {
        if ($__f) {
            if (filemtime( ROOT_PATH . $__f) > $__mtime)
            {
                $__reloadCache = true;
                break;
            }
        }
    }
} // end file exists' . "\n";


                    $str .= 'if (!is_file(PAGE_PATH .".cache/data/assets/".$__hash.".js") || $__reloadCache) { $__cacheCode = ""; ';
                    $str .= '
foreach($__files as $idx => $__f) {

    if (isset($__inlineCode[$idx])) {
        $__jscacheCode .= $__inlineCode[$idx];
        unset($__inlineCode[$idx]);
    }
    else {
        if ($__f && file_exists( ROOT_PATH . $__f) )
        {
            $__jscacheCode .= file_get_contents(  ROOT_PATH . $__f );
        }
    }
}
foreach ($__inlineCode as $c ) { $__cacheCode .= $c; }
if ($__cacheCode)
{
    if ($__compress) {
        $__cacheCode = Minifier::minifyJs($__cacheCode);
    }

    Compiler_Library::makeDirectory(PAGE_CACHE_PATH . \'data/assets\');
    file_put_contents(PAGE_CACHE_PATH . \'data/assets/\' . $__hash . \'.js\' , $__cacheCode);

   // echo $__cacheCode;
    $__cacheCode = null;
}';
                    $str .= '} ';
                    $str .= Compiler_Abstract::PHP_CLOSE;
                }

                $str .= '<script type="application/x-javascript" src="' . PAGE_URL_PATH . '.cache/data/assets/' . $hash . '.js"></script>';


                $this->set( 'nophp', true );
                $this->setStartTag( $str );
                $this->set( 'nophp', false );
            }

            $this->set( 'remove_children', true );
        }
    }

    /**
     *
     * @param string $code
     * @return mixed
     */
    public static function compressCssCode(&$code)
    {
        $code = str_replace( array(
            "\r\n",
            "\r",
            "\n"), "\n", $code );

        /* remove comments */
        $code = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $code );

        /* remove tabs, spaces, newlines, etc. */
        $code = str_replace( array(
            "\r\n",
            "\r",
            "\n",
            "\t",
            '  ',
            '    ',
            '    '), ' ', $code );

        return $code;
    }

    /**
     * @param $code
     * @param $currentPathBase
     * @param bool $compress
     * @param null $src
     * @return mixed
     */
    public static function getCssImports($code, $currentPathBase, $compress = false, $src = null)
    {

        preg_match_all( '#(@charset\s*([\'"])([^\2]*)\2\s*(;))#isU', $code, $charsetmatches );
        preg_match_all( '#(@import\s*url\(([^\)]*)\)\s*(;))#isU', $code, $matches );

        if ( substr( $currentPathBase, -1 ) != '/' )
        {
            $currentPathBase .= '/';
        }

        if ( $charsetmatches[ 0 ] && is_array( $charsetmatches[ 3 ] ) )
        {
            $code = preg_replace( '#(@charset\s*([\'"])([^\2]*)\2\s*(;))#isU', '@@@CHARSET@@', $code );
            // print_r($charsetmatches);exit;
        }

        if ( $matches[ 0 ] && is_array( $matches[ 2 ] ) )
        {
            $code = preg_replace( '#(@import\s*url\(([^\)]*)\)\s*(;))#isU', '@@@IMPORT@@', $code );
        }

        $code = self::fixCssUrlRule( $code, $currentPathBase, $src );

        if ( $matches[ 0 ] && is_array( $matches[ 2 ] ) )
        {
            $cachePath = PAGE_URL_PATH . '.cache/data/assets/';
            foreach ( $matches[ 2 ] as $f )
            {
                $f = str_replace( '"', '', $f );
                $f = str_replace( '\'', '', $f );

                if ( file_exists( $currentPathBase . $f ) )
                {

                    $_code = file_get_contents( $currentPathBase . $f );

                    // fix url("../path")
                    $_code = self::fixCssUrlRule( $_code, $currentPathBase, $src );
                    $code  = preg_replace( '#@@@IMPORT@@#', $_code, $code, 1 );
                }
                else
                {
                    $code = preg_replace( '#@@@IMPORT@@#', '/** CSS File "' . $f . '" not exists! **/', $code, 1 );
                }
            }
        }


        if ( $charsetmatches[ 0 ] && is_array( $charsetmatches[ 3 ] ) )
        {
            foreach ( $charsetmatches[ 0 ] as $idx  => $charsetTag )
            {
                $code = preg_replace( '#@@@CHARSET@@#', '', $code, 1 );
            }

            if ( $charsetTag )
            {
                $code = $charsetTag . $code;
            }
        }

        if ( $compress )
        {
            $code = Minifier::minifyCss( $code, $currentPathBase );
        }

        //
        return $code;
    }

    /**
     * @param $code
     * @param $currentPathBase
     * @param null $src
     * @internal param $currentPath
     * @return mixed
     */
    private static function fixCssUrlRule($code, $currentPathBase, $src = null)
    {
        $cachePath     = PAGE_URL_PATH . '.cache/data/assets/';
        $backwardsBase = substr_count( $cachePath, '/' );

        $backPath = '';
        if ( $backwardsBase )
        {
            $backPath = str_repeat( "../", $backwardsBase );
        }

        $_currentPath = str_replace( PUBLIC_PATH, '', $currentPathBase );

        if ( substr( $_currentPath, -1 ) != '/' )
        {
            $_currentPath .= '/';
        }

        preg_match_all( '#(url\(([^\)]*)\))#isU', $code, $matches );

        if ( $matches[ 0 ] && is_array( $matches[ 2 ] ) )
        {
            foreach ( $matches[ 2 ] as $finput )
            {
                $f = str_replace( '"', '', $finput );
                $f = str_replace( '\'', '', $f );

                if ( preg_match( '#data:image#', $f ) )
                {
                    continue;
                }

                $backwards = substr_count( $f, '../' );

                if ( $backwards )
                {
                    $curr = explode( '/', $_currentPath );
                    for ( $i = 0; $i < $backwards + 1; ++$i )
                    {
                        $pop = array_pop( $curr );
                    }

                    $__currentPath = implode( '/', $curr );

                    if ( substr( $__currentPath, -1 ) != '/' )
                    {
                        $__currentPath .= '/';
                    }

                    $f = '/' . $__currentPath . str_replace( '../', '', $f );
                }
                else
                {
                    $f = $_currentPath . preg_replace( '#^\./#', '', $f );
                }


                $f = '"' . $backPath . $f . '"';
                $f = str_replace( '//', '/', $f );
                if ( strpos( $finput, 'fancybox.png' ) !== false )
                {
                    //  die( $f );
                }
                $code = preg_replace( '#url\(' . preg_quote( $finput, '#' ) . '\)#', 'url(' . $f . ')', $code, 1 );
            }
        }

        return $code;
    }

}