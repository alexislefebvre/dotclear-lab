<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of smiliesEditor, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

if (!isset($__resources['help']['smiliesEditor'])) {
	$__resources['help']['smiliesEditor'] = dirname(__FILE__).'/help.html';
}

$core->addBehavior('adminCurrentThemeDetails','smilies_editor_details');

function smilies_editor_details($core,$id)
{
	if ($id != 'default' && $core->auth->isSuperAdmin()) {
		return '<p><a href="plugin.php?p=smiliesEditor" class="button">'.__('Smilies Editor').'</a></p>';
	}
}
?>
