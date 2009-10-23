<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcCron, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['System']->addItem(__('Cron'),'plugin.php?p=dcCron','index.php?pf=dcCron/icon.png',
		preg_match('/plugin.php\?p=dcCron(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('usage,contentadmin',$core->blog->id));

?>