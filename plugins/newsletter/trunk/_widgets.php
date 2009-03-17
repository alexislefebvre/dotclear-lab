<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009 Benoit de Marne
# benoit.de.marne@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

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
