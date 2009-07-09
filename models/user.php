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
 * User model. Wraps user-related database operations.
 *
 * @package Crindigan
 */
class UserModel extends RPG_Model
{
	/**
	 * Name of the table this model deals with.
	 *
	 * @var string
	 */
	protected $_table = 'user';
	
	/**
	 * Columns of the table.
	 *
	 * @var array
	 */
	protected $_columns = array(
		'user_id'       => 0,
		'user_name'     => '',
		'user_password' => '',
		'user_salt'     => '',
		'user_email'    => '',
		'user_autologin' => '',
		'user_autologin_time' => 0,
		'user_money'       => 0,
		'user_external_id' => 0,
		'user_joindate'    => 0,
	);
	
	/**
	 * Name of the primary key.
	 *
	 * @var string
	 */
	protected $_primary = 'user_id';
	
	public function getUserInfo($userId)
	{
		return RPG::database()->queryFirst(
			'SELECT * FROM {user} WHERE user_id = :0', $userId);
	}
	
	public function getAutoLoginInfo($userId)
	{
		return RPG::database()->queryFirst(
			'SELECT user_autologin, user_autologin_time
			 FROM {user}
			 WHERE user_id = :0',
			$userId
		);
	}
	
	public function updateAutoLogin($userId, $key = '', $time = 0)
	{
		$affected = RPG::database()->update('user', array(
			'user_autologin'      => $key,
			'user_autologin_time' => $time,
		), array('user_id = :0', $userId));
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
}
