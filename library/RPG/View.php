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
 * Handles the view and layout components of the program.
 *
 * @package Crindigan
 */
class RPG_View
{
	/**
	 * Layout template.
	 *
	 * @var RPG_Template
	 */
	protected $_layout = null;
	
	/**
	 * List of stylesheets to include.
	 *
	 * @var array
	 */
	protected $_styleSheets = array();
	
	/**
	 * Embedded CSS to include.
	 *
	 * @var string
	 */
	protected $_inlineCss = '';
	
	/**
	 * List of javascript files to include.
	 *
	 * @var array
	 */
	protected $_scriptFiles = array();
	
	/**
	 * Inline javascript to include.
	 *
	 * @var string
	 */
	protected $_inlineScript = '';
	
	/**
	 * Navigation menu.
	 *
	 * @var array
	 */
	protected $_navigation = array();
	
	/**
	 * Sub-navigation entries.
	 *
	 * @var array
	 */
	protected $_subNavigation = array();
	
	/**
	 * Navigation bits/breadcrumbs.
	 *
	 * @var array
	 */
	protected $_navbits = array();
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		
	}
	
	/**
	 * Sends a redirect to the browser.
	 * 
	 * @param  string $path
	 * @param  array  $query
	 * @see    RPG::url()
	 */
	public function redirect($path, array $query = array())
	{
		header('Location: ' . RPG::url($path, $query));
		exit;
	}
	
	/**
	 * Sets the layout template file to use.
	 *
	 * @param  string $layout Path to the layout template file, relative to
	 *                        the template path.
	 */
	public function setLayout($layout = '')
	{
		if ($layout === '')
		{
			$layout = 'layouts/empty.php';
		}
		
		if (file_exists(RPG_Template::getPath() . "/$layout"))
		{
			$this->_layout = RPG::template($layout);
		}
		else
		{
			throw new RPG_Exception('Specified layout "' . $layout . '" does not exist.');
		}
		
		$this->setContent('');
		
		return $this;
	}
	
	/**
	 * Returns the layout template instance. If no layout has been set,
	 * it creates an empty layout first.
	 *
	 * @return RPG_Template
	 */
	public function getLayout()
	{
		if ($this->_layout === null)
		{
			$this->setLayout();
		}
		
		return $this->_layout;
	}
	
	/**
	 * Sets the title of the page. If no parameter is given, clears the title.
	 *
	 * @param  string $title
	 * @return RPG_View
	 */
	public function setTitle($title = null)
	{
		if ($title === null)
		{
			$this->getLayout()->clear('title');
		}
		else
		{
			$this->getLayout()->set('title', $title);
		}
		
		return $this;
	}
	
	/**
	 * Adds a link to a stylesheet file in the page header.
	 *
	 * @param  string $file Path to the CSS file.
	 * @param  bool $raw    If false, prepends the RPG base URL to the file path.
	 * @return RPG_View
	 */
	public function addStyleSheet($file, $raw = false)
	{
		if ($raw === false)
		{
			$file = RPG::config('baseUrl') . '/' . $file;
		}
		$this->_styleSheets[] = $file;
		
		return $this;
	}
	
	/**
	 * Adds a string of inline CSS to the page header.
	 *
	 * @param  string $css
	 * @return RPG_View
	 */
	public function addInlineCss($css)
	{
		$this->_inlineCss .= $css . "\n\n";
		return $this;
	}
	
	/**
	 * Adds a script tag to include a given JavaScript file.
	 *
	 * @param  string $file Path to the JS file.
	 * @param  bool $raw    If false, prepends the RPG base URL to the file path.
	 * @return RPG_View
	 */
	public function addScript($file, $raw = false)
	{
		if ($raw === false)
		{
			$file = RPG::config('baseUrl') . '/' . $file;
		}
		$this->_scriptFiles[] = $file;
		
		return $this;
	}
	
	/**
	 * Adds a string of inline JavaScript to the page header.
	 *
	 * @param  string $js
	 * @return RPG_View
	 */
	public function addInlineScript($js)
	{
		$this->_inlineScript .= $js . "\n\n";
		return $this;
	}
	
	/**
	 * Sets the content portion of the layout. If $content is an instance
	 * of RPG_Template, it will set the content to the rendered template.
	 *
	 * @param  string|RPG_Template $content
	 */
	public function setContent($content)
	{
		if ($content instanceof RPG_Template)
		{
			$content = $content->render();
		}
		$this->getLayout()->set('content', $content);
		
		return $this;
	}
	
	// --------------------------------
	// Navigation management
	// --------------------------------
	
	/**
	 * Adds an entry to the navigation.
	 *
	 * @param  string $id  Short alphanumeric key to reference this entry.
	 * @param  string $url The target of the navigation entry, formatted as
	 *                     controller/action/param1/param2/etc.
	 * @param  string $text The text of the entry.
	 * @param  bool $current If true, will be highlighted.
	 * @return RPG_View
	 */
	public function setNavEntry($id, $url, $text, $current = false)
	{
		$this->_navigation[$id] = array(
			'url'     => $url,
			'text'    => $text,
			'current' => $current,
		);
		
		return $this;
	}
	
	/**
	 * Adds a sub navigation entry, meant to be in the row underneath the
	 * main navigation bar.
	 *
	 * @param  string $id This should match a given ID from calling setNavEntry().
	 *                    This entry will be matched with the parent.
	 * @param  array $links An array of url => link text. The link text may be
	 *                      substituted with an array with 'text' and 'current'
	 *                      keys, where text is the link text and current
	 *                      is a boolean that determines if the link is highlighted.
	 * @return RPG_View
	 */
	public function setSubNavEntry($id, array $links)
	{
		$this->_subNavigation[$id] = array(
			'current' => $this->_navigation[$id]['current'],
			'entries' => array(),
		);
		
		foreach ($links AS $url => $entry)
		{
			if (!is_array($entry))
			{
				$entry = array('text' => $entry, 'current' => false);
			}
			$this->_subNavigation[$id]['entries'][$url] = $entry;
		}
		
		return $this;
	}
	
	/**
	 * Sets a navigation and sub-navigation entry to be current.
	 *
	 * @param  string $main The main navigation entry to highlight.
	 * @param  string $sub  The sub navigation entry to highlight.
	 * @return RPG_View
	 */
	public function setNavCurrent($main = '', $sub = '')
	{
		// set current to false for everything except the given main/sub
		foreach ($this->_navigation AS $id => &$entry)
		{	
			$entry['current'] = ($main === $id) ? true : false;
			$this->_subNavigation[$id]['current'] = ($main === $id) ? true : false;
			foreach ($this->_subNavigation[$id]['entries'] AS $url => &$subEntry)
			{
				$subEntry['current'] = ($sub === $url) ? true : false;
			}
		}
		
		return $this;
	}
	
	/**
	 * Clears the main and sub navigation entries for a given ID.
	 *
	 * @param  string $id
	 * @return RPG_View
	 */
	public function clearNavEntry($id)
	{
		if (isset($this->_navigation[$id]))
		{
			unset($this->_navigation[$id]);
		}
		if (isset($this->_subNavigation[$id]))
		{
			unset($this->_subNavigation[$id]);
		}
		
		return $this;
	}
	
	/**
	 * Sets the navigation with the given raw array. The array has navigation
	 * IDs for keys, and the values are three element arrays with keys 'url', 
	 * 'text', and 'current'.
	 *
	 * @param  array $nav
	 * @return RPG_View
	 */
	public function setNavigation(array $nav)
	{
		$this->_navigation = $nav;
		return $this;
	}
	
	/**
	 * Gets the raw navigation array. See setNavigation() for array format.
	 *
	 * @return array
	 * @see RPG_View::setNavigation()
	 */
	public function getNavigation()
	{
		return $this->_navigation;
	}
	
	// --------------------------------
	// Navbit management
	// --------------------------------
	
	/**
	 * Adds a navigation bit. Currently unused.
	 * 
	 * @param  string $text Text of navbit.
	 * @param  string $url  URL of navbit.
	 * @return RPG_View
	 */
	public function pushNavbit($text, $url = '')
	{
		// if there's no title, keep setting it to the newest navbit
		if ($this->getLayout()->get('title') === null)
		{
			$this->setTitle($text);
		}
		
		$this->_navbits[$url] = $text;
		return $this;
	}
	
	/**
	 * Outputs the page to the browser.
	 * 
	 * @todo In the future, have multiple output formats? XML, JSON, etc.
	 */
	public function render()
	{
		// set the styles/css/javascript, and render to $output
		$output = $this->getLayout()->set(array(
			'styleSheets'   => $this->_styleSheets,
			'inlineCss'     => $this->_inlineCss,
			'scriptFiles'   => $this->_scriptFiles,
			'inlineScript'  => $this->_inlineScript,
			'navigation'    => $this->_navigation,
			'subNavigation' => $this->_subNavigation,
			'navbits'       => $this->_navbits,
		))->render();
		
		$gzworked = false;
		
		// gzip the output if we can.
		// headers can't be sent or else we won't be able to set content-encoding.
		// only gzipping if output is >1kb, make this configurable?
		if (RPG::config('usegzip') AND !RPG::isRegistered('nogzip')
			AND isset($_SERVER['HTTP_ACCEPT_ENCODING'])
			AND strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false
			AND !headers_sent()
			AND strlen($output) > 1024)
		{
			$output = $this->getGzippedText($output, $gzworked);
		}
		
		if (!headers_sent())
		{
			// send encoding headers if gzip worked
			if ($gzworked)
			{
				header('Content-Encoding: gzip');
				header('Vary: Accept-Encoding', false);
			}
			
			header('Content-Length: ' . strlen($output));
			header('Cache-Control: private');
			header('Pragma: private');
		}
		
		echo $output;
	}
	
	/**
	 * Returns text encoded in GZIP format.
	 *
	 * @param  string $text Text to encode.
	 * @param  bool &$success Will be filled with the result of the operation.
	 * @param  int $level The compression level.
	 *
	 * @todo See about manually creating GZIP headers/crc32 and using
	 *       gzcompress. A lot of other people do it, but the last time I
	 *       tried it, IE barfed and constantly had a blank page or refused
	 *       to even load it.
	 */
	public function getGzippedText($text, &$success, $level = 3)
	{
		$success = false;
		
		if (function_exists('gzencode'))
		{
			$gztext = gzencode($text, $level);
			if ($gztext !== false)
			{
				$success = true;
				return $gztext;
			}
		}
		
		return $text;
	}
}
