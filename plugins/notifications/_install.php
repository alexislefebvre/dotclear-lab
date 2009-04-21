<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of notifications, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
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

$s = new dbStruct($core->con,$core->prefix);
 
$s->notification
	->notification_id('bigint',0,false)
	->user_id('varchar',32,true)
	->blog_id('varchar',32,false)
	->notification_type('varchar',255,false)
	->notification_msg('text',0,true)
	->notification_dt('timestamp',0,false)
	->notification_ip('varchar',255,true)
	;
$s->notification->primary('pk_notification','notification_id');
$s->notification->reference('fk_notification_blog','blog_id','blog','blog_id','cascade','cascade');
 
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

# Set config
$config['posts'] = false;
$config['categories'] = false;
$config['comments'] = false;
$config['trackbacks'] = false;
$config['404'] = false;
$config['sticky_new'] = false;
$config['sticky_upd'] = false;
$config['sticky_del'] = false;
$config['sticky_msg'] = false;
$config['sticky_err'] = false;
$config['position'] = 'top-right';
$config['display_time'] = 5;
$config['refresh_time'] = 10;
$config['autoclean'] = false;

$settings = new dcSettings($core,null);
$settings->setNamespace('notifications');
$settings->put('notifications_config',serialize($config),'string','notifications settings');

$core->setVersion('notifications',$m_version);

?>