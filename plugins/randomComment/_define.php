<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of randomComment, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zesntyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
		/* Name */			'randomComment',
		/* Description */		'Display random comments on your blog',
		/* Author */			'Tomtom (http://plugins.zenstyle.fr/)',
		/* Version */			'1.0.2',
		/* Premission */		'usage,contentadmin'
);

?>