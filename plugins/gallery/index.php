<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Gallery plugin.
# Copyright (c) 2007 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Gallery plugin for DC2 is free sofwtare; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

if (is_null($core->blog->settings->gallery_gallery_url_prefix)) {
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
	}
} else {
	require dirname(__FILE__).'/gals.php';
}
?>
