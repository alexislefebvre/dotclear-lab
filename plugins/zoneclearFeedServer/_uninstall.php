<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of zoneclearFeedServer, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis, BG and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$this->addUserAction(
	/* type */ 'settings',
	/* action */ 'delete_all',
	/* ns */ 'zoneclearFeedServer',
	/* description */ __('delete all settings')
);
$this->addUserAction(
	/* type */ 'tables',
	/* action */ 'delete',
	/* ns */ 'zc_feed',
	/* description */ __('delete table')
);
$this->addUserAction(
	/* type */ 'plugins',
	/* action */ 'delete',
	/* ns */ 'zoneclearFeedServer',
	/* description */ __('delete plugin files')
);
$this->addUserAction(
	/* type */ 'versions',
	/* action */ 'delete',
	/* ns */ 'zoneclearFeedServer',
	/* description */ __('delete the version number')
);


$this->addDirectAction(
	/* type */ 'settings',
	/* action */ 'delete_all',
	/* ns */ 'zoneclearFeedServer',
	/* description */ sprintf(__('delete all %s settings'),'zoneclearFeedServer')
);
$this->addDirectAction(
	/* type */ 'tables',
	/* action */ 'delete',
	/* ns */ 'zc_feed',
	/* description */ sprintf(__('delete %s table'),'zoneclearFeedServer')
);
$this->addDirectAction(
	/* type */ 'plugins',
	/* action */ 'delete',
	/* ns */ 'zoneclearFeedServer',
	/* description */ sprintf(__('delete %s plugin files'),'zoneclearFeedServer')
);
$this->addDirectAction(
	/* type */ 'versions',
	/* action */ 'delete',
	/* ns */ 'zoneclearFeedServer',
	/* description */  sprintf(__('delete %s version number'),'zoneclearFeedServer')
);
$this->addDirectCallback(
	/* function */ 'zoneclearfeedServerUninstall',
	/* description */ 'delete feeds relations'
);

function zoneclearfeedServerUninstall($core,$id)
{
	if ($id != 'zoneclearFeedServer') return;
	//...
}
?>