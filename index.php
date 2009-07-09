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

// Bootstrap the system
define('RPG_ROOT', dirname(__FILE__));
define('RPG_NOW', time());
define('RPG_VERSION', '0.0.1');

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

// default config items
$defaultConfig = array(
	'modelPath' => RPG_ROOT . '/models',
	'viewPath'  => RPG_ROOT . '/views',
	'controllerPath' => RPG_ROOT . '/controllers',
);

// Initialize the configuration array
$config = require RPG_ROOT . '/config.php';

$config = array_merge($defaultConfig, $config);

try
{
	// Initialize the system
	RPG::setConfig($config);
	RPG_Template::setPath($config['viewPath']);
	RPG_Model::setPath($config['modelPath']);
	
	RPG::session();
	RPG::user();
	
	// add this now, so controllers can include CSS that overrides defaults
	RPG::view()->addStyleSheet('media/styles/light.css');
	
	// Process the request
	RPG::router($config['controllerPath'])->processRequest();
	
	// Render the output - TODO: handle styles differently later
	RPG::view()->render();
}
catch (RPG_Exception $ex)
{
	echo '<html>
<head>
	<title>Application Error</title>
	<style type="text/css">
	body { font-family: sans-serif; }
	</style>
</head>
<body>
	<h1>Application Error</h1>', "\n";
	
	if (isset($config['debug']) AND $config['debug'] === true)
	{
		echo $ex;
	}
	else
	{
		echo "There has been an internal error within Crindigan.\n";
	}
	
	echo "</body>\n</html>";
	exit;
}
