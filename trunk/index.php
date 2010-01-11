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
$config = require RPG_ROOT . '/config.php';

// If you want to move the library/ folder outside of public_html for security
// reasons, define RPG_LIBRARY_PATH inside of your config.php
if (!defined('RPG_LIBRARY_PATH')) {
	define('RPG_LIBRARY_PATH', RPG_ROOT . '/library');
}

//
// Set up the autoloader
//

function rpg_autoload($className)
{
	$filePath = RPG_LIBRARY_PATH . '/' . str_replace('_', '/', $className) . '.php';
	if (file_exists($filePath))
	{
		require $filePath;
		return class_exists($className);
	}
	return false;
}
spl_autoload_register('rpg_autoload');

//
// Preload files that are always needed to avoid having to autoload everything.
//

require RPG_LIBRARY_PATH . '/RPG/Exception.php';
require RPG_LIBRARY_PATH . '/RPG.php';

// start the debug timer
RPG::set('__debug_time', microtime(true));

require RPG_LIBRARY_PATH . '/RPG/Database.php';
require RPG_LIBRARY_PATH . '/RPG/Database/Result.php';
require RPG_LIBRARY_PATH . '/RPG/Input.php';
require RPG_LIBRARY_PATH . '/RPG/Model.php';
require RPG_LIBRARY_PATH . '/RPG/View.php';
require RPG_LIBRARY_PATH . '/RPG/Controller.php';
require RPG_LIBRARY_PATH . '/RPG/Router.php';
require RPG_LIBRARY_PATH . '/RPG/Template.php';
require RPG_LIBRARY_PATH . '/RPG/Session.php';
require RPG_LIBRARY_PATH . '/RPG/User.php';

// maybe add hybrid session handler
if (isset($config['sessionHybrid']) AND $config['sessionHybrid'] === true) {
	require RPG_LIBRARY_PATH . '/RPG/Session/Hybrid.php';
}

// Set up the error handler
set_error_handler(array('RPG', 'handlePhpError'));

// Default configuration items
$defaultConfig = array(
	'modelPath' => RPG_ROOT . '/models',
	'viewPath'  => RPG_ROOT . '/views',
	'controllerPath' => RPG_ROOT . '/controllers',
	'cachePath' => RPG_ROOT . '/cache',
	'tmpPath' => RPG_ROOT . '/tmp',
	'sessionPath' => RPG_ROOT . '/tmp/sessions',
	'objectsPath' => RPG_ROOT . '/cache/objects',
);

// Override defaults if needed
$config = array_merge($defaultConfig, $config);

//
// Start the main execution!
// Top-level try/catch block for a last-ditch effort error page.
//

try
{
	// Initialize the system
	RPG::setConfig($config);
	RPG_Template::setPath($config['viewPath']);
	RPG_Model::setPath($config['modelPath']);
	
	RPG::session();
	RPG::user(RPG::model('user'));
	
	// add this now, so controllers can include CSS that overrides defaults
	RPG::view()->addStyleSheet('media/styles/light.css');
	
	// Process the request
	RPG::router($config['controllerPath'])->processRequest();
	
	// stop the timer - needs to be here so it can get rendered via templates
	RPG::debug('Execution Time (pre-render): ' . round(microtime(true) - RPG::get('__debug_time'), 4));
	
	// Render the output - TODO: handle styles differently later
	RPG::view()->render();
}
catch (RPG_Exception $ex)
{
	// Basic error page
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
