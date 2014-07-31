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
 * @copyright    2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        File.php
 *
 */
class File
{

    /**
     * @var string
     */
    private $strFile = null;

    /**
     * @var resource
     */
    private $resFile = null;


    private $_handle = null;

    private $_offset = 0;

    private $_content = null;


    private $_skipCheck = false;

    /**
     *
     * @param string $strFile
     * @param boolean $skipCreate
     * @throws BaseException
     * @return \File
     */
    public function __construct($strFile = '', $skipCreate = false)
    {

        // Handle open_basedir restrictions
        if ( $strFile == '.' )
        {
            $strFile = '';
        }

        // Check whether it is a file
        if ( $strFile && is_dir( $strFile ) )
        {
            throw new BaseException( sprintf( 'Directory "%s" is not a file', $strFile ) );
        }

        $this->strFile = $strFile;

        if ( !$skipCreate )
        {
            // Create file if it does not exist
            if ( !file_exists( $this->strFile ) )
            {
                // Handle open_basedir restrictions
                if ( ( $strFolder = dirname( $this->strFile ) ) == '.' )
                {
                    $strFolder = '';
                }

                // Create folder
                if ( !is_dir( $strFolder ) )
                {
                    Library::makeDirectory( $strFolder, 0777 );
                }

                $this->_skipCheck = true;
                $this->open( 'w+' );
                $this->close();
                $this->_skipCheck = false;

            }
        }
    }

    /**
     * @param string $path
     * @return bool
     */
    public function isZipFile($path)
    {
        $zip = zip_open( $path );
        if ( is_resource( $zip ) )
        {
            // it's ok
            zip_close( $zip ); // always close handle if you were just checking
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * @param string $path
     * @return bool
     */
    public function isGzFile($path)
    {
        $zip = gzopen( $path, 'r' );
        if ( is_resource( $zip ) )
        {
            // it's ok
            gzclose( $zip ); // always close handle if you were just checking
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * @param null|string $name
     * @return bool
     * @throws BaseException
     */
    private function checkIsFile($name = null)
    {
        if ( $this->_skipCheck )
        {
            return true;
        }

        if ( is_string( $name ) )
        {
            if ( !is_file( $name ) )
            {
                throw new BaseException( 'Could not set chmod to file or directory "' . $name . '"' );
            }

            return true;
        }

        if ( $this->strFile && !is_file( $this->strFile ) )
        {
            throw new BaseException( 'The file "' . $this->strFile . '" not exits!' );
        }

        return true;
    }

    /**
     * @param null|string $name
     * @throws BaseException
     */
    private function checkFileExists($name = null)
    {
        if ( $this->_skipCheck )
        {
            return;
        }


        if ( is_string( $name ) )
        {
            if ( !file_exists( $name ) )
            {
                throw new BaseException( 'Could not set chmod to file or directory "' . $name . '"' );
            }

            return;
        }

        if ( !file_exists( $this->strFile ) )
        {
            throw new BaseException( 'Could not set chmod to file or directory "' . $this->strFile . '"' );
        }
    }

    /**
     * @param $strFile
     */
    public function setFile($strFile)
    {
        $this->strFile = $strFile;
    }


    /**
     * @return int
     * @throws BaseException
     */
    public function getSize()
    {
        if ( !$this->strFile )
        {
            throw new BaseException( 'Please use File::setFile before use the function File::getSize' );
        }

        $this->checkIsFile();

        return filesize( $this->strFile );
    }

    /**
     * @return bool
     * @throws BaseException
     */
    public function isReadable()
    {
        if ( !$this->strFile )
        {
            throw new BaseException( 'Please use File::setFile before use the function File::getSize' );
        }

        $this->checkIsFile();

        return is_readable( $this->strFile );
    }


    /**
     * @param null|string $filepath
     * @return bool|null|string
     * @throws BaseException
     */
    public function getContents($filepath = null)
    {

        if ( is_string( $filepath ) )
        {
            if ( !$filepath )
            {
                throw new BaseException( 'Please use File::setFile before use the function File::getSize' );
            }
            $this->checkIsFile( $filepath );

            if ( function_exists( 'file_get_contents' ) )
            {
                return file_get_contents( $filepath );
            }
            elseif ( $size = filesize( $filepath ) )
            {
                return fread( fopen( $filepath, 'rb' ), $size );
            }

            return null;

        }
        else
        {


            if ( !$this->strFile )
            {
                throw new BaseException( 'Please use File::setFile before use the function File::getSize' );
            }

            $this->checkIsFile();

            if ( function_exists( 'file_get_contents' ) )
            {
                return file_get_contents( $this->strFile );
            }
            elseif ( $size = filesize( $this->strFile ) )
            {
                return fread( fopen( $this->strFile, 'rb' ), $size );
            }

            return null;
        }

    }


    /**
     *
     * @param string $mode
     * @param null|string $filepath
     * @return File
     * @throws BaseException
     */
    public function open($mode = 'rb', $filepath = null)
    {
        if ( is_string( $filepath ) )
        {
            if ( $filepath )
            {
                if ( is_file( $filepath ) )
                {
                    $this->_handle = fopen( $filepath, $mode );


                    // Make sure the parameter is a valid resource
                    if ( !is_resource( $this->_handle ) )
                    {
                        throw new BaseException( 'Invalid resource for file ' . $filepath );
                    }

                }
                else
                {
                    throw new BaseException( 'The file "' . $filepath . '" not exits or not a file!' );
                }
            }
            else
            {
                throw new BaseException( 'Empty string not allowed' );
            }
        }
        else
        {
            if ( !$this->strFile )
            {
                throw new BaseException( 'Please use File::setFile before use the function File::getSize' );
            }

            $this->checkIsFile();

            $this->_handle = fopen( $this->strFile, $mode );


            // Make sure the parameter is a valid resource
            if ( !is_resource( $this->_handle ) )
            {
                throw new BaseException( 'Invalid resource for file ' . $this->strFile );
            }

        }

        return $this;
    }


    /**
     *
     * @param int $length default is 1024
     * @param null|resource $fh
     * @return string
     * @throws BaseException
     */
    public function read($length = 1024, $fh = null)
    {
        if ( is_resource( $fh ) )
        {
            if ( $length )
            {
                return fread( $fh, $length );
            }
            else
            {
                throw new BaseException( 'Could not read file' );
            }
        }
        else
        {
            if ( $length )
            {
                return fread( $this->_handle, $length );
            }
            else
            {
                throw new BaseException( 'Could not read file' );
            }
        }
    }


    /**
     *
     * @param string $content
     * @param null|resource $fh
     * @param null|int $length default is null
     * @return int
     */
    public function write($content = '', $fh = null, $length = null)
    {
        if ( is_resource( $fh ) )
        {
            if ( is_int( $length ) )
            {
                return fwrite( $fh, $content, $length );
            }

            return fwrite( $fh, $content );

        }
        else
        {
            if ( is_int( $length ) )
            {
                return fwrite( $this->_handle, $content, $length );
            }

            return fwrite( $this->_handle, $content );
        }
    }


    /**
     * @return File
     * @throws BaseException
     */
    public function close()
    {
        // Make sure the parameter is a valid resource
        if ( !is_resource( $this->_handle ) )
        {
            throw new BaseException( 'Invalid file resource' );
        }

        fclose( $this->_handle );

        return $this;
    }

    /**
     * @param null|resource $fh
     * @return File
     */
    public function readlock($fh = null)
    {
        if ( is_resource( $fh ) )
        {
            @flock( $fh, LOCK_SH );
        }
        else
        {
            @flock( $this->_handle, LOCK_SH );
        }

        return $this;
    }


    /**
     * @param null|resource $fh
     * @return File
     */
    public function writelock($fh = null)
    {
        if ( is_resource( $fh ) )
        {
            @flock( $fh, LOCK_EX );
        }
        else
        {
            @flock( $this->_handle, LOCK_EX );
        }

        return $this;
    }


    /**
     * @param null|resource $fh
     * @return File
     */
    public function unlock($fh = null)
    {
        if ( is_resource( $fh ) )
        {
            @flock( $fh, LOCK_UN );
        }
        else
        {
            @flock( $this->_handle, LOCK_UN );
        }

        return $this;
    }


    /**
     * Returns the file handle
     *
     * @return resource file handle
     */
    public function getHandle()
    {
        if ( null === $this->_handle )
        {
            $this->open();
        }

        return $this->_handle;
    }

    /**
     * Sets the file handle
     *
     * @param object $handle file handle
     * @return void
     */
    public function setHandle($handle)
    {
        $this->_handle = $handle;
    }


    /**
     * Returns the offset
     *
     * @return integer the offset
     */
    public function getOffset()
    {
        return $this->_offset;
    }

    /**
     * Returns the length of the content in the file
     *
     * @return integer the length of the file content
     */
    public function getContentLength()
    {
        return strlen( $this->_content );
    }

    /**
     * Returns whether the end of the file has been reached
     *
     * @return boolean whether the end of the file has been reached
     */
    public function eof()
    {
        if ( $this->getHandle() )
        {
            return feof( $this->getHandle() );
        }
        else
        {
            return ( $this->_offset >= $this->getContentLength() );
        }

    }


    /**
     * Open a file and return the handle
     * @param string
     * @param string
     * @return resource
     */
    public function fopen($strFile, $strMode)
    {
        $this->validate( $strFile );

        return @fopen( ROOT_PATH . $strFile, $strMode );
    }

    /**
     * Write content to a file
     * @param string
     * @param string
     * @return boolean
     */
    public function fputs($resFile, $strContent)
    {
        return @fputs( $resFile, $strContent );
    }

    /**
     * Close a file
     * @param resource
     * @return boolean
     */
    public function fclose($resFile)
    {
        return @fclose( $resFile );
    }

    /**
     * Rename a file or folder
     * @param string
     * @param string
     * @return boolean
     */
    public function rename($strOldName, $strNewName)
    {
        // Source file == target file
        if ( $strOldName == $strNewName )
        {
            return true;
        }

        $this->validate( $strOldName, $strNewName );

        // Windows fix: delete target file
        if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' && file_exists( ROOT_PATH . $strNewName ) )
        {
            $this->delete( $strNewName );
        }

        // Unix fix: rename case sensitively
        if ( strcasecmp( $strOldName, $strNewName ) === 0 && strcmp( $strOldName, $strNewName ) !== 0 )
        {
            @rename( ROOT_PATH . $strOldName, ROOT_PATH . $strOldName . '__' );
            $strOldName .= '__';
        }

        return @rename( ROOT_PATH . $strOldName, ROOT_PATH . $strNewName );
    }

    /**
     * Copy a file or folder
     * @param string $strSource
     * @param string $strDestination
     * @return boolean
     */
    public function copy($strSource, $strDestination)
    {
        $this->validate( $strSource, $strDestination );

        return @copy( ROOT_PATH . $strSource, ROOT_PATH . $strDestination );
    }

    /**
     * Delete a file
     * @param string $strFile
     * @return boolean
     */
    public function delete($strFile = null)
    {

        if ( is_string( $strFile ) )
        {
            if ( !is_file( $strFile ) )
            {
                return false;
            }
            else
            {
                return unlink( $strFile );
            }
        }

        return unlink( $this->strFile );
    }


    /**
     * @param int $varMode
     * @param null $strFile
     * @return bool
     * @throws BaseException
     */
    public function setChmod($varMode, $strFile = null)
    {
        if ( is_string( $strFile ) )
        {
            $this->checkIsFile( $strFile );

            return chmod( $strFile, $varMode );
        }


        return chmod( $this->strFile, $varMode );
    }

    /**
     * Change file mode
     * @param string $strFile
     * @param mixed $varMode
     * @return boolean
     */
    public function chmod($strFile, $varMode)
    {
        $this->validate( $strFile );

        return @chmod( ROOT_PATH . $strFile, $varMode );
    }

    /**
     * Check whether a file is writeable
     * @param string $strFile
     * @return boolean
     */
    public function isWriteable($strFile = null)
    {
        if ( is_string( $strFile ) )
        {
            $this->checkFileExists( $strFile );

            return is_writeable( $strFile );
        }


        $this->checkFileExists( $this->strFile );

        return is_writeable( $this->strFile );
    }

    /**
     * Validate the path
     * @throws BaseException
     */
    protected function validate()
    {
        foreach ( func_get_args() as $strPath )
        {
            if ( strpos( $strPath, '../' ) !== false )
            {
                throw new BaseException( 'Invalid file or folder name ' . $strPath );
            }
        }
    }

    /**
     * Get file(es) of a directory, not including the directory which
     * its name is one of ".", "..", ".htaccess" or ".svn"
     *
     * @param string $dir Path to the directory
     * @return array
     */
    public static function getFilesFromDir($dir)
    {
        $dir = ( substr( $dir, -1 ) === '/' ) ? substr( $dir, 0, -1 ) : $dir;

        if ( !file_exists( $dir ) )
        {
            return array();
        }

        $files       = array();
        $dirIterator = new DirectoryIterator( $dir );
        foreach ( $dirIterator as $dir )
        {

            if ( $dir->isDot() || !$dir->isFile() || $dir->getExtension() != 'php' )
            {
                continue;
            }

            $dir = $dir->getFilename();
            if ( $dir == '.svn' || $dir == '.htaccess' )
            {
                continue;
            }


            $files[ ] = $dir->getFilename();
        }


        return $files;
    }

    /**
     * Get sub-directori(es) of a directory, not including the directory which
     * its name is one of ".", ".." or ".svn"
     *
     * @param string $dir Path to the directory
     * @return array
     */
    public static function getSubDir($dir)
    {
        $dir = ( substr( $dir, -1 ) === '/' ) ? substr( $dir, 0, -1 ) : $dir;

        if ( !file_exists( $dir ) )
        {
            return array();
        }

        $subDirs     = array();
        $dirIterator = new DirectoryIterator( $dir );
        foreach ( $dirIterator as $dir )
        {
            if ( $dir->isDot() || !$dir->isDir() )
            {
                continue;
            }

            $dir = $dir->getFilename();
            if ( $dir == '.svn' || substr( $dir, 0, 1 ) == '@' || substr( $dir, 0, 1 ) == '.' )
            {
                continue;
            }


            $subDirs[ ] = $dir;
        }

        return $subDirs;
    }

    /**
     * @param string $dir
     * @return boolean
     */
    public static function deleteRescursiveDir($dir)
    {
        $dir = str_replace( '\\', '/', $dir );

        if ( is_dir( $dir ) )
        {
            $dir = ( substr( $dir, -1 ) !== '/' ) ? $dir . '/' : $dir;

            $openDir = opendir( $dir );
            while ( $file = readdir( $openDir ) )
            {
                if ( !in_array( $file, array(
                    ".",
                    "..") )
                )
                {
                    if ( !is_dir( $dir . $file ) )
                    {
                        @unlink( $dir . $file );
                    }
                    else
                    {
                        self::deleteRescursiveDir( $dir . $file );
                    }
                }
            }
            closedir( $openDir );
            @rmdir( $dir );
        }

        return true;
    }

    /**
     * @param string $source
     * @param string $dest
     * @return boolean
     */
    public static function copyRescursiveDir($source, $dest)
    {
        $openDir = opendir( $source );
        if ( !file_exists( $dest ) )
        {
            @mkdir( $dest );
        }
        while ( $file = readdir( $openDir ) )
        {
            if ( !in_array( $file, array(
                ".",
                "..") )
            )
            {
                if ( is_dir( $source . '/' . $file ) )
                {
                    self::copyRescursiveDir( $source . '/' . $file, $dest . '/' . $file );
                }
                else
                {
                    copy( $source . '/' . $file, $dest . '/' . $file );
                }
            }
        }
        closedir( $openDir );

        return true;
    }

    /**
     * Create sub-directories of given directory
     *
     * @param string $root Path to root directory
     * @param string $path Relative path to new created directory in format a/b/c
     */
    public static function createDirs($root, $path)
    {
        $root = str_replace( '\\', '/', $root );
        $path = str_replace( '\\', '/', $path );


        $root    = rtrim( $root, '/' );
        $subDirs = explode( '/', $path );
        if ( $subDirs == null )
        {
            return;
        }
        $currDir = $root;
        foreach ( $subDirs as $dir )
        {
            $currDir = $currDir . '/' . $dir;
            if ( !file_exists( $currDir ) )
            {
                mkdir( $currDir );
            }
        }
    }

}

?>