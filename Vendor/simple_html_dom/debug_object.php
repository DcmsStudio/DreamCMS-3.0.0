<?php
/**
 * * This file contains the global debug object.
 *
 * PLEASE read the documentation at: https://sourceforge.net/p/debugobject/wiki/Home/
 * For how to add your debug code, please read "Instrumenting the code" at: https://sourceforge.net/p/debugobject/wiki/Instrumenting%20The%20Code/
 * For how to turn the debug_object on at any level or scope that you need, please read: "Turning it on" at: https://sourceforge.net/p/debugobject/wiki/Turning%20It%20On/
 *
 * Just before the end of the file the global $debug_object is created, and if you wish to set it's behavior to echo, there is a line you can uncomment.
 * Also at the end of the file are a number of examples of turning on the debug object.
 *
 * @author John Schlick
 * @version 1.0
 * @package Debug_Object
 */

/**
 * DebugObject
 * Contains the DebugObject.  This object controls all debug execution.
 * It should be included by the bootstrap of your codebase and be available available thruout the ENTIRE codebase.
 *
 * @author John Schlick
 * @version 1.0
 * @package Debug_Object
 */
class Debug_Object
{
	/**
	 * Debug array
	 * Used for control of whether to execute debug code.
	 *
	 * @var array
	 * @access public
	 */
	public $debug = array();

	/**
	 * Debug Output Method
	 * determines the output method to use for the error_log text.
	 * Sometimes, you want the output to the logfile, and sometimes you want it to the screen.
	 *
	 * @var string ('error_log' or 'echo')
	 * @access public
	 */
	public $debug_output_method = 'error_log';

	/**
	 * Prepend File And Function
	 * Determines whether or not to prepend the calling_file . calling_function to the beginning of the debug_output text.
	 *
	 * @var boolean
	 * @access public
	 */
	public $prepend_file_and_function = true;


	/**
	 * debug_log_entry
	 * Wrapper function for common construct of: $debugObject->debug_log("Entry", $calling[, $parameters]);
	 * Note: This function requires the debug level to test against, and will automatically generate the word "Entry:", and get the list of parameters.
	 *
	 * @author John Schlick
	 * @version 1.0
	 * @access public
	 * @param integer level - the debug level to test against.
	 * @return boolean yes or no as to whether the call to _debug_output was made or not.
	 */
	public function debug_log_entry($debug_level, $xxx=null)
	{

		if ($this->execute_debug($debug_level))
		{

			$log_text = "Entry:";

			// Get the variables that define how we got here.
			$variables_array = $this->_calling_variables();
			$calling_file = $variables_array['calling_file'];
			$calling_function = $variables_array['calling_function'];
			$backtrace_array = $variables_array['backtrace_array'];
			$backtrace_index = $variables_array['backtrace_index'];

			if ($this->prepend_file_and_function)
			{
				// Add the calling file and function to the beginning...
				$log_text = $calling_file . ":" . $calling_function . " " . $log_text;
			}
			// Pass in the text identifying us, and the calling parameters.
			// But of there are no arguments then don't put something empty on the stack.
			// NOTE: These arguments ONLY show up if they are PASSED, not if they are defaulted.
			if (array_key_exists('0', $backtrace_array))
			{
//$this->_debug_output('backtrace element 0 exists: ' . $backtrace_index, $backtrace_array[$backtrace_index]);
				$this->_debug_output($log_text, $backtrace_array[$backtrace_index]['args']);
			}
			else
			{
				$this->_debug_output($log_text);
			}

			return true;
		}
		return false;
	}


	/**
	 * debug_log
	 * Wrapper function for common construct of: if execute_debug(level) then call _debug_output
	 * Note: at least 2 parameters must be passed in for this function to do anything.
	 *
	 * @author John Schlick
	 * @version 1.0
	 * @access public
	 * @param integer level - the debug level to test against.
	 * @param text list - a variable length list of parameters that will be passed to _debug_output
	 * @return boolean yes or no as to whether the call to _debug_output was made or not.
	 */
	public function debug_log()
	{
		$arguments_array = func_get_args();
		$debug_level = array_shift($arguments_array);

		if (is_null($debug_level))
		{
			return false;
		}

		if ($this->execute_debug($debug_level))
		{

			if ($this->prepend_file_and_function)
			{
				// Get the variables that define how we got here.
				$variables_array = $this->_calling_variables();
				$calling_file = $variables_array['calling_file'];
				$calling_function = $variables_array['calling_function'];

				// Add the calling file and function to the beginning...
				if (is_array($arguments_array[0]) )
				{
					$arguments_array[0] = array_unshift($arguments_array[0], $calling_file . ":" . $calling_function);
				}
				else
				{
					$arguments_array[0] = $calling_file . ":" . $calling_function . " " . $arguments_array[0];
				}
			}

			// If there is only one element pass just that element, otherwise pass the whole array in.
			if (count($arguments_array) == 1)
			{
				$this->_debug_output($arguments_array[0]);
			}
			else
			{
				if (is_array($arguments_array[0]) )
				{
					$this->_debug_output($arguments_array);
				}
				else
				{
					// We do this so that it's not the 0th element of an array and it's properly indented as the start of a call.
					$first_element = array_shift($arguments_array);
					$this->_debug_output($first_element, $arguments_array);
				}
			}

			return true;
		}
		return false;
	}


	/**
	 * execute_debug
	 * Determines whether or not to execute debug code based on a number of factors.
	 *
	 * @author John Schlick
	 * @version 1.0
	 * @access public
	 * @param integer level - the debug level to test against.
	 * @return Boolean true or false as to whether to execute the debug code.
	 */
	public function execute_debug($level)
	{
		// Clearly no debug if there is nothing in the array.
		if (empty($this->debug))
		{
			return false;
		}

		// Assume we aren't going to say yes to them.
		$result = false;

		// Get the variables that define how we got here.
		$variables_array = $this->_calling_variables();

		$calling_namespace = $variables_array['calling_namespace'];
		$calling_directory = $variables_array['calling_directory'];
		$calling_file = $variables_array['calling_file'];
		$calling_function = $variables_array['calling_function'];
		$calling_class = $variables_array['calling_class'];
//$this->_debug_output('namespace: ' . $calling_namespace . 'directory: ', $calling_directory, 'file: ' .$calling_file, 'function: ' . $calling_function, 'class: ' . $calling_class);


		// Note, we are going from most local to most global of the possible entries.

		// See if we want a specific function that is inside of a specific class inside of a specific namespace...
		if (($calling_namespace != '') && ($calling_class != ''))
		{
			$index_string = $calling_namespace  . $calling_class . $calling_function;
//$this->_debug_output('index: ' . $index_string, $this->debug);
			if (isset($this->debug[$index_string]) && $this->_execute_debug_level($this->debug[$index_string], $level))
			{
				$result = true;
			}
		}

		// See if we want a specific function that is inside of a specific namespace...
		if ($calling_namespace != "")
		{
			$index_string = $calling_namespace . $calling_function;
			if (isset($this->debug[$index_string]) && $this->_execute_debug_level($this->debug[$index_string], $level))
			{
				$result = true;
			}
		}

		// See if we want a specific function that is inside of a specific class...
		if ($calling_class != "")
		{
			$index_string = $calling_class . $calling_function;
			if (isset($this->debug[$index_string]) && $this->_execute_debug_level($this->debug[$index_string], $level))
			{
				$result = true;
			}
		}

		// No matter what namespace or class we are in...  See if there is an entry for the specific routine (that called us).
		if (isset($this->debug[$calling_function]) && $this->_execute_debug_level($this->debug[$calling_function], $level))
		{
			$result = true;
		}

		// See if we want a specific class inside of a specific namespace...
		if (($calling_namespace != '') && ($calling_class != ''))
		{
			$index_string = $calling_namespace  . $calling_class;
//$this->_debug_output('index: ' . $index_string, $this->debug);
			if (isset($this->debug[$index_string]) && $this->_execute_debug_level($this->debug[$index_string], $level))
			{
				$result = true;
			}
		}

		// See if we want a everything that is inside of a specific class...
		if ($calling_class != "")
		{
			if (isset($this->debug[$calling_class]) && $this->_execute_debug_level($this->debug[$calling_class], $level))
			{
				$result = true;
			}
		}

		// See if there is an entry for our current namespace...
		if ($calling_namespace != '')
		{
			if (isset($this->debug[$calling_namespace]) && $this->_execute_debug_level($this->debug[$calling_namespace], $level))
			{
				$result = true;
			}
		}

		// See if they want a file thats an exact match for the file we are in.
		if (isset($this->debug[$calling_file]) && $this->_execute_debug_level($this->debug[$calling_file], $level))
		{
			$result = true;
		}

		// See if there is an entry for any of our current directories...
		foreach ($calling_directory as $directory)
		{
			if (isset($this->debug[$directory]) && $this->_execute_debug_level($this->debug[$directory], $level))
			{
				$result = true;
			}
		}

		// See if there is an entry for the a directory and the specific file...
		$index_string = $calling_file;
		foreach ($calling_directory as $directory)
		{
			// Add the current diurectory to the index string (making it longer each time thru the loop).
			$index_string = $directory . $index_string;
			if (isset($this->debug[$index_string]) && $this->_execute_debug_level($this->debug[$index_string], $level))
			{
				$result = true;
			}
		}

		// If the global 'DEBUG' entry is filled in let er rip
		// Note that the poor programmer won't know what hit them.
		if (isset($this->debug['DEBUG']) && $this->_execute_debug_level($this->debug['DEBUG'], $level))
		{
			$result = true;
		}

		// We have been the great decider - let them know of our omniscient decision.
		return $result;
	}


	/**
	 * debug_output
	 * Function to just spit out a log entry formatted like a debug_log entry.
	 * BUT: It will not check the debug level AT ALL.
	 *
	 * @author John Schlick
	 * @version Jan 4 2011
	 * @access public
	 * @param text list - a variable length list of parameters that will be passed to _debug_output
	 * @return boolean yes or no as to whether the call to _debug_output was made or not.
	 */
	public function debug_output()
	{
		$arguments_array = func_get_args();

		if ($this->prepend_file_and_function)
		{
			// Get the variables that define how we got here.
			$variables_array = $this->_calling_variables();
			$calling_file = $variables_array['calling_file'];
			$calling_function = $variables_array['calling_function'];

			// Add the calling file and function to the beginning...
			if (is_array($arguments_array[0]) )
			{
				$arguments_array[0] = array_unshift($arguments_array[0], $calling_file . ":" . $calling_function);
			}
			else
			{
				$arguments_array[0] = $calling_file . ":" . $calling_function . " " . $arguments_array[0];
			}
		}

		// If there is only one element then pass just that element, otherwise pass the whole array in.
		if (count($arguments_array) == 1)
		{
			$this->_debug_output($arguments_array[0]);
		}
		else
		{
			if (is_array($arguments_array[0]) )
			{
				$this->_debug_output($arguments_array);
			}
			else
			{
				// We do this so that it's not the 0th element of an array and it's properly indented as the start of a call.
				$first_element = array_shift($arguments_array);
				$this->_debug_output($first_element, $arguments_array);
			}
		}

		return true;
	}


	/**
	 * _calling_variables
	 * Get the variables that define how we are being called.
	 *
	 * @author John Schlick
	 * @version 1.0
	 * @access protected
	 * @return array  calling_function, calling_file, calling_directory, calling_namespace, calling_class, backtrace_array, backtrace_index
	 */
	public function _calling_variables()
	{
		// Get ourselves a backtrace array, so that we can see who or what has knocked on the door.
		$backtrace_array = debug_backtrace();

		// The 0'th array contains the file that called us.
		$backtrace_index = 0;
		$calling_file = $backtrace_array[$backtrace_index]['file'];
		$calling_file = basename($calling_file);

		// Make sure that it's not us calling ourselves...
		if ($calling_file == "debug_object.php")
		{
			$backtrace_index = $backtrace_index + 1;
			$calling_file = $backtrace_array[$backtrace_index]['file'];
			$calling_file = basename($calling_file);
		}

		// Do this twice since we are possibly buried 2 levels deep at this point.
		if ($calling_file == "debug_object.php")
		{
			$backtrace_index = $backtrace_index + 1;
			$calling_file = $backtrace_array[$backtrace_index]['file'];
			$calling_file = basename($calling_file);
		}

		// Now lets get the function that called us.
		// Note: the function in the [0]'th element is always us (_calling_variables),
		// and the [1] element is always execute_debug - so go one back from that.
		$calling_namespace = '';
		$calling_function = '';
		$backtrace_index = 2;
		if (isset($backtrace_array[$backtrace_index]) && isset($backtrace_array[$backtrace_index]['function']))
		{
			$calling_function = $backtrace_array[$backtrace_index]['function'];
			// Make sure it's not US calling ourselves.
			if (($calling_function == "debug_log") || ($calling_function == "debug_log_entry"))
			{
				$calling_function = "";
				$backtrace_index = $backtrace_index + 1;
				if (isset($backtrace_array[$backtrace_index]) && isset($backtrace_array[$backtrace_index]['function']))
				{
					$calling_function = $backtrace_array[$backtrace_index]['function'];
					$slash_position = strrpos($calling_function, "\\");
					if ($slash_position !== false)
					{
						$calling_namespace = substr($calling_function, 0, $slash_position+1);
						$calling_function = substr($calling_function, $slash_position+1);
					}
				}
			}
		}

		// Apparently, if this is a class...  it may not contain the file.  However, the file is correct for backtrace_index - 1.
		if (!isset($backtrace_array[$backtrace_index]['file']))
		{
			$directory_path = $backtrace_array[$backtrace_index - 1]['file'];
		}
		else
		{
			$directory_path = $backtrace_array[$backtrace_index]['file'];
		}
		// Note that this is an array of all of the elements in the directory structure leading to here.
		// They are always prepended with /, and "/" (the root) is always present.
		$directory_path = dirname($directory_path);
		$calling_directory = explode('/', $directory_path);
		foreach ($calling_directory as $key => $directory)
		{
			$calling_directory[$key] = $directory . '/';
		}
		// Reverse this array so that the closest one gets checked first.
		$calling_directory = array_reverse($calling_directory);

		// Figure out the class (if any) of the routine that called us.
		$calling_class = '';
		if (isset($backtrace_array[$backtrace_index]['class']))
		{
			$calling_class = $backtrace_array[$backtrace_index]['class'] . '::';
			$slash_position = strrpos($calling_class, "\\");
			if ($slash_position !== false)
			{
				$calling_namespace = substr($calling_class, 0, $slash_position+1);
				$calling_class = substr($calling_class, $slash_position+1);
			}
		}

		return array(
			'calling_function'	=> $calling_function,
			'calling_file'		=> $calling_file,
			'calling_directory'	=> $calling_directory,
			'calling_namespace'	=> $calling_namespace,
			'calling_class'		=> $calling_class,
			'backtrace_array'	=> $backtrace_array,
			'backtrace_index'	=> $backtrace_index);
	}


	/**
	 * execute_debug_level
	 * Determines whether or not to execute debug code based on a number of factors.
	 * First, is the debug_array_level >0 and if so greater than (or equal to) the level requested...
	 * If less <= 0, then does it exactly match one of the ['DEBUG'] entries...
	 * If it's a "*" then of course it matches.
	 *
	 * @author John Schlick
	 * @version July 2010
	 * @access protected
	 * @param integer debug_array_level - the debug level in the debug array.
	 * @param integer requested_level - the debug level passed to us to test against.
	 * @return Boolean true or false as to whether to execute the debug code.
	 */
	protected function _execute_debug_level($debug_array_level, $requested_level)
	{
		// If the debug level in the array is a *, then we ALWAYS match.
		if (is_string($debug_array_level) && ($debug_array_level == "*"))
		{
			return true;
		}

		// If the requested_level is a *, then we always match.
		if (is_string($requested_level) && ($requested_level == "*"))
		{
			return true;
		}

		// If the requested_level is a comma separated list, then we need to call ourselves for each of the elements and return true if ANY of them match.
		if (strpos($requested_level, ","))
		{
			$requested_level_array = explode(",", $requested_level);
			foreach ($requested_level_array as $requested_level_element)
			{
				if ($this->_execute_debug_level($debug_array_level, trim($requested_level_element)))
				{
					return true;
				}
			}
			return false;
		}

		// If the debug_array_level is a comma separated list, then we need to turn it into an array and foreach recursively call ourselves.
		if (strpos($debug_array_level, ","))
		{
			$debug_array_level_array = explode(",", $debug_array_level);
			foreach ($debug_array_level_array as $value)
			{
				// Only return a true if the result is "yes, do this.".
				if ($this->_execute_debug_level($value, $requested_level))
				{
					return true;
				}
			}
			return false;
		}

		// Assume that we aren't going to execute the debug code.
		$result = false;

		// If the debug level is 1 or more then it's ok to test for >=
		if (($requested_level >= 1) && ($debug_array_level >= $requested_level))
		{
			$result = true;
		}

		// If the debug level is 0 or less, then a specific match must be made.
		if (($requested_level <= 0) && ($debug_array_level == $requested_level))
		{
			$result = true;
		}
		return $result;
	}


	/**
	 * internal debug output
	 * Used to dump a list of strings, arrays or objects in a way that looks nice to the currently selected "output" mechanism.
	 */
	function _debug_output()
	{
		foreach (func_get_args() as $value)
		{
			if (is_array($value) || is_object($value))
			{
				$this->_debug_output_array($value);
			}
			else
			{

				if (is_null($value))
				{
					$value = '\\NULL\\';
				}
				if ($value === '')
				{
					$value = "\\''\\";
				}
				if ($value === false)
				{
					$value = "\\FALSE\\";
				}
				if ($value === true)
				{
					$value = "\\TRUE\\";
				}

				$this->_final_output($value);
			}
		}
	}

	/**
	 * Array or object dumping mecahnism used by _debug_output.
	 */
	function _debug_output_array($values, $indent=1)
	{
		// Get an indent string appropriate to the type of output we are doing.
		if ($this->debug_output_method == 'error_log')
		{
			$indent_string = str_repeat("  ", $indent);
		}
		else
		{
			$indent_string = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $indent);
		}
		$numbered_array = true;
		foreach ($values as $key => $value)
		{
			if (!preg_match("/^\d+$/", $key))
			{
				$numbered_array = false;
			}
		}
		foreach ($values as $key => $value)
		{
			if ($numbered_array || !preg_match("/^\d+$/", $key))
			{
				if (is_array($value) || is_object($value))
				{
					$this->_final_output($indent_string . "[$key] =>");
					$this->_debug_output_array($value, $indent + 1);
				}
				else
				{
					if (is_null($value))
					{
						$value = '\\NULL\\';
					}
					if ($value === '')
					{
						$value = "\\''\\";
					}
					$this->_final_output($indent_string . "[$key] => $value");
				}
			}
		}
	}

	/**
	 * final output function
	 * Implements the outout to the correct output mechanism
	 * Today that means: to the error log file or echoing to the screen.
	 *
	 * @author John Schlick
	 * @version 1.0
	 * @param string $value is the value to output.
	 */
	function _final_output($value)
	{
		if ($this->debug_output_method == 'error_log')
		{
			error_log($value);
		}
		else
		{
			echo $value . "<br/>";
		}
	}


}//end DebugObject class



/**
 * DebugTimer
 * Another class usefull for debugging ongoing issues is the debugTimer class.
 * Creation will initialize it, and then we will be able to reset it's time, get elapsed time, and get a formatted output version of the time.
 *
 * @author John Schlick
 * @version 1.0
 * @package Debug_Timer
 */
class Debug_Timer
{
	/**
	 * Current Start
	 * This is the microtime that we started the timer at.
	 * @var real
	 * @access public
	 */
	protected $timer_start = 0.0;

	/**
	 * Class constructor.
	 * Sets up the current_start variable.
	 *
	 * @author John Schlick
	 * @version 1.0
	 * @param None
	 * @access public
	 */
	public function __construct ()
	{
		$this->timer_start = microtime(true);
	}

	/**
	 * Reset the timers start time.
	 *
	 * @author John Schlick
	 * @version 1.0
	 * @param None
	 * @access public
	 */
	public function reset_timer()
	{
		$this->timer_start = microtime(true);
	}

	/**
	 * Get the interval since the timer started
	 *
	 * @author John Schlick
	 * @version 1.0
	 * @param boolean $output_text - determines whether to return a text string or just the real.
	 * @param boolean $reset_start - used to determine whether to reset the timers start time or not.
	 * @access public
	 */
	public function get_interval($output_text=false, $reset_start=true)
	{
		$current_time = microtime(true);
		$duration = ($current_time - $this->timer_start);

		if ($reset_start)
		{
			$this->reset_timer();
		}

		if ($output_text)
		{
			return " took " . $duration . " seconds";
		}
		else
		{
			return $duration;
		}
	}
}



/**
 * debug_object
 * Instantiate an object that will be present EVERYWHERE.
 * @var $debug_object
 * @global object $debug_object
 */
$debug_object = new Debug_Object();
// Uncomment the next line if you wish to echo the output to the screen.
//$debug_object->debug_output_method = 'echo';


// It's interesting figuring out where in an application to turn the debug code on and off.
// and since the debug_object is a global object it can be done anywhere.
// I've always placed it below here, so that when I check things in, if I see this object on the list, I know I forgot to remove the "trigger"
// So, be sure not to check this code in when you do your commit.

// Uncommenting the next line will turn on ALL debugging.
// Note: This may generate a LOT of output.
//$debug_object->debug['DEBUG'] = 1;

// The most common case: 
// To turn on debug for a specific routine:
//$debug_object->debug['routine_name_you_want'] = 1;

// The next most common case:
// To turn on debug for everything inside of a specific file:
//$debug_object->debug['file_name_you_want.php'] = 1;

// To turn on debug for a specific routine inside of a specific class that is part of a specific namespace:
//$debug_object->debug['namespace_you_want\\class_you_want::routine_you_want'] = 1;

// To turn on debug for a specific routine INSIDE of a specific class:
//$debug_object->debug['class_you_want::routine_you_want'] = 1;

// To turn on debug for a specific routine INSIDE of a specific namespace:
//$debug_object->debug['namespace_you_want/routine_you_want'] = 1;

// To turn on debug for ALL routines INSIDE of a specific class:
//$debug_object->debug['class_you_want::'] = 1;

// To turn on all debugging for ALL routines in a given namespace use the \ after the namespace.
//$debug_object->debug['namespace_you_want\\'] = 1;

// To turn on debugging inside of a specific file inside of a specific directory:
//$debug_object->debug['directory_you_want/file_you_want.php'] = 1;
// or
//$debug_object->debug['directory_you_want_a/directory_you_want_b/file_you_want.php'] = 1;

// To turn on debugging inside of a specific directory, use a / after the directory name.
//$debug_object->debug['directory_you_want/'] = 1;

// Uncomment this to turn on all screen output related debug output.
//$debug_object->debug['DEBUG'] = 0;

// Uncomment this to turn on all timing related debug output.
//$debug_object->debug['DEBUG'] = -1;

// Uncomment this to turn on all memory related debug output.
//$debug_object->debug['DEBUG'] = -2;

// The one you are using:
//$debug_object->debug['uncomment'] = 3;

?>