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
 * Class to represent the result of a MySQLi select query.
 *
 * @package AnfinitiRPG
 */
class RPG_Database_Result
{
	/**#@+
	 * MySQLi fetch modes.
	 */
	const ASSOC  = MYSQLI_ASSOC;
	const NUM    = MYSQLI_NUM;
	const BOTH   = MYSQLI_BOTH;
	const OBJECT = 9999;
	/**#@-*/
	
	/**
	 * Array of fetch modes.
	 *
	 * @var array
	 */
	protected static $_fetchModes = array('ASSOC', 'NUM', 'BOTH', 'OBJECT');
	
	/**
	 * Base MySQLi_Result instance.
	 *
	 * @var MySQLi_Result
	 */
	protected $_result = null;
	
	/**
	 * Whether the result set is currently open.
	 *
	 * @var boolean
	 */
	protected $_open = true;
	
	/**
	 * Creates a new result instance, given a MySQLi result.
	 *
	 * @param  MySQLi_Result $result
	 */
	public function __construct(MySQLi_Result $result)
	{
		$this->_result = $result;
	}
	
	/**
	 * Fetches the next row from the result.
	 *
	 * @param  integer $mode One of RPG_Database_Result::(ASSOC|NUM|BOTH|OBJECT).
	 *                       Defaults to ASSOC.
	 * @return array|stdClass
	 */
	public function fetch($mode = self::ASSOC)
	{
		if (is_string($mode))
		{
			$mode = strtoupper($mode);
			if (in_array($mode, self::$_fetchModes))
			{
				$mode = constant('self::' . $mode);
			}
		}
		
		if ($mode === self::OBJECT)
		{
			return $this->_result->fetch_object();
		}
		return $this->_result->fetch_array($mode);
	}
	
	/**
	 * Returns the first column of the first row in the result.
	 *
	 * @return mixed
	 */
	public function fetchOne()
	{
		$row = $this->fetch(self::NUM);
		return $row[0];
	}
	
	/**
	 * Returns all rows of the result in a single array.
	 *
	 * @param  integer $mode See fetch()
	 * @return array
	 */
	public function fetchAll($mode = self::ASSOC)
	{
		$list = array();
		while ($row = $this->fetch($mode))
		{
			$list[] = $row;
		}
		return $list;
	}
	
	/**
	 * Returns all rows of the result in a single array, indexed by
	 * the column name given in $keyColumn.
	 *
	 * @param  string $keyColumn Name of the column from which to index the
	 *                           returned array.
	 * @return array
	 */
	public function fetchMapped($keyColumn)
	{
		$list = array();
		while ($row = $this->fetch(self::ASSOC))
		{
			$list[$row[$keyColumn]] = $row;
		}
		return $list;
	}
	
	/**
	 * Returns all rows of the result in a single array. Two columns are
	 * returned, placed in the key and value of each array element.
	 *
	 * @param  string $keyColumn Name of the column from which the keys of the
	 *                           array will be created. If not given, will use
	 *                           the first column in the result row.
	 * @param  string $valueColumn Name of the column from which the values of
	 *                           the array will be created. If not given, will
	 *                           use the second column in the result row.
	 * @return array
	 */
	public function fetchPair($keyColumn = null, $valueColumn = null)
	{
		$keyColumn   = ($keyColumn === null) ? 0 : $keyColumn;
		$valueColumn = ($valueColumn === null) ? 1 : $valueColumn;
		
		$list = array();
		while ($row = $this->fetch(self::BOTH))
		{	
			$list[$row[$keyColumn]] = $row[$valueColumn];
		}
		return $list;
	}
	
	/**
	 * Seeks to the given position in the result.
	 *
	 * @param  integer $index
	 * @return boolean
	 */
	public function dataSeek($index = 0)
	{
		return $this->_result->data_seek($index);
	}
	
	/**
	 * Frees memory associated with the result.
	 */
	public function free()
	{
		if ($this->_open === true)
		{
			$this->_result->free();
			$this->_open = false;
		}
	}
	
	/**
	 * Returns the number of rows in the result.
	 *
	 * @return integer
	 */
	public function getNumRows()
	{
		return $this->_result->num_rows;
	}
	
	/**
	 * Frees memory associated with the result on object destruction.
	 */
	public function __destruct()
	{
		$this->free();
	}
}
