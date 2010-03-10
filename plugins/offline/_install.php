<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Offline mode, a plugin for Dotclear 2.
# 
# Copyright (c) 2008-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }
 
$m_version = $core->plugins->moduleInfo('offline','version');
 
$i_version = $core->getVersion('offline');
 
if (version_compare($i_version,$m_version,'>=')) {
     return;
}

$core->blog->settings->setNamespace('offline');
$s =& $core->blog->settings;

$s->put('blog_off_flag',false,'boolean',true,true);
$s->put('blog_off_ip_ok','','string','Authorized IP',true,true);
$s->put('blog_off_page_title',__('Maintenance'),'string','Maintenance page title',true,true);
$s->put('blog_off_msg',__('<p class="message">D\'oh! The blog is offline.</p>'),'string','Maintenance message',true,true);

$core->setVersion('offline',$m_version);
return true;
?>