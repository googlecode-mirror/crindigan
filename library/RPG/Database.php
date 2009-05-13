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
 * Database interaction class.
 *
 * @package AnfinitiRPG
 */
class RPG_Database
{
	/**
	 * MySQLi class instance.
	 *
	 * @var MySQLi
	 */
	protected $_mysqli = null;
	
	/**
	 * The number of queries executed.
	 *
	 * @var integer
	 */
	protected $_queryCount = 0;
	
	/**
	 * The current table prefix.
	 *
	 * @var string
	 */
	protected $_tablePrefix = '';
	
	/**
	 * Creates an internal instance of MySQLi and connects to the MySQL
	 * server, with the given parameters.
	 *
	 * @param  array $params Array containing "server", "username", "password",
	 *                       and "database" elements.
	 * @throws RPG_Exception If cannot connect to database.
	 */
	public function __construct(array $params)
	{
		$this->_mysqli = new MySQLi($params['server'], $params['username'],
			$params['password'], $params['database']);
		
		if (mysqli_connect_errno())
		{
			throw new RPG_Exception('MySQLi connection error: ' . mysqli_connect_error());
		}
	}
	
	/**
	 * Executes the given SQL query on the database. If a SELECT, SHOW,
	 * DESCRIBE, or EXPLAIN query is run, this function will return an
	 * RPG_Database_Result instance. Otherwise, it will return a boolean
	 * value, signifying whether the query succeeded or not.
	 *
	 * @param  string $sql
	 * @return boolean|RPG_Database_Result
	 */
	public function query($sql)
	{
		$result = $this->_mysqli->query($sql);
		
		if ($result === false)
		{
			throw new RPG_Exception('MySQLi query error: [' . $this->errno() . '] ' . $this->error() . "\n\nSQL: " . $sql);
		}
		
		$this->_queryCount++;
		
		// wrap MySQLi_Result with RPG_Database_Result
		if ($result instanceof MySQLi_Result)
		{
			$result = new RPG_Database_Result($result);
		}
		
		return $result;
	}
	
	/**
	 * Executes the given SQL and calls the fetch() method on the result.
	 *
	 * @param  string $sql
	 * @param  integer $mode
	 * @return array
	 */
	public function queryFirst($sql, $mode = self::ASSOC)
	{
		return $this->query($sql)->fetch($mode);
	}
	
	/**
	 * Executes the given SQL and calls the fetchOne() method on the result.
	 *
	 * @param  string $sql
	 * @return mixed
	 */
	public function queryOne($sql)
	{
		return $this->query($sql)->fetchOne();
	}
	
	/**
	 * Executes the given SQL and calls the fetchAll() method on the result.
	 *
	 * @param  string $sql
	 * @param  integer $mode
	 * @return array
	 */
	public function queryAll($sql, $mode = self::ASSOC)
	{
		return $this->query($sql)->fetchAll($mode);
	}
	
	/**
	 * Executes the given SQL and calls the fetchMapped() method on the result.
	 *
	 * @param  string $sql
	 * @param  string $keyColumn
	 * @return array
	 */
	public function queryMapped($sql, $keyColumn)
	{
		return $this->query($sql)->fetchMapped($keyColumn);
	}
	
	/**
	 * Executes the given SQL and calls the fetchPair() method on the result.
	 *
	 * @param  string $sql
	 * @param  string $keyColumn
	 * @param  string $valueColumn
	 * @return array
	 */
	public function queryPair($sql, $keyColumn = null, $valueColumn = null)
	{
		return $this->query($sql)->fetchPair($keyColumn, $valueColumn);
	}
	
	/**
	 * Inserts data into the given table.
	 *
	 * @param  string $table
	 * @param  array $fields Array of column_name => value
	 * @return integer Auto-generated insert id, if available
	 */
	public function insert($table, array $fields)
	{
		$columnNames = array();
		$fieldValues = array();
		foreach ($fields AS $col => $value)
		{
			$columnNames[] = $this->prepareIdentifier($col);
			$fieldValues[] = $this->prepareValue($value);
		}
		
		$table = $this->prepareIdentifier($this->_tablePrefix . $table);
		$query = "INSERT INTO {$table} (\n"
		       . "\t" . implode(', ', $columnNames) . "\n"
		       . ") VALUES (\n"
		       . "\t" . implode(', ', $fieldValues) . "\n"
		       . ")";
		$this->query($query);
		
		return $this->_mysqli->insert_id;
	}
	
	/**
	 * Updates data in the given table.
	 *
	 * @param  string $table
	 * @param  array $fields Array of column_name => value. If value is an
	 *                       expression, like "column_name + 5", prepend the
	 *                       key with "expr:" like "expr:col" => "col + 5"
	 * @param  string $condition The WHERE clause for the update
	 * @return integer The number of affected rows
	 */
	public function update($table, array $fields, $condition = '')
	{
		if (empty($fields))
		{
			return;
		}
		
		$fieldList = '';
		$isExpr    = false;
		foreach ($fields AS $col => $value)
		{
			if (strpos($col, 'expr:') === 0)
			{
				$isExpr = true;
				$col = substr($col, 5);
			}
			
			$fieldList .= "\t" . $this->prepareIdentifier($col) . ' = ' . 
				($isExpr ? $value : $this->prepareValue($value)) . "\n";
			$isExpr = false;
		}
		
		$table = $this->prepareIdentifier($this->_tablePrefix . $table);
		$query = "UPDATE {$table}\n"
		       . "SET\n"
		       . $fieldList
		       . (empty($condition) ? '' : "WHERE $condition");
		$this->query($query);
		
		return $this->_mysqli->affected_rows;
	}
	
	/**
	 * Deletes data from the given table.
	 *
	 * @param  string $table
	 * @param  string $condition The WHERE clause of the delete
	 * @return integer The number of affected rows
	 */
	public function delete($table, $condition = '')
	{
		$table = $this->prepareIdentifier($this->_tablePrefix . $table);
		$query = "DELETE FROM {$table}\n"
		       . (empty($condition) ? '' : "WHERE $condition");
		$this->query($query);
		
		return $this->_mysqli->affected_rows;
	}
	
	/**
	 * Cleanses MySQL identifiers and surrounds them with backticks.
	 *
	 * @param  string $ident
	 * @return string
	 */
	public function prepareIdentifier($ident)
	{
		$ident = '`' . preg_replace('#[^A-Za-z0-9_.]#', '', $ident) . '`';
		return $ident;
	}
	
	/**
	 * Cleanses a value. If numeric, simply returns the number. If a string,
	 * it escapes the text and surrounds it with single quotes.
	 *
	 * @param  mixed $value
	 * @return mixed
	 */
	public function prepareValue($value)
	{
		if (is_numeric($value))
		{
			return $value;
		}
		
		return "'" . $this->escape($value) . "'";
	}
	
	/**
	 * Escapes a string for use in an SQL query.
	 *
	 * @param  string $value
	 * @param  boolean $like If true, escapes % and _ for LIKE clauses
	 * @return string
	 */
	public function escape($value, $like = false)
	{
		if ($like)
		{
			$value = str_replace(array('%', '_'), array('\\%', '\\_'), $value);
		}
		
		return $this->_mysqli->real_escape_string($value);
	}
	
	/**
	 * Returns the current number of executed queries.
	 *
	 * @return integer
	 */
	public function getQueryCount()
	{
		return $this->_queryCount;
	}
	
	/**
	 * Sets the table prefix for use in insert() and update()
	 *
	 * @param  string $prefix
	 */
	public function setTablePrefix($prefix = '')
	{
		$this->_tablePrefix = $prefix;
	}
	
	/**
	 * Returns the currently set table prefix.
	 *
	 * @return string
	 */
	public function getTablePrefix()
	{
		return $this->_tablePrefix;
	}
	
	/**
	 * Returns the current MySQLi error string, if any.
	 *
	 * @return string
	 */
	public function error()
	{
		return $this->_mysqli->error;
	}
	
	/**
	 * Returns the current MySQLi error number, if any.
	 *
	 * @return integer
	 */
	public function errno()
	{
		return $this->_mysqli->errno;
	}
	
	/**
	 * Closes the database connection.
	 */
	public function close()
	{
		$this->_mysqli->close();
	}
}
