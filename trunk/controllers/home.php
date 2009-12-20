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
 * Home controller. Handles news as well.
 *
 * @package Crindigan
 */
class HomeController extends RPG_Controller
{
	protected $_layout = 'layouts/frontend.php';
	
	/**
	 * Displays the main frontend dashboard. Contains latest news entries
	 * along with miscellaneous/important information in the sidebar.
	 */
	public function doIndex()
	{
		$t = RPG::template('home/index.php');
		$t->newsEntries = RPG::model('news')->getEntries(array(
			'where' => array('news_time <= :0', RPG_NOW)
		));
		
		// characters, squads, money, active battles
		// not finalized, some could be injected right in the template itself
		$t->characters    = 5;
		$t->maxCharacters = 16;
		$t->squads        = 3;
		$t->maxSquads     = 8;
		$t->money         = 17338;
		$t->moneyName     = 'Aurum';
		$t->activeBattles = 'N/A';
		
		RPG::view()->setNavCurrent('home', 'home')
		           ->setTitle('RPG Home')
		           ->setContent($t);
	}
	
	/**
	 * Displays more news articles and a navigable archive.
	 */
	public function doNews()
	{
		RPG::view()->setNavCurrent('home', 'home/news')
		           ->setTitle('News');
	}
}
