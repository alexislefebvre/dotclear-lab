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

// est-ce qu'on a besoin d'installer et est-ce qu'on peut le faire ?
// on vérifie qu'il s'agit bien d'une version plus récente
///* suppression de la gestion du versionning
$versionnew = $core->plugins->moduleInfo(newsletterPlugin::pname(), 'version');
$versionold = $core->getVersion(newsletterPlugin::pname());
//*/
if (version_compare($versionold, $versionnew, '>=')) 
	return;
else
{
	// chargement des librairies
	require_once dirname(__FILE__).'/inc/class.newsletter.admin.php';
	if (newsletterAdmin::Install())
	{
		$core->setVersion(newsletterPlugin::pname(), $versionnew);
		unset($versionnew, $versionold);
		return true;		
	}
	else
	{
		$core->error->add(__('Unable to install Newsletter.'));
		return false;
	}
}

?>
