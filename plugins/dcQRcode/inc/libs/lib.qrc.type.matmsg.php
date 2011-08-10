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

class QRcodeTypeMATMSG extends QRcodeType
{
	public static function getTitle($qrc)
	{
		return __('E-mail');
	}
	
	public static function getForm($qrc)
	{
		$email = empty($_POST['MATMSG_email']) ? '' : html::escapeHTML($_POST['MATMSG_email']);
		$title = empty($_POST['MATMSG_title']) ? '' : html::escapeHTML($_POST['MATMSG_title']);
		$text = empty($_POST['MATMSG_text']) ? '' : html::escapeHTML($_POST['MATMSG_text']);
		
		echo 
		'<p><label for="MATMSG_email" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('Receiver:').
		form::field('MATMSG_email',60,255,$email).
		'</label></p>'.
		
		'<p><label for="MATMSG_title" class="required">'.
		__('Subject:').
		form::field('MATMSG_title',60,255,$title).
		'</label></p>'.
		
		'<p><label for="MATMSG_text" class="required">'.
		__('Message:').
		form::field('MATMSG_text',60,255,$text).
		'</label></p>';
	}
	
	public static function saveForm($qrc)
	{
		$email = empty($_POST['MATMSG_email']) ? '' : html::escapeHTML($_POST['MATMSG_email']);
		$title = empty($_POST['MATMSG_title']) ? '' : html::escapeHTML($_POST['MATMSG_title']);
		$text = empty($_POST['MATMSG_text']) ? '' : html::escapeHTML($_POST['MATMSG_text']);
		
		$id = $qrc->encodeData($email,$title,$text);
		self::returnImg($qrc,$id);
	}
	
	public static function getTemplate($qrc,$attr)
	{
		return empty($attr['email']) ? '' : 
			"<?php \n".
			" \$email = '".html::escapeHTML($attr['email'])."'; \n".
			" \$title = '".(isset($attr['title']) ? html::escapeHTML($attr['title']) : '')."'; \n".
			" \$text = '".(isset($attr['text']) ? html::escapeHTML($attr['text']) : '')."'; \n".
			" \$id = \$_ctx->qrcode->encodeData(\$email,\$title,\$text); \n".
			"?>\n";
	}
	
	public static function encodeData($qrc,$args)
	{
		$data = '';
		if (count($args) == 1)
		{
			$data = 'mailto:'.self::escape($args[0],true).';';
		}
		# MATMSG (preformed email)
		elseif (count($args) > 1)
		{
			$data = 'MATMSG:';
			$data .= 'TO:'.QRcodeCore::escape($args[0],true).';';
			$data .= 'SUB:'.(empty($args[1]) ? '' : QRcodeCore::escape($args[1],true)).';';
			$data .= 'BODY:'.(empty($args[2]) ? '' : QRcodeCore::escape($args[2],true)).';';
			$data .= ';';
		}
		return $data;
	}
}
?>