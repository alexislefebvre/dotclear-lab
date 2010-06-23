<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Benoit de Marne.
# benoit.de.marne@gmail.com
# Many thanks to Association Dotclear and special thanks to Olivier Le Bris
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

// filtrage des droits
if (!defined('DC_CONTEXT_ADMIN')) exit;
global $core;

// chargement des librairies
require_once dirname(__FILE__).'/inc/class.newsletter.plugin.php';
require_once dirname(__FILE__).'/inc/class.newsletter.core.php';
require_once dirname(__FILE__).'/inc/class.newsletter.admin.php';

// est-ce qu'on a besoin d'installer et est-ce qu'on peut le faire ?
// on vérifie qu'il s'agit bien d'une version plus récente
$this_version = $core->plugins->moduleInfo('newsletter', 'version');
$installed_version = $core->getVersion('newsletter');

if (version_compare($installed_version, $this_version, '>=')) {
	return;
}

try {

 	# Settings compatibility test
	if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
		$core->blog->settings->addNamespace('newsletter');
		$GLOBALS['newsletter_settings'] =& $core->blog->settings->newsletter;
		$GLOBALS['system_settings'] =& $core->blog->settings->system;
	} else {
		$core->blog->settings->setNamespace('newsletter');
		$GLOBALS['newsletter_settings'] =& $core->blog->settings;
		$GLOBALS['system_settings'] =& $core->blog->settings;
	}	
	
	if ($installed_version != '') {
		// update
		
		// activation des paramètres par défaut
		$core->blog->dcNewsletter = new dcNewsletter($core);

		if (version_compare($installed_version, '3.6.0', '<')) {
			// import des paramètres existants
			$core->blog->dcNewsletter->newsletter_settings->repriseSettings();
		} else {
			$core->blog->dcNewsletter->newsletter_settings->defaultsSettings();
		}
		
		// activate plugin
		$GLOBALS['newsletter_settings']->put('newsletter_flag',false,'boolean','Newsletter plugin enabled');

		// Prise en compte de la nouvelle version
		$core->setVersion('newsletter', $this_version);
		return true;
		
	} else {
		// nouvelle install
		// création du schéma de la table
		$_s = new dbStruct($core->con, $core->prefix);
		require dirname(__FILE__).'/inc/db-schema.php';
	
		$si = new dbStruct($core->con, $core->prefix);
		$changes = $si->synchronize($_s);

		// activation des paramètres par défaut
		$core->blog->dcNewsletter = new dcNewsletter($core);
		$core->blog->dcNewsletter->newsletter_settings->defaultsSettings();
		
		// activate plugin
		$GLOBALS['newsletter_settings']->put('newsletter_flag',false,'boolean','Newsletter plugin enabled');
		
		// Prise en compte de la nouvelle version
		$core->setVersion('newsletter', $this_version);
				
		return true;
	}

} catch (Exception $e) { 
	$core->error->add(__('Unable to install the plugin Newsletter'));
	$core->error->add($e->getMessage()); 
	return false;
}

?>