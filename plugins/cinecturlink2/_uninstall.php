<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of cinecturlink2, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
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
	/* ns */ 'cinecturlink2',
	/* description */ __('delete all settings')
);

$this->addUserAction(
	/* type */ 'tables',
	/* action */ 'delete',
	/* ns */ 'cinecturlink2',
	/* description */ sprintf(__('delete %s table'),'cinecturlink2')
);

$this->addUserAction(
	/* type */ 'tables',
	/* action */ 'delete',
	/* ns */ 'cinecturlink2_cat',
	/* description */ sprintf(__('delete %s table'),'cinecturlink2_cat')
);

$this->addUserAction(
	/* type */ 'versions',
	/* action */ 'delete',
	/* ns */ 'cinecturlink2',
	/* description */ __('delete the version number')
);

$this->addUserAction(
	/* type */ 'plugins',
	/* action */ 'delete',
	/* ns */ 'cinecturlink2',
	/* description */ __('delete plugin files')
);

$this->addDirectAction(
	/* type */ 'settings',
	/* action */ 'delete_all',
	/* ns */ 'cinecturlink2',
	/* description */ sprintf(__('delete all %s settings'),'cinecturlink2')
);

$this->addDirectAction(
	/* type */ 'tables',
	/* action */ 'delete',
	/* ns */ 'cinecturlink2',
	/* description */ sprintf(__('delete %s table'),'cinecturlink2')
);

$this->addDirectAction(
	/* type */ 'tables',
	/* action */ 'delete',
	/* ns */ 'cinecturlink2_cat',
	/* description */ sprintf(__('delete %s table'),'cinecturlink2_cat')
);

$this->addDirectAction(
	/* type */ 'versions',
	/* action */ 'delete',
	/* ns */ 'cinecturlink2',
	/* description */ sprintf(__('delete %s version number'),'cinecturlink2')
);

$this->addDirectAction(
	/* type */ 'plugins',
	/* action */ 'delete',
	/* ns */ 'cinecturlink2',
	/* description */ sprintf(__('delete %s plugin files'),'cinecturlink2')
);
?>