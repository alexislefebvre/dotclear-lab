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
	/* Version */			'1.6.1',
	/* Permissions */		'admin,contentadmin'
);
?>