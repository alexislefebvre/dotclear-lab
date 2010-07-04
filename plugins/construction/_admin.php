<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of construction, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->blog->settings->addNamespace('construction');
$menu_class = '';

if ($core->blog->settings->construction->construction_flag)
{
	$core->addBehavior('adminPageHTMLHead','constructionadminPageHTMLHead');
	$menu_class = 'construction-blog';
}

$_menu['Plugins']->addItem(__('Construction'),
	'plugin.php?p=construction','index.php?pf=construction/icon.png',
	preg_match('/plugin.php\?p=construction(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id),
	$menu_class
);

function constructionadminPageHTMLHead()
{
	echo '<style type="text/css">'."\n".'@import "index.php?pf=construction/css/admin.css";'."\n".'</style>'."\n";
}
?>