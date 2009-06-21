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

/*
	CREATE TABLE hello (
		hello_key varchar(32),
		hello_msg text,
		INDEX hello_key (hello_key)
	);
*/

/**
 * Test controller for various things.
 *
 * @package AnfinitiRPG
 */
class HelloController extends RPG_Controller
{
	public function __construct()
	{
		parent::__construct();
		RPG::view()->setLayout('layouts/frontend.php');
	}
	
	/**
	 * Default action of the hello controller.
	 */
	public function doIndex()
	{
		RPG::view()->setContent(
			RPG::template('hello_index.php')->set('somevar', 'hoohah')
		);
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
	
	// Below routines will be inside of models in the future.
	// Just here for the purpose of quick testing.
	
	public function doList()
	{		
		$list = RPG::database()->queryPair("SELECT hello_key, hello_msg FROM {hello}");
		foreach ($list AS $key => $msg)
		{
			echo htmlentities($key), ': ', htmlentities($msg), "<br />\n";
		}
	}
	
	public function doView($key)
	{
		$name = RPG::database()->queryOne("SELECT hello_msg FROM {hello}
										   WHERE hello_key = :0", array($key));
		echo 'Hello, ' . htmlentities($name);
	}
	
	public function doInsert($key, $msg)
	{
		RPG::database()->insert('hello', array('hello_key' => $key, 'hello_msg' => $msg));
	}
	
	public function doUpdate($key, $msg)
	{
		$db = RPG::database();
		$db->update('hello', array('hello_msg' => $msg),
			array('hello_key = :0', $key));
	}
	
	public function doDelete($key)
	{
		$db = RPG::database();
		$db->delete('hello', array('hello_key = :0', $key));
	}
}
