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
 * Class to represent the current user browsing the application.
 * 
 * @package Crindigan
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
	 * User model instance, since it will be used frequently.
	 *
	 * @var UserModel
	 */
	protected $_model = null;
	
	/**
	 * RPG_Session instance.
	 *
	 * @var RPG_Session
	 */
	protected $_session = null;
	
	/**
	 * RPG_Input instance.
	 *
	 * @var RPG_Input
	 */
	protected $_input = null;
	
	/**
	 * Initializes the user, setting them up as a member or guest, and
	 * checking for automatic logins.
	 * 
	 * @param RPG_Model $model Instance of a user model.
	 * @param RPG_Session $session Instance of session class.
	 * @param RPG_Input $input Instance of input class.
	 */
	public function __construct($model = null, $session = null, $input = null)
	{
		if ($model === null)
		{
			$model = RPG::model('user');
		}
		
		if ($session === null)
		{
			$session = RPG::session();
		}
		
		if ($input === null)
		{
			$input = RPG::input();
		}
		
		$this->_model   = $model;
		$this->_session = $session;
		$this->_input   = $input;
		
		// try to see if we're logged in according to the session
		if ($this->isLoggedIn())
		{
			// setup registered user
			$this->setupMember();
		}
		else if (!$this->_attemptAutoLogin())
		{
			// if auto-login failed, we're a guest
			$this->setupGuest();
		}
	}
	
	/**
	 * Sets up the instance for a registered member.
	 */
	public function setupMember()
	{
		$userId = $this->_session->userId;
		//$this->data = $this->_model->getUserInfo($userId);
		$this->_model->load($userId);
		
		$this->data['user_logouthash'] = sha1($this->id . sha1($this->salt) . 
			sha1($this->name) . sha1(RPG::config('cookieSalt')));
	}
	
	/**
	 * Sets up the instance for a guest.
	 */
	public function setupGuest()
	{
		$this->data = array(
			'user_id' => 0,
			'user_name' => 'Guest',
		);
		$this->_session->userId   = 0;
		$this->_session->loggedIn = false;
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
		$userId   = $this->_input->cookie('userid', 'uint');
		$loginKey = $this->_input->cookie('autologin', 'string');
		
		if (!$userId OR !$loginKey)
		{
			return false;
		}
		
		// we check the auto-login to make sure it isn't older than 30 days
		$user = $this->_model->getAutoLoginInfo($userId);
		
		if ($user['user_autologin_time'] < RPG_NOW - (86400 * 30))
		{
			return false;
		}
		
		if (sha1($user['user_autologin'] . RPG::config('cookieSalt')) !== $loginKey)
		{
			return false;
		}
		
		// we succeeded. log in, set up the member, and refresh auto login details.
		$this->_session->loggedIn = true;
		$this->_session->userId   = $userId;
		$this->setupMember();
		$this->refreshAutoLogin();
		
		return true;		
	}
	
	/**
	 * Generates a new autologin key, saves it to the database, and updates
	 * the user's cookie.
	 */
	public function refreshAutoLogin()
	{
		$loginKey = sha1($this->_model->generateSalt(20));
		$this->_model->updateAutoLogin($this->user_id, $loginKey, RPG_NOW);
		
		// set httponly cookie for 30 days
		$this->_input->setCookie('autologin', sha1($loginKey . RPG::config('cookieSalt')),
			86400 * 30, true);
		$this->_input->setCookie('userid', $this->user_id, 86400 * 30, true);
	}
	
	/**
	 * Clears the user's autologin information.
	 */
	public function clearAutoLogin()
	{
		// no params clears the autologin
		$this->_model->updateAutoLogin($this->user_id);
		
		$this->_input->setCookie('autologin', null);
		$this->_input->setCookie('userid', null);
	}
	
	/**
	 * Returns whether or not the user is logged in.
	 *
	 * @return bool
	 */
	public function isLoggedIn()
	{
		return $this->_session->isLoggedIn();
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
		//return isset($this->data["user_$key"]) ? $this->data["user_$key"] : null;
		return isset($this->_model->$key) ? $this->_model->$key : null;
	}
}
