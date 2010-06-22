<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of MemCache, a plugin for Dotclear 2.
#
# Copyright (c) 2008-2010  Pep, Alain Vagner and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) return;

$this->registerModule(
	/* Name */			"MemCache",
	/* Description*/		"Blog pages cache using memcached",
	/* Author */			"Pep, Alain Vagner and contributors",
	/* Version */			'1.0-RC1',
	/* Permissions */		null
);
?>