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

// filtrage des droits
if (!defined('DC_CONTEXT_ADMIN')) exit;

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
    public static function widget(&$widgets)
    {
		global $core, $plugin_name;
        try
        {
            $widgets->create(pluginNewsletter::pname(), __('Newsletter'), array('WidgetsNewsletter', 'widget'));

			// ATTENTION: modifier le nom du widget
            $widgets->newsletter->setting('title', __('Title:'), __('Newsletter'));
            $widgets->newsletter->setting('showtitle', __('Show title'), 1, 'check');
            $widgets->newsletter->setting('homeonly', __('Home page only'), 0, 'check');
	        $widgets->newsletter->setting('inwidget', __('In widget'), 0, 'check');
	        $widgets->newsletter->setting('insublink', __('In sublink'), 1, 'check');
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
    }

	/**
	* avant suppression du plugin par le gestionnaire, on le déinstalle proprement
	*/
    public static function pluginsBeforeDelete($plugin)
    {
		global $core;
        try
        {
            $name = (string) $plugin['name'];
            if (strcmp($name, pluginNewsletter::pname()) == 0)
            {
                require dirname(__FILE__).'/class.admin.php';
                adminNewsletter::Uninstall();
            }
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
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
