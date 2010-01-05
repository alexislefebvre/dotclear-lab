<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcOpenSearch, a plugin for Dotclear.
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
		/* Name */			"dcOpenSearch",
		/* Description*/		"Add an advanced search on your blog (posts, pages, comments, medias, meta and other)",
		/* Author */			"Tomtom (http://blog.zenstyle.fr/)",
		/* Version */			'1.1.2',
		/* Permissions */		'usage,contentadmin',
		/* Priority */			20
);

?>