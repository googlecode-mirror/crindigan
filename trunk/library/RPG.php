<?php

/**
 * This file is part of Anfiniti RPG.
 *
 * Anfiniti RPG is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Anfiniti RPG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Anfiniti RPG. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   AnfinitiRPG
 * @version   $Revision$
 * @copyright Copyright (c) 2009 Steven Harris
 * @license   http://www.gnu.org/licenses/gpl.txt GPL
 */

/**
 * Static class to serve as a registry and to contain various shortcuts.
 * 
 * @package AnfinitiRPG
 */
class RPG
{
	/**
	 * Configuration array.
	 * 
	 * @var array
	 */
	private static $_config = array();
	
	/**
	 * Variable register.
	 *
	 * @var array
	 */
	private static $_registry = array();
	
	/**
	 * Input library instance.
	 *
	 * @var RPG_Input
	 */
	private static $_input = null;
	
	/**
	 * Loads a model class and instantiates it.
	 */
	public static function model($name)
	{
		
	}
	
	/**
	 * Sets the internal $_config property to the given configuration array.
	 *
	 * @param  array $config
	 */
	public static function setConfig(array $config)
	{
		self::$_config = $config;
	}
	
	/**
	 * Loads a configuration value given a key.
	 * 
	 * Sub-arrays within the configuration can be accessed by splitting the 
	 * key by a slash. For example, $config['x']['y']['z'] can be accessed
	 * with RPG::config('x/y/z').
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public static function config($key)
	{
		if (strpos($key, '/') === false)
		{
			return isset(self::$_config[$key]) ? self::$_config[$key] : null;
		}
		
		$parts = explode('/', $key);
		$value = self::$_config;
		while (sizeof($parts) > 0)
		{
			$part = array_shift($parts);
			if (!isset($value[$part]))
			{
				return null;
			}
			$value = $value[$part];
		}
		
		return $value;
	}
	
	/**
	 * Sets a value to the variable registry.
	 *
	 * @param  string $key
	 * @param  mixed  $value
	 */
	public static function set($key, $value)
	{
		self::$_registry[$key] = $value;
	}
	
	/**
	 * Retrieves a value from the variable registry.
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public static function get($key)
	{
		return self::isRegistered($key) ? self::$_registry[$key] : null;
	}
	
	/**
	 * Returns if a the given key exists in the variable registry.
	 *
	 * @param  string $key
	 * @return boolean
	 */
	public static function isRegistered($key)
	{
		return isset(self::$_registry[$key]);
	}
	
	/**
	 * Fetches the instance of the input library, instantiating it if
	 * necessary.
	 * 
	 * @return RPG_Input
	 */
	public static function input()
	{
		if (self::$_input === null)
		{
			self::$_input = new RPG_Input;
		}
		return self::$_input;
	}
}
