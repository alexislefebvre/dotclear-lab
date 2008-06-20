<?php
# -- BEGIN LICENSE BLOCK ---------------------------------
#
# This file is part of Antiflood,a spam filter for Dotclear 2.
#
# Copyright (c) 2003-2008 dcTeam and contributors. All rights reserved
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ---------------------------------- */

	global $__autoload, $core;
	$__autoload['dcFilterAntiFlood'] = dirname(__FILE__).'/class.dc.filter.antiflood.php';
	$core->spamfilters[] = 'dcFilterAntiFlood';
?>
