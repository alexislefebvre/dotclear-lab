<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Grumph,
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

$GLOBALS['__autoload']['dcGrumph'] = dirname(__FILE__).'/class.grumph.php';

$core->grumph = new dcGrumph($core);

?>
