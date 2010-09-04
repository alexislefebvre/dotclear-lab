<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of muppet, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$my_types = muppet::getPostTypes();

if (!empty($my_types))
{
	$_menu['Muppet'] = new dcMenu('muppet-menu',__('Content'));
	foreach ($my_types as $k => $v)
	{
		$plural = empty($v['plural']) ? $v['name'].'s' : $v['plural'];
		$pattern = '/p=muppet&type='.$k.'.*?$/';
		$_menu['Muppet']->addItem(ucfirst($plural),
				'plugin.php?p=muppet&amp;type='.$k.'&amp;list=all',
				'index.php?pf=muppet/img/'.$v['icon'],
				preg_match($pattern,$_SERVER['REQUEST_URI']),
				$core->auth->check('contentadmin,'.$v['perm'],$core->blog->id));
		$core->auth->setPermissionType($v['perm'],sprintf(__('manage the %s'),$plural));
	}

	// JS for Menu Content
	$core->addBehavior('adminPageHTMLHead',array('muppetBehaviors','adminPageHTMLHead'));
}

$core->addBehavior('adminPostsActionsCombo',array('muppetBehaviors','adminPostsActionsCombo'));
$core->addBehavior('adminPostsActions',array('muppetBehaviors','adminPostsActions'));
$core->addBehavior('adminPostsActionsContent',array('muppetBehaviors','adminPostsActionsContent'));
$core->addBehavior('adminPagesActionsCombo',array('muppetBehaviors','adminPostsActionsCombo'));
$core->addBehavior('adminPagesActions',array('muppetBehaviors','adminPostsActions'));
$core->addBehavior('adminPagesActionsContent',array('muppetBehaviors','adminPostsActionsContent'));

$_menu['Plugins']->addItem(__('My types'),'plugin.php?p=muppet','index.php?pf=muppet/icon.png',
	(preg_match('/plugin.php\?p=muppet(&.*)?/',$_SERVER['REQUEST_URI']))
	&& (!preg_match('/type/',$_SERVER['REQUEST_URI'])),
	$core->auth->check('contentadmin',$core->blog->id));


?>