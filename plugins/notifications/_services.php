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

class notificationsRestMethods
{
	public static function getNotifications()
	{
		global $core;

		$config = $core->blog->notifications->getConfig();

		$dt_pattern = sprintf(__('%s at %s'),$core->blog->settings->date_format,$core->blog->settings->time_format);

		$strReq = 
		'SELECT N.notification_id, N.user_id, N.notification_type, '.
		'N.notification_msg, N.notification_ip, N.notification_dt '.
		'FROM '.$core->prefix.'notification N '.
		'WHERE N.notification_dt > ('.
		'SELECT MAX(L.log_dt) FROM '.$core->prefix.'log L '.
		"WHERE '".$core->auth->userID()."' = L.user_id AND L.log_table = '".$core->prefix."notifications') ".
		"AND N.blog_id = '".$core->blog->id."'";

		$rs = $core->con->select($strReq);

		$rsp = new xmlTag();

		while ($rs->fetch()) {
			$msg = sprintf(__('By %s on %s'),$rs->user_id,dt::dt2str($dt_pattern,$rs->notification_dt));

			$notification = new xmlTag('notification');
			$notification->insertAttr('id',$rs->notification_id);
			$notification->insertAttr('class',$rs->notification_type);
			$notification->insertAttr('header',$rs->notification_msg);
			$notification->insertAttr('msg',$msg);
			$notification->insertAttr('position',$config['position']);
			$notification->insertAttr('life',$config['display_time']*1000);
			$notification->insertAttr('sticky',($config['sticky_'.$rs->notification_type] ? 'true' : 'false'));
			
			$rsp->insertNode($notification);
		}

		notificationsBehaviors::update($core,(!$rs->isEmpty() ? strtotime($rs->notification_dt) : ''));

		return $rsp;
	}
}

?>