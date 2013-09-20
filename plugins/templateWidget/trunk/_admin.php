<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of templateWidget
# Copyright (c) 2009 Olivier Azeau and contributors. All rights reserved.
#
# This is free software; you can redistribute it and/or modify
# it under the terms of the Affero GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
# 
# This is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the Affero GNU General Public License
# along with templateWidget; If not, see <http://www.gnu.org/licenses/>.
#
# templateWidget is a plugin for Dotclear software.
# templateWidget is not part of Dotclear.
# templateWidget can be used inside Dotclear but this license only applies to templateWidget
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { return; }

require_once(dirname(__FILE__).'/WidgetAdmin.php');
$core->addBehavior('initWidgets',array('templateWidgetAdmin','InitWidgets'));

$_menu['Blog']->addItem(
  'Template Widget', // title
  'plugin.php?p=templateWidget', // url
  'index.php?pf=templateWidget/icon.png',  // img
  preg_match('/plugin.php\?p=templateWidget/',$_SERVER['REQUEST_URI']), // active
  $core->auth->check('usage,contentadmin',$core->blog->id) // show
);

?>
