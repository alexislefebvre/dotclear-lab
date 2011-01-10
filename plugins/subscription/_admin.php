<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Subscription, a plugin for Dotclear.
# 
# Copyright (c) 2010 Marc Vachette
# marc.vachette@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------


$_menu['Plugins']->addItem('Subscription','plugin.php?p=subscription','index.php?pf=subscription/icon.png',
		preg_match('/plugin.php\?p=subscription(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('',$core->blog->id));

?>