<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of plugin feedburner for Dotclear 2.
# Copyright (c) 2008 Thomas Bouron.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

global $__autoload;

$__autoload['feedburner'] = dirname(__FILE__).'/inc/class.feedburner.php';
$__autoload['feedburnerUi'] = dirname(__FILE__).'/inc/lib.feedburner.ui.php';
$__autoload['feedburnerReader'] = dirname(__FILE__).'/inc/class.feedburner.reader.php';
$__autoload['feedburnerParser'] = dirname(__FILE__).'/inc/class.feedburner.parser.php';

?>
