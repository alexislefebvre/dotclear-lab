<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of clean:config.
# Copyright 2007 Moe (http://gniark.net/)
#
# clean:config is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# clean:config is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {exit;}

$_menu['Plugins']->addItem(__('clean:config'),'plugin.php?p=cleanConfig',
	'index.php?pf=cleanConfig/icon.png',preg_match('/plugin.php\?p=cleanConfig(&.*)?$/',
	$_SERVER['REQUEST_URI']),$core->auth->check('admin',$core->blog->id));
?>