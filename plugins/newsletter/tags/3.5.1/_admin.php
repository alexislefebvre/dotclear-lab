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

// filtrage des droits
if (!defined('DC_CONTEXT_ADMIN')) { return; }

// ajout des comportements
$core->addBehavior('pluginsBeforeDelete', array('dcBehaviorsNewsletter', 'pluginsBeforeDelete'));

// envoi automatique
$core->addBehavior('adminAfterPostCreate', array('dcBehaviorsNewsletter', 'adminAutosend'));
$core->addBehavior('adminAfterPostUpdate', array('dcBehaviorsNewsletter', 'adminAutosend'));

// initialisation du widget
$core->addBehavior('initWidgets', array('dcBehaviorsNewsletter', 'widget'));

// ajout de la gestion des url
$core->url->register('newsletter', 'newsletter', '^newsletter/(.+)$', array('urlNewsletter', 'newsletter'));

// chargement des librairies
require_once dirname(__FILE__).'/class.plugin.php';

// intégration au menu
$_menu['Plugins']->addItem(('Newsletter'), 'plugin.php?p='.pluginNewsletter::pname(), pluginNewsletter::urldatas().'/icon.png',
    preg_match('/plugin.php\?p='.pluginNewsletter::pname().'(&.*)?$/', $_SERVER['REQUEST_URI']),
    $core->auth->check('usage,admin', $core->blog->id));

// définition des comportements	
class dcBehaviorsNewsletter
{
	/**
	* initialisation du widget
	*/
	public static function widget(&$w)
	{
		global $core, $plugin_name;
      	try {
			$w->create(pluginNewsletter::pname(), __('Newsletter'), array('WidgetsNewsletter', 'widget'));

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
    
	/**
	* avant suppression du plugin par le gestionnaire, on le déinstalle proprement
	*/
	public static function pluginsBeforeDelete($plugin)
	{
		global $core;
      	try {
      		$name = (string) $plugin['name'];
         		if (strcmp($name, pluginNewsletter::pname()) == 0) {
         			require dirname(__FILE__).'/class.admin.php';
            		adminNewsletter::Uninstall();
      		}
     	} catch (Exception $e) { 
     		$core->error->add($e->getMessage()); 
     	}
	}
    
	/**
	* après création d'un billet dans l'admin
	*/
	public static function adminAutosend($cur, $post_id)
	{
		dcNewsletter::autosendNewsletter();
	}
}
	
?>
