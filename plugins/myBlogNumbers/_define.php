<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of myBlogNumbers, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {

	return null;
}

$this->registerModule(
	/* Name */
	"My blog numbers",
	/* Description*/
	"Show some figures of your blog",
	/* Author */
	"Jean-Christian Denis",
	/* Version */
	'2013.11.12',
	array(
		'permissions' => 'usage,contentadmin',
		'type' => 'plugin',
		'dc_min' => '2.6',
		'support' => 'http://jcd.lv/q=myBlogNumbers',
		'details' => 'http://plugins.dotaddict.org/dc2/details/myBlogNumbers'
	)
);
