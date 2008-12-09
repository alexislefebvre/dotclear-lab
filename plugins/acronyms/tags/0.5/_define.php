<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of acronyms, a plugin for DotClear2.
#
# Copyright (c) 2008 Vincent Garnier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */				"Acronyms Manager",
	/* Description*/		"Add, remove and modify acronyms for the wiki syntax.",
	/* Author */			"Vincent Garnier",
	/* Version */			'0.5',
	/* Permissions */		'acronyms'
);
