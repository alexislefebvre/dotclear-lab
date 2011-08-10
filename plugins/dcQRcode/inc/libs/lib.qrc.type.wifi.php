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

class QRcodeTypeWIFI extends QRcodeType
{
	public static function getTitle($qrc)
	{
		return __('Wifi');
	}
	
	public static function getForm($qrc)
	{
		$combo_auths = array(__('WPA') => 'WPA',__('WEP') => 'WEP');
		$auth = empty($_POST['MARKET_auth']) ? '' : $_POST['MARKET_auth'];
		$auth = 'WPA' == $auth ? 'WPA' : 'WEP';
		$ssid = empty($_POST['MARKET_ssid']) ? '' : html::escapeHTML($_POST['MARKET_ssid']);
		$pass = empty($_POST['MARKET_pass']) ? '' : html::escapeHTML($_POST['MARKET_pass']);
		
		echo 
		'<p><label for="MARKET_auth">'.
		__('Category:').
		form::combo('MARKET_auth',$combo_auths,$auth).
		'</label></p>'.
		
		'<p><label for="MARKET_ssid" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('SSID:').
		form::field('MARKET_ssid',60,255,$ssid).
		'</label></p>'.
		
		'<p><label for="MARKET_pass" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('Password:').
		form::field('MARKET_pass',60,255,$pass).
		'</label></p>';
	}
	
	public static function saveForm($qrc)
	{
		$combo_auths = array(__('WPA') => 'WPA',__('WEP') => 'WEP');
		$auth = empty($_POST['MARKET_auth']) ? '' : $_POST['MARKET_auth'];
		$auth = 'WPA' == $auth ? 'WPA' : 'WEP';
		$ssid = empty($_POST['MARKET_ssid']) ? '' : html::escapeHTML($_POST['MARKET_ssid']);
		$pass = empty($_POST['MARKET_pass']) ? '' : html::escapeHTML($_POST['MARKET_pass']);
		
		$id = $qrc->encodeData($auth,$ssid,$pass);
		self::returnImg($qrc,$id);
	}
	
	public static function getTemplate($qrc,$attr)
	{
		return empty($attr['auth']) || empty($attr['ssid']) ? '' : 
			"<?php \n".
			"\$auth = '".html::escapeHTML($attr['auth'])."'; \n".
			"\$ssid = '".html::escapeHTML($attr['ssid'])."'; \n".
			"\$pass = '".(isset($attr['pass']) ? html::escapeHTML($attr['pass']) : '')."'; \n".
			"\$id = \$_ctx->qrcode->encodeData(\$auth,\$ssid,\$pass); \n".
			"?>\n";
	}
	
	public static function encodeData($qrc,$args)
	{
		$data = '';
		if (count($args) > 1) {
			$data = 'WIFI:';
			$data .= 'T:'.QRcodeCore::escape($args[0],true).';'; // Authentication type
			$data .= 'S:'.QRcodeCore::escape($args[1],true).';'; // Network SSID
			$data .= 'P:'.(empty($args[2]) ? '' : QRcodeCore::escape($args[2],true)).';'; // Password
		}
		return $data;
	}
}
?>