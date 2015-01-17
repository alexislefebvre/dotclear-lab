<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of disclaimer, a plugin for Dotclear 2.
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
	"disclaimer",
	/* Description*/
	"Add a disclaimer to your blog entrance",
	/* Author */
	"Jean-Christian Denis, Pierre Van Glabeke",
	/* Version */
	'0.7',
	/* Properties */
	array(
		'permissions' => 'admin',
		'type' => 'plugin',
		'dc_min' => '2.7',
		'support' => 'http://lab.dotclear.org/wiki/plugin/disclaimer',
		'details' => 'http://plugins.dotaddict.org/dc2/details/disclaimer'
	)
);
