<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of cinecturlink2, a plugin for Dotclear 2.
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

$__autoload['cinecturlink2'] = dirname(__FILE__).'/inc/class.cinecturlink2.php';
$__autoload['c2_context'] = dirname(__FILE__).'/inc/lib.cinecturlink2.context.php';

$core->url->register('cinecturlink2','cinecturlink','^cinecturlink(?:/(.+))?$',array('urlCinecturlink2','c2Page'));

# Add cinecturlink2 report on plugin activityReport
if (defined('ACTIVITY_REPORT'))
{
	require_once dirname(__FILE__).'/inc/lib.cinecturlink2.activityreport.php';
}

# cinecturlink2 libraries for sitemaps
$__autoload['sitemapsCinecturlink2'] = dirname(__FILE__).'/inc/lib.sitemaps.cinecturlink2.php';
$core->addBehavior('sitemapsDefineParts',array('sitemapsCinecturlink2','sitemapsDefineParts'));
$core->addBehavior('sitemapsURLsCollect',array('sitemapsCinecturlink2','sitemapsURLsCollect'));
?>