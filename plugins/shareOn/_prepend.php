<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of shareOn, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) return;

global $core;

function shareOnSettings($core,$ns='shareOn') {
	if (!version_compare(DC_VERSION,'2.1.6','<=')) { 
		$core->blog->settings->addNamespace($ns); 
		return $core->blog->settings->{$ns}; 
	} else { 
		$core->blog->settings->setNamespace($ns); 
		return $core->blog->settings; 
	}
}

if (!isset($core->shareOnButtons)) { $core->shareOnButtons = array(); }

$core->shareOnButtons['flattr'] = 'flattrButton';
$core->shareOnButtons['tweetmeme'] = 'tweetmemeButton';
$core->shareOnButtons['fbshare'] = 'fbshareButton';
$core->shareOnButtons['fblove'] = 'fbloveButton';
$core->shareOnButtons['digg'] = 'diggButton';
$core->shareOnButtons['reddit'] = 'redditButton';
$core->shareOnButtons['dzone'] = 'dzoneButton';
$core->shareOnButtons['ybuzz'] = 'ybuzzButton';
?>