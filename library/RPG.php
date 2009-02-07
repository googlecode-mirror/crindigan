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
final class RPG
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
	 * @param  mixed  $else If the key does not exist, returns this instead.
	 * @return mixed
	 */
	public static function get($key, $else = null)
	{
		return self::isRegistered($key) ? self::$_registry[$key] : $else;
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
			self::$_input = RPG_Input::getInstance();
		}
		return self::$_input;
	}
	
	/**
	 * Returns a URL string suitable for inclusion into an anchor tag,
	 * given the controller, action, params, and query.
	 * 
	 * For example:
	 * echo RPG::url('test', 'something', array('one', 'two'), array('q' => 'value'));
	 * // => [theBaseUrl]/test/something/one/two?q=value
	 *
	 * @param  string $controller  Use "*" for current controller.
	 * @param  string $action      Use "*" for current action.
	 * @param  array  $params      Slash-separated params included after the action.
	 * @param  array  $query       Parameters to be included in the query string.
	 * @return string  The constructed URL.
	 */
	public static function url($controller = '*', $action = '*',
		array $params = array(), array $query = array())
	{
		if ($controller === '*')
		{
			$controller = self::get('current_controller', 'index');
		}
		if ($action === '*')
		{
			$action = self::get('current_action', 'index');
		}
		
		$url = self::config('baseUrl') . "/$controller/$action";
		if (!empty($params))
		{
			$url .= '/' . implode('/', $params);
		}
		if (!empty($query))
		{
			$url .= '?' . http_build_query($query);
		}
		
		return $url;
	}
}
