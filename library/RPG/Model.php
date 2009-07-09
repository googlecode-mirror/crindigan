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
	 * Column definition for the table, in the form of column_name => default
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
	 * Constructor.
	 */
	public function __construct()
	{
		
	}
	
	/**
	 * Inserts a new record based on the given object, and the subclass'
	 * table and column definitions.
	 *
	 * @param  stdClass $object
	 * @return int Insert ID
	 */
	public function insert($object)
	{
		$insert = array();
		
		foreach ($this->_columns AS $colName => $default)
		{
			if ($colName === $this->_primary)
			{
				continue;
			}
			
			$insert[$colName] = isset($object->$colName) ? $object->$colName : $default;
		}
		
		return RPG::database()->insert($this->_table, $insert);
	}
	
	/**
	 * Updates one or more existing records based on the given object, the
	 * subclass' table/column definitions, and an optional condition.
	 * If the object contains the primary key, it will override the condition
	 * and update the row based on the key.
	 * 
	 * @param  stdClass $object
	 * @param  array $condition
	 * @return int Number of affected rows.
	 */
	public function update($object, array $condition = array())
	{
		$update = array();
		
		foreach ($this->_columns AS $colName => $x)
		{
			if (isset($object->$colName))
			{
				if ($colName === $this->_primary)
				{
					$condition = array("{$this->_primary} = :0", $object->$colName);
				}
				else
				{
					$update[$colName] = $object->$colName;
				}
			}
			else if (isset($object->{'expr__' . $colName}))
			{
				$update["expr:$colName"] = $object->$colName;
			}
		}
		
		return RPG::database()->update($this->_table, $update, $condition);
	}
	
	/**
	 * Returns an stdClass to start building an object record.
	 *
	 * @return stdClass
	 */
	public function getObject()
	{
		return new stdClass();
	}
	
	/**
	 * Sets the path to the models directory.
	 *
	 * @param  string $path
	 */
	public static function setPath($path)
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
	public static function getPath()
	{
		return self::$_path;
	}
}
