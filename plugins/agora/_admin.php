<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2012 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

if ($core->blog->settings->agora->agora_flag)
{
	$_menu['Blog']->addItem(__('Messages'),
		'plugin.php?p=agora&amp;act=messages',
		'index.php?pf=agora/icon-messages-16.png',
		(preg_match('/plugin.php\?p=agora&act=messages(.*)?$/',$_SERVER['REQUEST_URI'])),
		$core->auth->check('ussage,contentadmin',$core->blog->id));

	$p = array('user_status' => -1);
	$count = $core->agora->getUsers($p,true)->f(0);
	 if ($count > 0 ) {
		$label = sprintf(__('Public users <br/>(%s pending)'),$count);
	} else {
		$label = __('Public users');
	}

	$_menu['Blog']->addItem($label,
		'plugin.php?p=agora',
		'index.php?pf=agora/icon-users-16.png',
		preg_match('/plugin.php\?p=agora(.*)?$/',$_SERVER['REQUEST_URI'])
		&& (!preg_match('/(options|messages)/',$_SERVER['REQUEST_URI'])),
		$core->auth->check('admin',$core->blog->id));
}

$_menu['Plugins']->addItem(__('agora:config'),
	'plugin.php?p=agora&amp;act=options',
	'index.php?pf=agora/icon-16.png',
	preg_match('/plugin.php\?p=agora&act=options(.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));

$core->auth->setPermissionType('member',__('is an agora member'));

$core->addBehavior('adminDashboardFavs',array('agoraBehaviors','dashboardFavs'));

# Admin behaviors
$core->addBehavior('adminPostHeaders',array('agoraBehaviors','adminPostHeaders'));
$core->addBehavior('adminPostFormSidebar',array('agoraBehaviors','adminPostFormSidebar'));
//$core->addBehavior('adminPageForm',array('dcRevisionsBehaviors','adminPostForm'));
$core->addBehavior('adminPostsActions',array('agoraBehaviors','adminPostsActions'));
$core->addBehavior('adminPostsActionsCombo',array('agoraBehaviors','adminPostsActionsCombo'));

$core->addBehavior('adminAfterPostCreate',array('agoraBehaviors','adminBeforePostUpdate'));
$core->addBehavior('adminBeforePostUpdate',array('agoraBehaviors','adminBeforePostUpdate'));
$core->addBehavior('adminAfterPageCreate',array('agoraBehaviors','adminBeforePostUpdate'));
$core->addBehavior('adminBeforePageUpdate',array('agoraBehaviors','adminBeforePostUpdate'));

# Import/Export behaviors : message table
 $core->addBehavior('exportFull',array('agoraBehaviors','exportFull'));
 $core->addBehavior('exportSingle',array('agoraBehaviors','exportSingle'));
 $core->addBehavior('importInit',array('agoraBehaviors','importInit'));
 $core->addBehavior('importFull',array('agoraBehaviors','importFull'));
 $core->addBehavior('importSingle',array('agoraBehaviors','importSingle'));

# Rest methods
$core->rest->addFunction('getMessageById',array('agoraRestMethods','getMessageById'));
?>
