<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcQRcode, a plugin for Dotclear 2.
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
	/* ns */ 'dcQRcode',
	/* description */ __('delete all settings')
);

$this->addUserAction(
	/* type */ 'tables',
	/* action */ 'delete',
	/* ns */ 'qrcode',
	/* description */ __('delete table')
);

$this->addUserAction(
	/* type */ 'plugins',
	/* action */ 'delete',
	/* ns */ 'dcQRcode',
	/* description */ __('delete plugin files')
);

$this->addUserAction(
	/* type */ 'versions',
	/* action */ 'delete',
	/* ns */ 'dcQRcode',
	/* description */ __('delete the version number')
);


# Remove all on delete from pluginsBeforeDelete

$this->addDirectAction(
	/* type */ 'settings',
	/* action */ 'delete_all',
	/* ns */ 'dcQRcode',
	/* description */ __('delete all dcQRcode settings')
);

$this->addDirectAction(
	/* type */ 'tables',
	/* action */ 'delete',
	/* ns */ 'qrcode',
	/* description */ __('delete dcQRcode table')
);

$this->addDirectAction(
	/* type */ 'plugins',
	/* action */ 'delete',
	/* ns */ 'dcQRcode',
	/* description */ __('delete dcQRcode plugin files')
);

$this->addDirectAction(
	/* type */ 'versions',
	/* action */ 'delete',
	/* ns */ 'dcQRcode',
	/* description */ __('delete dcQRcode version number')
);

?>