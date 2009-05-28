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

// Bootstrap the system
define('RPG_ROOT', dirname(__FILE__));
define('RPG_NOW', time());

// Set up the autoloader
function __autoload($className)
{
	$filePath = RPG_ROOT . '/library/' . str_replace('_', '/', $className) . '.php';
	if (file_exists($filePath))
	{
		require $filePath;
		return class_exists($className);
	}
	return false;
}

// Set up the error handler
set_error_handler(array('RPG', 'handlePhpError'));

// Initialize the configuration array
$config = require RPG_ROOT . '/config.php';

try
{
	RPG::setConfig($config);
	RPG::router(RPG_ROOT . '/controllers')->processRequest();
}
catch (RPG_Exception $ex)
{
	echo '<html>
<head>
	<title>Application Error</title>
</head>
<body>
	<h1>Application Error</h1>', "\n";
	
	if (isset($config['debug']) AND $config['debug'] === true)
	{
		echo $ex;
	}
	else
	{
		echo "There has been an internal error within Anfiniti RPG.\n";
	}
	
	echo "</body>\n</html>";
	exit;
}
