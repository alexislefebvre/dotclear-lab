<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcQRcode, a plugin for Dotclear 2.
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

$__autoload['dcQRcode'] = dirname(__FILE__).'/inc/class.dc.qr.code.php';
$__autoload['dcQRcodeIndexLib'] = dirname(__FILE__).'/inc/lib.dc.qr.code.index.php';
$__autoload['dcQRcodeList'] = dirname(__FILE__).'/inc/lib.dc.qr.code.list.php';

$core->url->register(
	'dcQRcodeImage',
	'QRcode',
	'^QRcode/(.+)$',
	array('dcQRcodeUrl','image')
);

# Add dcQrcode events on plugin activityReport
if (defined('ACTIVITY_REPORT'))
{
	require_once dirname(__FILE__).'/inc/lib.dcqrcode.activityreport.php';
}
?>