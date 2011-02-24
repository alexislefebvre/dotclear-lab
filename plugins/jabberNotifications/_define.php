<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Jabber Notifications, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>, Olivier Tétard
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */		'Jabber Notifications',
	/* Description*/	'Jabber notifications for new comments',
	/* Author */		'Alex Pirine',
	/* Version */		'2011.02',
	/* Permissions */	'admin,usage,publish,delete,contentadmin,categories,media,media_admin'
);
?>