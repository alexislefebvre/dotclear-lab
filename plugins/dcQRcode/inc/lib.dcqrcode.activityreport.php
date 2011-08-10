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

# This file is used with plugin activityReport
$core->activityReport->addGroup('qrcode',__('Plugin dcQRcode'));

# from BEHAVIOR dcQRcodeAfterSet in dcQRcode/inc/class.dc.qr.code.php
$core->activityReport->addAction(
	'qrcode',
	'create',
	__('QR code creation'),
	__('New QR code of type "%s" and id "%s" was created'),
	'dcQRcodeAfterCreate',
	array('dcQRcodeActivityReportBehaviors','dcQRcodeCreate')
);

# from BEHAVIOR dcQRcodeBeforeDelete in dcQRcode/inc/class.dc.qr.code.php
$core->activityReport->addAction(
	'qrcode',
	'delete',
	__('QR code deletion'),
	__('QR code of id "%s" has been deleted by "%s"'),
	'dcQRcodeBeforeDelete',
	array('dcQRcodeActivityReportBehaviors','dcQRcodeDelete')
);

class dcQRcodeActivityReportBehaviors
{
	public static function dcQRcodeCreate($cur)
	{
		$logs = array(
			$cur->qrcode_type,
			$cur->qrcode_id
		);

		$GLOBALS['core']->activityReport->addLog('qrcode','create',$logs);
	}

	public static function dcQRcodeDelete($id)
	{
		global $core;

		$logs = array(
			$id,
			$core->auth->getInfo('user_cn')
		);

		$core->activityReport->addLog('qrcode','delete',$logs);
	}
}
?>