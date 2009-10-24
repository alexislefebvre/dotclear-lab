<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcScrobbler, a plugin for Dotclear.
# 
# Copyright (c) 2008 Boris de Laage
# bdelaage@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem('dcScrobbler','plugin.php?p=dcscrobbler',
                           'index.php?pf=dcscrobbler/icon.png',
                           preg_match('/plugin.php\?p=dcscrobbler(&.*)?$/',$_SERVER['REQUEST_URI']),
                           $core->auth->check('usage, contentadmin', $core->blog->id));
                           
require dirname(__FILE__).'/_widgets.php';	

?>