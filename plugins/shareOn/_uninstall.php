<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of shareOn, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# This file is used by plugin dcAdvancedCleaner

$this->addUserAction(
	/* type */ 'settings',
	/* action */ 'delete_all',
	/* ns */ 'shareOn',
	/* description */ __('delete all settings')
);

$this->addUserAction(
	/* type */ 'versions',
	/* action */ 'delete',
	/* ns */ 'shareOn',
	/* description */ __('delete the version number')
);

$this->addUserAction(
	/* type */ 'plugins',
	/* action */ 'delete',
	/* ns */ 'shareOn',
	/* description */ __('delete plugin files')
);

$this->addDirectAction(
	/* type */ 'settings',
	/* action */ 'delete_all',
	/* ns */ 'shareOn',
	/* description */ sprintf(__('delete all %s settings'),'shareOn')
);

$this->addDirectAction(
	/* type */ 'versions',
	/* action */ 'delete',
	/* ns */ 'shareOn',
	/* description */ sprintf(__('delete %s version number'),'shareOn')
);

$this->addDirectAction(
	/* type */ 'plugins',
	/* action */ 'delete',
	/* ns */ 'shareOn',
	/* description */ sprintf(__('delete %s plugin files'),'shareOn')
);
?>