<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Carnaval a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Me and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$__autoload['dcCarnaval'] = dirname(__FILE__).'/inc/class.dc.carnaval.php';
$__autoload['carnavalConfig'] = dirname(__FILE__).'/inc/class.carnaval.config.php';

$carnaval = new dcCarnaval ($core->blog);
?>
