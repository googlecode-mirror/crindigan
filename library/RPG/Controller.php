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

/**
 * Base class for controllers.
 *
 * @package AnfinitiRPG
 */
class RPG_Controller
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->_setupPublicMenu();
	}
	
	/**
	 * Sets up the navigation for the public side.
	 */
	protected function _setupPublicMenu()
	{
		RPG::view()
			->setNavEntry('home', 'home', 'Home', true)
				->setSubNavEntry('home', array(
					'home' => 'RPG Home',
					'home/news' => 'Latest News',
				))
			->setNavEntry('character', 'character', 'Characters')
				->setSubNavEntry('character', array(
					'party' => 'Manage Parties',
					'party/exchange' => 'Exchange Members',
					'recruit' => 'Recruit Soldiers',
					'character/search' => 'Search Characters',
				))
			->setNavEntry('inventory', 'inventory', 'Inventory')
				->setSubNavEntry('inventory', array(
					'inventory' => 'View Inventory',
					'inventory/exchange' => 'Exchange Items',
					'item-creation' => 'Item Creation',
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
			->setNavEntry('library', 'library', 'Library')
				->setSubNavEntry('library', array(
					'library/jobs'    => 'Jobs',
					'library/skills'  => 'Skills',
					'library/items'   => 'Items',
					'library/deities' => 'Deities',
				));
	}
	
	/**
	 * Lists all available actions to the controller.
	 */
	public function doDebugListActions()
	{
		if (RPG::config('debug') === true)
		{
			echo '<h2>', get_class($this), ' - Actions</h2>', "\n";
			echo "<ul>\n";
			
			$class = new ReflectionObject($this);
			foreach ($class->getMethods() AS $method)
			{
				$methodName = $method->getName();
				if (strpos($methodName, 'do') === 0)
				{
					echo "\t<li><a href=\"", RPG::url('*/debug-view-action/' . $methodName),
						 "\">", substr($methodName, 2), '</a>', 
						 ($method->getDocComment() === false) ? ' - No doc comment!' : '', "</li>\n";
				}
			}
			echo "</ul>";
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
			echo '<h2>', $method->getDeclaringClass()->getName(), "::$actionName()</h2>\n";
			echo '<a href="', RPG::url('*/debug-list-actions'), '">&laquo; Go Back</a><br />';
			
			$start  = $method->getStartLine() - 1;
			$end    = $method->getEndLine();
			$file   = file($method->getFileName());
			$lines  = array_slice($file, $start, $end - $start);
			
			echo "<pre>\n";
			echo '    ', str_replace("\t", '    ', $method->getDocComment()), "\n";
			foreach ($lines AS $line)
			{
				echo htmlentities(str_replace("\t", '    ', $line));
			}
			echo '</pre>';
		}
	}
}
