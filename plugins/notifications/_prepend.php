<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of notifications, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zesntyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

global $__autoload;

$__autoload['notificationsBehaviors'] = dirname(__FILE__).'/inc/class.notifications.behaviors.php';
$__autoload['notifications'] = dirname(__FILE__).'/inc/class.notifications.php';
$__autoload['notificationsRestMethods'] = dirname(__FILE__).'/_services.php';

$core->blog->notifications = new notifications($core);

# Notification behaviors
$core->addBehavior('adminAfterPostCreate',array('notificationsBehaviors','postCreate'));
$core->addBehavior('adminAfterPostUpdate',array('notificationsBehaviors','postUpdate'));
$core->addBehavior('adminBeforePostDelete',array('notificationsBehaviors','postDelete'));
$core->addBehavior('adminAfterCategoryCreate',array('notificationsBehaviors','categoryCreate'));
$core->addBehavior('adminAfterCategoryUpdate',array('notificationsBehaviors','categoryUpdate'));
$core->addBehavior('coreAfterCommentCreate',array('notificationsBehaviors','commentCreate'));
$core->addBehavior('coreAfterCommentUpdate',array('notificationsBehaviors','commentUpdate'));
$core->addBehavior('publicAfterTrackbackCreate',array('notificationsBehaviors','trackback'));
$core->addBehavior('publicHeadContent',array('notificationsBehaviors','p404'));
# Plugin behaviors
$core->addBehavior('adminDashboardHeaders',array('notificationsBehaviors','headers'));
$core->addBehavior('adminDashboardItems',array('notificationsBehaviors','clean'));
# Export behaviors
$core->addBehavior('exportFull',array('communityBehaviors','exportFull'));
$core->addBehavior('exportSingle',array('communityBehaviors','exportSingle'));

$core->rest->addFunction('getNotifications',array('notificationsRestMethods','getNotifications'));


?>