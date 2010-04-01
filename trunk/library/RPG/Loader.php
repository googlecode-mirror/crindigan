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
 * Handles autoload procedures for the framework.
 *
 * @package Crindigan
 */
class RPG_Loader {
	
	/**
	 * Attempts to autoload a library or model class.
	 *
	 * @param  string $class Class name.
	 * @return bool
	 */
	static public function autoload($class) {
		if ( self::loadLibrary($class) ) {
			return true;
		}
		if ( preg_match('#^([A-Z][A-Za-z0-9]+)Model$#', $class, $match) ) {
			if ( self::loadModel($match[1]) ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Attempts to autoload a library class.
	 *
	 * @param  string $class Class name.
	 * @return bool
	 */
	static protected function loadLibrary($class) {
		$path = RPG_LIBRARY_PATH . '/' . str_replace('_', '/', $class) . '.php';
		if ( file_exists($path) ) {
			require $path;
			return class_exists($class);
		}
	}
	
	/**
	 * Attempts to autoload a model class.
	 *
	 * @param  string $model Model class name, without Model suffix.
	 * @return bool
	 */
	static protected function loadModel($model) {
		$path = RPG_Model::getPath() . '/' . strtolower($model) . '.php';
		if ( file_exists($path) ) {
			require $path;
			$class = $model . 'Model';
			return class_exists($class) && is_subclass_of($class, 'RPG_Model');
		}
	}
}
