<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of specificsTemplates, a plugin for Dotclear.
# 
# Copyright (c) 2009 Thierry Poinot
# dev@thierrypoinot.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
$_menu['Plugins']->addItem(__('Specifics Templates'),'plugin.php?p=specifics_templates','index.php?pf=specifics_templates/bricks.png',
		preg_match('/plugin.php\?p=specifics_templates(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('usage',$core->blog->id));
		
?>