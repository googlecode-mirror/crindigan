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
 * Authentication adapter for vBulletin.
 *
 * @package AnfinitiRPG
 */
class RPG_Auth_Vbulletin extends RPG_Auth
{
	/**
	 * Attempts to find a user record from the vBulletin database, using
	 * the set username and password properties. Sets _userId property
	 * to the ID of the local user record.
	 *
	 * @return bool True if a user record is found, false if not.
	 */
	public function authenticate()
	{
		// connect to vBulletin's database
		$vbDatabase = RPG::database('extDatabase');
		
		$username = htmlspecialchars($this->_username, ENT_COMPAT, 'UTF-8', false);
		$result   = $vbDatabase->query('SELECT userid, username, password, salt, email
										FROM {user}
										WHERE username = :0', $username);
		if ($result->getNumRows() !== 1)
		{
			return false;
		}
		
		$user = $result->fetch();
		
		// check the password
		if ($user['password'] !== md5(md5($this->_password) . $user['salt']))
		{
			return false;
		}
		
		// create a new user record locally, if one doesn't exist
		$this->_userId = $this->_createLocalRecord($user);
		
		return true;
	}
	
	/**
	 * Creates a new user record on the local database, if it doesn't exist.
	 *
	 * @param  array $user
	 */
	protected function _createLocalRecord(array $user)
	{
		$db = RPG::database();
		
		$existing = $db->query('SELECT user_id FROM {user}
								WHERE user_external_id = :0', $user['userid']);
		
		if ($existing->getNumRows() > 0)
		{
			$userId = $existing->fetchOne();
		}
		else
		{
			$userId = $db->insert('user', array(
				'user_name'     => htmlspecialchars_decode($user['username'], ENT_COMPAT),
				'user_password' => '',
				'user_salt'     => RPG::user()->generateSalt(5),
				'user_email'    => $user['email'],
				// autologin will be handled in auth controller
				'user_autologin'      => '',
				'user_autologin_time' => 0,
				'user_money'          => 0,
				'user_external_id'    => $user['userid'],
				'user_joindate'       => RPG_NOW,
			));
		}
		
		return $userId;
	}
}
