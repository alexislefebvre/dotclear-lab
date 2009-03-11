<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Private mode, a plugin for Dotclear.
# 
# Copyright (c) 2008, 2009 Osku
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__).'/_widgets.php';

if ($core->blog->settings->private_flag)
{
	$privatefeed = md5($core->blog->settings->blog_private_pwd);
	$core->url->register('feed',sprintf('%s-feed',$privatefeed),sprintf('^%s-feed/(.+)$',$privatefeed),array('urlPrivate','privateFeed'));
}
?>
