<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) return;

global $__autoload, $core;

# Main class
$__autoload['kutrlServices'] = dirname(__FILE__).'/inc/lib.kutrl.srv.php';
$__autoload['kutrlLog'] = dirname(__FILE__).'/inc/lib.kutrl.log.php';

# Services
if (!isset($core->kutrlServices)) { $core->kutrlServices = array(); }

$__autoload['localKutrlService'] = dirname(__FILE__).'/inc/services/class.local.service.php';
$core->kutrlServices['local'] = 'localKutrlService';

$__autoload['isgdKutrlService'] = dirname(__FILE__).'/inc/services/class.isgd.service.php';
$core->kutrlServices['isgd'] = 'isgdKutrlService';

$__autoload['trimKutrlService'] = dirname(__FILE__).'/inc/services/class.trim.service.php';
$core->kutrlServices['trim'] = 'trimKutrlService';

$__autoload['bitlyKutrlService'] = dirname(__FILE__).'/inc/services/class.bitly.service.php';
$core->kutrlServices['bitly'] = 'bitlyKutrlService';

$__autoload['bilbolinksKutrlService'] = dirname(__FILE__).'/inc/services/class.bilbolinks.service.php';
$core->kutrlServices['bilbolinks'] = 'bilbolinksKutrlService';

# Shorten url passed through wiki functions
$__autoload['kutrlWiki'] = dirname(__FILE__).'/inc/lib.wiki.kutrl.php';
$core->addBehavior('coreInitWikiPost',array('kutrlWiki','coreInitWiki'));
$core->addBehavior('coreInitWikiComment',array('kutrlWiki','coreInitWiki'));
$core->addBehavior('coreInitWikiSimpleComment',array('kutrlWiki','coreInitWiki'));

# Public page
$core->url->register('kutrl','go','^go(/(.*?)|)$',array('urlKutrl','redirectUrl'));


# Add kUtRL events on plugin activityReport
if ($core->blog->settings->kutrl_extend_activityreport && defined('ACTIVITY_REPORT'))
{
	require_once dirname(__FILE__).'/inc/lib.kutrl.activityreport.php';
}
?>