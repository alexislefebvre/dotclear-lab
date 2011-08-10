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

class QRcodeTypeTEL extends QRcodeType
{
	public static function getTitle($qrc)
	{
		return __('Phone');
	}
	
	public static function getForm($qrc)
	{
		$text = empty($_POST['TEL_text']) ? '' : html::escapeHTML($_POST['TEL_text']);
		
		echo 
		'<p><label for="TEL_text" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('Phone number:').
		form::field('TEL_text',60,255,$text).
		'</label></p>';
	}
	
	public static function saveForm($qrc)
	{
		$text = empty($_POST['TEL_text']) ? '' : html::escapeHTML($_POST['TEL_text']);
		
		$id = $qrc->encodeData($text);
		self::returnImg($qrc,$id);
	}
	
	public static function getTemplate($qrc,$attr)
	{
		return empty($attr['call']) ? '' : 
			"<?php \n".
			"\$call = '".html::escapeHTML($attr['call'])."'; \n".
			"\$id = \$_ctx->qrcode->encodeData(\$call); \n".
			"?>\n";
	}
	
	public static function encodeData($qrc,$args)
	{
		return empty($args[0]) ? '' :
			'tel:'.QRcodeCore::escape($args[0],true).';';
	}
}
?>