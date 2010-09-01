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

if (version_compare(DC_VERSION,'2.2-beta','<')) { return; }

require dirname(__FILE__).'/_widgets.php';

$core->blog->settings->addNamespace('private'); 

#Rewrite Feeds with new URL and representation 
$feeds_url = new ArrayObject(array('feed','tag_feed'));
$core->callBehavior('initFeedsPrivateMode',$feeds_url);

if ($core->blog->settings->private->private_flag)
{
	$privatefeed = md5($core->blog->settings->private->blog_private_pwd);

	#Obfuscate all feeds URL
	foreach ($core->url->getTypes() as $k => $v) {
		if (in_array($k,(array)$feeds_url)) {
			$core->url->register(
				$k,
				sprintf('%s/%s',$privatefeed,$v['url']),
				sprintf('^%s/%s/(.+)$',$privatefeed,$v['url']),
				$v['handler']
			);
		}
	}

	$core->url->register('pubfeed',
		'feed',
		'^feed/(.+)$',
		array('urlPrivate','publicFeed')
	);
	
	#Trick.. 
	$core->url->register('xslt','feed/rss2/xslt','^feed/rss2/xslt$',array('urlPrivate','feedXslt'));

}
?>