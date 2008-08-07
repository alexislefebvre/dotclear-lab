<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(__('Aliases'),'plugin.php?p=alias','index.php?pf=alias/icon.png',
	preg_match('/plugin.php\?p=alias(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));

if (!isset($__resources['help']['alias'])) {
	$__resources['help']['alias'] = dirname(__FILE__).'/locales/en/help.html';
	
	if (file_exists(dirname(__FILE__).'/locales/'.$_lang.'/help.html')) {
		$__resources['help']['alias'] = dirname(__FILE__).'/locales/'.$_lang.'/help.html';
	}
}
?>