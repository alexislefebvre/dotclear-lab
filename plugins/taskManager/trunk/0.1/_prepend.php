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

$GLOBALS['__autoload']['DcTaskManager'] = dirname(__FILE__).'/inc/class.dc.taskManager.php';
$GLOBALS['__autoload']['View'] = dirname(__FILE__).'/inc/class.view.php';
?>