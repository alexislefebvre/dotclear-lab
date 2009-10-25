<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of miniToc, a plugin for Dotclear.
# 
# Copyright (c) 2009 Kozlika (ahem) and kindly contributors
# Actually, this plugin shows how I gracefully use copy-paste
# of Olivier Meunier's & Pep's code. Big thanks to them :-)
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$_menu['Plugins']->addItem(__('MiniToc'),'plugin.php?p=miniToc','index.php?pf=miniToc/icon.png',
		preg_match('/minitoc.php\?p=minitoc/',$_SERVER['REQUEST_URI']),
		$core->auth->check('usage,contentadmin',$core->blog->id));
?>