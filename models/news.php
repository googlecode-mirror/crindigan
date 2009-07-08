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

class NewsModel
{
	protected $_table = 'news';
	
	protected $_columns = array(
		'news_id'     => 0,
		'news_author' => 0,
		'news_title'  => '',
		'news_body'   => '',
		'news_time'   => 0,
	);
	
	protected $_primary = 'news_id';
	
	/**
	 * Fetches a set of entries, given a series of options.
	 *
	 * - getBody: Will fetch the body of each entry (true)
	 * - getUser: Will fetch the author name (true)
	 * - limit:   Max number of entries to fetch (5)
	 * - offset:  Number to start fetching entries (0)
	 * - where:   Optional where clause (empty)
	 * - order:   How to order the result (array('news_time' => 'DESC'))
	 *
	 * @param  array $options List of options.
	 * @return array
	 */
	public function getEntries(array $options = array())
	{
		$default = array(
			'getBody' => true,
			'getUser' => true,
			'limit'   => 5,
			'offset'  => 0,
			'where'   => '',
			'order'   => array('news_time' => 'DESC'),
		);
		$options = array_merge($default, $options);
		
		$select = RPG::database()->select('news')
		                         ->addColumns(array('news_id', 'news_author',
		                                            'news_title', 'news_time'));
		if ($options['getBody'])
		{
			$select->addColumns('news_body');
		}
		
		if ($options['getUser'])
		{
			$select->addColumns('user_name')
			       ->addLeftJoin('user', 'user_id = news_author');
		}
		
		$select->setOrderBy($options['order'])
		       ->setLimit($options['limit'], $options['offset']);
		
		return $select->execute();
	}
}
