<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of newsletter, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Benoit de Marne
# benoit.de.marne@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
		/* Name */
		"Newsletter",
		/* Description*/
		"Manage your newsletters in Dotclear 2",
		/* Author */
		"Benoit de Marne",
		/* Version */
		'3.9.0r9',
		/* Properties */
		array(
				'permissions' => 'usage,contentadmin',
				'type' => 'plugin',
				'dc_min' => '2.6',
				'support' => 'http://forum.dotclear.org/viewtopic.php?id=41494',
				'details' => 'http://plugins.dotaddict.org/dc2/details/newsletter'
		)		
);

?>