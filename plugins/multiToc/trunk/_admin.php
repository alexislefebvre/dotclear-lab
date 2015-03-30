<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiToc, a plugin for Dotclear.
# 
# Copyright (c) 2009-2015 Tomtom and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminPostHeaders',array('multiTocBehaviors','postHeaders'));
$core->addBehavior('adminPageHeaders',array('multiTocBehaviors','postHeaders'));

$_menu['Blog']->addItem(
	__('Tables of content'),
	'plugin.php?p=multiToc',
	'index.php?pf=multiToc/icon.png',
	preg_match('/plugin.php\?p=multiToc(&.*)?$/',
	$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id)
);

$core->addBehavior('adminDashboardFavorites','multiTocDashboardFavorites');

function multiTocDashboardFavorites($core,$favs)
{
	$favs->register('multiToc', array(
		'title' => __('Tables of content'),
		'url' => 'plugin.php?p=multiToc',
		'small-icon' => 'index.php?pf=multiToc/icon.png',
		'large-icon' => 'index.php?pf=multiToc/icon-big.png',
		'permissions' => 'usage,contentadmin'
	));
}