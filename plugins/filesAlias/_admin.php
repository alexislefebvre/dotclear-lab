<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of filesAlias, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(__('Medias sharing'),'plugin.php?p=filesAlias','index.php?pf=filesAlias/icon.png',
	preg_match('/plugin.php\?p=filesAlias(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));

if (!isset($__resources['help']['filesAlias'])) {
	$__resources['help']['filesAlias'] = dirname(__FILE__).'/locales/en/help.html';
	
	if (file_exists(dirname(__FILE__).'/locales/'.$_lang.'/help.html')) {
		$__resources['help']['filesAlias'] = dirname(__FILE__).'/locales/'.$_lang.'/help.html';
	}
}
?>