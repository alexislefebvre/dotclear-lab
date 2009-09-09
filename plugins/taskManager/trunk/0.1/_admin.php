<?php
# -- BEGIN LICENSE BLOCK -------------------------
# This file is part of taskManager, a plugin for Dotclear.
#
# Copyright (c) 2009 Louis Cherel
# cherel.louis@gmail.com
#
# Licensed under the GPL version 3.0 license.
# See COPIRIGHT file or
# http://www.gnu.org/licenses/gpl-3.0.txt
# -- END LICENCE BlOC -----------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminDashboardIcons','taskManager_dashboard');
function taskManager_dashboard(&$core,&$icons)
{
	$icons['taskManager'] = new ArrayObject(array('Task Manager','plugin.php?p=taskManager','index.php?pf=taskManager/img/icon.big.png'));
}

$_menu['Plugins']->addItem(__('Task Manager'),'plugin.php?p=taskManager','index.php?pf=taskManager/img/icon.png',
                preg_match('/plugin.php\?p=taskManager(&.*)?$/',$_SERVER['REQUEST_URI']),
                $core->auth->check('usage,contentadmin',$core->blog->id));

require dirname(__FILE__).'/_widgets.php';
?>
