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
if (version_compare(DC_VERSION,'2.2-alpha','<')){return;}

global $core, $__autoload;
$core->blog->settings->addNamespace('shareOn');

if (!isset($core->shareOnButtons)) { $core->shareOnButtons = array(); }

$__autoload['shareOn'] = dirname(__FILE__).'/inc/class.shareon.php';

$__autoload['flattrButton'] = dirname(__FILE__).'/inc/class.shareon.php';
$__autoload['tweetmemeButton'] = dirname(__FILE__).'/inc/class.shareon.php';
$__autoload['fbshareButton'] = dirname(__FILE__).'/inc/class.shareon.php';
$__autoload['fbloveButton'] = dirname(__FILE__).'/inc/class.shareon.php';
$__autoload['diggButton'] = dirname(__FILE__).'/inc/class.shareon.php';
$__autoload['redditButton'] = dirname(__FILE__).'/inc/class.shareon.php';
$__autoload['dzoneButton'] = dirname(__FILE__).'/inc/class.shareon.php';
$__autoload['ybuzzButton'] = dirname(__FILE__).'/inc/class.shareon.php';
$__autoload['gbuzzButton'] = dirname(__FILE__).'/inc/class.shareon.php';

$core->shareOnButtons['flattr'] = 'flattrButton';
$core->shareOnButtons['tweetmeme'] = 'tweetmemeButton';
$core->shareOnButtons['fbshare'] = 'fbshareButton';
$core->shareOnButtons['fblove'] = 'fbloveButton';
$core->shareOnButtons['digg'] = 'diggButton';
$core->shareOnButtons['reddit'] = 'redditButton';
$core->shareOnButtons['dzone'] = 'dzoneButton';
$core->shareOnButtons['ybuzz'] = 'ybuzzButton';
$core->shareOnButtons['gbuzz'] = 'gbuzzButton';
?>