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
 * Base model class.
 *
 * @package Crindigan
 */
class RPG_Model
{
	/**
	 * Path to the models directory.
	 *
	 * @var string
	 */
	protected static $_path = '';
	
	/**
	 * The table associated with this model.
	 *
	 * @var string
	 */
	protected $_table = '';
	
	/**
	 * Column definition for the table, in the form of column_name => data_type
	 *
	 * @var array
	 */
	protected $_columns = array();
	
	/**
	 * Name of the primary key column. Multiple primary keys are unsupported.
	 *
	 * @var string
	 */
	protected $_primary = '';
	
	/**
	 * Actual data mapped to a table row.
	 *
	 * @var array
	 */
	protected $_data = array();
	
	/**
	 * Array of column names that were changed.
	 *
	 * @var array
	 */
	protected $_changed = array();
	
	/**
	 * Constructor.
	 */
	public function __construct($id = null) {
		if ( !is_null($id) ) {
			$this->load($id);
		}
	}
	
	/**
	 * Attempts to load data from the model's related table, and the given ID.
	 *
	 * @param  int $id
	 */
	public function load($id) {
		$r = RPG::database()->select($this->_table)
		                    ->addWhere($this->_primary . ' = ' . intval($id))
		                    ->execute();
		if ( $r->getNumRows() > 0 ) {
			$row = $r->fetch();
			$this->buildFromRow($row);
		} else {
			throw new RPG_Exception_DB("Row ID $id does not exist in {$this->_table}");
		}
	}
	
	/**
	 * Builds the model object from a row returned from the database.
	 *
	 * @param  array $row
	 */
	protected function buildFromRow(array $row) {
		foreach ( $this->_columns as $name => $type ) {
			$this->_data[$name] = RPG::input()->filter($row[$name], $type);
		}
	}
	
	/**
	 * Retrieves the value of a column.
	 *
	 * @param  string $name
	 * @return mixed
	 */
	public function __get($name) {
		if ( !isset($this->_columns[$name]) ) {
			throw new RPG_Exception_DB('Column "' . $name '" does not exist on "' . $this->_table . '"');
		}
		return $this->_data[$name];
	}
	
	/**
	 * Sets the value of a column.
	 *
	 * @param  string $name
	 * @param  mixed  $value
	 */
	public function __set($name, $value) {
		if ( !isset($this->_columns[$name]) ) {
			throw new RPG_Exception_DB('Column "' . $name '" does not exist on "' . $this->_table . '"');
		}
		$this->_data[$name]    = $value;
		$this->_changed[$name] = true;
	}
	
	public function __isset($name) {
		return isset($this->_data[$name]);
	}
	
	/**
	 * Clears all data and changes.
	 */
	public function clear() {
		$this->_data    = array();
		$this->_changed = array();
	}
	
	/**
	 * Inserts or updates the record in the database.
	 */
	public function save() {
		if ( !$this->_data[$this->_primary] ) {
			$insert = array();
			foreach ( $this->_columns as $col_name => $type ) {
				if ( $col_name === $this->_primary ) {
					continue;
				}
				$insert[$col_name] = $this->_beforeColumnSave($col_name);
			}
			$this->_data[$this->_primary] = RPG::database()->insert($this->_table, $insert);
		} else {
			$update    = array();
			$condition = null;
			foreach ( $this->_columns as $col_name => $type ) {
				if ( !isset($this->_changed[$col_name]) || !$this->_changed[$col_name] ) {
					continue;
				}
				
				$value = $this->_beforeColumnSave($col_name);
				if ( $col_name === $this->_primary ) {
					$condition = array("{$this->_primary} = :primary_value",
					                   'primary_value' => $value);
				} else {
					$update[$col_name] = $value;
				}
			}
			if ( !is_null($condition) ) {
				RPG::database()->update($this->_table, $update, $condition);
			}
		}
	}
	
	protected function _beforeColumnSave($col_name) {
		if ( isset($this->_data[$col_name]) ) {
			$value = $this->_data[$col_name];
		} else {
			$value = RPG::input()->filter(null, $this->_columns[$col_name]);
		}
		
		if ( $this->_columns[$col_name] === 'datetime' && $value instanceof DateTime ) {
			$value = $value->format('Y-m-d H:i:s');
		}
		
		return $value;
	}
	
	/**
	 * Sets the path to the models directory.
	 *
	 * @param  string $path
	 */
	static public function setPath($path)
	{
		if (!is_dir($path))
		{
			throw new RPG_Exception("Model path \"$path\" does not exist.");
		}
		
		self::$_path = $path;
	}
	
	/**
	 * Returns the path to the models directory.
	 *
	 * @return string
	 */
	static public function getPath()
	{
		return self::$_path;
	}
}
