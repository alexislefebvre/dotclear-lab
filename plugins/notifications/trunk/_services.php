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
		$dt_pattern = sprintf(__('%s at %s'),$core->blog->settings->system->date_format,$core->blog->settings->system->time_format);
		
		foreach ($components as $id => $component) {
			if ($component['disabled']) {
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
							$notifications->getPermissionsTypes($id,true)
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
		
		if (!$rs->isEmpty()) {
			notificationsRestMethods::writeLog();
		}
		
		return $rsp;
	}
	
	public static function writeLog()
	{
		global $core;
		
		$params = array();
		$params['user_id'] = $core->auth->userID();
		# For admins and super admins
		if (
			($core->auth->check('admin',$core->blog->id) || $core->auth->isSuperAdmin()) &&
			$core->blog->settings->notifications->display_all
		) {
			$params['blog_id'] = 'all';
		}
		$params['log_table'] = 'notifications';
		$params['limit'] = 1;
		
		$rs = $core->log->getLogs($params);
		
		if (!$rs->isEmpty()) {
			$core->log->delLogs($rs->log_id);	
		}
		
		$cur				= $core->con->openCursor($core->prefix.'log');
		$cur->user_id		= $core->auth->userID();
		$cur->log_table	= 'notifications';
		$cur->log_msg		= __('Last notification update');
		
		$core->log->addLog($cur);
	}
}

?>