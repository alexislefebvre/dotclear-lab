<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of translatedwidgets, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Franck Paul and contributors
# carnet.franck.paul@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Blog']->addItem(__('Presentation widgets'),'plugin.php?p=translatedwidgets','index.php?pf=translatedwidgets/icon.png',
		preg_match('/plugin.php\?p=translatedwidgets(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));
?>