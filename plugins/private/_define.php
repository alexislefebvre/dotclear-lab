<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Private mode, a plugin for Dotclear.
# 
# Copyright (c) 2008, 2009 Osku
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */		"Private mode",
	/* Description*/	"Protect your blog with a password",
	/* Author */		"Osku",
	/* Version */		'0.7-gamma',
	/* Permissions */	'admin'
);
?>
