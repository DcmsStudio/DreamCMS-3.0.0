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
 * @file         Zip.php
 */
class Zip extends ZipArchive
{


    /**
     * Gets zip file contents
     *
     * @param string $file           zip file
     * @param string $specific_entry regular expression to match a file
     * @return array ($error_message, $file_data); $error_message is empty if no error
     */
    public function getZipContent($file, $specific_entry = null)
    {
        $error_message = '';
        $file_data = '';
        $zip_handle = zip_open($file);
        if (!is_resource($zip_handle))
        {
            $error_message = __('Error in ZIP archive:') . ' ' . PMA_getZipError($zip_handle);
            zip_close($zip_handle);
            return (array('error' => $error_message, 'data' => $file_data));
        }

        $first_zip_entry = zip_read($zip_handle);
        if (false === $first_zip_entry)
        {
            $error_message = __('No files found inside ZIP archive!');
            zip_close($zip_handle);

            return (array('error' => $error_message, 'data' => $file_data));
        }

        /* Is the the zip really an ODS file? */
        $read = zip_entry_read($first_zip_entry);
        $ods_mime = 'application/vnd.oasis.opendocument.spreadsheet';
        if (!strcmp($ods_mime, $read)) {
            $specific_entry = '/^content\.xml$/';
        }

        if (!isset($specific_entry))
        {
            zip_entry_open($zip_handle, $first_zip_entry, 'r');
            /* File pointer has already been moved,
             * so include what was read above */
            $file_data = $read;
            $file_data .= zip_entry_read(
                $first_zip_entry,
                zip_entry_filesize($first_zip_entry)
            );
            zip_entry_close($first_zip_entry);
            zip_close($zip_handle);

            return (array('error' => $error_message, 'data' => $file_data));
        }

        /* Return the correct contents, not just the first entry */
        for ( ; ; ) {
            $entry = zip_read($zip_handle);
            if (is_resource($entry)) {
                if (preg_match($specific_entry, zip_entry_name($entry))) {
                    zip_entry_open($zip_handle, $entry, 'r');
                    $file_data = zip_entry_read(
                        $entry,
                        zip_entry_filesize($entry)
                    );
                    zip_entry_close($entry);
                    break;
                }
            } else {
                /**
                 * Either we have reached the end of the zip and still
                 * haven't found $specific_entry or there was a parsing
                 * error that we must display
                 */
                if ($entry === false) {
                    $error_message = __('Error in ZIP archive:') . ' Could not find "' . $specific_entry . '"';
                } else {
                    $error_message = __('Error in ZIP archive:') . ' ' . PMA_getZipError($zip_handle);
                }

                break;
            }
        }

        zip_close($zip_handle);

        return (array('error' => $error_message, 'data' => $file_data));
    }



    /**
     * Extracts a set of files from the given zip archive to a given destinations.
     *
     * @param string $zip_path path to the zip archive
     * @param string $destination destination to extract files
     * @param array $entries files in archive that should be extracted
     * @return bool true on sucess, false otherwise
     */
    public function extract($zip_path, $destination, $entries)
    {
        $zip = new ZipArchive;
        if ( $zip->open( $zip_path ) === true )
        {
            $zip->extractTo( $destination, $entries );
            $zip->close();

            return true;
        }

        return false;
    }


    /**
     * Returns the file name of the first file that matches the given $file_regexp.
     *
     * @param string $file_regexp regular expression for the file name to match
     * @param string $file zip archive
     * @return string the file name of the first file that matches the given regexp
     */
    public function findFileInZipArchive($file_regexp, $file)
    {
        $zip_handle = zip_open( $file );
        if ( is_resource( $zip_handle ) )
        {
            $entry = zip_read( $zip_handle );
            while ( is_resource( $entry ) )
            {
                if ( preg_match( $file_regexp, zip_entry_name( $entry ) ) )
                {
                    $file_name = zip_entry_name( $entry );
                    zip_close( $zip_handle );

                    return $file_name;
                }
                $entry = zip_read( $zip_handle );
            }
        }
        zip_close( $zip_handle );

        return false;
    }


    /**
     * Returns the number of files in the zip archive.
     *
     * @param string $file zip archive
     * @return int the number of files in the zip archive
     */
    public function getNumOfFilesInZip($file)
    {
        $count = 0;
        $zip_handle = zip_open($file);
        if (is_resource($zip_handle)) {
            $entry = zip_read($zip_handle);
            while (is_resource($entry)) {
                $count++;
                $entry = zip_read($zip_handle);
            }
        }
        zip_close($zip_handle);
        return $count;
    }

}