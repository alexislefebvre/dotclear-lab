<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcMiniUrl, a plugin for Dotclear 2.
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

$__autoload['dcMiniUrl'] = 
	dirname(__FILE__).'/inc/class.dc.miniurl.php';
$__autoload['coreUpdateMiniUrl'] = 
	dirname(__FILE__).'/inc/lib.miniurl.blog.update.php';

$core->url->register('miniUrl','go','^go(/(.*?)|)$',
	array('urlMiniUrl','redirectMiniUrl'));


if ($core->blog->settings->miniurl_active 
 && $core->blog->settings->miniurl_core_autoshorturl) {

	$core->addBehavior('coreBeforePostUpdate',
		array('coreUpdateMiniUrl','post'));
	$core->addBehavior('coreBeforePostCreate',
		array('coreUpdateMiniUrl','post'));

	$core->addBehavior('coreBeforeCommentUpdate',
		array('coreUpdateMiniUrl','comment'));
	$core->addBehavior('coreBeforeCommentCreate',
		array('coreUpdateMiniUrl','comment'));

	$core->addBehavior('coreBeforeCategoryUpdate',
		array('coreUpdateMiniUrl','category'));
	$core->addBehavior('coreBeforeCategoryCreate',
		array('coreUpdateMiniUrl','category'));
}
?>