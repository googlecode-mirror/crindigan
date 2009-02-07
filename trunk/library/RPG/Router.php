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
 * The router is responsible for handling requests and passing them off
 * to the proper controller and action.
 * 
 * @package AnfinitiRPG
 */
class RPG_Router
{
	/**
	 * Initializes the router with the given controller directory.
	 *
	 * @param  string $controllerDir  Path to the base controller directory.
	 */
	protected function __construct($controllerDir)
	{
		if (is_dir($controllerDir))
		{
			$this->_controllerDir = $controllerDir;
		}
		else
		{
			throw new Exception('Specified controller directory does not exist.');
		}
	}
	
	/**
	 * Initializes the router and processes the current request.
	 *
	 * @param  string $controllerDir  Path to the base controller directory.
	 */
	public static function processRequest($controllerDir)
	{
		$router = new self($controllerDir);
		$path   = RPG::input()->getPath();
		$parts  = $router->_getUrlParts($path);
		
		$controller = $router->_getController($parts['controller']);
		$action     = $router->_getActionName($parts['action']);
		
		if (!method_exists($controller, $action))
		{
			throw new Exception('Action "' . $action . '" does not exist.');
		}
		
		call_user_func_array(array($controller, $action), $parts['params']);
	}
	
	/**
	 * Given a URL path, returns an array of the controller, action,
	 * and params components.
	 *
	 * @param  string $path
	 * @return array  ('controller' => ..., 'action' => ..., 'params' => ...)
	 */
	protected function _getUrlParts($path)
	{
		// Split the path into controller/action/params
		$parts = explode('/', trim($path, '/'));
		
		$controller = 'index';
		$action     = 'index';
		$params     = array();
		
		if (!empty($parts[0]))
		{
			$controller = $parts[0];
		}
		
		if (isset($parts[1]) AND !empty($parts[1]))
		{
			$action = $parts[1];
		}
		
		if (sizeof($parts) > 2)
		{
			$params = array_slice($parts, 2);
		}
		
		return array(
			'controller' => $controller,
			'action'     => $action,
			'params'     => $params,
		);
	}
	
	/**
	 * Attempts to find and load the controller class, given the controller
	 * component inside of the URL.
	 *
	 * @param  string $urlPart
	 * @return RPG_Controller
	 */
	protected function _getController($urlPart)
	{
		$urlPart  = preg_replace('#[^a-z0-9:]#', '', $urlPart);
		$urlPart  = preg_replace('#:{2,}#', ':', $urlPart);
		$fileName = $this->_controllerDir . '/' . str_replace(':', '/', $urlPart) . '.php';
		
		// Build class name - admin:char => AdminCharController
		$className  = implode('', array_map('ucfirst', explode(':', $urlPart)));
		$className .= 'Controller';
		
		if (!file_exists($fileName))
		{
			throw new Exception('Controller "' . $className . '" not found.');
		}
		
		RPG::set('current_controller', $urlPart);
		
		require $fileName;
		return new $className();
	}
	
	/**
	 * Returns the action method name based on the action component of
	 * the request URI. Dashes create separate words, which are all
	 * capitalized and placed after "do."
	 * 
	 * For example, "update-stats" translates into doUpdateStats()
	 *
	 * @param  string $urlPart
	 * @return string
	 */
	protected function _getActionName($urlPart)
	{
		$urlPart = preg_replace('#-{2,}#', '-', $urlPart);
		RPG::set('current_action', $urlPart);
		
		$method  = 'do' . implode('', array_map('ucfirst', explode('-', $urlPart)));
		$method  = preg_replace('#[^a-zA-Z0-9]#', '', $method);
		return $method;
	}
}
