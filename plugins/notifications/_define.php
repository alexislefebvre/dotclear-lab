<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of notifications, a plugin for Dotclear.
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
		/* Name */			'notifications',
		/* Description */		'Displays few notifications on administration pages',
		/* Author */			'Tomtom (http://blog.zenstyle.fr/)',
		/* Version */			'0.6',
		/* Permissions */		'usage',
							null,
		/* Priority */			10000
);

?>