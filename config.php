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

return array(
	'baseUrl' => '/rpg2',
	'debug'   => true,
	'database' => array(
		'username' => 'root',
		'password' => 'test',
		'database' => 'rpg2',
		'server'   => 'localhost',
		'prefix'   => '',
	),
	'extDatabase' => array(
		'username' => 'root',
		'password' => 'test',
		'database' => 'forum',
		'server'   => 'localhost',
		'prefix'   => 'vb3_',
	),
	'sessionLifetime' => 1800,
);