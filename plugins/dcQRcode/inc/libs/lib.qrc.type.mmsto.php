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

class QRcodeTypeMMSTO extends QRcodeType
{
	public static function getTitle($qrc)
	{
		return __('MMS');
	}
	
	public static function getForm($qrc)
	{
		$num = empty($_POST['MMSTO_num']) ? '' : html::escapeHTML($_POST['MMSTO_num']);
		$text = empty($_POST['MMSTO_text']) ? '' : html::escapeHTML($_POST['MMSTO_text']);
		
		echo 
		'<p><label for="MMSTO_num" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('Phone number:').
		form::field('MMSTO_num',60,255,$num).
		'</label></p>'.
		
		'<p><label for="MMSTO_text">'.
		__('Message:').
		form::field('MMSTO_text',60,255,$text).
		'</label></p>';
	}
	
	public static function saveForm($qrc)
	{
		$num = empty($_POST['MMSTO_num']) ? '' : html::escapeHTML($_POST['MMSTO_num']);
		$text = empty($_POST['MMSTO_text']) ? '' : html::escapeHTML($_POST['MMSTO_text']);
		
		$id = $qrc->encodeData($num,$text);
		self::returnImg($qrc,$id);
	}
	
	public static function getTemplate($qrc,$attr)
	{
		return empty($attr['call']) ? '' : 
			"<?php \n".
			"\$call = '".html::escapeHTML($attr['call'])."'; \n".
			"\$txt = '".html::escapeHTML($attr['msg'])."'; \n".
			"\$id = \$_ctx->qrcode->encodeData(\$call,\$msg); \n".
			"?>\n";
	}
	
	public static function encodeData($qrc,$args)
	{
		$data = '';
		if (count($args) > 0) {
			$data = 'MMSTO:'.QRcodeCore::escape($args[0],true); //mms num
			
			if (!empty($args[1]))
				$data .= ':'.QRcodeCore::escape($args[1],true).';'; // mms content
			
			$data .= ';';
		}
		return $data;
	}
}
?>