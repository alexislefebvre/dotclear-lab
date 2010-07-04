<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of construction, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { exit; }
 
$new_version = $core->plugins->moduleInfo('construction','version');

$current_version = $core->getVersion('construction');

if (version_compare($current_version,$new_version,'>=')) {
	return;
}

$core->blog->settings->addNamespace('construction');
$s =& $core->blog->settings->construction;

$s->put('construction_flag',
	false,
	'boolean',
	'Construction blog flag',
	true,
	true
);

$s->put('construction_allowed_ip',
	serialize(array('127.0.0.1')),
	'string',
	'Construction blog allowed ip',
	true,
	true
);

$s->put('construction_title',
	__('Work in progress'),
	'string',
	'Construction blog title',
	true,
	true
);

$s->put('construction_message',
	__('<p>The blog is currently under construction.</p>'),
	'string',
	'Construction blog message',
	true,
	true
);

$core->setVersion('construction',$new_version);
return true;
?>