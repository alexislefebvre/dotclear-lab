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

$core->url->register('cinecturlink2',
	'cinecturlink','^cinecturlink(?:/(.+))?$',
	array('urlCinecturlink2','c2Page')
);

## addon ##

# Add cinecturlink2 report on plugin activityReport
if (defined('ACTIVITY_REPORT'))
{
	require_once dirname(__FILE__).'/inc/lib.cinecturlink2.activityreport.php';
}
# cinecturlink2 libraries for sitemaps
$__autoload['sitemapsCinecturlink2'] = dirname(__FILE__).'/inc/lib.sitemaps.cinecturlink2.php';
$core->addBehavior('sitemapsDefineParts',array('sitemapsCinecturlink2','sitemapsDefineParts'));
$core->addBehavior('sitemapsURLsCollect',array('sitemapsCinecturlink2','sitemapsURLsCollect'));


# cinecturlink2 libraries for rateIt
$__autoload['templateRateItCinecturlink2'] = dirname(__FILE__).'/inc/lib.rateit.cinecturlink2.template.php';
$__autoload['adminRateItCinecturlink2'] = dirname(__FILE__).'/inc/lib.rateit.cinecturlink2.admin.php';
$__autoload['backupRateItCinecturlink2'] = dirname(__FILE__).'/inc/lib.rateit.cinecturlink2.backup.php';
$__autoload['widgetRateItCinecturlink2'] = dirname(__FILE__).'/inc/lib.rateit.cinecturlink2.widget.php';
# cinecturlink2 type
$core->addBehavior('addRateItType',create_function('&$types','$types[] = "cinecturlink2";'));
# rateit widget
$core->addBehavior('initWidgetRateItRank',array('widgetRateItCinecturlink2','initRank'));
$core->addBehavior('parseWidgetRateItRank',array('widgetRateItCinecturlink2','parseRank'));
# cinecturlink2 admin tab
$core->addBehavior('adminRateItHeader',array('adminRateItCinecturlink2','adminHeader'));
$core->addBehavior('adminRateItTabs',array('adminRateItCinecturlink2','adminTabs'));
# cinecturlink2 templates
$core->addBehavior('publicC2EntryAfterContent',array('templateRateItCinecturlink2','publicC2EntryAfterContent'));
$core->addBehavior('templateRateIt',array('templateRateItCinecturlink2','params'));
$core->addBehavior('templateRateItTitle',array('templateRateItCinecturlink2','title'));
$core->addBehavior('templateRateItRedirect',array('templateRateItCinecturlink2','redirect'));
# cinecturlink2 backups
$core->addBehavior('exportSingle',array('backupRateItCinecturlink2','exportSingle'));
$core->addBehavior('importInit',array('backupRateItCinecturlink2','importInit'));
$core->addBehavior('importSingle',array('backupRateItCinecturlink2','importSingle'));
?>