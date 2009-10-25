<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of allBlogs, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Philippe Amalgame and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			"All Blogs",
	/* Description*/		"Display a filtered list of blogs from a multiblog . Can be an unordered list or a drop-down menu. ",
	/* Author */			"Philippe",
	/* Version */			'2.0',
	/* Permissions */		'usage, contentadmin'
);
?>