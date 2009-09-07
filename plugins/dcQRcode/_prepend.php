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
$__autoload['dcQrCodeIndexLib'] = dirname(__FILE__).'/inc/lib.dc.qr.code.index.php';

$core->url->register(
	'dcQRcodeImage',
	'QRcode',
	'^QRcode/(.+)$',
	array('dcQRcodeUrl','image')
);

$qrc_cache_path = $core->blog->settings->qrc_public_path;

if (!is_writable($qrc_cache_path))
{
	if (!is_dir($core->blog->public_path.'/qrc/'))
	{
		mkdir($core->blog->public_path.'/qrc/');
	}
	$qrc_cache_path = $core->blog->public_path.'/qrc';
}

if (!$core->blog->settings->qrc_cache_use)
{
	define('QRC_CACHE_PATH',null);
}
else
{
	define('QRC_CACHE_PATH',$qrc_cache_path);
}
?>