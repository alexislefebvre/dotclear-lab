<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of ExpAt,
# a plugin for DotClear2.
#
# Copyright (c) 2010 Bruno Hondelatte and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$GLOBALS['__autoload']['expatParser'] = dirname(__FILE__).'/class.expatparser.php';
$GLOBALS['__autoload']['expatDict'] = dirname(__FILE__).'/class.expatdict.php';
$GLOBALS['__autoload']['expatEngine'] = dirname(__FILE__).'/class.expatengine.php';
$GLOBALS['__autoload']['parse_engine'] = dirname(__FILE__).'/parse_engine.php';

?>