<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of authorMode, a plugin for DotClear2.
#
# Copyright (c) 2003 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			"authorMode",
	/* Description*/		"Post entries per author + author desc handling",
	/* Author */			"xave, Pierre Van Glabeke",
	/* Version */			'1.7.1',
	/* Properties */
	array(
		'permissions' => 'admin,contentadmin',
		'type' => 'plugin',
		'dc_min' => '2.7',
		'support' => 'http://forum.dotclear.org/viewtopic.php?pid=323173#p323173',
		'details' => 'http://plugins.dotaddict.org/dc2/details/authorMode'
		)
);