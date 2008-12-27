<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of pluginBootstrap,
# a plugin for DotClear2.
#
# Copyright (c) 2008 Vincent Garnier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$GLOBALS['__autoload']['pluginBootstrap'] = dirname(__FILE__).'/class.dc.plugin.bootstrap.php';
$GLOBALS['__autoload']['bsText'] = dirname(__FILE__).'/lib.bstext.php';

$_menu['Plugins']->addItem(__('Plugin Bootstrap'),'plugin.php?p=pluginBootstrap','index.php?pf=pluginBootstrap/icon.png',
	preg_match('/plugin.php\?p=pluginBootstrap(&.*)?$/',$_SERVER['REQUEST_URI']));
