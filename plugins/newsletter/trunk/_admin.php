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

// filtrage des droits
if (!defined('DC_CONTEXT_ADMIN')) { return; }

// intégration au menu d'administration
$_menu['Plugins']->addItem(('Newsletter'), 'plugin.php?p='.newsletterPlugin::pname(), newsletterPlugin::urldatas().'/icon.png',
    preg_match('/plugin.php\?p='.newsletterPlugin::pname().'(&.*)?$/', $_SERVER['REQUEST_URI']),
    $core->auth->check('usage,admin', $core->blog->id));

// ajout des comportements
$core->addBehavior('pluginsBeforeDelete', array('dcBehaviorsNewsletter', 'pluginsBeforeDelete'));

// envoi automatique
$core->addBehavior('adminAfterPostCreate', array('dcBehaviorsNewsletter', 'adminAutosend'));
$core->addBehavior('adminAfterPostUpdate', array('dcBehaviorsNewsletter', 'adminAutosend'));

// chargement du widget
require dirname(__FILE__).'/_widgets.php';

// définition des comportements	
class dcBehaviorsNewsletter
{
	/**
	* avant suppression du plugin par le gestionnaire, on le déinstalle proprement
	*/
	public static function pluginsBeforeDelete($plugin)
	{
		global $core;
      	try {
      		$name = (string) $plugin['name'];
         		if (strcmp($name, newsletterPlugin::pname()) == 0) {
         			require dirname(__FILE__).'/inc/class.newsletter.admin.php';
            		newsletterAdmin::Uninstall();
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
		try {
			newsletterCore::autosendNewsletter();
     	} catch (Exception $e) { 
     		$core->error->add($e->getMessage()); 
     	}			
	}
}
	
?>
