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
 * Base class for controllers.
 *
 * @package Crindigan
 */
class RPG_Controller
{
	/**
	 * Default layout for the controller.
	 *
	 * @var string
	 */
	protected $_layout = 'layouts/empty.php';
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		// add YUI 3 and common JS
		// @DETACH - remove the default javascript includes
		RPG::view()->setLayout($this->_layout)
		           ->addScript('http://yui.yahooapis.com/3.0.0/build/yui/yui-min.js', true)
		           ->addScript('media/js/common.js');
		
		$this->_setupPublicMenu();
	}
	
	/**
	 * Sets up the navigation for the public side.
	 */
	protected function _setupPublicMenu()
	{
		// @DETACH - Remove app-specific menu stuff
		RPG::view()
			->setNavEntry('home', 'home', 'Home', true)
				->setSubNavEntry('home', array(
					'home' => 'RPG Home',
					'home/news' => 'Latest News',
				))
			->setNavEntry('character', 'character', 'Characters')
				->setSubNavEntry('character', array(
					'squad' => 'Manage Squads',
					'squad/exchange' => 'Exchange Members',
					'recruit' => 'Recruit Soldiers',
					'character/search' => 'Search Characters',
				))
			->setNavEntry('inventory', 'inventory', 'Items')
				->setSubNavEntry('inventory', array(
					'inventory' => 'View Inventory',
					'inventory/exchange' => 'Inventory Exchange',
					'create' => 'Item Creation',
					'trade' => 'Item Trading',
				))
			->setNavEntry('battle', 'battle', 'Battles')
				->setSubNavEntry('battle', array(
					'battle'           => 'Active Battles',
					'battle/my'        => 'My Battles',
					'battle/challenge' => 'Challenges',
					'battle/history'   => 'Archives',
				))
			->setNavEntry('world', 'world', 'World')
				->setSubNavEntry('world', array(
					'world'    => 'Explore',
					'quest'    => 'Quests',
					'usershop' => 'Your Shops',
				))
			->setNavEntry('info', 'info', 'Library')
				->setSubNavEntry('info', array(
					'info/jobs'    => 'Jobs',
					'info/skills'  => 'Skills',
					'info/items'   => 'Items',
					'info/deities' => 'Deities',
				))
			->setNavEntry('user', 'user', 'User')
				->setSubNavEntry('user', array(
					'user'       => 'My Profile',
					'user/edit'  => 'Edit Settings',
					'user/money' => 'Exchange Money',
				));
	}
	
	/**
	 * Lists all available actions to the controller.
	 */
	public function doDebugListActions()
	{
		if (RPG::config('debug') === true)
		{
			$out = '<h2>' . get_class($this) . " - Actions</h2>\n"
			     . '<a href="' . RPG::url('*') . '">&laquo; Exit Debug</a><br />'
			     . "<ul>\n";
			
			$class = new ReflectionObject($this);
			foreach ($class->getMethods() AS $method)
			{
				$methodName = $method->getName();
				if (strpos($methodName, 'do') === 0
					AND $method->getDeclaringClass()->getName() !== 'RPG_Controller')
				{
					$out .= "\t<li><a href=\"" . RPG::url('*/debug-view-action/' . $methodName)
					      . "\">" . substr($methodName, 2) . '</a>'
					      . (($method->getDocComment() === false) ? ' - No doc comment!' : '')
					      . "</li>\n";
				}
			}
			$out .= '</ul>';
			
			RPG::view()->setLayout('layouts/empty.php')
			           ->setContent($out);
		}
	}
	
	/**
	 * Displays the source code of the given action name.
	 *
	 * @param  string $actionName  Name of the controller's action method.
	 */
	public function doDebugViewAction($actionName)
	{
		if (RPG::config('debug') === true AND strpos($actionName, 'do') === 0)
		{
			$method = new ReflectionMethod($this, $actionName);
			$out = '<h2>' . $method->getDeclaringClass()->getName() . "::$actionName()</h2>\n"
			     . '<a href="' . RPG::url('*/debug-list-actions') . '">&laquo; Action List</a><br /><br />';
			
			$start  = $method->getStartLine() - 1;
			$end    = $method->getEndLine();
			$file   = file($method->getFileName());
			$lines  = array_slice($file, $start, $end - $start);
			
			$out .= "<pre>\n    " . str_replace("\t", '    ', $method->getDocComment()) . "\n";
			foreach ($lines AS $line)
			{
				$out .= htmlentities(str_replace("\t", '    ', $line));
			}
			$out .= '</pre>';
			
			RPG::view()->setLayout('layouts/empty.php')
			           ->setContent($out);
		}
	}
	
	/**
	 * Default 404 action if an action does not exist. Can be overridden
	 * to provide alternate paths (eg. /user/1 instead of /user/view/1)
	 *
	 * @param  string $action Name of the action that tried to be called.
	 * @param  string $param... List of parameters as variable-length list
	 *                          of arguments.
	 */
	public function do404($action)
	{
		throw new RPG_Exception('Action "' . $action . '" does not exist.');
	}
}
