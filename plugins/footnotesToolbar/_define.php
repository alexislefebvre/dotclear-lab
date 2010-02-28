<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of footnotesToolbar, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }
 
$this->registerModule(
	/* Name */		"Footnotes toolbar",
	/* Description*/	"Adds a toolbar button to create footnotes",
	/* Author */		"Aurélien Bompard",
	/* Version */		'1.1',
	/* Permissions */	'usage,contentadmin'
);
?>
