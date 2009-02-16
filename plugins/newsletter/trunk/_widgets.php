<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

$core->addBehavior('initWidgets',array('newsletterWidgets','initWidgets'));

class newsletterWidgets
{
	/**
	* initialisation du widget
	*/
	public static function initWidgets(&$widgets)
   {
		global $core, $plugin_name;
      try {
			$widgets->create(pluginNewsletter::pname(), __('Newsletter'), array('WidgetsNewsletter', 'widget'));

			// ATTENTION: modifier le nom du widget
         $widgets->newsletter->setting('title', __('Title:'), __('Newsletter'));
         $widgets->newsletter->setting('showtitle', __('Show title'), 1, 'check');
         $widgets->newsletter->setting('homeonly', __('Home page only'), 0, 'check');
	      //$widgets->newsletter->setting('inwidget', __('In widget'), 0, 'check');
	      $widgets->newsletter->setting('insublink', __('In sublink'), 1, 'check');
	      
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

}
?>
