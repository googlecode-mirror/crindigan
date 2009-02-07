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

class HelloController extends RPG_Controller
{
	/**
	 * Default action of the hello controller.
	 */
	public function doIndex()
	{
		echo 'Index action of the hello controller.';
	}
	
	/**
	 * Prints your standard "Hello, world!" to the browser.
	 */
	public function doWorld()
	{
		echo 'Hello, world!';
	}
	
	/**
	 * Prints a "Hello, name" message based on a URL parameter.
	 * 
	 * @param  string $name  The name this action will greet.
	 */
	public function doCustom($name = '')
	{
		echo 'Hello, ', htmlentities($name), '!';
	}
	
	/**
	 * Lists all filters available with the filter extension.
	 */
	public function doListFilters()
	{
		$list = filter_list();
		foreach ($list AS $filter)
		{
			echo filter_id($filter), ": $filter<br />\n";
		}
	}
}
