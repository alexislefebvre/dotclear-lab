<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of anonymousComment, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }
 
$this->registerModule(
	/* Name */		"Anonymous comments",
	/* Description*/	"Allows posting comments as anonymous",
	/* Author */		"Aurélien Bompard, Pierre Van Glabeke",
	/* Version */		'1.3',
	/* Properties */
	array(
		'permissions' => 'admin',
		'type' => 'plugin',
		'dc_min' => '2.6',
		'support' => 'http://lab.dotclear.org/wiki/plugin/anonymousComment',
		'details' => 'http://plugins.dotaddict.org/dc2/details/anonymousComment'
		)
);