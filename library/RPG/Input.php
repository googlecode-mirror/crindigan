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
		
		// ---------------------------------
		// First we'll need a request URI
		// ---------------------------------
		
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
}
