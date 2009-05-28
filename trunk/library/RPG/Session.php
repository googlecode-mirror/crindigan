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
 *
 */
class RPG_Session
{
	/**
	 * Initializes the session instance. Sets up the save handler, proper
	 * cookie params, and starts the session.
	 */
	public function __construct()
	{
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
	
	public function __destruct()
	{
		session_write_close();
	}
	
	public function open($savePath, $sessionName)
	{
		return true;
	}
	
	public function close()
	{
		return true;
	}
	
	public function read($sessionId)
	{
		RPG::database()->queryFirst("SELECT * FROM ");
	}
	
	public function write($sessionId, $sessionData)
	{
		
	}
	
	public function destroy($sessionId)
	{
		
	}
	
	public function gc($maxLifetime)
	{
		
	}
}
