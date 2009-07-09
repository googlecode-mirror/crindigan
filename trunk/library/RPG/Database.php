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
 * Database interaction class.
 *
 * @package Crindigan
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
	 * Temporary bind data when processing bound parameters.
	 *
	 * @var array
	 */
	protected $_tempBind = array();
	
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
		
		if ($params['prefix'])
		{
			$this->setTablePrefix($params['prefix']);
		}
		
		if (mysqli_connect_errno())
		{
			throw new RPG_Exception('MySQLi connection error: ' . mysqli_connect_error());
		}
	}
	
	/**
	 * Executes the given SQL query on the database. If a SELECT, SHOW,
	 * DESCRIBE, or EXPLAIN query is run, this function will return an
	 * RPG_Database_Result instance. Otherwise, it will return a boolean
	 * value, signifying whether the query succeeded or not. To apply the
	 * current table prefix to a table, surround it with braces:
	 *
	 * {table_name} => `prefix_table_name`
	 *
	 * @param  string $sql
	 * @param  mixed $bind Array of param_name => value. :param_name in the SQL
	 *                     will be replaced by value. ?param_name in the SQL
	 *                     will be replaced by the raw uncleansed value. For
	 *                     simple queries, you can also skip the param_name
	 *                     and mark your placeholders as :0, :1, etc. You can
	 *                     also put a static value, which will be converted
	 *                     into an array with one element.
	 * @return boolean|RPG_Database_Result
	 */
	public function query($sql, $bind = array())
	{
		if (is_string($bind) OR is_numeric($bind))
		{
			$bind = array($bind);
		}
		
		// only run through the replacements if things can be done
		if (!empty($bind) OR strpos($sql, '{') !== false)
		{
			$sql = $this->_processReplacements($sql, $bind);
		}
		
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
	 * @param  mixed $bind
	 * @param  integer $mode
	 * @return array
	 */
	public function queryFirst($sql, $bind = array(), $mode = RPG_Database_Result::ASSOC)
	{
		return $this->query($sql, $bind)->fetch($mode);
	}
	
	/**
	 * Executes the given SQL and calls the fetchOne() method on the result.
	 *
	 * @param  string $sql
	 * @param  mixed $bind
	 * @return mixed
	 */
	public function queryOne($sql, $bind = array())
	{
		return $this->query($sql, $bind)->fetchOne();
	}
	
	/**
	 * Executes the given SQL and calls the fetchAll() method on the result.
	 *
	 * @param  string $sql
	 * @param  mixed $bind
	 * @param  integer $mode
	 * @return array
	 */
	public function queryAll($sql, $bind = array(), $mode = RPG_Database_Result::ASSOC)
	{
		return $this->query($sql, $bind)->fetchAll($mode);
	}
	
	/**
	 * Executes the given SQL and calls the fetchMapped() method on the result.
	 *
	 * @param  string $sql
	 * @param  mixed $bind
	 * @param  string $keyColumn
	 * @return array
	 */
	public function queryMapped($sql, $bind = array(), $keyColumn)
	{
		return $this->query($sql, $bind)->fetchMapped($keyColumn);
	}
	
	/**
	 * Executes the given SQL and calls the fetchPair() method on the result.
	 *
	 * @param  string $sql
	 * @param  mixed $bind
	 * @param  string $keyColumn
	 * @param  string $valueColumn
	 * @return array
	 */
	public function queryPair($sql, $bind = array(), $keyColumn = null, $valueColumn = null)
	{
		return $this->query($sql, $bind)->fetchPair($keyColumn, $valueColumn);
	}
	
	/**
	 * Returns an RPG_Database_Select instance with the "from" parameter
	 * optionally set.
	 *
	 * @param  string $from Main table to select from.
	 * @return RPG_Database_Select
	 */
	public function select($from = '')
	{
		return new RPG_Database_Select($from);
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
		$bindValues  = array();
		foreach ($fields AS $col => $value)
		{
			$columnNames[] = $this->prepareIdentifier($col);
			$fieldValues[] = ':' . $col;
			$bindValues[$col] = $value;
		}
		
		$table = '{' . $table . '}';
		$query = "INSERT INTO {$table} (\n"
		       . "\t" . implode(', ', $columnNames) . "\n"
		       . ") VALUES (\n"
		       . "\t" . implode(', ', $fieldValues) . "\n"
		       . ")";
		$this->query($query, $bindValues);
		
		return $this->_mysqli->insert_id;
	}
	
	/**
	 * Updates data in the given table.
	 *
	 * @param  string $table
	 * @param  array $fields Array of column_name => value. If value is an
	 *                       expression, like "column_name + 5", prepend the
	 *                       key with "expr:" like "expr:col" => "col + 5"
	 * @param  array $condition The WHERE clause for the update. The first
	 *                          element is the SQL, and the following are
	 *                          key/value pairs for parameter binding.
	 * @return integer The number of affected rows
	 */
	public function update($table, array $fields, array $condition = array())
	{
		if (empty($fields))
		{
			return;
		}
		
		$fieldList  = '';
		$isExpr     = false;
		$bindValues = array();
		foreach ($fields AS $col => $value)
		{
			if (strpos($col, 'expr:') === 0)
			{
				$isExpr = true;
				$col = substr($col, 5);
			}
			
			// make the replacements :__col_name to avoid interfering with
			// the where condition
			$fieldList .= "\n\t" . $this->prepareIdentifier($col) . ' = ' . 
				($isExpr ? '?' : ':') . '__' . $col . ',';
			$bindValues['__' . $col] = $value;
			$isExpr = false;
		}
		
		if (!empty($condition))
		{
			$whereClause = array_shift($condition);
			$bindValues  = array_merge($bindValues, $condition);
		}
		
		$table = '{' . $table . '}';
		$query = "UPDATE {$table}\n"
		       . "SET"
		       . substr($fieldList, 0, -1) . "\n"
		       . (empty($condition) ? '' : "WHERE $whereClause");
		$this->query($query, $bindValues);
		
		return $this->_mysqli->affected_rows;
	}
	
	/**
	 * Deletes data from the given table.
	 *
	 * @param  string $table
	 * @param  array $condition The WHERE clause of the delete
	 * @return integer The number of affected rows
	 */
	public function delete($table, array $condition = array())
	{
		if (!empty($condition))
		{
			$whereClause = array_shift($condition);
		}
		
		$table = '{' . $table . '}';
		$query = "DELETE FROM {$table}\n"
		       . (empty($whereClause) ? '' : "WHERE $whereClause");
		$this->query($query, $condition);
		
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
	 * Process replacements, including table prefixes and bound parameters.
	 *
	 * @param  string $sql
	 * @param  array $bind
	 * @return string Processed SQL.
	 */
	protected function _processReplacements($sql, array $bind)
	{
		// replace {table_name} with `prefix_table_name`
		$sql = preg_replace_callback('#\{(\w+)\}#', array($this, '_processTableName'), $sql);
		
		// replace :param_name with prepareValue($bind['param_name'])
		// replace ?param_name with raw $bind['param_name']
		$this->_tempBind = $bind;
		$sql = preg_replace_callback('#(:|\?)(\w+)#', array($this, '_processBind'), $sql);
		$this->_tempBind = array();
		
		return $sql;
	}
	
	/**
	 * Applies the table prefix to the match and quotes it with backticks.
	 *
	 * @param  array $matches
	 * @return string
	 */
	protected function _processTableName($matches)
	{
		return $this->prepareIdentifier($this->_tablePrefix . $matches[1]);
	}
	
	/**
	 * Replaces the parameter placeholders with their actual value.
	 *
	 * @param  array $matches
	 * @return string
	 * @throws RPG_Exception if a placeholder has no replacement
	 */
	protected function _processBind($matches)
	{
		if (is_numeric($matches[2]))
		{
			$matches[2] = (int) $matches[2];
		}
		
		if (!isset($this->_tempBind[$matches[2]]))
		{
			throw new RPG_Exception('No bind replacement for ' . $matches[0] . ' in query.');
		}
		
		$value = $this->_tempBind[$matches[2]];
		if ($matches[1] === ':')
		{
			$value = $this->prepareValue($value);
		}
		
		return $value;
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
