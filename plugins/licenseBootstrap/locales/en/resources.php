<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of licenseBootstrap, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {

	return null;
}

if (!empty($_REQUEST['module']) && $_REQUEST['module'] == 'licenseBootstrap') {
	$__resources['help']['core_plugins_conf'] = dirname(__FILE__).'/help/help.html';
}
