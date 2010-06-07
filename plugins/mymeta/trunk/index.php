<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Mymeta plugin.
# Copyright (c) 2010 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Mymeta plugin for DC2 is free software; you can redistribute it and/or modify
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
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$mymeta = new myMeta($core);

if (!empty($_REQUEST['m'])) {
	switch ($_REQUEST['m']) {
		case 'options' :
			require dirname(__FILE__).'/index_options.php';
			break;
		case 'edit' :
			require dirname(__FILE__).'/index_edit.php';
			break;
		case 'view' :
			require dirname(__FILE__).'/index_view.php';
			break;
		case 'viewposts' :
			require dirname(__FILE__).'/index_view_posts.php';
			break;
		case 'editsection' :
			require dirname(__FILE__).'/index_edit_section.php';
			break;
	}
} else {
	require dirname(__FILE__).'/index_home.php';
}
?>