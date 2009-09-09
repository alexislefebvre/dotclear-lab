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
 
$core->addBehavior('initWidgets', array('taskManagerWidgetBehaviors','initWidgets'));
 
class taskManagerWidgetBehaviors {

	public static function initWidgets(&$w)
	{
		$w->create('taskManager',__('Task Manager'), array('tplTaskManager', 'taskManagerWidget'));
 
		$w->taskManager->setting('title',__('Title:'), 'Task Manager','text');
	}
}
?>