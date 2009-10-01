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

// ajout de l'import / export
$core->addBehavior('exportFull',array('dcBehaviorsNewsletter','exportFull'));
$core->addBehavior('exportSingle',array('dcBehaviorsNewsletter','exportSingle'));
$core->addBehavior('importInit',array('dcBehaviorsNewsletter','importInit'));
$core->addBehavior('importFull',array('dcBehaviorsNewsletter','importFull'));
$core->addBehavior('importSingle',array('dcBehaviorsNewsletter','importSingle'));

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
		$name = (string) $plugin['name'];
		if (strcmp($name, newsletterPlugin::pname()) == 0) {
         		require dirname(__FILE__).'/inc/class.newsletter.admin.php';
			newsletterAdmin::uninstall();
		}
	}
    
	/**
	* après création d'un billet dans l'admin
	*/
	public static function adminAutosend($cur, $post_id)
	{
		newsletterCore::autosendNewsletter();
	}


	public static function exportFull($core,$exp)
	{
		$exp->exportTable('newsletter');
	}

	public static function exportSingle($core,$exp,$blog_id)
	{
		$exp->export('newsletter',
	    		'SELECT subscriber_id, blog_id, email, regcode, state, subscribed, lastsent, modesend '.
	    		'FROM '.$core->prefix.'newsletter N '.
	    		"WHERE N.blog_id = '".$blog_id."'"
		);
	}

	public static function importInit($bk,$core)
	{
		$bk->cur_newsletter = $core->con->openCursor($core->prefix.'newsletter');
	}

	public static function importSingle($line,$bk,$core)
	{
		if ($line->__name == 'newsletter') {
			
			$cur = $core->con->openCursor($core->prefix.'newsletter');
			
			$bk->cur_newsletter->subscriber_id	= (integer) $line->subscriber_id;
			$bk->cur_newsletter->blog_id 		= (string) $core->blog_id;
			$bk->cur_newsletter->email 		= (string) $line->email;
			$bk->cur_newsletter->regcode 		= (string) $line->regcode;
			$bk->cur_newsletter->state 		= (string) $line->state;
			$bk->cur_newsletter->subscribed 	= (string) $line->subscribed;
			$bk->cur_newsletter->lastsent 	= (string) $line->lastsent;
			$bk->cur_newsletter->modesend 	= (string) $line->modesend;
			
			newsletterCore::add($bk->cur_newsletter->email, (string) $core->blog_id, $bk->cur_newsletter->regcode, $bk->cur_newsletter->modesend);

			$subscriber = newsletterCore::getEmail($bk->cur_newsletter->email);
			if ($subscriber != null) {
				newsletterCore::update($subscriber->subscriber_id, 
					$bk->cur_newsletter->email, 
					$bk->cur_newsletter->state, 
					$bk->cur_newsletter->regcode, 
					$bk->cur_newsletter->subscribed, 
					$bk->cur_newsletter->lastsent, 
					$bk->cur_newsletter->modesend
				);
			}
		}
	}

	public static function importFull($line,$bk,$core)
	{
		if ($line->__name == 'newsletter') {
			
			$bk->cur_newsletter->clean();
			
			$bk->cur_newsletter->subscriber_id	= (integer) $line->subscriber_id;
			$bk->cur_newsletter->blog_id 		= (string) $line->blog_id;
			$bk->cur_newsletter->email 		= (string) $line->email;
			$bk->cur_newsletter->regcode 		= (string) $line->regcode;
			$bk->cur_newsletter->state 		= (string) $line->state;
			$bk->cur_newsletter->subscribed 	= (string) $line->subscribed;
			$bk->cur_newsletter->lastsent 	= (string) $line->lastsent;
			$bk->cur_newsletter->modesend 	= (string) $line->modesend;
			
			$bk->cur_newsletter->insert();
		}
	}


}
	
?>
