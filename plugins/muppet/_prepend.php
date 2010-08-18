<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of muppet, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

if (version_compare(DC_VERSION,'2.2-beta','<')) { return; }

$__autoload['muppet'] = dirname(__FILE__).'/inc/class.setting.muppet.php';
$__autoload['toolsmuppet'] = dirname(__FILE__).'/inc/lib.behaviors.muppet.php';

$core->blog->settings->addNamespace('muppet'); 
require dirname(__FILE__).'/_widgets.php';

$post_types = muppet::getPostTypes();

if (!empty($post_types))
{
	foreach ($post_types as $k => $v)
	{
		$core->url->register($k,$k,sprintf('^%s/(.+)$',$k),array('urlMuppet','singlepost'));
		$core->url->register(sprintf('%spreview',$k),sprintf('%spreview',$k),sprintf('^%spreview/(.+)$',$k),array('urlMuppet','singlepreview'));
		$core->setPostType($k,'plugin.php?p=muppet&type='.$k.'&id=%d',$core->url->getBase($k).'/%s');
		$core->url->register($k.'s',$k.'s',sprintf('^%s(.*)$',$k.'s'),array('urlMuppet','listpost'));
	}
}
?>