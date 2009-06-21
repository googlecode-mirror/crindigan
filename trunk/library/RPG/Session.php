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
 * Custom database handler for PHP sessions, along with other shortcuts.
 * 
 * @package AnfinitiRPG
 */
class RPG_Session
{
	/**
	 * Initializes the session instance. Sets up the save handler, proper
	 * cookie params, and starts the session.
	 */
	public function __construct()
	{
		// use sha1 hashing, 5 bits per character (160/5 = 32 bytes)
		ini_set('session.hash_function', '1');
		ini_set('session.hash_bits_per_character', '5');
		
		session_name('rpgsess');
		
		// open, close, read, write, destroy, gc
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);
		
		$params = session_get_cookie_params();
		
		// lifetime, path, domain, secure, httponly
		session_set_cookie_params(
			RPG::config('sessionLifetime'),
			RPG::config('baseUrl') . '/',
			$params['domain'],
			$params['secure'],
			true
		);
		
		session_start();
	}
	
	/**
	 * Destructor. Calls session_write_close explicitly to work around the
	 * fact that objects are destroyed before sessions are written.
	 */
	public function __destruct()
	{
		session_write_close();
	}
	
	/**
	 * Opens the session.
	 *
	 * @param  string $savePath unused
	 * @param  string $sessionName unused
	 * @return bool
	 */
	public function open($savePath, $sessionName)
	{
		return true;
	}
	
	/**
	 * Closes the session.
	 *
	 * @return bool
	 */
	public function close()
	{
		return true;
	}
	
	/**
	 * Reads and returns the data stored with the given $sessionId.
	 *
	 * @param  string $sessionId
	 * @return string The session data
	 */
	public function read($sessionId)
	{
		$sessionData = RPG::database()->queryOne(
			"SELECT session_data FROM {session} WHERE session_id = :0", $sessionId);
		
		return $sessionData;
	}
	
	/**
	 * Writes the given $sessionData to the record specified by $sessionId.
	 *
	 * @param  string $sessionId
	 * @param  string $sessionData
	 * @return bool
	 */
	public function write($sessionId, $sessionData)
	{
		RPG::database()->query(
			'INSERT INTO {session} (
			     session_id, session_data, session_time
			 )
			 VALUES (
			     :session_id, :session_data, :session_time
			 )
			 ON DUPLICATE KEY UPDATE
			     session_data = :session_data,
			     session_time = :session_time',
			 array(
			 	'session_id'   => $sessionId,
			 	'session_data' => $sessionData,
			 	'session_time' => RPG_NOW,
			 )
		);
		
		return true;
	}
	
	/**
	 * Destroys a single session given the session ID.
	 *
	 * @param  string $sessionId
	 * @return bool
	 */
	public function destroy($sessionId)
	{
		RPG::database()->delete('session', array('session_id = :0', $sessionId));
		return true;
	}
	
	/**
	 * Cleans up sessions older than $maxLifetime seconds.
	 *
	 * @param  int $maxLifetime
	 * @return bool
	 */
	public function gc($maxLifetime)
	{
		RPG::database()->delete('session', array('session_time < :0', RPG_NOW - $maxLifetime));
		return true;
	}
	
	/**
	 * Sets a one-time read value to the given key.
	 *
	 * @param  string $key
	 * @param  mixed $value
	 */
	public function setFlash($key, $value)
	{
		$_SESSION['_flash'][$key] = $value;
	}
	
	/**
	 * Retrieves the flash data for a given key, and removes it.
	 *
	 * @param  string $key
	 * @return mixed  The flash data, or null if nonexistant
	 */
	public function getFlash($key)
	{
		if (!$this->hasFlash($key))
		{
			return null;
		}
		
		$val = $_SESSION['_flash'][$key];
		unset($_SESSION['_flash'][$key]);
		return $val;
	}
	
	/**
	 * Checks if there is current flash data for the given key.
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function hasFlash($key)
	{
		return isset($_SESSION['_flash'][$key]);
	}
	
	/**
	 * Verifies that the current session is that of a logged in user.
	 *
	 * @return bool
	 */
	public function isLoggedIn()
	{
		if (isset($this->loggedIn) AND $this->loggedIn === true
			AND isset($this->userId) AND $this->userId > 0)
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Allows access to $_SESSION variables through object syntax.
	 * Example: $foo = RPG::session()->something;
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $_SESSION['_user'][$key];
	}
	
	/**
	 * Allows access to $_SESSION variables through object syntax.
	 * Example: RPG::session()->something = 'foobar';
	 *
	 * @param  string $key
	 * @param  mixed $value
	 */
	public function __set($key, $value)
	{
		$_SESSION['_user'][$key] = $value;
	}
	
	/**
	 * Checks if session variable is set.
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function __isset($key)
	{
		return isset($_SESSION['_user'][$key]);
	}
}
