<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of licenseBootstrap, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$this->addUserAction(
	/* type */ 'settings',
	/* action */ 'delete_all',
	/* ns */ 'licenseBootstrap',
	/* description */ __('delete all settings')
);

$this->addUserAction(
	/* type */ 'plugins',
	/* action */ 'delete',
	/* ns */ 'licenseBootstrap',
	/* description */ __('delete plugin files')
);

$this->addDirectAction(
	/* type */ 'settings',
	/* action */ 'delete_all',
	/* ns */ 'licenseBootstrap',
	/* description */ __('delete all licenseBootstrap settings')
);

$this->addDirectAction(
	/* type */ 'plugins',
	/* action */ 'delete',
	/* ns */ 'licenseBootstrap',
	/* description */ __('delete licenseBootstrap plugin files')
);
?>