<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcQRcode, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# Main class
$__autoload['dcQRcode'] = dirname(__FILE__).'/inc/class.dc.qr.code.php';
$__autoload['dcQRcodeList'] = dirname(__FILE__).'/inc/lib.dc.qr.code.list.php';
$__autoload['QRcodeCore'] = dirname(__FILE__).'/inc/lib.qrc.core.php';
$__autoload['QRcodeType'] = dirname(__FILE__).'/inc/lib.qrc.type.php';

# Formats
$__autoload['QRcodeTypeTXT'] = dirname(__FILE__).'/inc/libs/lib.qrc.type.txt.php';
$core->addBehavior('registerType',create_function(null,'return array("TXT","QRcodeTypeTXT");'));
$__autoload['QRcodeTypeURL'] = dirname(__FILE__).'/inc/libs/lib.qrc.type.url.php';
$core->addBehavior('registerType',create_function(null,'return array("URL","QRcodeTypeURL");'));
$__autoload['QRcodeTypeMECARD'] = dirname(__FILE__).'/inc/libs/lib.qrc.type.mecard.php';
$core->addBehavior('registerType',create_function(null,'return array("MECARD","QRcodeTypeMECARD");'));
$__autoload['QRcodeTypeBIZCARD'] = dirname(__FILE__).'/inc/libs/lib.qrc.type.bizcard.php';
$core->addBehavior('registerType',create_function(null,'return array("BIZCARD","QRcodeTypeBIZCARD");'));
$__autoload['QRcodeTypeGEO'] = dirname(__FILE__).'/inc/libs/lib.qrc.type.geo.php';
$core->addBehavior('registerType',create_function(null,'return array("GEO","QRcodeTypeGEO");'));
$__autoload['QRcodeTypeICAL'] = dirname(__FILE__).'/inc/libs/lib.qrc.type.ical.php';
$core->addBehavior('registerType',create_function(null,'return array("ICAL","QRcodeTypeICAL");'));
$__autoload['QRcodeTypeMARKET'] = dirname(__FILE__).'/inc/libs/lib.qrc.type.market.php';
$core->addBehavior('registerType',create_function(null,'return array("MARKET","QRcodeTypeMARKET");'));
$__autoload['QRcodeTypeIAPPLI'] = dirname(__FILE__).'/inc/libs/lib.qrc.type.iappli.php';
$core->addBehavior('registerType',create_function(null,'return array("IAPPLI","QRcodeTypeIAPPLI");'));
$__autoload['QRcodeTypeMATMSG'] = dirname(__FILE__).'/inc/libs/lib.qrc.type.matmsg.php';
$core->addBehavior('registerType',create_function(null,'return array("MATMSG","QRcodeTypeMATMSG");'));
$__autoload['QRcodeTypeTEL'] = dirname(__FILE__).'/inc/libs/lib.qrc.type.tel.php';
$core->addBehavior('registerType',create_function(null,'return array("TEL","QRcodeTypeTEL");'));
$__autoload['QRcodeTypeSMSTO'] = dirname(__FILE__).'/inc/libs/lib.qrc.type.smsto.php';
$core->addBehavior('registerType',create_function(null,'return array("SMSTO","QRcodeTypeSMSTO");'));
$__autoload['QRcodeTypeMMSTO'] = dirname(__FILE__).'/inc/libs/lib.qrc.type.mmsto.php';
$core->addBehavior('registerType',create_function(null,'return array("MMSTO","QRcodeTypeMMSTO");'));
$__autoload['QRcodeTypeWIFI'] = dirname(__FILE__).'/inc/libs/lib.qrc.type.wifi.php';
$core->addBehavior('registerType',create_function(null,'return array("WIFI","QRcodeTypeWIFI");'));

# Public URL to serve QR code
$core->url->register(
	'dcQRcodeImage',
	'QRcode',
	'^QRcode/(.+)$',
	array('dcQRcodeUrl','image')
);

# Add dcQrcode events on plugin activityReport
if (defined('ACTIVITY_REPORT')) {
	require_once dirname(__FILE__).'/inc/lib.dcqrcode.activityreport.php';
}
?>