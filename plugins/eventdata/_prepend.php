<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventdata, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) return;

# Event generic class
$GLOBALS['__autoload']['dcEventdata'] = 
	dirname(__FILE__).'/inc/class.dc.eventdata.php';
# Event generic rest class
$GLOBALS['__autoload']['dcEventdataRest'] = 
	dirname(__FILE__).'/inc/class.dc.eventdata.rest.php';
# Eventdata plugin main class
$GLOBALS['__autoload']['eventdata'] = 
	dirname(__FILE__).'/inc/class.eventdata.php';
# Eventdata install class
$GLOBALS['__autoload']['eventdataInstall'] = 
	dirname(__FILE__).'/inc/lib.eventdata.install.php';
# Eventdata entries table
$GLOBALS['__autoload']['eventdataEventdataList'] = 
	dirname(__FILE__).'/inc/lib.eventdata.list.php';
?>