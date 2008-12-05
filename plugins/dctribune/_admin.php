<?php
# ***** BEGIN LICENSE BLOCK *****
#
# Tribune Libre is a small chat system for Dotclear 2
# Copyright (C) 2007  Antoine Libert
# 
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****
/*
if (isset($__dashboard_icons) && $core->auth->check('tribune',$core->blog->id)) {
	$__dashboard_icons[] = array(__('Tribune Libre'),'plugin.php?p=tribune','index.php?pf=tribune/icon.png');
}
*/

$_menu['Plugins']->addItem('Tribune Libre','plugin.php?p=tribune','index.php?pf=tribune/icon-small.png',
				preg_match('/plugin.php\?p=tribune(&.*)?$/',$_SERVER['REQUEST_URI']),
				$core->auth->check('usage,contentadmin',$core->blog->id));

$core->auth->setPermissionType('tribune',__('manage tribune'));

require dirname(__FILE__).'/_widgets.php';
?>
