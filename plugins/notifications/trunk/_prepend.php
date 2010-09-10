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

$__autoload['notificationsBehaviors'] = dirname(__FILE__).'/inc/class.notifications.behaviors.php';
$__autoload['notifications'] = dirname(__FILE__).'/inc/class.notifications.php';
$__autoload['notificationsRestMethods'] = dirname(__FILE__).'/_services.php';

# Notification behaviors
$core->addBehavior('notificationsRegister',array('notificationsBehaviors','registerComponents'));
$core->addBehavior('adminAfterPostCreate',array('notificationsBehaviors','postCreate'));
$core->addBehavior('adminAfterPostUpdate',array('notificationsBehaviors','postUpdate'));
$core->addBehavior('adminBeforePostDelete',array('notificationsBehaviors','postDelete'));
$core->addBehavior('adminAfterCategoryCreate',array('notificationsBehaviors','categoryCreate'));
$core->addBehavior('adminAfterCategoryUpdate',array('notificationsBehaviors','categoryUpdate'));
$core->addBehavior('coreAfterCommentCreate',array('notificationsBehaviors','commentCreate'));
$core->addBehavior('coreAfterCommentUpdate',array('notificationsBehaviors','commentUpdate'));
$core->addBehavior('publicAfterTrackbackCreate',array('notificationsBehaviors','trackback'));
//$core->addBehavior('publicHeadContent',array('notificationsBehaviors','p404'));
# Plugin behaviors
$core->addBehavior('adminPageHTMLHead',array('notificationsBehaviors','adminPageHTMLHead'));
//$core->addBehavior('adminDashboardHeaders',array('notificationsBehaviors','clean'));
$core->addBehavior('adminPreferencesForm',array('notificationsBehaviors','adminUserForm'));
$core->addBehavior('adminUserForm',array('notificationsBehaviors','adminUserForm'));
$core->addBehavior('adminBeforeUserCreate',array('notificationsBehaviors','setUserNotifications'));
$core->addBehavior('adminBeforeUserUpdate',array('notificationsBehaviors','setUserNotifications'));
# Export behaviors
$core->addBehavior('exportFull',array('notificationsBehaviors','exportFull'));
$core->addBehavior('exportSingle',array('notificationsBehaviors','exportSingle'));

$core->rest->addFunction('getNotifications',array('notificationsRestMethods','getNotifications'));

?>