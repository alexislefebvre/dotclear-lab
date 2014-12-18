<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of authorMode, a plugin for DotClear2.
#
# Copyright (c) 2003 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) exit;

$new_version = $core->plugins->moduleInfo('authorMode','version');
$cur_version = $core->getVersion('authorMode');
if (version_compare($cur_version,$new_version,'>=')) {
	return;
}

$core->blog->settings->addNameSpace('authormode');
if ($cur_version === null)
{
	$core->blog->settings->authormode->put('authormode_active',false,'boolean');
	$core->blog->settings->authormode->put('authormode_url_author','author','string');
	$core->blog->settings->authormode->put('authormode_url_authors','authors','string');
	$core->blog->settings->authormode->put('authormode_default_alpha_order',true,'boolean');
	$core->blog->settings->authormode->put('authormode_default_posts_only',true,'boolean');
}
elseif (version_compare($cur_version,'1.1','<='))
{
	$core->blog->settings->authormode->put('authormode_default_alpha_order',true,'boolean');
	$core->blog->settings->authormode->put('authormode_default_posts_only',true,'boolean');
}
$core->setVersion('authorMode',$new_version);
return true;