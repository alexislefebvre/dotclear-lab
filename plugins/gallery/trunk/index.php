<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2004-2008 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

if (is_null($core->blog->settings->gallery->gallery_enabled) || !$core->blog->settings->gallery->gallery_enabled) {
	require dirname(__FILE__).'/options.php';
}elseif (!empty($_REQUEST['m'])) {
	switch ($_REQUEST['m']) {
		case 'gal' :
			require dirname(__FILE__).'/gal.php';
			break;
		case 'galthumb' :
			require dirname(__FILE__).'/galthumbnail.php';
			break;
		case 'newitems' :
			require dirname(__FILE__).'/newitems.php';
			break;
		case 'galsactions':
			require dirname(__FILE__).'/gals_actions.php';
			break;
		case 'items':
			if ($core->blog->settings->gallery->gallery_adv_items)
				require dirname(__FILE__).'/items_adv.php';
			else
				require dirname(__FILE__).'/items.php';
			break;
		case 'itemsactions':
			require dirname(__FILE__).'/items_actions.php';
			break;
		case 'item':
			require dirname(__FILE__).'/item.php';
			break;
		case 'options':
			require dirname(__FILE__).'/options.php';
			break;
		case 'maintenance':
			require dirname(__FILE__).'/maintenance.php';
			break;
	}
} else {
	require dirname(__FILE__).'/gals.php';
}
?>