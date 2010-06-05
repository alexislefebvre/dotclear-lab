<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of lastBlogUpdate, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }
 
$this->registerModule(
	/* Name */			"lastBlogUpdate",
	/* Description*/		"Show the dates of last updates of your blog in a widget",
	/* Author */			"JC Denis",
	/* Version */			'0.5',
	/* Permissions */		'usage,contentadmin'
);
	/* date */		#20100605
?>