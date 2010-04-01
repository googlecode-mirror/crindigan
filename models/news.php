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
 * Model to handle news entries.
 *
 * @package Crindigan
 */
class NewsModel extends RPG_Model
{
	protected $_table = 'news';
	
	protected $_columns = array(
		'news_id'     => 'uint',
		'news_author' => 'uint',
		'news_title'  => 'string',
		'news_body'   => 'string',
		'news_time'   => 'datetime',
	);
	
	protected $_primary = 'news_id';
	
	/**
	 * Fetches a set of entries, given a series of options.
	 *
	 * - getBody: Will fetch the body of each entry (true)
	 * - getUser: Will fetch the author name (true)
	 * - limit:   Max number of entries to fetch (5)
	 * - offset:  Number to start fetching entries (0)
	 * - where:   Optional where clause (array())
	 * - order:   How to order the result (array('news_time' => 'DESC'))
	 *
	 * @param  array $options List of options.
	 * @return array News entries referenced by news_id.
	 */
	public function getEntries(array $options = array())
	{
		$default = array(
			'getBody' => true,
			'getUser' => true,
			'limit'   => 5,
			'offset'  => 0,
			'where'   => array(),
			'order'   => array('news_time' => 'DESC'),
		);
		$options = array_merge($default, $options);
		
		$select = RPG::database()->select('news')
		                         ->addColumns('news_id', 'news_author', 'news_title', 'news_time');
		if ($options['getBody'])
		{
			$select->addColumns('news_body');
		}
		
		if ($options['getUser'])
		{
			$select->addColumns('user_name')
			       ->addLeftJoin('user', 'user_id = news_author');
		}
		
		if ($options['where'])
		{
			// first element is condition, and the rest are bind params
			$where = array_shift($options['where']);
			$select->addWhere($where);
			$select->setBind($options['where']);
		}
		
		$select->setOrderBy($options['order'])
		       ->setLimit($options['limit'], $options['offset']);
		
		return $select->execute()->fetchMapped('news_id');
	}
}
