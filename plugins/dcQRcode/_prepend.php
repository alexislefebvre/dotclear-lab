<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcQRcode, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

global $__autoload, $core;

$__autoload['dcQRcode'] = dirname(__FILE__).'/inc/class.dc.qr.code.php';
$__autoload['dcQRcodeIndexLib'] = dirname(__FILE__).'/inc/lib.dc.qr.code.index.php';
$__autoload['dcQRcodeList'] = dirname(__FILE__).'/inc/lib.dc.qr.code.list.php';

$core->url->register(
	'dcQRcodeImage',
	'QRcode',
	'^QRcode/(.+)$',
	array('dcQRcodeUrl','image')
);

$find = false;
$custom = $core->blog->settings->qrc_public_path;
$default = $core->blog->public_path;

# See if don't want cache
if (!$core->blog->settings->qrc_cache_use) {
	$qrc_cache_path = null;
}
# See if custom cache path exists and it is writable
elseif (is_writable($custom))
{
	$qrc_cache_path = $custom;
}
# See if default cache path exists
elseif (is_writable($default))
{
	if (!is_dir($default.'/qrc/'))
	{
		@mkdir($default.'/qrc/');
	}
	$qrc_cache_path = $default.'/qrc';
}
# Set constant
define('QRC_CACHE_PATH',$qrc_cache_path);

?>