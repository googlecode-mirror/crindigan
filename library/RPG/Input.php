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
 * Description
 * 
 * @package AnfinitiRPG
 */
class RPG_Input
{
	/**
	 * Contains a singleton instance of this class.
	 *
	 * @var RPG_Input
	 */
	protected static $_instance = null;
	
	/**
	 * The current request method, in lowercase.
	 *
	 * @var string
	 */
	protected $_requestMethod = '';
	
	/**
	 * Constructs an instance of the input class, cleansing out some
	 * potential attack vectors and reversing magic_quotes if needed.
	 * Protected to enforce the singleton pattern.
	 */
	protected function __construct()
	{
		// reverse the effects of magic quotes if necessary
		if (get_magic_quotes_gpc() === 1)
		{
			$this->_stripSlashes($_GET);
			$this->_stripSlashes($_POST);
			$this->_stripSlashes($_COOKIE);

			if (is_array($_FILES))
			{
				foreach ($_FILES AS &$file)
				{
					$file['tmp_name'] = str_replace('\\', '\\\\', $file['tmp_name']);
				}
				$this->_stripSlashes($_FILES);
			}
		}
		
		if (get_magic_quotes_runtime() === 1)
		{
			set_magic_quotes_runtime(0);
			@ini_set('magic_quotes_sybase', 0);
		}
		
		$this->_requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
		
		if (!$this->isGet() AND !$this->isPost())
		{
			throw new Exception('Invalid request method - may only accept GET and POST.');
		}
	}
	
	/**
	 * Fetches a singleton instance of this class.
	 *
	 * @return RPG_Input
	 */
	public function getInstance()
	{
		if (self::$_instance === null)
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * Returns true if the request method is POST.
	 *
	 * @return boolean
	 */
	public function isPost()
	{
		return $this->_requestMethod === 'post';
	}
	
	/**
	 * Returns true if the request method is GET.
	 *
	 * @return boolean
	 */
	public function isGet()
	{
		return $this->_requestMethod === 'get';
	}
	
	/**
	 * Returns the path info for the request.
	 *
	 * @return string
	 */
	public function getPath()
	{
		if (isset($this->_path))
		{
			return $this->_path;
		}
		
		// First we'll need a request URI
		
		if (isset($_SERVER['HTTP_X_REWRITE_URL']))
		{
			$path = $_SERVER['HTTP_X_REWRITE_URL'];
		}
		else if (isset($_SERVER['REQUEST_URI']))
		{
			$path = $_SERVER['REQUEST_URI'];
			if (isset($_SERVER['HTTP_HOST']) AND strpos($path, $_SERVER['HTTP_HOST']) !== false)
			{
				$path = preg_replace('#^[^:]*://[^/]*/#', '/', $path);
			}
		}
		else if (isset($_SERVER['ORIG_PATH_INFO']))
		{
			$path = $_SERVER['ORIG_PATH_INFO'];
		}
		else
		{
			$path = '';
		}
		
		// Remove the query string if it's present
		if (($query = strpos($path, '?')) !== false)
		{
			$path = substr($path, 0, $query);
		}
		
		// Remove the base URL
		$baseUrl = RPG::config('baseUrl');
		if (!empty($baseUrl))
		{
			$baseUrl = rtrim($baseUrl, '/');
			$path = substr($path, strlen($baseUrl));
		}
		
		$this->_path = $path;
		return $path;
	}
	
	/**
	 * Recursively strips slashes from a given input array
	 *
	 * @param  array &$input
	 */
	protected function _stripSlashes(array &$input)
	{
		foreach ($input AS &$value)
		{
			if (is_string($value))
			{
				$value = stripslashes($value);
			}
			else if (is_array($value))
			{
				$this->_stripSlashes($value);
			}
		}
	}
}
