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
 * Session class that splits data between a MySQL memory table and files.
 * The table stores a session ID, user ID, and last update time. This allows
 * us to retrieve an online list of users in the future without having to
 * seek through a directory. Other various metadata is stored within files,
 * by default within /tmp/sessions.
 *
 * @package Crindigan
 */
class RPG_Session_Hybrid extends RPG_Session
{
	/**
	 * Reads session data from the temporary session file.
	 *
	 * @param  string $sessionId
	 * @return string Encoded session data.
	 */
	public function read($sessionId)
	{
		//$userId = RPG::database()->queryOne("SELECT session_user_id FROM {session_memory}
		//									 WHERE session_id = :0", $sessionId);
		
		$file = $this->_getFile($sessionId);
		if (file_exists($file))
		{
			return file_get_contents($file);
		}
		
		return '';
	}
	
	/**
	 * Writes session data, splitting basic information into a MEMORY table,
	 * and other metadata into a temporary file.
	 *
	 * @param  string $sessionId
	 * @param  string $sessionData
	 * @return bool
	 */
	public function write($sessionId, $sessionData)
	{
		// the file portion
		file_put_contents($this->_getFile($sessionId), $sessionData);
		
		// database portion
		RPG::database()->query(
			'INSERT INTO {session_memory} (
			     session_id, session_user_id, session_time
			 )
			 VALUES (
			     :session_id, :session_user_id, :session_time
			 )
			 ON DUPLICATE KEY UPDATE
			     session_user_id = :session_user_id,
			     session_time    = :session_time',
			 array(
			 	'session_id'      => $sessionId,
			 	'session_user_id' => $this->userId,
			 	'session_time'    => RPG_NOW,
			 )
		);
		
		return true;
	}
	
	/**
	 * Deletes the temporary session file and the database record.
	 *
	 * @param  string $sessionId
	 */
	public function destroy($sessionId)
	{
		$file = $this->_getFile($sessionId);
		if (file_exists($file))
		{
			unlink($file);
		}
		
		RPG::database()->delete('session_memory', array('session_id = :0', $sessionId));
	}
	
	/**
	 * Removes all expired session records from the database along with their
	 * corresponding temporary files.
	 *
	 * @param  int $maxLifetime
	 * @return bool
	 */
	public function gc($maxLifetime)
	{
		$cut = RPG_NOW - $maxLifetime;
		
		$set = RPG::database()->queryAll('SELECT session_id FROM {session_memory}
										  WHERE session_time < :0', $cut);
		RPG::database()->delete('session_memory', array('session_time < :0', $cut));
		
		foreach ($set AS $row)
		{
			$file = $this->_getFile($row['session_id']);
			if (file_exists($file))
			{
				unlink($file);
			}
		}
		
		return true;
	}
	
	/**
	 * Returns the path to the temporary file for the given session ID, using
	 * the session path configured in the config file as a base.
	 *
	 * @param  string $sessionId
	 * @return string Path to temporary file: {$sessionPath}/sess_{$sessionId}
	 */
	protected function _getFile($sessionId)
	{
		return RPG::config('sessionPath') . '/sess_' . $sessionId;
	}
}
