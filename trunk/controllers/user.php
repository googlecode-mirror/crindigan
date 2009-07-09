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
 * Controller for user profile viewing and editing.
 *
 * @package Crindigan
 */
class UserController extends RPG_Controller
{
	protected $_layout = 'layouts/frontend.php';
	
	/**
	 * View the profile of a user. If provided no parameters, and the
	 * user is logged in, it will show the profile of the current user.
	 *
	 * @param  int $userId
	 */
	public function doIndex($userId = 0)
	{
		RPG::view()->setNavCurrent('user', ($userId === 0) ? 'user' : '');
		
		if ($userId === 0)
		{
			RPG::view()->setTitle('My Profile');
		}
		else
		{
			$name = 'Crimson'; // todo
			RPG::view()->setTitle("$name's Profile");
		}
	}
	
	/**
	 * Alias for doIndex.
	 *
	 * @param  int $userId
	 */
	public function doView($userId = 0)
	{
		$this->doIndex($userId);
	}
	
	/**
	 * Edit your profile information.
	 */
	public function doEdit()
	{
		RPG::view()->setNavCurrent('user', 'user/edit')
		           ->setTitle('Edit Profile');
	}
	
	/**
	 * Exchange money with an external system.
	 */
	public function doMoney()
	{
		RPG::view()->setNavCurrent('user', 'user/money')
		           ->setTitle('Exchange Money');
	}
}
