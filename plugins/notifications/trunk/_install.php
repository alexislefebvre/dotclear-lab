<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of notifications, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }
 
$m_version = $core->plugins->moduleInfo('notifications','version');
 
$i_version = $core->getVersion('notifications');
 
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# Init DB schema
$s = new dbStruct($core->con,$core->prefix);
	
$s->notification
	->notification_id('bigint',0,false)
	->user_id('varchar',32,true)
	->blog_id('varchar',32,false)
	->notification_component('varchar',255,false)
	->notification_type('varchar',255,false)
	->notification_msg('text',0,true)
	->notification_dt('timestamp',0,false)
	->notification_ip('varchar',255,true)
	;
$s->notification->primary('pk_notification','notification_id');
$s->notification->reference('fk_notification_blog','blog_id','blog','blog_id','cascade','cascade');

$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

# Init config
$core->blog->settings->addNamespace('notifications');
$core->blog->settings->notifications->put('enable',false,'boolean','Enabled notifications flag',false,true);
$core->blog->settings->notifications->put('sticky',false,'boolean','Sticky notifications flag',false,true);
$core->blog->settings->notifications->put('display_all',false,'boolean','Display all notifications flag',false,true);
$core->blog->settings->notifications->put('position','top-right','string','Notifications position',false,true);
$core->blog->settings->notifications->put('display_time',5,'integer','Time of notification display',false,true);
$core->blog->settings->notifications->put('refresh_time',10,'integer','Time between each refresh',false,true);
$core->blog->settings->notifications->put('auto_clean',false,'boolean','Auto clean notifications flag',false,true);
$core->blog->settings->notifications->put('disabled_components',serialize(array()),'string','Disabled components',false,true);
$core->blog->settings->notifications->put('permissions',serialize(array()),'string','Display permissions by components',false,true);

$core->setVersion('notifications',$m_version);
return true;

?>