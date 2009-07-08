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
	 * Singleton instance of this class.
	 *
	 * @var RPG_Router
	 */
	protected static $_instance = null;
	
	/**
	 * Path to the directory containing controllers.
	 *
	 * @var string
	 */
	protected $_controllerPath = '';
	
	/**
	 * Initializes the router. Protected to enforce singleton pattern.
	 */
	protected function __construct()
	{
		
	}
	
	/**
	 * Initializes a singleton instance of this class.
	 * 
	 * @return RPG_Router
	 */
	public static function getInstance()
	{
		if (self::$_instance === null)
		{
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	/**
	 * Sets the controller path.
	 *
	 * @param  string $controllerPath  Path to the base controller directory
	 */
	public function setControllerPath($controllerPath = '')
	{
		if (is_dir($controllerPath))
		{
			$this->_controllerPath = $controllerPath;
		}
		else
		{
			throw new RPG_Exception('Specified controller path does not exist.');
		}
	}
	
	/**
	 * Processes the current request, handing it off to the proper
	 * controller and action.
	 */
	public function processRequest()
	{
		$path  = RPG::input()->getPath();
		$parts = $this->getUrlParts($path);
		
		$controller = $this->_getController($parts['controller']);
		$action     = $this->_getActionName($parts['action']);
		RPG::set('current_params', $parts['params']);
		
		if (!method_exists($controller, $action))
		{
			throw new RPG_Exception('Action "' . $action . '" does not exist.');
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
	public function getUrlParts($path)
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
		$urlPart  = preg_replace('#[^a-z0-9:]#', '', strtolower($urlPart));
		$urlPart  = preg_replace('#:{2,}#', ':', $urlPart);
		$fileName = $this->_controllerPath . '/' . str_replace(':', '/', $urlPart) . '.php';
		
		// Build class name - admin:char => AdminCharController
		$className  = implode('', array_map('ucfirst', explode(':', $urlPart)));
		$className .= 'Controller';
		
		if (!file_exists($fileName))
		{
			throw new RPG_Exception('Controller "' . $className . '" not found in "' . $this->_controllerPath . '".');
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
