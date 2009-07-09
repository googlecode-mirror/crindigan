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
 * Class to load and render a single template.
 *
 * @package Crindigan
 */
class RPG_Template
{
	/**
	 * Path to find template files.
	 *
	 * @var string
	 */
	protected static $_path = '';
	
	/**
	 * Variables to expose to the template.
	 *
	 * @var array
	 */
	protected $_vars = array();
	
	/**
	 * The template to use.
	 *
	 * @var string
	 */
	protected $_template = '';
	
	/**
	 * Creates a new instance of the template class, setup with the given
	 * template name and optionally variables.
	 *
	 * @param  string $template
	 * @param  array  $vars
	 */
	public function __construct($template = '', array $vars = array())
	{
		if (!empty($template))
		{
			$this->setTemplate($template);
		}
		
		if (!empty($vars))
		{
			$this->set($vars);
		}
	}
	
	/**
	 * Sets the path to search for templates.
	 *
	 * @param  string
	 */
	public static function setPath($path)
	{
		if (!is_dir($path))
		{
			throw new RPG_Exception('Specified template path does not exist.');
		}
		
		self::$_path = $path;
	}
	
	/**
	 * Retrieves the template search path.
	 *
	 * @return string
	 */
	public static function getPath()
	{
		return self::$_path;
	}
	
	/**
	 * Sets the template file to use.
	 *
	 * @param  string $template
	 * @return RPG_Template
	 */
	public function setTemplate($template)
	{
		if (empty(self::$_path))
		{
			throw new RPG_Exception('Template path is not set.');
		}
		
		if (!file_exists(self::$_path . "/$template"))
		{
			throw new RPG_Exception('Template file does not exist.');
		}
		
		$this->_template = $template;
		return $this;
	}
	
	/**
	 * Sets one key to a value. You may also pass an array to $key, and it
	 * will set all of its keys to their corresponding values.
	 * 
	 * @param  string|array $key
	 * @param  mixed $value
	 * @return RPG_Template
	 */
	public function set($key, $value = null)
	{
		if (is_array($key) AND $value === null)
		{
			$this->_vars = array_merge($this->_vars, $key);
		}
		else
		{
			$this->_vars[$key] = $value;
		}
		
		return $this;
	}
	
	/**
	 * Sets a key to a value via property modification:
	 * $template->foo = 'bar'; // $template->set('foo', 'bar');
	 *
	 * @param  string $key
	 * @param  mixed $value
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}
	
	/**
	 * Retrieves a value given its key.
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function get($key)
	{
		return isset($this->_vars[$key]) ? $this->_vars[$key] : null;
	}
	
	/**
	 * Retrieves a value through accessing properties.
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->get($key);
	}
	
	/**
	 * Clears one or all template variables.
	 * 
	 * @param  string $key Key of single variable to clear. Can pass multiple
	 *                     arguments to clear many, or pass no arguments to
	 *                     clear all variables.
	 */
	public function clear()
	{
		$num = func_num_args();
		if ($num === 0)
		{
			$this->_vars = array();
			return;
		}
		
		foreach (func_get_args() AS $key)
		{
			if (isset($this->_vars[$key]))
			{
				unset($this->_vars[$key]);
			}
		}
	}
	
	/**
	 * Renders a template and returns its contents.
	 *
	 * @return string
	 */
	public function render()
	{
		extract($this->_vars, EXTR_SKIP);
		ob_start();
		include self::$_path . '/' . $this->_template;
		return ob_get_clean();
	}
	
	public function partial($template, array $vars = array())
	{
		$t = new self($template, $vars);
		return $t->render();
	}
	
	public function partialLoop($template, array $varArray = array())
	{
		$t = new self($template);
		$s = '';
		foreach ($varArray AS $index => $vars)
		{
			$t->clear();
			$t->set($vars);
			$t->set('loopIndex', $index);
			$s .= $t->render();
		}
		
		return $s;
	}
	
	public function escape($str, $return = false)
	{
		$str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8', false);
		if ($return)
		{
			return $str;
		}
		echo $str;
	}
	
	public function url($path, array $query = array())
	{
		return $this->escape(RPG::url($path, $query), true);
	}
}
