<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Offline mode, a plugin for Dotclear 2.
# 
# Copyright (c) 2008-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(__('Offline mode'),
		'plugin.php?p=offline','index.php?pf=offline/icon.png',
		preg_match('/plugin.php\?p=offline(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));
		
if ($core->blog->settings->blog_off_flag)
{
	$core->addBehavior('adminPageHTMLHead','offlineadminPageHTMLHead');
	$core->addBehavior('adminDashboardItems', 'offlineDashboardItems'); 
}

function offlineDashboardItems($core,$__dashboard_items)
{
	$__dashboard_items[1][] = '<p class="offlline-msg">'.__('Offline mode active.').'</p>';
}

function offlineadminPageHTMLHead()
{
	echo '  <style type="text/css">'."\n".'  @import "index.php?pf=offline/css/admin.css";'."\n".'  </style>'."\n";
}

?>