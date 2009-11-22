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
 * Abstract authentication adapter.
 *
 * @package Crindigan
 */
abstract class RPG_Auth
{
	/**
	 * Username to authenticate.
	 *
	 * @var string
	 */
	protected $_username = '';
	
	/**
	 * Password of the user.
	 *
	 * @var string
	 */
	protected $_password = '';
	
	/**
	 * The ID of the user, as set within authenticate()
	 *
	 * @var integer
	 */
	protected $_userId = 0;
	
	/**
	 * Constructor. Protected so that only subclasses may be instantiated.
	 *
	 * @param  string $username
	 * @param  string $password
	 */
	protected function __construct($username, $password)
	{
		$this->_username = $username;
		$this->_password = $password;
	}
	
	/**
	 * Returns an instance of an RPG_Auth subclass, given the username,
	 * password, and an adapter. If the adapter is not given, it will
	 * use the authAdapter setting as defined in config.php.
	 *
	 * @param  string $username
	 * @param  string $password
	 * @param  string $adapter
	 * @return RPG_Auth subclass
	 */
	public static function factory($username, $password, $adapter = null)
	{
		if ($adapter === null)
		{
			$adapter = RPG::config('authAdapter');
		}
		
		$className = 'RPG_Auth_' . ucfirst($adapter);
		
		return new $className($username, $password);
	}
	
	/**
	 * Returns the user ID as set after authentication.
	 *
	 * @return integer
	 */
	public function getUserId()
	{
		return $this->_userId;
	}
	
	/**
	 * Authenticates the given username and password, and sets the _userId
	 * property to the ID of the matched user (if any).
	 *
	 * @return bool Whether authentication was a success.
	 */
	abstract public function authenticate();
}
