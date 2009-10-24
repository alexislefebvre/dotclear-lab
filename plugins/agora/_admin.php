<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku , Tomtom and contributors
## Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminDashboardIcons','agora_dashboard');

function agora_dashboard(&$core,&$icons)
{
	$icons['agora'] = new ArrayObject(array(__('Agora'),'plugin.php?p=agora','index.php?pf=agora/icon.png'));
}

$_menu['Plugins']->addItem(__('Agora'),
		'plugin.php?p=agora','index.php?pf=agora/icon-small.png',
		preg_match('/plugin.php\?p=agora(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));

$core->auth->setPermissionType('member',__('is an agora member'));
$core->auth->setPermissionType('moderator',__('can moderate the agora'));

?>