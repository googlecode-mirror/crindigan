<?php

/**
 * This file is part of Crindigan.
 *
 * Crindigan is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Crindigan is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Crindigan. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   Crindigan
 * @version   $Revision$
 * @copyright Copyright (c) 2009 Steven Harris
 * @license   http://www.gnu.org/licenses/gpl.txt GPL
 */

/**
 * Custom exception class to support setting the file and line.
 *
 * @package Crindigan
 */
class RPG_Exception extends Exception
{
	/**
	 * Constructs a new exception.
	 *
	 * @param  string  $message
	 * @param  integer $code
	 * @param  string  $file
	 * @param  integer $line
	 */
	public function __construct($message = '', $code = 0, $file = '', $line = 0)
	{
		parent::__construct($message, $code);
		
		if ($file !== '')
		{
			$this->file = $file;
		}
		if ($line > 0)
		{
			$this->line = $line;
		}
	}
	
	/**
	 * Outputs detailed information for an exception.
	 */
	public function __toString()
	{
		$out  = $this->getMessage() . "\n";
		$out .= "<h3>Stack Trace</h3>\n";
		
		// It's duplicating the top entry for some reason
		$trace = $this->getTrace();
		//array_shift($trace);
		
		if (is_array($trace))
		{
			foreach ($trace AS $entry)
			{
				if (!isset($entry['file']))
				{
					continue;
				}
				$file = str_replace(RPG_ROOT, '', $entry['file']);
				$src  = $this->_getSourceLines($entry);
				$out .= "<strong style=\"font-size: 10pt\">$file : $entry[line]</strong><br />\n<span style=\"font-size:9pt\">- $entry[function](" . $this->_formatArgs($entry['args']) . ")</span>\n$src\n";
			}
		}
		
		return $out;
	}
	
	/**
	 * Given a stack trace entry, return HTML-formatted source lines, with
	 * line numbers, and the offending line highlighted.
	 *
	 * @param  array $trace
	 * @return string
	 */
	protected function _getSourceLines(array $trace)
	{
		$lines = file($trace['file']);
		$start = max(0, $trace['line'] - 5);
		$end   = min(sizeof($lines) - 1, $trace['line'] + 3);
		
		$lines = array_slice($lines, $start, $end - $start + 1);
		
		$out = "<pre style=\"background-color:#E8E8E8; font-size: 8pt;\">\n";
		
		for ($i = $start; $i <= $end; $i++)
		{
			$fmt = sprintf('%4d. %s', $i + 1, 
				$this->_formatSource($lines[$i - $start], $trace['function']));
			if ($i === $trace['line'] - 1)
			{
				$fmt = "<strong style=\"background-color:#FFC0C0\">$fmt</strong>";
			}
			$out .= "$fmt\n";
		}
		
		return $out . "</pre>\n";
	}
	
	/**
	 * Formats a single line of source code, stripping HTML, changing tabs
	 * to spaces, and underlining the function call.
	 *
	 * @param  string $line
	 * @param  string $function
	 * @return string
	 */
	protected function _formatSource($line, $function)
	{
		$line = htmlentities(str_replace("\t", '    ', rtrim($line)));
		return str_replace($function, "<ins>$function</ins>", $line);
	}
	
	/**
	 * Transforms an array of function arguments into a comma-separated
	 * and printable parameter list, via var_export.
	 *
	 * @param  array $args
	 * @return string
	 */
	protected function _formatArgs(array $args)
	{
		if (empty($args))
		{
			return '';
		}
		
		$out = array();
		foreach ($args AS $arg)
		{
// 			if (is_string($arg) AND strlen($arg) > 70)
// 			{
// 				$arg = substr($arg, 0, 68) . '...';
// 			}
			$s = str_replace("\n", '', var_export($arg, true));
			$s = str_replace('::__set_state(array', '(', $s);
			
			$out[] = $s;
		}
		
		return htmlentities(implode(', ', $out));
	}
}
