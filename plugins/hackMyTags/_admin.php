<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of hackMyTags,
# a plugin for DotClear2.
#
# Copyright (c) 2008 Bruno Hondelatte and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$GLOBALS['__autoload']['dcHackMyTags'] = dirname(__FILE__).'/class.dc.hackmytags.php';

$_menu['Plugins']->addItem(__('HackMyTags'),'plugin.php?p=hackMyTags','index.php?pf=hackMyTags/icon.png',
	preg_match('/plugin.php\?p=hackMyTags(&.*)?$/',$_SERVER['REQUEST_URI']));
?>
