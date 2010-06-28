<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) return;
if (version_compare(DC_VERSION,'2.2-alpha','<')){return;}

global $__autoload, $core;

# Namespace for settings
$core->blog->settings->addNamespace('kUtRL');

# Main class
$__autoload['kutrlServices'] = dirname(__FILE__).'/inc/lib.kutrl.srv.php';
$__autoload['kutrlLog'] = dirname(__FILE__).'/inc/lib.kutrl.log.php';

# Services
if (!isset($core->kutrlServices)) { $core->kutrlServices = array(); }

$__autoload['localKutrlService'] = dirname(__FILE__).'/inc/services/class.local.service.php';
$__autoload['isgdKutrlService'] = dirname(__FILE__).'/inc/services/class.isgd.service.php';
$__autoload['shorttoKutrlService'] = dirname(__FILE__).'/inc/services/class.shortto.service.php';
$__autoload['trimKutrlService'] = dirname(__FILE__).'/inc/services/class.trim.service.php';
$__autoload['bitlyKutrlService'] = dirname(__FILE__).'/inc/services/class.bitly.service.php';
$__autoload['bilbolinksKutrlService'] = dirname(__FILE__).'/inc/services/class.bilbolinks.service.php';

$core->kutrlServices['local'] = 'localKutrlService';
$core->kutrlServices['isgd'] = 'isgdKutrlService';
$core->kutrlServices['shortto'] = 'shorttoKutrlService';
$core->kutrlServices['trim'] = 'trimKutrlService';
$core->kutrlServices['bitly'] = 'bitlyKutrlService';
$core->kutrlServices['bilbolinks'] = 'bilbolinksKutrlService';

# Shorten url passed through wiki functions
$__autoload['kutrlWiki'] = dirname(__FILE__).'/inc/lib.wiki.kutrl.php';
$core->addBehavior('coreInitWikiPost',array('kutrlWiki','coreInitWiki'));
$core->addBehavior('coreInitWikiComment',array('kutrlWiki','coreInitWiki'));
$core->addBehavior('coreInitWikiSimpleComment',array('kutrlWiki','coreInitWiki'));

# Public page
$core->url->register('kutrl','go','^go(/(.*?)|)$',array('urlKutrl','redirectUrl'));

# Generic Dotclear class for Twitter and Identi.ca
$__autoload['kutrlLibDcTwitter'] = dirname(__FILE__).'/inc/lib.dc.twitter.php';

# Add kUtRL events on plugin activityReport
if ($core->blog->settings->kUtRL->kutrl_extend_activityreport && defined('ACTIVITY_REPORT'))
{
	require_once dirname(__FILE__).'/inc/lib.kutrl.activityreport.php';
}
?>