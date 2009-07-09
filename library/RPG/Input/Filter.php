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
 * Contains a set of filters for handling user input.
 *
 * @package Crindigan
 */
class RPG_Input_Filter
{
	/**
	 * Filters a given variable with the specified filter and options.
	 *
	 * @param  mixed $var
	 * @param  string $filter
	 * @param  array $options
	 * @return mixed Filtered variable.
	 */
	public static function filter($var, $filter, array $options)
	{
		// make sure self::{blah}Filter exists.
		$function = strtolower($filter) . 'Filter';
		if (!is_callable('RPG_Input_Filter::' . $function))
		{
			throw new RPG_Exception("Filter \"$filter\" does not exist.");
		}
		
		// if $var is null, and $options['default'] is set, do that first
		if ($var === null AND isset($options['default']))
		{
			return $options['default'];
		}
		
		// pass it on to a filter-specific function
		return self::$function($var, $options);
	}
	
	/**
	 * Filters the given variable as an integer.
	 *
	 * @param  mixed $var
	 * @param  array $options See numFilter.
	 * @return int
	 */
	public static function intFilter($var, array $options)
	{
		$options['integer'] = true;
		return self::numFilter($var, $options);
	}
	
	/**
	 * Filters the given variable as an unsigned integer (min = 0).
	 *
	 * @param  mixed $var
	 * @param  array $options See numFilter.
	 * @return int
	 */
	public static function uintFilter($var, array $options)
	{
		$options['min'] = 0;
		$options['integer'] = true;
		return self::numFilter($var, $options);
	}
	
	/**
	 * Filters the given variable as a real number.
	 *
	 * @param  mixed $var
	 * @param  array $options integer - Force number to be an integer
	 *                        bound   - Set number restrictions as "min,max"
	 *                        min     - Minimum value for number
	 *                        max     - Maximum value for number
	 *                        round   - Rounding precision
	 * @return float|int
	 */
	public static function numFilter($var, array $options)
	{
		// handle default
		if ($var === null)
		{
			$var = 0;
		}
		
		// force float or integer
		if (isset($options['integer']) AND $options['integer'])
		{
			$var = intval($var);
		}
		else
		{
			$var = floatval($var);
		}
		
		// handle bound, min, and max options
		if (isset($options['bound']))
		{
			$bound = explode(',', $options['bound']);
			if ($bound[0] !== '')
			{
				$options['min'] = $bound[0];
			}
			if ($bound[1] !== '')
			{
				$options['max'] = $bound[1];
			}
		}
		
		if (isset($options['min']))
		{
			$var = max($options['min'], $var);
		}
		if (isset($options['max']))
		{
			$var = min($options['max'], $var);
		}
		
		// handle round option
		if (isset($options['round']))
		{
			$var = round($var, (int) $options['round']);
		}
		
		// done
		return $var;
	}
	
	/**
	 * Filters the given variable as an unsigned real number (min = 0).
	 *
	 * @param  mixed $var
	 * @param  array $options See numFilter.
	 * @return float|int
	 */
	public static function unumFilter($var, array $options)
	{
		$options['min'] = 0;
		return self::numFilter($var, $options);
	}
	
	/**
	 * Filters the given variable as a string.
	 *
	 * @param  mixed $var
	 * @param  array $options allowNull - True to allow null bytes
	 *                        maxLength - Will trim string to this length
	 *                        noHtml    - Will run htmlentities() on string
	 *                        noTrim    - Will not trim() the string
	 *                        onlyChars - Only allow the given chars (regex charclass)
	 * @return string
	 */
	public static function stringFilter($var, array $options)
	{
		$var = strval($var);
		
		if (!isset($options['allowNull']) OR !$options['allowNull'])
		{
			$var = str_replace("\0", '', $var);
		}
		
		if (isset($options['maxLength']))
		{
			$var = substr($var, 0, $options['maxLength']);
		}
		
		if (isset($options['noHtml']) AND $options['noHtml'])
		{
			$var = htmlspecialchars($var, ENT_QUOTES, 'UTF-8', false);
		}
		
		if (!isset($options['noTrim']) OR !$options['noTrim'])
		{
			$var = trim($var);
		}
		
		if (isset($options['onlyChars']))
		{
			//$options['onlyChars'] = str_replace(array('\\', '[', ']', '#'), 
			//	array('\\\\', '\\[', '\\]', '\\#'), $options['onlyChars']);
			$options['onlyChars'] = preg_quote($options['onlyChars'], '#');
			$var = preg_replace("#[^{$options['onlyChars']}]#", '', $var);
		}
		
		return $var;
	}
}
