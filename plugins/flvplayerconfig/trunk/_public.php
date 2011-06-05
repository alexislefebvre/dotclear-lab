<?php if (!defined('DC_RC_PATH')) { return; }
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of flvplayerconfig, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 lipki and contributors
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$core->blog->settings->addNamespace('flvplayerconfig');
if ($core->blog->settings->flvplayerconfig->enabled) {
	
	# Class
	$GLOBALS['__autoload']['dcflvplayerconfig'] = dirname(__FILE__).'/inc/class.dc.flvplayerconfig.php';
	
	# Modif du code à l'affichage
	$core->addBehavior('publicBeforeContentFilter',array('dcflvplayerconfig','publicBeforeContentFilter'));
	
}