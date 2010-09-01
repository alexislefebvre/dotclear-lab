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

if (!defined('DC_CONTEXT_ADMIN')) { exit; }
 
$new_version = $core->plugins->moduleInfo('private','version');
$current_version = $core->getVersion('private');
 
if (version_compare($current_version,$new_version,'>=')) {
     return;
}

$s = $core->blog->settings->private;

$s->put('private_flag',
	false,
	'boolean',
	'Private mode activation flag',
	true,true
);
	
$s->put('private_conauto_flag',
	false,
	'boolean',
	'Private mode automatic connection option',
	true,true
);

$s->put('message',
	__('<h2>Private blog</h2><p class="message">You need the password to view this blog.</p>'),
	'string',
	'Private mode public welcome message',
	true,true
);

$core->setVersion('private',$new_version);
return true;
?>