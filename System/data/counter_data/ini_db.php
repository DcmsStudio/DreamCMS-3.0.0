<?php

//
// Open Web Analytics - An Open Source Web Analytics Framework
//
// Copyright 2006 Peter Adams. All rights reserved.
//
// Licensed under GPL v2.0 http://www.gnu.org/copyleft/gpl.html
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//
// $Id$
//

/**
 * INI Database 
 * 
 * Searches INI files for matches based on various lookup methods.
 * 
 * @author      Peter Adams <peter@openwebanalytics.com>
 * @copyright   Copyright &copy; 2006 Peter Adams <peter@openwebanalytics.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GPL v2.0
 * @category    wa
 * @package     wa
 * @version		$Revision$	      
 * @since		wa 1.0.0
 */

function _sortBrowscap($a,$b)
{
	$sa=strlen($a);
	$sb=strlen($b);
	if ($sa>$sb) return -1;
	elseif ($sa<$sb) return 1;
	else return strcasecmp($a,$b);
}

function _lowerBrowscap($r) {return array_change_key_case($r,CASE_LOWER);}

class ini_db {

	var $browscapIni=null; //Cache
	var $browscapPath=''; //Cached database




	/**
	 * Data file
	 *
	 * @var unknown_type
	 */
	var $ini_file;

	/**
	 * Result Format
	 *
	 * @var string
	 */
	var $return_format;

	/**
	 * Cache flag
	 *
	 * @var boolean
	 */
	var $cache = true;


	/**
	 * Database Access Object
	 *
	 * @var object
	 */
	var $db;

	/**
	 * Constructor
	 *
	 * @param string $ini_file
	 * @param string_type $sections
	 * @param string $return_format
	 * @access public
	 * @return ini_db
	 */
	function ini_db($ini_file, $sections = null, $return_format = 'object') {
		$this->ini_file = $ini_file;
		$this->return_format = $return_format;

		if (!empty($sections)):
		$this->db = $this->readINIfile($this->ini_file, ';');
		else:
		$this->db = file($this->ini_file);
		endif;
		return;
	}

	/**
	 * Returns a section from an ini file based on regex match rule 
	 * contained as keys in an ini file.
	 * 
	 * @param string
	 * @access public
	 */
	function fetch($haystack) {

		$record = null;

		foreach ($this->db as $key=>$value) {
			if (($key!='#*#')&&(!array_key_exists('parent',$value))) continue;

			$keyEreg = '#'.$key.'#';

			if (preg_match($keyEreg, $haystack)) {
				$record=array('regex'=>strtolower($keyEreg),'pattern'=>$key)+$value;

				$maxDeep=8;
				while (array_key_exists('parent',$value)&&(--$maxDeep>0))
				{
					$record+=($value = $this->db[strtolower($value['parent'])]);
				}

				break;
			}
		}

		switch ($this->return_format) {
			case "array":
				return $record;
				break;
			case "object":
				return ((object)$record);
				break;
		}
		return $record;
	}

	/**
	 * Returns part of the passed string based on regex match rules 
	 * contained as keys in an ini file.
	 * 
	 * @param string
	 * @access public
	 * @return string
	 */
	function match($haystack) {

		if (!empty($haystack)):

		$tmp = '';

		foreach ($this->db as $key => $value) {

			if (!empty($value)):
			//$this->e->debug('ref db:'.print_r($this->db, true));
			preg_match(trim((string)$value), $haystack, $tmp);
			if (!empty($tmp)):
			$needle = $tmp;
			//$this->e->debug('ref db:'.print_r($tmp, true));
			endif;
			endif;
		}

		return $needle;

		else:
		return;
		endif;
	}

	/**
	 * Fetch a record set and perfrom a regex replace on the name
	 *
	 * @param 	string $haystack
	 * @return 	string
	 */
	function fetch_replace($haystack) {

		$record = $this->fetch($haystack);
		$new_record = preg_replace($record->regex, $record->name, $haystack);

		return $new_record;
	}

	/**
	 * Reads INI file
	 *
	 * @param string $filename
	 * @param string $commentchar
	 * @return array
	 */
	function readINIfile ($filename, $commentchar) {
		$array1 = file($filename);
		$section = '';
		foreach ($array1 as $filedata) {
			$dataline = trim((string)$filedata);
			$firstchar = substr($dataline, 0, 1);
			if ($firstchar!=$commentchar && $dataline!='') {
				//It's an entry (not a comment and not a blank line)
				if ($firstchar == '[' && substr($dataline, -1, 1) == ']') {
					//It's a section
					$section = strtolower(substr($dataline, 1, -1));
				}else{
					//It's a key...
					$delimiter = strpos($dataline, '=');
					if ($delimiter > 0) {
						//...with a value
						$key = strtolower(trim((string)substr($dataline, 0, $delimiter)));
						$value = trim((string)substr($dataline, $delimiter + 1));
						if (substr($value, 1, 1) == '"' && substr($value, -1, 1) == '"') { $value = substr($value, 1, -1); }
						$array2[$section][$key] = stripcslashes($value);
					}else{
						//...without a value
						$array2[$section][strtolower(trim((string)$dataline))]='';
					}
				}
			}else{
				//It's a comment or blank line.  Ignore.
			}
		}
		return $array2;
	}









	function get_browser_local($user_agent=null,$return_array=false,$brdb='',$cache=false)
	{//http://alexandre.alapetite.net/doc-alex/php-local-browscap/
		
		global $db;
		
		if (($user_agent==null) && isset($_SERVER['HTTP_USER_AGENT'])) $user_agent=$_SERVER['HTTP_USER_AGENT'];
		if ( $this->browscapIni === null )
		{
			$create_cache = false;
			
			if ( !file_exists(ROOT_PATH."pages/". SERVER_PAGE ."/cache/browscap.db"))
			{
				$create_cache = true;
			}
			
			if ( $create_cache )
			{
				if ((!isset($this->browscapIni))||(!$cache)||($this->browscapPath!==$brdb))
				{
					$this->browscapIni=parse_ini_file($brdb,true); //Get php_browscap.ini on http://browsers.garykeith.com/downloads.asp
					$this->browscapPath=$brdb;
					uksort($this->browscapIni,'_sortBrowscap');
					$this->browscapIni=array_map('_lowerBrowscap',$this->browscapIni);
				}
				
				$fp = fopen(ROOT_PATH."pages/". SERVER_PAGE ."/cache/browscap.db", "w+");
				fwrite($fp, serialize($this->browscapIni));
				fclose($fp);
				
			}
			else
			{
				$this->browscapIni = unserialize(implode("", file(ROOT_PATH."pages/". SERVER_PAGE ."/cache/browscap.db") ) );
			}
		}

		$cap=null;
		while (list($key,$value) = each($this->browscapIni))
		// foreach ($this->browscapIni as $key=>$value)
		{
			if (($key!='*')&&(!array_key_exists('parent',$value))) continue;
			$keyEreg='^'.str_replace(
			array('\\','.','?','*','^','$','[',']','|','(',')','+','{','}','%'),
			array('\\\\','\\.','.','.*','\\^','\\$','\\[','\\]','\\|','\\(','\\)','\\+','\\{','\\}','\\%'),
			$key).'$';
			if (preg_match('%'.$keyEreg.'%i',$user_agent))
			{
				$cap=array('browser_name_regex'=>strtolower($keyEreg),'browser_name_pattern'=>$key)+$value;
				$maxDeep=8;
				while (array_key_exists('parent',$value)&&array_key_exists($parent=$value['parent'],$this->browscapIni)&&(--$maxDeep>0))
				$cap+=($value=$this->browscapIni[$parent]);
				break;
			}
		}
		if (!$cache) $this->browscapIni=null;

		return ( $return_array ? $cap : (object)$cap );

	}



}

?>