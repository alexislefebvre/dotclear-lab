<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of contextualMenu, a plugin for Dotclear.
# 
# Copyright (c) 2008 Frédéric Leroy
# bestofrisk@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

require dirname(__FILE__).'/_widgets.php';

if (isset($__dashboard_icons) && $core->auth->check('contextualMenu',$core->blog->id)) {
	$__dashboard_icons[] = array(__('Contextual Menu'),'plugin.php?p=contextualMenu','index.php?pf=contextualMenu/icon.png');
}

$_menu['Plugins']->addItem('Contextual Menu','plugin.php?p=contextualMenu','index.php?pf=contextualMenu/icon-small.png',
                preg_match('/plugin.php\?p=contextualMenu(&.*)?$/',$_SERVER['REQUEST_URI']),
                $core->auth->check('usage,contentadmin',$core->blog->id));

$core->auth->setPermissionType('contextualMenu',__('manage menu'));

if (!isset($__resources['help']['contextualMenu'])) {
	$__resources['help']['contextualMenu'] = dirname(__FILE__).'/locales/fr/help.html';
	
		if (file_exists(dirname(__FILE__).'/locales/'.$_lang.'/help.html')) {
		$__resources['help']['contextualMenu'] = dirname(__FILE__).'/locales/'.$_lang.'/help.html';
	}
}

?>