<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of webOptimizer,
# a plugin for DotClear2.
#
# Copyright (c) 2008 Peck and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$GLOBALS['__autoload']['dcWebOptimizer'] = dirname(__FILE__).'/class.dc.weboptimizer.php';

$_menu['Plugins']->addItem(__('webOptimizer'),'plugin.php?p=webOptimizer','index.php?pf=webOptimizer/icon.png',
	preg_match('/plugin.php\?p=webOptimizer(&.*)?$/',$_SERVER['REQUEST_URI']));
