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
if (!defined('DC_CONTEXT_ADMIN')) exit;

// chargement des librairies
require_once dirname(__FILE__).'/inc/class.newsletter.plugin.php';
require_once dirname(__FILE__).'/inc/class.newsletter.core.php';
require_once dirname(__FILE__).'/inc/class.newsletter.admin.php';

// est-ce qu'on a besoin d'installer et est-ce qu'on peut le faire ?
// on vérifie qu'il s'agit bien d'une version plus récente
$versionnew = $core->plugins->moduleInfo(newsletterPlugin::pname(), 'version');
$versionold = $core->getVersion(newsletterPlugin::pname());

try {
	if (version_compare($versionold, $versionnew, '>=')) {
		// version a jour
		return;
	} else if ($versionold != '') {
		// update
		
		// activation des paramètres par défaut
		$core->blog->dcNewsletter = new dcNewsletter($core);

		if (version_compare($versionold, '3.6.0', '<')) {
			// import des paramètres existants
			$core->blog->dcNewsletter->newsletter_settings->repriseSettings();
		} else {
			$core->blog->dcNewsletter->newsletter_settings->defaultsSettings();
		}

		// Prise en compte de la nouvelle version
		$core->setVersion(newsletterPlugin::pname(), $versionnew);
		unset($versionnew, $versionold);

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

		// Prise en compte de la nouvelle version
		$core->setVersion(newsletterPlugin::pname(), $versionnew);
		unset($versionnew, $versionold);
				
		return true;
	}

} catch (Exception $e) { 
	$core->error->add(__('Unable to install the plugin Newsletter'));
	$core->error->add($e->getMessage()); 
	return false;
}

?>
