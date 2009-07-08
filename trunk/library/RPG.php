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
	 * Router library instance.
	 *
	 * @var RPG_Router
	 */
	private static $_router = null;
	
	/**
	 * Model class instances.
	 *
	 * @var array of RPG_Model subclasses
	 */
	private static $_models = array();
	
	/**
	 * View/layout library instance.
	 *
	 * @var RPG_View
	 */
	private static $_view = null;
	
	/**
	 * Current session instance.
	 *
	 * @var RPG_Session
	 */
	private static $_session = null;
	
	/**
	 * Current user instance.
	 *
	 * @var RPG_User
	 */
	private static $_user = null;
	
	/**
	 * Array of RPG_Database instances, to support having multiple connections
	 * open at once. Indexed by their configuration key.
	 *
	 * @var array of RPG_Database
	 */
	private static $_databases = array();
	
	/**
	 * Private constructor to enforce static class.
	 */
	private function __construct() {}
	
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
	public static function config($key = '')
	{
		if (empty($key))
		{
			return self::$_config;
		}
		
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
	 * Loads a model class and instantiates it if necessary.
	 * 
	 * @param  string $name
	 * @return RPG_Model subclass
	 */
	public static function model($name)
	{
		if (!isset(self::$_models[$name]))
		{
			if (!file_exists(RPG_Model::getPath() . "/$name.php"))
			{
				throw new RPG_Exception("Model \"$name\" does not exist.");
			}
			
			require RPG_Model::getPath() . "/$name.php";
			
			// foo/bar -> FooBarModel
			$className  = str_replace(' ', '', ucwords(str_replace('/', ' ', $name)));
			$className .= 'Model';
			
			self::$_models[$name] = new $className();
		}
		
		return self::$_models[$name];
	}
	
	/**
	 * Fetches the instance of RPG_View, instantiating it if necessary.
	 *
	 * @return RPG_View
	 */
	public static function view()
	{
		if (self::$_view === null)
		{
			self::$_view = new RPG_View();
		}
		
		return self::$_view;
	}
	
	/**
	 * Creates and returns an instance of RPG_Template with the given
	 * template file and variables (optionally).
	 *
	 * @param  string $template
	 * @param  array $vars
	 * @return RPG_Template
	 */
	public static function template($template, array $vars = array())
	{
		return new RPG_Template($template, $vars);
	}
	
	/**
	 * Fetches the instance of RPG_Session, instantiating it if necessary.
	 *
	 * @return RPG_Session
	 */
	public static function session()
	{
		if (self::$_session === null)
		{
			self::$_session = new RPG_Session();
		}
		
		return self::$_session;
	}
	
	/**
	 * Fetches the instance of RPG_User, instantiating it if necessary.
	 *
	 * @return RPG_User
	 */
	public static function user()
	{
		if (self::$_user === null)
		{
			self::$_user = new RPG_User();
		}
		
		return self::$_user;
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
	 * Fetches an instance of the router library, initializing if necessary.
	 *
	 * @param  string $controllerPath Path where controllers are located.
	 * @return RPG_Router
	 */
	public static function router($controllerPath = '')
	{
		if (self::$_router === null)
		{
			if (empty($controllerPath))
			{
				throw new RPG_Exception('Controller path cannot be empty on first call to RPG::router()');
			}
			
			self::$_router = RPG_Router::getInstance();
			self::$_router->setControllerPath($controllerPath);
		}
		return self::$_router;
	}
	
	/**
	 * Returns an instance of RPG_Database, creating it if neccessary, given
	 * the config file key containing the connection information. If the key
	 * is not given, it uses "database" by default. If the second parameter is
	 * present, it will create a new database connection with the given params,
	 * and reference it with the given $configKey.
	 *
	 * @param  string $configKey
	 * @return RPG_Database
	 */
	public static function database($configKey = null, array $newParams = array())
	{
		if ($configKey === null)
		{
			$configKey = 'database';
		}
		
		// Create a new instance if it doesn't exist
		if (!isset(self::$_databases[$configKey])
			OR !(self::$_databases[$configKey] instanceof RPG_Database))
		{
			// If $newParams is given, instantiate RPG_Database with those.
			// Otherwise, use the params inside the config key.
			if (!empty($newParams))
			{
				self::$_databases[$configKey] = new RPG_Database($newParams);
			}
			else
			{
				self::$_databases[$configKey] = new RPG_Database(self::config($configKey));
			}
		}
		
		return self::$_databases[$configKey];
	}
	
	/**
	 * Returns a URL string suitable for inclusion into an anchor tag.
	 * 
	 * For example:
	 * echo RPG::url('test/something/one/two'), array('q' => 'value'));
	 * // => [theBaseUrl]/test/something/one/two?q=value
	 *
	 * In addition, an asterisk can be used in the place of the controller
	 * or action, which will be replaced with their current values.
	 *
	 * @param  string $path  URL path, formed like controller/action/params...
	 * @param  array  $query Parameters to be included in the query string.
	 * @return string  The constructed URL.
	 */
	public static function url($path, $query = array())
	{
		$parts = self::router()->getUrlParts($path);
		extract($parts);
		
		if ($controller === '*')
		{
			$controller = self::get('current_controller', 'index');
		}
		if ($action === '*')
		{
			$action = self::get('current_action', 'index');
		}
		
		if (isset($params[0]) AND $params[0] === '*')
		{
			$params = self::get('current_params', array());
		}
		
		if (is_string($query) AND $query === '*')
		{
			$query = $_GET;
		}
		
		$url = self::config('baseUrl') . "/$controller";
		if ($action !== 'index' OR !empty($params))
		{
			$url .= '/' . $action;
		}
		
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
	 * Receiver for standard PHP errors, turning them into exceptions.
	 *
	 * @param  integer $errNo
	 * @param  string  $errMsg
	 * @param  string  $errFile
	 * @param  integer $errLine
	 * @throws RPG_Exception
	 */
	public static function handlePhpError($errNo, $errMsg, $errFile, $errLine)
	{
		throw new RPG_Exception($errMsg, $errNo, $errFile, $errLine);
	}
}
