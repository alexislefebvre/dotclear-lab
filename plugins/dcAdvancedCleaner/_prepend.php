<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcAdvancedCleaner, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) return;

global $__autoload, $core;

$__autoload['dcAdvancedCleaner'] = 
	dirname(__FILE__).'/inc/class.dc.advanced.cleaner.php';

$__autoload['behaviorsDcAdvancedCleaner'] = 
	dirname(__FILE__).'/inc/lib.dc.advanced.cleaner.behaviors.php';

$__autoload['dcUninstaller'] = 
	dirname(__FILE__).'/inc/class.dc.uninstaller.php';

$core->addBehavior('pluginsToolsTabs',
	array('behaviorsDcAdvancedCleaner','pluginsToolsTabs'));

$core->addBehavior('pluginsBeforeDelete',
	array('behaviorsDcAdvancedCleaner','pluginsBeforeDelete'));

$core->addBehavior('themeBeforeDelete',
	array('behaviorsDcAdvancedCleaner','themeBeforeDelete'));

$core->addBehavior('dcAdvancedCleanerAdminTabs',
	array('behaviorsDcAdvancedCleaner','dcAdvancedCleanerAdminTabs'));
?>