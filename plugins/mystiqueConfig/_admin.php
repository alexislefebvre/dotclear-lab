<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Mystique Config plugin.
#
# Copyright (c) 2010 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminCurrentThemeDetails','mystique_config_details');

if (!isset($__resources['help']['mystiqueConfig'])) {
	$__resources['help']['mystiqueConfig'] = dirname(__FILE__).'/help.html';
}

function mystique_config_details($core,$id)
{
	if ($id == 'mystique' && $core->auth->check('admin',$core->blog->id)) {
		return '<p><a href="plugin.php?p=mystiqueConfig" class="button">'.__('Theme configuration').'</a></p>';
	}
}
?>
