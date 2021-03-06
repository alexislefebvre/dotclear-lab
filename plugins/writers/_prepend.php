<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Writers, a plugin for Dotclear.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

# Following constants can be overrided in your install config.php:
#
# DC_WR_ALLOW_ADMIN (allow admin permission to be set)

if (!defined('DC_WR_ALLOW_ADMIN')) {
	define('DC_WR_ALLOW_ADMIN',false);
}

# Super admins don't need this extension
if ($GLOBALS['core']->auth->isSuperAdmin()) {
	return;
}