<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of zoneclearFeedServer, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis, BG and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

global $__autoload, $core;

$__autoload['zoneclearFeedServer'] = dirname(__FILE__).'/inc/class.zoneclear.feed.server.php';
$__autoload['zoneclearFeedServerLists'] = dirname(__FILE__).'/inc/lib.zoneclear.feed.server.index.php';

# public url for page of description of the flux
$core->url->register('zoneclearFeedsPage','zcfeeds','^zcfeeds(.*?)$',array('zoneclearFeedServerURL','zcFeedsPage'));

# Add personal Twitter class
$__autoload['zcfsLibDcTwitter'] = dirname(__FILE__).'/inc/lib.dc.twitter.php';
//$__autoload['zcfsLibDcTwitterSender'] = dirname(__FILE__).'/inc/lib.dc.twitter.php';

# Add to report on plugin activityReport
if (defined('ACTIVITY_REPORT')) {
	require_once dirname(__FILE__).'/inc/lib.zoneclear.feed.server.activityreport.php';
}
?>