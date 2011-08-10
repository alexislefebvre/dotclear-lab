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

class QRcodeTypeGEO extends QRcodeType
{
	public static function getTitle($qrc)
	{
		return __('Geographic');
	}
	
	public static function getForm($qrc)
	{
		$latitude = empty($_POST['GEO_latitude']) ? '' : html::escapeHTML($_POST['GEO_latitude']);
		$longitude = empty($_POST['GEO_longitude']) ? '' : html::escapeHTML($_POST['GEO_longitude']);
		$altitude = empty($_POST['GEO_altitude']) ? '' : html::escapeHTML($_POST['GEO_altitude']);
		
		echo 
		'<p><label for="GEO_latitude" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('Latitude:').
		form::field('GEO_latitude',60,255,$latitude).
		'</label></p>'.
		
		'<p><label for="GEO_longitude" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('Longitude:').
		form::field('GEO_longitude',60,255,$longitude).
		'</label></p>'.
		
		'<p><label for="GEO_altitude">'.
		__('Altitude:').
		form::field('GEO_altitude',60,255,$altitude).
		'</label></p>';
	}
	
	public static function saveForm($qrc)
	{
		$latitude = empty($_POST['GEO_latitude']) ? '' : html::escapeHTML($_POST['GEO_latitude']);
		$longitude = empty($_POST['GEO_longitude']) ? '' : html::escapeHTML($_POST['GEO_longitude']);
		$altitude = empty($_POST['GEO_altitude']) ? '' : html::escapeHTML($_POST['GEO_altitude']);
		
		$id = $qrc->encodeData($latitude,$longitude,$altitude);
		self::returnImg($qrc,$id);
	}
	
	public static function getTemplate($qrc,$attr)
	{
		return empty($attr['latitude']) || empty($attr['longitude']) ? '' : 
			"<?php \n".
			" \$latitude = '".html::escapeHTML($attr['latitude'])."'; \n".
			" \$longitude = '".html::escapeHTML($attr['longitude'])."'; \n".
			" \$altitude = '".(isset($attr['altitude']) ? html::escapeHTML($attr['altitude']) : '0')."'; \n".
			" \$id = \$_ctx->qrcode->encodeData(\$latitude,\$longitude,\$altitude); \n".
			"?>\n";
	}
	
	public static function encodeData($qrc,$args)
	{
		$data = '';
		if (count($args) > 1)
		{
			$data = 'geo:';
			$data .= QRcodeCore::escape(str_replace(',','.',$args[0])).',';
			$data .= QRcodeCore::escape(str_replace(',','.',$args[1])).',';
			$data .= isset($args[2]) ? QRcodeCore::escape($args[2]) : '100';
		}
		return $data;
	}
}
?>