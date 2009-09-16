<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of whiteListCom, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

global $__autoload, $core;

$__autoload['whiteListComBehaviors'] = 
	dirname(__FILE__).'/inc/lib.whitelistcom.behaviors.php';

$core->addBehavior(
	'adminBeforeBlogSettingsUpdate',
	array('whiteListComBehaviors','adminBeforeBlogSettingsUpdate')
);

$core->addBehavior(
	'adminBlogPreferencesForm',
	array('whiteListComBehaviors','adminBlogPreferencesForm')
);

$core->addBehavior(
	'publicBeforeCommentPreview',
	array('whiteListComBehaviors','publicBeforeCommentPreview')
);

$core->addBehavior(
	'publicBeforeCommentCreate',
	array('whiteListComBehaviors','publicBeforeCommentCreate')
);

?>