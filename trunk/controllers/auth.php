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
 * Handles user authentication. Handles login and logout, and if configured
 * to use the internal member system, registration.
 * 
 * @package Crindigan
 */
class AuthController extends RPG_Controller
{
	/**
	 * Handles the login procedure.
	 * 
	 * If the user is authenticated, it will log the user in, set up the
	 * user instance to handle a registered user. If "remember" is given,
	 * also refresh the user's auto login key.
	 * 
	 * POST Parameters
	 * - username: string
	 * - password: string
	 * - remember: uint
	 * - returnto: string
	 */
	public function doLogin()
	{
		if (!RPG::input()->isPost())
		{
			RPG::view()->redirect('home');
		}
		
		$post = RPG::input()->post(array('username' => 'string',
		                                 'password' => 'string',
		                                 'remember' => 'uint',
		                                 'returnto' => 'string'));
		// initialize auth class
		$auth = RPG_Auth::factory($post['username'], $post['password']);
		
		if ($auth->authenticate())
		{
			RPG::session()->loggedIn = true;
			RPG::session()->userId   = $auth->getUserId();
			RPG::user()->setupMember();
			
			if ($post['remember'] === 1)
			{
				RPG::user()->refreshAutoLogin();
			}
			
			// redirect to previous page user visited
			$returnTo = $post['returnto'];
			$query = array();
			if (strpos($returnTo, '?') !== false)
			{
				list($path, $queryString) = explode('?', $returnTo);
				parse_str($queryString, $query);
			}
			else
			{
				$path = $returnTo;
			}
			
			RPG::view()->redirect($path, $query);
		}
		else
		{
			// need a nice error system
			RPG::view()->redirect('home');
		}
	}
	
	/**
	 * Logs the user out of the system.
	 * 
	 * GET Parameters
	 * - hash: string
	 * - returnto: string
	 */
	public function doLogout()
	{
		$user = RPG::user();
		$hash = RPG::input()->get('hash', 'string');
		
		if ($hash === sha1($user->id . sha1($user->salt) . sha1($user->name)
		                   . sha1(RPG::config('cookieSalt'))))
		{
			$user->clearAutoLogin();
			RPG::session()->loggedIn = false;
			RPG::session()->userId   = 0;
			$user->setupGuest();
		}
		
		$returnTo = urldecode(RPG::input()->get('returnto', 'string'));
		$query = array();
		if (strpos($returnTo, '?') !== false)
		{
			list($path, $queryString) = explode('?', $returnTo);
			parse_str($queryString, $query);
		}
		else
		{
			$path = $returnTo;
		}
		
		RPG::view()->redirect($path, $query);
	}
}
