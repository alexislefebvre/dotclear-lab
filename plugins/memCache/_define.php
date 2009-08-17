<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of MemCache, a plugin for Dotclear 2.
#
# Copyright (c) 2008-2009 Alain Vagner, Pep and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			"MemCache",
	/* Description*/		"Blog pages cache using memcached",
	/* Author */			"Alain Vagner, Pep and contributors",
	/* Version */			'1.0-beta4',
	/* Permissions */		null
);
?>