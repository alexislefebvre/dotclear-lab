<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventdata, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

global $__autoload, $core;

# Event generic class
$__autoload['dcEventdata'] = 
	dirname(__FILE__).'/inc/class.dc.eventdata.php';
# Event generic rest class
$__autoload['dcEventdataRest'] = 
	dirname(__FILE__).'/inc/class.dc.eventdata.rest.php';
# Eventdata plugin main class
$__autoload['eventdata'] = 
	dirname(__FILE__).'/inc/class.eventdata.php';
# Eventdata entries table
$__autoload['eventdataEventdataList'] = 
	dirname(__FILE__).'/inc/lib.eventdata.list.php';
# Public page url  //Can be changed with plugin myUrlHandlers
$core->url->register('eventdatapage',
	'events','^events(|/.+)$',array('eventdataPublic','eventdatas'));
# Public url for eventdata files  //Can be changed with plugin myUrlHandlers
$core->url->register('eventdatafiles',
	'eventstheme','^eventstheme/(.+)$',array('eventdataPublic','eventdatastheme'));
# Add eventdata report on plugin activityReport
if (defined('ACTIVITY_REPORT')) {
	require_once dirname(__FILE__).'/inc/lib.rateit.activityreport.php';
}
?>