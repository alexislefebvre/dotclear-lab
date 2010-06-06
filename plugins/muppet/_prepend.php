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

$__autoload['muppet'] = dirname(__FILE__).'/inc/class.setting.muppet.php';

require dirname(__FILE__).'/_widgets.php';

function muppetSettings($core,$ns='muppet') {
	if (version_compare(DC_VERSION,'2.2-alpha','>=')) {  
		$core->blog->settings->addNamespace($ns); 
		return $core->blog->settings->{$ns}; 
	} else { 
		$core->blog->settings->setNamespace($ns); 
		return $core->blog->settings; 
	}
}

$post_types = muppet::getPostTypes();

if (!empty($post_types))
{
	foreach ($post_types as $k => $v)
	{
		$core->url->register($k,$k,sprintf('^%s/(.+)$',$k),array('urlMuppet','single'));
		$core->url->register(sprintf('%spreview',$k),sprintf('%spreview',$k),sprintf('^%spreview/(.+)$',$k),array('urlMuppet','singlepreview'));
		$core->setPostType($k,'plugin.php?p=muppet&type='.$k.'&id=%d',$core->url->getBase($k).'/%s');
	}
}
?>
