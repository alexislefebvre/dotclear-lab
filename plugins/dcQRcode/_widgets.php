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

$core->addBehavior('initWidgets',array('dcQRcodeAdminWidget','posts'));

class dcQRcodeAdminWidget
{
	public static function posts($w)
	{
		# Create widget
		$w->create(
			'qrc_posts',
			__('QR code'),
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
				'64x64 pixels' => 64,
				'92x92 pixels' => 92,
				'128x128 pixels' => 128,
				'256x256 pixels' => 256,
				'512x512 pixels' => 512
			)
		);
		# context
		$w->qrc_posts->setting(
			'context',
			__('Type:'),
			'posts',
			'combo',
			array(
				__('Entries') => 'posts',
				__('Categories') => 'categories',
				__('Tags') => 'tags'
			)
		);
	}
}
?>