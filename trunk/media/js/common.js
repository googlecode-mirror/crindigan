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
 * Global RPG namespace.
 */
var RPG = {};

YUI().use('node', function(Y)
{
	/**
	 * Frontend navigation manager.
	 */
	RPG.NavManager = function()
	{
		/**
		 * ID of the navigation entry we are currently hovering over.
		 *
		 * @var String
		 */
		this.currentId = '';
		
		/**
		 * Timeout reference. False if not set, and an object returned
		 * from Y.later if it is set.
		 */
		this.timeout = false;
		
		/**
		 * Array of element IDs registered with the navigation manager.
		 *
		 * @var Array
		 */
		this.navIds = [];
		
		/**
		 * Looks in #nav, and collects all anchor tags with an ID starting
		 * with "nav_", and registers them with the class.
		 */
		this.init = function()
		{
			Y.all('#nav a').each(function(el) {
				if (el.get('id').substr(0, 4) === 'nav_') {
					this.addHoverTab(el);
				}
			}, this);
		};
		
		/**
		 * Registers a single navigation tab element with the manager.
		 *
		 * @param  Node
		 */
		this.addHoverTab = function(el)
		{
			el.on('mouseover', function() { this.start(el); }, this);
			el.on('mouseout', this.cancel, this);
			this.navIds.push(el.get('id'));
		};
		
		/**
		 * Initializes the timer to switch the tab to the given element.
		 *
		 * @param  Node
		 */
		this.start = function(el)
		{
			if (!this.timeout) {
				this.timeout   = Y.later(400, this, this.show);
				this.currentId = el.get('id');
			}
		};
		
		/**
		 * Changes the current tab and displays the proper sub-navigation menu.
		 */
		this.show = function()
		{
			for (var i = 0, l = this.navIds.length; i < l; i++) {
				Y.get('#sub' + this.navIds[i]).setStyle('display', 'none');
				Y.get('#' + this.navIds[i]).removeClass('current');
			}
			
			Y.get('#sub' + this.currentId).setStyle('display', '');
			Y.get('#' + this.currentId).addClass('current');
		};
		
		/**
		 * Cancels the timer. Called if the mouse leaves the current tab.
		 */
		this.cancel = function()
		{
			if (this.timeout) {
				this.timeout.cancel();
				this.timeout = false;
			}
		};
	};
	
	RPG.toggle = function(id)
	{
		var el = Y.get(id),
		    ds = el.getStyle('display');
		el.setStyle('display', (el.getStyle('display') !== 'none') ? 'none' : '');
	};
	
	/**
	 * Sets up an input box so that its default value is removed when
	 * focused, and brought back if blurred and the box is empty.
	 *
	 * @param  String  Element ID
	 */
	var registerDefaultInput = function(id)
	{
		var el = Y.get('#' + id);
		if (!el) {
			return;
		}
		
		var value = el.get('value');
		
		el.on('focus', function() { 
			if (el.get('value') === value) {
				el.set('value', '');
			}
		});
		
		el.on('blur', function() {
			if (el.get('value') === '') {
				el.set('value', value);
			}
		});
	};
	
	/*
	 * Initialize navigation and maybe login box.
	 */
	Y.on('domready', function() {
		new RPG.NavManager().init()
		registerDefaultInput('login_username');
		registerDefaultInput('login_password');
	});
});
