<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcCron, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
		/* Name */			"dcCron",
		/* Description*/		"Schedule any tasks",
		/* Author */			"Tomtom (http://blog.zenstyle.fr)",
		/* Version */			'1.1.8',
		/* Permissions */		'admin',
							null,
		/* Priority */			10000
);

?>