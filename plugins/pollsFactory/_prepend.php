<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pollsFactory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# DB class
$__autoload['pollsFactory'] = dirname(__FILE__).'/inc/class.pollsfactory.php';
# Shortcuts for advanced actions
$__autoload['libPollsFactory'] = dirname(__FILE__).'/inc/lib.pollsfactory.php';
# Charts class
$__autoload['pollsFactoryChart'] = dirname(__FILE__).'/inc/class.pollsfactory.chart.php';
# Admin list and pagers
$__autoload['adminPollList'] = dirname(__FILE__).'/inc/lib.index.pager.php';
# Url for vote
$core->url->register('pollsFactoryPage','polls','^polls(|/.+)$',array('publicUrlPollsFactory','page'));
# Url for graphic charts
$core->url->register('pollsFactoryChart','pollschart','^pollschart/([^/]+/[^/]+)$',array('publicUrlPollsFactory','chart'));
# Add pollsFactory report on plugin activityReport
if (defined('ACTIVITY_REPORT')) {
	require_once dirname(__FILE__).'/inc/lib.pollsfactory.activityreport.php';
}
?>