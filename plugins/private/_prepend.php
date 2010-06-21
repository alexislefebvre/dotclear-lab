<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Private mode, a plugin for Dotclear 2.
# 
# Copyright (c) 2008-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__).'/_widgets.php';

$s = privateSettings($core);

if ($s->private_flag)
{
	$privatefeed = md5($s->blog_private_pwd);
	$core->url->register('feed',
		sprintf('%s-feed',$privatefeed),
		sprintf('^%s-feed/(.+)$',$privatefeed),
		array('urlPrivate','privateFeed')
	);
	$core->url->register('pubfeed',
		'feed',
		'^feed/(.+)$',
		array('urlPrivate','publicFeed')
	);
}

function privateSettings($core,$ns='private') {
	if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
		$core->blog->settings->addNamespace($ns); 
		return $core->blog->settings->{$ns}; 
	} else { 
		$core->blog->settings->setNamespace($ns); 
		return $core->blog->settings; 
	}
}
?>