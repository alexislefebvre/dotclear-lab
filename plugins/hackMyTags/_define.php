<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of hackMyTags,
# a plugin for DotClear2.
#
# Copyright (c) 2008 Bruno Hondelatte and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */				"HackMyTags",
	/* Description*/		"Enable to tune tags attributes in templates, without modifying the template files",
	/* Author */			"Bruno Hondelatte",
	/* Version */			"0.1",
	/* Permissions */		null,
	/* Priority */ 			1
);
?>