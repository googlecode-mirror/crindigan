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
 * Class to represent the current user browsing the application.
 * 
 * @package AnfinitiRPG
 */
class RPG_User
{
	/**
	 * Stores raw user information pulled from the database.
	 *
	 * @var array
	 */
	public $data = array();
	
	/**
	 * Initializes the user, setting them up as a member or guest, and
	 * checking for automatic logins.
	 */
	public function __construct()
	{
		// try to see if we're logged in according to the session
		if ($this->isLoggedIn())
		{
			// setup registered user
			$this->_setupMember();
		}
		else if (!$this->_attemptAutoLogin())
		{
			// if auto-login failed, we're a guest
			$this->_setupGuest();
		}
	}
	
	/**
	 * Sets up the instance for a registered member.
	 */
	protected function _setupMember()
	{
		$userId = RPG::session()->userId;
		$this->data = RPG::database()->queryFirst(
			'SELECT * FROM {user} WHERE user_id = :0', $userId);
	}
	
	/**
	 * Sets up the instance for a guest.
	 */
	protected function _setupGuest()
	{
		$this->data = array(
			'user_id' => 0,
			'user_name' => 'Guest',
		);
	}
	
	/**
	 * Attempts to auto-login a user based on data stored in cookies.
	 * If successful, this will log in the user and set up this instance for
	 * a registered member. It will then regenerate the auto-login information
	 * for the user to another random value.
	 *
	 * @return bool True if logged in, false if not.
	 */
	protected function _attemptAutoLogin()
	{
		$userId   = (int) RPG::input()->getCookie('userid');
		$loginKey = RPG::input()->getCookie('autologin');
		
		if (!$userId AND !$loginKey)
		{
			return false;
		}
		
		// we check the auto-login to make sure it isn't older than 30 days
		$user = RPG::database()->queryFirst(
			"SELECT user_autologin, user_autologin_time
			 FROM {user}
			 WHERE user_id = :0",
			$userId
		);
		
		if ($user['user_autologin_time'] < RPG_NOW - (86400 * 30))
		{
			return false;
		}
		
		if (sha1($user['user_autologin'] . RPG::config('cookieSalt')) !== $loginKey)
		{
			return false;
		}
		
		// we succeeded. log in, set up the member, and refresh auto login details.
		RPG::session()->loggedIn = true;
		RPG::session()->userId   = $userId;
		$this->_setupMember();
		$this->refreshAutoLogin();
		
		return true;		
	}
	
	/**
	 * Generates a new autologin key, saves it to the database, and updates
	 * the user's cookie.
	 */
	public function refreshAutoLogin()
	{
		$loginKey = sha1($this->data['user_password'] . $this->generateSalt(32));
		
		RPG::database()->update('user', array(
			'user_autologin' => $loginKey,
			'user_autologin_time' => RPG_NOW,
		), array('user_id = :0', $this->data['user_id']));
		
		// set httponly cookie for 30 days
		RPG::input()->setCookie('autologin', sha1($loginKey . RPG::config('cookieSalt')),
			86400 * 30, true);
	}
	
	/**
	 * Generates a random salt.
	 *
	 * @param  integer $length
	 * @return string
	 */
	public function generateSalt($length = 5)
	{
		$salt = '';
		for ($i = 0; $i < $length; $i++)
		{
			$rand = mt_rand(1, 93);
			
			// if rand is 1-59, add 32 (33-91). else, add 33 (93-126).
			// skips backslash while still creating even distribution.
			$rand += ($rand < 60) ? 32 : 33;
			$salt .= chr($rand);
		}
		
		return $salt;
	}
	
	/**
	 * Returns whether or not the user is logged in.
	 *
	 * @return bool
	 */
	public function isLoggedIn()
	{
		return RPG::session()->isLoggedIn();
	}
	
	/**
	 * Allows access to $data properties via object notation.
	 * $user->name == $user->data['user_name']
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return isset($this->data["user_$key"]) ? $this->data["user_$key"] : null;
	}
}
