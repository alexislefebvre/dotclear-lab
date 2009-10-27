<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Puzzle, a plugin for Dotclear.
# 
# Copyright (c) 2009 kévin lepeltier
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$_menu['Plugins']->addItem(	__('Puzzle'),
				'plugin.php?p=puzzle', 
				'index.php?pf=puzzle/icon.png', 
				preg_match('/plugin.php\?p=puzzle(&.*)?$/', 
				$_SERVER['REQUEST_URI']), 
				$core->auth->check('usage,contentadmin',$core->blog->id));