<?php if (!defined('DC_CONTEXT_ADMIN')) {return;}
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
	
	# Plugin menu
	$_menu['Plugins']->addItem(
		__('flvPlayer config'),
		'plugin.php?p=flvplayerconfig',
		'index.php?pf=flvplayerconfig/icon.png',
		preg_match('/plugin.php\?p=flvplayerconfig(&.*)?$/', $_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));
	
	# Class
	$GLOBALS['__autoload']['dcflvplayerconfig'] = dirname(__FILE__).'/inc/class.dc.flvplayerconfig.php';
	
	# js and css load
	$core->addBehavior('adminPostHeaders',array('dcflvplayerconfig','jsLoad'));
	$core->addBehavior('adminPageHeaders',array('dcflvplayerconfig','jsLoad'));
	$core->addBehavior('adminRelatedHeaders',array('dcflvplayerconfig','jsLoad'));
	$core->addBehavior('adminDashboardHeaders',array('dcflvplayerconfig','jsLoad'));

	$core->addBehavior('coreAfterPostContentFormat',array('dcflvplayerconfig','flvPlayerConfig'));
	
}

# Class
$GLOBALS['__autoload']['dcflvplayerconfig'] = dirname(__FILE__).'/inc/class.dc.flvplayerconfig.php';
$GLOBALS['__autoload']['LipkiUtils'] = dirname(__FILE__).'/inc/class.lipki.utils.php'; 

# Préférences du blog
$core->addBehavior('adminBlogPreferencesForm',array('LipkiUtils','adminEnabledPlugin'));
$core->addBehavior('adminEnabledPlugin',array('dcflvplayerconfig','adminEnabledPlugin'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('dcflvplayerconfig','adminBeforeBlogSettingsUpdate'));