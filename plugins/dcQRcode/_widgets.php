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

$core->addBehavior('initWidgets',array('dcQRcodeAdminWidget','posts'));

class dcQRcodeAdminWidget
{
	public static function posts($w)
	{
		# Create widget
		$w->create(
			'qrc_posts',
			__('QRcode for posts'),
			array('dcQRcodePublicWidget','posts')
		);
		# Title
		$w->qrc_posts->setting(
			'title',
			__('Title:'),
			__('QR code'),
			'text'
		);
		# Size
		$w->qrc_posts->setting(
			'size',
			__('Size:'),
			128,
			'combo',
			array(
				'S' => 64,
				'M' => 92,
				'L' => 128,
				'X' => 256,
				'XL' => 512
			)
		);
		# Mebkm
		$w->qrc_posts->setting(
			'mebkm',
			__('Use MEBKM'),
			1,
			'check'
		);
	}
}
?>