<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Newsletter, a plugin for Dotclear 2.
# Copyright (C) 2009 Benoit de Marne, and contributors. All rights
# reserved.
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 3
# of the License, or (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# ***** END LICENSE BLOCK *****

// initialisation du widget
$core->addBehavior('initWidgets', array('newsletterWidgets', 'initWidgets'));

class newsletterWidgets 
{
	/**
	* initialisation du widget
	*/
	public static function initWidgets(&$w)
	{
		global $core, $plugin_name;
      	try {
			$w->create(newsletterPlugin::pname(), __('Newsletter'), array('publicWidgetsNewsletter', 'initWidgets'));

			$w->newsletter->setting('title', __('Title:'), __('Newsletter'));
			$w->newsletter->setting('showtitle', __('Show title'), 1, 'check');
			$w->newsletter->setting('homeonly', __('Home page only'), 0, 'check');
			$w->newsletter->setting('inwidget', __('In widget'), 0, 'check');
			$w->newsletter->setting('insublink', __('In sublink'), 1, 'check');
			$w->newsletter->setting('subscription_link',__('Title subscription link:'),__('Subscription link'));
	      
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

}
	
?>
