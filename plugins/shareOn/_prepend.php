<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of shareOn, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) return;

global $core;

if (!isset($core->shareOnButtons)) { $core->shareOnButtons = array(); }

$core->shareOnButtons['tweetmeme'] = 'tweetmemeButton';
$core->shareOnButtons['fbshare'] = 'fbshareButton';
$core->shareOnButtons['digg'] = 'diggButton';
$core->shareOnButtons['reddit'] = 'redditButton';
$core->shareOnButtons['dzone'] = 'dzoneButton';
$core->shareOnButtons['ybuzz'] = 'ybuzzButton';
?>