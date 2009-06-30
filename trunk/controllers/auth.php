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
 * Handles user authentication. Handles login and logout, and if configured
 * to use the internal member system, registration.
 * 
 * @package AnfinitiRPG
 */
class AuthController extends RPG_Controller
{
	/**
	 * Handles the login procedure. Receives "username", "password", and
	 * "remember" via a POST request, and tries to authenticate the user.
	 * 
	 * If the user is authenticated, it will log the user in, set up the
	 * user instance to handle a registered user. If "remember" is given,
	 * also refresh the user's auto login key.
	 */
	public function doLogin()
	{
		if (!RPG::input()->isPost())
		{
			RPG::view()->redirect('home');
		}
		
		$post = RPG::input()->post(array('username' => 'string',
										 'password' => 'string',
										 'remember' => 'uint'));
		// initialize auth class
		$auth = RPG_Auth::factory($post['username'], $post['password']);
		
		if ($auth->authenticate())
		{
			RPG::session()->loggedIn = true;
			RPG::session()->userId   = $auth->getUserId();
			RPG::user()->setupMember();
			
			if (true or $post['remember'] === 1)
			{
				RPG::user()->refreshAutoLogin();
			}
			
			// TODO - redirect to previous page user visited
			RPG::view()->redirect('home');
		}
		else
		{
			// need a nice error system
			RPG::view()->redirect('home');
		}
	}
	
	/**
	 * Logs the user out of the system.
	 */
	public function doLogout()
	{
		// todo - have a logout hash
		
		RPG::user()->clearAutoLogin();
		RPG::session()->loggedIn = false;
		RPG::session()->userId   = 0;
		RPG::user()->setupGuest();
		
		RPG::view()->redirect('home');
	}
}
