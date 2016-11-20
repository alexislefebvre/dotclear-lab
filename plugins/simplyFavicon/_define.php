<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of simplyFavicon, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2016 JC Denis and contributors
# contact@jcdenis.fr http://jcdenis.net
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}
 
$this->registerModule(
	/* Name */			"Simply favicon",
	/* Description*/		"Multi-agents favicon",
	/* Author */			"JC Denis and contributors",
	/* Version */			'2016.11.20',
	/* Properties */
	array(
		'permissions' => 'admin',
		'type' => 'plugin',
		'dc_min' => '2.6',
		'support' => 'http://forum.dotclear.org/viewforum.php?id=16',
		'details' => 'http://plugins.dotaddict.org/dc2/details/simplyFavicon'
		)
);