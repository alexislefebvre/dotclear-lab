<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Benoit de Marne.
# benoit.de.marne@gmail.com
# Many thanks to Association Dotclear and special thanks to Olivier Le Bris
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

// filtrage des droits
if (!defined('DC_CONTEXT_ADMIN')){return;}

$this->addUserAction(
	/* type */ 'settings',
	/* action */ 'delete_all',
	/* ns */ 'newsletter',
	/* description */ __('delete all settings')
);

$this->addUserAction(
	/* type */ 'tables',
	/* action */ 'delete',
	/* ns */ 'newsletter',
	/* description */ __('delete table')
);

$this->addUserAction(
	/* type */ 'plugins',
	/* action */ 'delete',
	/* ns */ 'newsletter',
	/* description */ __('delete plugin files')
);

$this->addUserAction(
	/* type */ 'versions',
	/* action */ 'delete',
	/* ns */ 'newsletter',
	/* description */ __('delete the version number')
);


# Keep settings and table on delete from pluginsBeforeDelete

$this->addDirectAction(
	/* type */ 'plugins',
	/* action */ 'delete',
	/* ns */ 'newsletter',
	/* description */ __('delete newsletter plugin files')
);

$this->addDirectAction(
	/* type */ 'versions',
	/* action */ 'delete',
	/* ns */ 'newsletter',
	/* description */ __('delete newsletter version number')
);

?>