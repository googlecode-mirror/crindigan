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
 * Class to assist in constructing SELECT queries. Should not be used
 * for all select queries or anything, just where it will make code
 * more readable when they need to be dynamically altered.
 *
 * @package Crindigan
 */
class RPG_Database_Select
{
	/**
	 * Array of column names to fetch.
	 *
	 * @var array
	 */
	protected $_columns = array();
	
	/**
	 * Name of the main table to fetch from.
	 * 
	 * @var string
	 */
	protected $_from = '';
	
	/**
	 * Array of joins. Contains individual arrays with keys "type",
	 * "table", and "on".
	 *
	 * @var array
	 */
	protected $_joins = array();
	
	/**
	 * The where clause. Each entry is wrapped in parenthesis and joined
	 * together with "AND" when constructing the query.
	 * 
	 * @var array
	 */
	protected $_where = array();
	
	/**
	 * The GROUP BY portion.
	 *
	 * @var string
	 */
	protected $_groupBy = '';
	
	/**
	 * The HAVING portion.
	 *
	 * @var string
	 */
	protected $_having = '';
	
	/**
	 * The ORDER BY portion, in the form of "col_name" => "ASC"|"DESC".
	 *
	 * @var array
	 */
	protected $_orderBy = array();
	
	/**
	 * Max number of results to return.
	 *
	 * @var int
	 */
	protected $_limit = 0;
	
	/**
	 * The offset to start retrieving results.
	 *
	 * @var int
	 */
	protected $_offset = 0;
	
	/**
	 * Array of parameters to bind.
	 *
	 * @var array
	 */
	protected $_bind = array();
	
	/**
	 * Creates a new instance, optionally setting the FROM portion.
	 *
	 * @param  string $from
	 */
	public function __construct($from = '')
	{
		if ($from !== '')
		{
			$this->setFrom($from);
		}
	}
	
	/**
	 * Sets the main table we are selecting from.
	 *
	 * @param  string $from
	 * @return RPG_Database_Select
	 */
	public function setFrom($from)
	{
		$this->_from = $from;
		return $this;
	}
	
	/**
	 * Sets the list of columns to retrieve.
	 *
	 * @param  array $cols
	 * @return RPG_Database_Select
	 */
	public function setColumns(array $cols)
	{
		$this->_columns = $cols;
		return $this;
	}
	
	/**
	 * Returns the list of columns.
	 *
	 * @return array
	 */
	public function getColumns()
	{
		return $this->_columns;
	}
	
	/**
	 * Adds one or more columns to the column list. You may pass multiple
	 * parameters to this function for more column names.
	 *
	 * @param  string|array $col ...
	 * @return RPG_Database_Select
	 */
	public function addColumns($col)
	{
		foreach (func_get_args() AS $c)
		{
			$this->_columns[] = $c;
		}
		
		$this->_columns = array_unique($this->_columns);
		return $this;
	}
	
	/**
	 * Removes a column from the column list.
	 *
	 * @param  string $col
	 * @return RPG_Database_Select
	 */
	public function removeColumn($col)
	{
		$index = array_search($col, $this->_columns);
		if ($index !== false)
		{
			unset($this->_columns[$index]);
		}
		return $this;
	}
	
	/**
	 * Adds a JOIN to the query, given the type, table, and ON clause.
	 *
	 * @param  string $type One of LEFT, RIGHT, or INNER.
	 * @param  string $table The table to join with.
	 * @param  string $on The ON clause.
	 * @return RPG_Database_Select
	 */
	protected function _addJoin($type, $table, $on)
	{
		$this->_joins[] = array(
			'type'  => $type,
			'table' => $table,
			'on'    => $on,
		);
		return $this;
	}
	
	/**
	 * Adds a left join to the query.
	 *
	 * @param  string $table The table to join with.
	 * @param  string $on The ON clause.
	 * @return RPG_Database_Select
	 */
	public function addLeftJoin($table, $on)
	{
		return $this->_addJoin('LEFT', $table, $on);
	}
	
	/**
	 * Adds a right join to the query.
	 *
	 * @param  string $table The table to join with.
	 * @param  string $on The ON clause.
	 * @return RPG_Database_Select
	 */
	public function addRightJoin($table, $on)
	{
		return $this->_addJoin('RIGHT', $table, $on);
	}
	
	/**
	 * Adds an inner join to the query.
	 *
	 * @param  string $table The table to join with.
	 * @param  string $on The ON clause.
	 * @return RPG_Database_Select
	 */
	public function addInnerJoin($table, $on)
	{
		return $this->_addJoin('INNER', $table, $on);
	}
	
	/**
	 * Adds a where clause to the query. Each where clause is surrounded
	 * by parenthesis, and joined with "AND" when the query is constructed.
	 *
	 * @param  string $where
	 * @return RPG_Database_Select
	 */
	public function addWhere($where)
	{
		$this->_where[] = "($where)";
		return $this;
	}
	
	/**
	 * Sets the GROUP BY portion of the query.
	 * 
	 * @param  string $groupBy
	 * @return RPG_Database_Select
	 */
	public function setGroupBy($groupBy)
	{
		$this->_groupBy = $groupBy;
		return $this;
	}
	
	/**
	 * Sets the HAVING portion of the query.
	 * 
	 * @param  string $having
	 * @return RPG_Database_Select
	 */
	public function setHaving($having)
	{
		$this->_having = $having;
		return $this;
	}
	
	/**
	 * Sets the ORDER BY portion of the query.
	 * 
	 * @param  array $orderBy In the form of array("col_name" => "ASC"|"DESC")
	 * @return RPG_Database_Select
	 */
	public function setOrderBy(array $orderBy)
	{
		$this->_orderBy = $orderBy;
		return $this;
	}
	
	/**
	 * Sets the limit and offset portions of the query.
	 *
	 * @param  int $limit Max number of results to fetch.
	 * @param  int $offset Where to start fetching the results.
	 * @return RPG_Database_Select
	 */
	public function setLimit($limit, $offset = 0)
	{
		$this->_limit  = $limit;
		$this->_offset = $offset;
		
		return $this;
	}
	
	/**
	 * Sets a key/value pair to bind with the query.
	 * 
	 * @param  string $key
	 * @param  mixed $value
	 * @return RPG_Database_Select
	 */
	public function setBind($key, $value = null)
	{
		if (is_array($key) AND $value === null)
		{
			$this->_bind = array_merge($this->_bind, $key);
		}
		else
		{
			$this->_bind[$key] = $value;
		}
		
		return $this;
	}
	
	/**
	 * Constructs and returns the SQL for the built query.
	 *
	 * @return string
	 */
	public function getSql() {
		// select, from
		if ( count($this->_columns) == 0 ) {
			$this->_columns[] = '*';
		}
		$sql = 'SELECT ' . implode(', ', $this->_columns) . "\n"
		     . 'FROM {' . $this->_from . "}\n";
		
		// joins
		foreach ($this->_joins AS $join)
		{
			$sql .= $join['type'] . ' JOIN {' . $join['table'] . '} ON (' . $join['on'] . ")\n";
		}
		
		// where
		if (!empty($this->_where))
		{
			$sql .= 'WHERE ' . implode(' AND ', $this->_where) . "\n";
		}
		
		// group by
		if ($this->_groupBy !== '')
		{
			$sql .= 'GROUP BY ' . $this->_groupBy . "\n";
		}
		
		// having
		if ($this->_having !== '')
		{
			$sql .= 'HAVING ' . $this->_having . "\n";
		}
		
		// order by
		if (!empty($this->_orderBy))
		{
			foreach ($this->_orderBy AS $col => $dir)
			{
				$order[] = " $col $dir";
			}
			$sql .= 'ORDER BY' . implode(',', $order) . "\n";
		}
		
		// limit
		if ($this->_limit !== 0)
		{
			$sql .= 'LIMIT ';
			if ($this->_offset !== 0)
			{
				$sql .= $this->_offset . ', ' . $this->_limit;
			}
			else
			{
				$sql .= $this->_limit;	
			}
		}
		
		return $sql;
	}
	
	/**
	 * Returns the SQL for the built query.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getSql();
	}
	
	/**
	 * Creates the SQL and executes the query.
	 *
	 * @return RPG_Database_Result
	 */
	public function execute()
	{
		return RPG::database()->query($this->getSql(), $this->_bind);
	}
}
