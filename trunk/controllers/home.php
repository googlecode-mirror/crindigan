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
	
	public function doIndex()
	{
		$t = RPG::template('home/index.php');
		$t->query = 'blah'; //RPG::model('news')->getEntries(5);
		
		RPG::view()->setNavCurrent('home', 'home')
		           ->setTitle('RPG Home')
		           ->setContent($t);
		
	}
	
	public function doNews()
	{
		RPG::view()->setNavCurrent('home', 'home/news')
		           ->setTitle('News');
	}
}
