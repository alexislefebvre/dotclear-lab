<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Mystique Config plugin.
#
# Copyright (c) 2010 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# This file is hugely inspired from blowupConfig admin page
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }
global $core;
$version = $core->plugins->moduleInfo('mystiqueConfig','version');

if (version_compare($core->getVersion('mystiqueConfig'),$version,'>=')) {
	return;
}

$core->blog->settings->addNamespace('mystique');
$core->blog->settings->mystique->put('mystique_style','','string','Mystique custom style',true);
$core->blog->settings->mystique->put('mystique_layout','col-3','string','Mystique sidebars configuration',true);
$core->blog->settings->mystique->put('mystique_width_type','fixed','string','Mystique page layout',true);

$core->setVersion('mystiqueConfig',$version);

return true;

?>