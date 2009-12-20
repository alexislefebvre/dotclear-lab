<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dcHistory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$__autoload['diff']			= dirname(__FILE__).'/lib/lib.diff.php';
$__autoload['uDiff']		= dirname(__FILE__).'/lib/lib.unified.diff.php';
$__autoload['dcHistory']		= dirname(__FILE__).'/inc/class.dc.history.php';

$core->history = new dcHistory($core);
?>