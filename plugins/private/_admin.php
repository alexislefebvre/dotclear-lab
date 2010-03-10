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
		$core->auth->check('admin',$core->blog->id));
		
if ($core->blog->settings->private_flag)
{
	$core->addBehavior('adminPageHTMLHead','privateadminPageHTMLHead');
	$core->addBehavior('adminDashboardItems', 'privateDashboardItems'); 
}

function privateDashboardItems($core,$__dashboard_items)
{
	$__dashboard_items[1][] = '<p class="private-msg">'.__('Private blog').'.</p>';
}

function privateadminPageHTMLHead()
{
	echo '  <style type="text/css">'."\n".'  @import "index.php?pf=private/css/admin.css";'."\n".'  </style>'."\n";
}
?>