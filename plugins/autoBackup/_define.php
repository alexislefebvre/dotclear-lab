<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of autoBackup, a plugin for Dotclear.
# 
# Copyright (c) 2008 k-net
# http://www.k-netweb.net/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			"Auto Backup",
	/* Description*/		"Make backups automatically",
	/* Author */			"k-net, brol, Oum, Franck Paul, Tomtom",
	/* Version */			'1.3.6',
	/* Permissions */		'admin'
);

?>