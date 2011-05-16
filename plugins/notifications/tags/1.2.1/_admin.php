<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of notifications, a plugin for Dotclear.
# 
# Copyright (c) 2009-2011 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(__('Notifications'),'plugin.php?p=notifications','index.php?pf=notifications/icon.png',
		preg_match('/plugin.php\?p=notifications(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));

$rs = $core->log->getLogs(array('log_table' => 'notifications','user_id' => $core->auth->userID()));

if ($rs->isEmpty()) {
	$cur = $core->con->openCursor($core->prefix.'log');
	$cur->user_id = $core->auth->userID();
	$cur->log_table = 'notifications';
	$cur->log_msg = __('Last notification update');
	
	$core->log->addLog($cur);
}

?>