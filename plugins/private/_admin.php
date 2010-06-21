<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Private mode, a plugin for Dotclear 2.
# 
# Copyright (c) 2008-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(__('Private mode'),
	'plugin.php?p=private','index.php?pf=private/icon.png',
	preg_match('/plugin.php\?p=private(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id)
);

$s = privateSettings($core);

if ($s->private_flag)
{
	$core->addBehavior('adminPageHTMLHead','privateadminPageHTMLHead');
	$core->addBehavior('adminDashboardItems', 'privateDashboardItems'); 
}

function privateDashboardItems($core,$__dashboard_items)
{
	$__dashboard_items[0][] = '<p class="private-msg">'.__('Password-protected blog').'.</p>';
}

function privateadminPageHTMLHead()
{
	echo '<link rel="stylesheet" href="index.php?pf=private/style/admin.css"type="text/css" media="screen" />'."\n";
}
?>