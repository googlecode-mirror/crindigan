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
 * Represents a MySQLi statement. May never be used, as I'm not a big fan
 * of how prepared statements look in MySQLi.
 *
 * @package Crindigan
 */
class RPG_Database_Statement
{
	/**
	 * MySQLi statement object.
	 *
	 * @var MySQLi_Stmt
	 */
	protected $_stmt = null;
	
	/**
	 * Creates a new statement instance, given a MySQLi statement.
	 *
	 * @param  MySQLi_Stmt
	 */
	public function __construct(MySQLi_Stmt $stmt)
	{
		$this->_stmt = $stmt;
	}
	
	/**
	 * Returns the raw MySQLi statement.
	 *
	 * @return MySQLi_Stmt
	 */
	public function getStatement()
	{
		return $this->_stmt;
	}
	
	/**
	 * Binds a value to the statement with the specified type.
	 *
	 * @param  mixed &$value
	 * @param  string $type (i)nt, (d)ouble, (s)tring, (b)lob
	 */
	public function bind(&$value, $type)
	{
		$t = strtolower($type[0]);
		if ($t === 'f')
		{
			$t = 'd';
		}
		
		if (!in_array($t, array('i', 'd', 's', 'b')))
		{
			throw new RPG_Exception('Invalid bind type given: ' . $t);
		}
		
		$this->_stmt->bind_param($t, $value);
	}
}
