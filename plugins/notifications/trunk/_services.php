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

class notificationsRestMethods
{
	public static function getNotifications()
	{
		global $core;
		
		$params = array();
		$notifications = new notifications($core);
		$components = $notifications->getComponents();
		$disabled_components = unserialize($core->blog->settings->notifications->disabled_components);
		$dt_pattern = sprintf(__('%s at %s'),$core->blog->settings->system->date_format,$core->blog->settings->system->time_format);
		
		foreach ($components as $id => $component) {
			if (array_key_exists($id,$disabled_components)) {
				unset($components[$id]);
			}
		}
		
		# For classique users (means not super admins nor admins)
		if (!$core->auth->check('admin',$core->blog->id)) {
			$sql = array();
			foreach ($components as $id => $component)
			{
				$sql[] = sprintf(
					"(notification_component = '%s' AND (%s))",
					$id,
					implode(' OR ',
						array_map(
							create_function('$a','return sprintf("notification_type = \'%s\'",$a);'),
							$notifications->getPermissionsTypes($id)
						)
					)
				);
			}
			$params['sql'] = 'AND ('.implode(' OR ',$sql).')';
			
		}
		# For super admins & admins 
		else {
			# For super admins
			if ($core->auth->isSuperAdmin() && $core->blog->settings->notifications->display_all) {
				$params['blog_id'] = 'all';
			}
			$params['notification_component'] = array_keys($components);
		}
		
		
		$rs = $notifications->getNotifications($params);
		
		$rsp = new xmlTag();
		
		while ($rs->fetch()) {
			$msg = sprintf(__('By %s on %s'),$rs->user_id,dt::dt2str($dt_pattern,$rs->notification_dt));
			$notification = new xmlTag('notification');
			$notification->insertAttr('id',$rs->notification_id);
			$notification->insertAttr('type',$rs->notification_type);
			$notification->insertAttr('component',$rs->notification_component);
			$notification->insertAttr('header',$rs->notification_msg);
			$notification->insertAttr('message',$msg);
			$rsp->insertNode($notification);
		}
		
		//notificationsBehaviors::update($core,(!$rs->isEmpty() ? strtotime($rs->notification_dt) : ''));
		
		return $rsp;
	}
}

?>