<?php if (!defined('DC_CONTEXT_ADMIN')) { return; }
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of lePluginDuJour, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 lipki and contributors
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------


$core->blog->settings->addNamespace('leplugindujour');
if ($core->blog->settings->leplugindujour->enabled) {
	
	# Class
	$GLOBALS['__autoload']['dcLePluginDuJour'] = dirname(__FILE__).'/inc/class.dc.leplugindujour.php';
	
	# behavior
	$core->addBehavior('adminDashboardItems',array('dcLePluginDuJour','lePluginDuJourDashboard'));
	
}

# Class
$GLOBALS['__autoload']['dcLePluginDuJour'] = dirname(__FILE__).'/inc/class.dc.leplugindujour.php';
$GLOBALS['__autoload']['LipkiUtils'] = dirname(__FILE__).'/inc/class.lipki.utils.php'; 

# Préférences du blog
$core->addBehavior('adminBlogPreferencesForm',array('LipkiUtils','adminEnabledPlugin'));
$core->addBehavior('adminEnabledPlugin',array('dcLePluginDuJour','adminEnabledPlugin'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('dcLePluginDuJour','adminBeforeBlogSettingsUpdate'));
	




