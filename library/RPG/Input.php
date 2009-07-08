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
 * Handles information from the HTTP request and input sanitizing.
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
	 * Constructs an instance of the input class, reversing magic_quotes
	 * if needed. Protected to enforce the singleton pattern.
	 */
	protected function __construct()
	{
		$this->_fixMagicQuotes();
				
		$this->_requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
		
		if (!$this->isGet() AND !$this->isPost())
		{
			throw new RPG_Exception('Invalid request method - may only accept GET and POST.');
		}
		
		// if i decide to use mb_* functions for everything later
		//mb_internal_encoding('UTF-8');
	}
	
	/**
	 * Fetches a singleton instance of this class.
	 *
	 * @return RPG_Input
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
	 * Filters a variable.
	 *
	 * @see    RPG_Input_Filter::filter()
	 * @param  mixed $var
	 * @param  string $filter
	 * @param  array $options
	 * @return mixed Filtered variable.
	 */
	public function filter($var, $filter = '', array $options = array())
	{
		return empty($filter) ? $var : RPG_Input_Filter::filter($var, $filter, $options);
	}
	
	/**
	 * Filters a variable from an array.
	 *
	 * @param  array $var Array of data
	 * @param  mixed $key Key from the $var array, or an array of multiple
	 *                    keys pointing to filters and options.
	 * @param  string $filter
	 * @param  array $options
	 * @return mixed Filtered variable.
	 */
	public function filterFromArray($var, $key, $filter = '', array $options = array())
	{
		if (is_array($key))
		{
			$return = array();
			foreach ($key AS $k => $value)
			{
				if (is_array($value))
				{
					$filter  = array_shift($value);
					$options = $value;
				}
				else
				{
					$filter  = $value;
					$options = array();
				}
				$return[$k] = $this->filterFromArray($var, $k, $filter, $options);
			}
			return $return;
		}
		
		if (empty($filter))
		{
			return isset($var[$key]) ? $var[$key] : null;
		}
		
		return $this->filter(isset($var[$key]) ? $var[$key] : null, $filter, $options);
	}
	
	/**
	 * 
	 * $input->get('charId', 'int', array('min' => 1));
	 * $input->get('charId', 'int');
	 
	 * $input->get(array('charId' => array('int', 'min' => 1)));
	 * $input->get(array('charId' => 'int'));
	 */
	public function get($key, $filter = '', array $options = array())
	{
		return $this->filterFromArray($_GET, $key, $filter, $options);
	}
	
	public function post($key, $filter = '', array $options = array())
	{
		return $this->filterFromArray($_POST, $key, $filter, $options);
	}
	
	public function cookie($key, $filter = '', array $options = array())
	{
		$cookiePrefix = RPG::config('cookiePrefix');
		
		if (is_array($key))
		{
			$pkey = array();
			foreach ($key AS $k => $v)
			{
				$pkey[$cookiePrefix . $k] = $v;
			}
		}
		else
		{
			$pkey = $cookiePrefix . $key;
		}
		
		return $this->filterFromArray($_COOKIE, $pkey, $filter, $options);
	}
	
	/**
	 * Sets a cookie, applying the cookie prefix given in the configuration.
	 *
	 * @param  string $name
	 * @param  string $value If left alone (as null), will delete the cookie.
	 * @param  int $expire
	 * @param  bool $httpOnly
	 */
	public function setCookie($name, $value = null, $expire = null, $httpOnly = false)
	{
		// TODO: have this in an output/response class?
		if ($value === null)
		{
			$expire = -86400;
		}
		else if ($expire === null)
		{
			$expire = 86400 * 365;
		}
		
		setcookie(RPG::config('cookiePrefix') . $name, $value, RPG_NOW + $expire,
			RPG::config('baseUrl') . '/', '', false, $httpOnly);
	}
	
	/**
	 * Returns the path info for the request.
	 *
	 * @param  bool $includeQuery If true, does not remove the query string
	 * @param  bool $includeBase If true, does not remove the base path
	 * @return string
	 */
	public function getPath($includeQuery = false, $includeBase = false)
	{
		// First we'll need a request URI
		$path = $_SERVER['REQUEST_URI'];
		if (isset($_SERVER['HTTP_HOST']) AND strpos($path, $_SERVER['HTTP_HOST']) !== false)
		{
			$path = preg_replace('#^[^:]*://[^/]*/#', '/', $path);
		}
		
		// Remove the query string if it's present
		if (!$includeQuery AND ($query = strpos($path, '?')) !== false)
		{
			$path = substr($path, 0, $query);
		}
		
		// Remove the base URL
		$baseUrl = RPG::config('baseUrl');
		if (!$includeBase AND !empty($baseUrl))
		{
			$baseUrl = rtrim($baseUrl, '/');
			$path = substr($path, strlen($baseUrl));
		}
		
		$this->_path = $path;
		return $path;
	}
	
	/**
	 * Gets the browsing user's IP address.
	 *
	 * @param  int $length # of parts to return (1.2.3.4)
	 * @return string
	 */
	public function getIP($length = 4)
	{
		// needs more, i know
		$ip = $_SERVER['REMOTE_ADDR'];
		
		if ($length === 4)
		{
			return $ip;
		}
		
		$parts = array_slice(explode('.', $ip), 0, $length);
		return implode('.', $parts);
	}
	
	/**
	 * Gets the browsing user's user agent string.
	 *
	 * @return string
	 */
	public function getUserAgent()
	{
		return $_SERVER['HTTP_USER_AGENT'];
	}
	
	/**
	 * Reverses the effects of magic_quotes settings, if neccessary.
	 * Recursively strips slashes in _GET, _POST, _COOKIE, and _FILES,
	 * and disables magic_quotes_runtime if enabled.
	 */
	protected function _fixMagicQuotes()
	{
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
