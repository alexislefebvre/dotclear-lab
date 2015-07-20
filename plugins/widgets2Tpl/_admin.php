<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Widgets to Template, a plugin for Dotclear 2
# Copyright 2010 Moe (http://gniark.net/)
#
# Widgets to Template is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Widgets to Template is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

$_menu['Plugins']->addItem(__('Widgets to Template'),'plugin.php?p=widgets2Tpl',
	'index.php?pf=widgets2Tpl/icon.png',
	preg_match('/plugin.php\?p=widgets2Tpl(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));	