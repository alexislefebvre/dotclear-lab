<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Private mode, a plugin for Dotclear 2.
# 
# Copyright (c) 2008-2010Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }
 
$m_version = $core->plugins->moduleInfo('private','version');
 
$i_version = $core->getVersion('private');
 
if (version_compare($i_version,$m_version,'>=')) {
     return;
}

$core->blog->settings->setNamespace('private');
$s =& $core->blog->settings;

$s->put('private_flag',false,'boolean','Protect your blog with a password',true,true);
$s->put('private_conauto',false,'boolean','Allow automatic connection',true,true);
$s->put('blog_off_page_title',__('Private blog'),'string','Private page title',true,true);
$s->put('blog_off_msg',__('<p class="message">You need the password to view this blog.</p>'),'string','Private message',true,true);

$core->setVersion('private',$m_version);
return true;
?>