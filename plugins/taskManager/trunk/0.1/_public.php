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

if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__).'/_widgets.php';

$core->tpl->addValue('TaskManager',array('tplTaskManager','taskManagerWidget'));


class tplTaskManager
{
	public static function taskManagerWidget(&$w)
	{
		$taskManager = new DcTaskManager();
		return '<p style="text-align:center;"><strong>'.$w->title.'</strong></p>' . $taskManager->showCSSandTasks();
	}
}
?>