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

# Plugin behaviors
$core->addBehavior('notificationsRegister',array('notificationsBehaviors','registerComponents'));
$core->addBehavior('notificationsSender',array('notificationsBehaviors','sendNotifications'));
$core->addBehavior('adminPageHTMLHead',array('notificationsBehaviors','adminPageHTMLHead'));
$core->addBehavior('loginPageHTMLHead',array('notificationsBehaviors','autoClean'));
$core->addBehavior('adminPreferencesForm',array('notificationsBehaviors','adminUserForm'));
$core->addBehavior('adminUserForm',array('notificationsBehaviors','adminUserForm'));
$core->addBehavior('adminBeforeUserCreate',array('notificationsBehaviors','setUserNotifications'));
$core->addBehavior('adminBeforeUserUpdate',array('notificationsBehaviors','setUserNotifications'));

# Export behaviors
$core->addBehavior('exportFull',array('notificationsBehaviors','exportFull'));
$core->addBehavior('exportSingle',array('notificationsBehaviors','exportSingle'));

# Notification behaviors
# Posts
$core->addBehavior('adminAfterPostCreate',array('notificationsBehaviors','adminAfterPostCreate'));
$core->addBehavior('adminAfterPostUpdate',array('notificationsBehaviors','adminAfterPostUpdate'));
$core->addBehavior('adminBeforePostDelete',array('notificationsBehaviors','adminBeforePostDelete'));
# Pages
$core->addBehavior('adminAfterPageCreate',array('notificationsBehaviors','adminAfterPageCreate'));
$core->addBehavior('adminAfterPageUpdate',array('notificationsBehaviors','adminAfterPageUpdate'));
$core->addBehavior('adminBeforePageDelete',array('notificationsBehaviors','adminBeforePageDelete'));
# Categories
$core->addBehavior('adminAfterCategoryCreate',array('notificationsBehaviors','adminAfterCategoryCreate'));
$core->addBehavior('adminAfterCategoryUpdate',array('notificationsBehaviors','adminAfterCategoryUpdate'));
# Comments / Trackbacks
$core->addBehavior('coreAfterCommentCreate',array('notificationsBehaviors','coreAfterCommentCreate'));
$core->addBehavior('coreAfterCommentUpdate',array('notificationsBehaviors','coreAfterCommentUpdate'));
$core->addBehavior('publicAfterTrackbackCreate',array('notificationsBehaviors','publicAfterTrackbackCreate'));
# System
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('notificationsBehaviors','adminBeforeBlogSettingsUpdate'));
$core->addBehavior('themeAfterDelete',array('notificationsBehaviors','themeAfterDelete'));
$core->addBehavior('pluginsAfterDelete',array('notificationsBehaviors','pluginsAfterDelete'));
$core->addBehavior('adminAfterUserCreate',array('notificationsBehaviors','adminAfterUserCreate'));
$core->addBehavior('adminAfterUserUpdate',array('notificationsBehaviors','adminAfterUserUpdate'));
$core->addBehavior('adminBeforeUserDelete',array('notificationsBehaviors','adminBeforeUserDelete'));

# Ajax service
$core->rest->addFunction('getNotifications',array('notificationsRestMethods','getNotifications'));

?>