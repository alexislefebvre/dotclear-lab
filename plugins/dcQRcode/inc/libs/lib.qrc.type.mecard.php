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

class QRcodeTypeMECARD extends QRcodeType
{
	public static function getTitle($qrc)
	{
		return __('Phonebook');
	}
	
	public static function getForm($qrc)
	{
		$lastname = empty($_POST['MECARD_lastname']) ? '' : html::escapeHTML($_POST['MECARD_lastname']);
		$firstname = empty($_POST['MECARD_firstname']) ? '' : html::escapeHTML($_POST['MECARD_firstname']);
		$address = empty($_POST['MECARD_address']) ? '' : html::escapeHTML($_POST['MECARD_address']);
		$phone = empty($_POST['MECARD_phone']) ? '' : html::escapeHTML($_POST['MECARD_phone']);
		$phone_b = empty($_POST['MECARD_phone_b']) ? '' : html::escapeHTML($_POST['MECARD_phone_b']);
		$email = empty($_POST['MECARD_email']) ? '' : html::escapeHTML($_POST['MECARD_email']);
		$email_b = empty($_POST['MECARD_email_b']) ? '' : html::escapeHTML($_POST['MECARD_email_b']);
		$url = empty($_POST['MECARD_url']) ? '' : html::escapeHTML($_POST['MECARD_url']);
		$birthdate = empty($_POST['MECARD_birthdate']) ? '' : html::escapeHTML($_POST['MECARD_birthdate']);
		$memo = empty($_POST['MECARD_memo']) ? '' : html::escapeHTML($_POST['MECARD_memo']);
		$nickname = empty($_POST['MECARD_nickname']) ? '' : html::escapeHTML($_POST['MECARD_nickname']);
		$videophone = empty($_POST['MECARD_videophone']) ? '' : html::escapeHTML($_POST['MECARD_videophone']);
		$sound = empty($_POST['MECARD_sound']) ? '' : html::escapeHTML($_POST['MECARD_sound']);
		
		echo 
		'<p><label for="MECARD_lastname" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('Last name:').
		form::field('MECARD_lastname',60,255,$lastname).
		'</label></p>'.
		
		'<p><label for="MECARD_firstname">'.
		__('First name:').
		form::field('MECARD_firstname',60,255,$firstname).
		'</label></p>'.
		
		'<p><label for="MECARD_address" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('Address:').
		form::field('MECARD_address',60,255,$address).
		'</label></p>'.
		
		'<p><label for="MECARD_phone" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('Phone:').
		form::field('MECARD_phone',60,255,$phone).
		'</label></p>'.
		
		'<p><label for="MECARD_phone_b">'.
		__('Second phone:').
		form::field('MECARD_phone_b',60,255,$phone_b).
		'</label></p>'.
		
		'<p><label for="MECARD_email" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('Email:').
		form::field('MECARD_email',60,255,$email).
		'</label></p>'.
		
		'<p><label for="MECARD_email_b">'.
		__('Second email:').
		form::field('MECARD_email_b',60,255,$email_b).
		'</label></p>'.
		
		'<p><label for="MECARD_url">'.
		__('URL:').
		form::field('MECARD_url',60,255,$url).
		'</label></p>'.
		
		'<p><label for="MECARD_birthdate">'.
		__('Birthdate:').
		form::field('MECARD_birthdate',60,255,$birthdate).
		'</label></p>'.
		
		'<p><label for="MECARD_memo">'.
		__('Memo:').
		form::field('MECARD_memo',60,255,$memo).
		'</label></p>'.
		
		'<p><label for="MECARD_nickname">'.
		__('Nickname:').
		form::field('MECARD_nickname',60,255,$nickname).
		'</label></p>'.
		
		'<p><label for="MECARD_videophone">'.
		__('Video phone:').
		form::field('MECARD_videophone',60,255,$videophone).
		'</label></p>'.
		
		'<p><label for="MECARD_sound">'.
		__('Sound:').
		form::field('MECARD_sound',60,255,$sound).
		'</label></p>'.
		'<p class="form-note">'.__('Designates a text string to be set as the kana name in the phonebook. ').'</p>';
	}
	
	public static function saveForm($qrc)
	{
		$lastname = empty($_POST['MECARD_lastname']) ? '' : html::escapeHTML($_POST['MECARD_lastname']);
		$firstname = empty($_POST['MECARD_firstname']) ? '' : html::escapeHTML($_POST['MECARD_firstname']);
		$address = empty($_POST['MECARD_address']) ? '' : html::escapeHTML($_POST['MECARD_address']);
		$phone = empty($_POST['MECARD_phone']) ? '' : html::escapeHTML($_POST['MECARD_phone']);
		$phone_b = empty($_POST['MECARD_phone_b']) ? '' : html::escapeHTML($_POST['MECARD_phone_b']);
		$email = empty($_POST['MECARD_email']) ? '' : html::escapeHTML($_POST['MECARD_email']);
		$email_b = empty($_POST['MECARD_email_b']) ? '' : html::escapeHTML($_POST['MECARD_email_b']);
		$url = empty($_POST['MECARD_url']) ? '' : html::escapeHTML($_POST['MECARD_url']);
		$birthdate = empty($_POST['MECARD_birthdate']) ? '' : html::escapeHTML($_POST['MECARD_birthdate']);
		$memo = empty($_POST['MECARD_memo']) ? '' : html::escapeHTML($_POST['MECARD_memo']);
		$nickname = empty($_POST['MECARD_nickname']) ? '' : html::escapeHTML($_POST['MECARD_nickname']);
		$videophone = empty($_POST['MECARD_videophone']) ? '' : html::escapeHTML($_POST['MECARD_videophone']);
		$sound = empty($_POST['MECARD_sound']) ? '' : html::escapeHTML($_POST['MECARD_sound']);
		
		if (!empty($lastname) && !empty($firstname)) {
			$name = $lastname.','.$firstname;
		}
		elseif (!empty($lastname)) {
			$name = $lastname;
		}
		else {
			$name = $firstname;
		}
		
		$id = $qrc->encodeData(
			$name,
			$address,
			array($phone,$phone_b),
			array($email,$email_b),
			$url,
			$birthdate,
			$memo,
			$nickname,
			$videophone,
			$sound
		);
		self::returnImg($qrc,$id);
	}
	
	public static function getTemplate($qrc,$attr)
	{
		return empty($attr['name']) ? '' : 
			"<?php \n".
			" \$name = '".html::escapeHTML($attr['name'])."'; \n".
			" \$address = '".(isset($attr['address']) ? html::escapeHTML($attr['address']) : '')."'; \n".
			" \$phone = '".(isset($attr['phone']) ? html::escapeHTML($attr['phone']) : '')."'; \n".
			" \$email = '".(isset($attr['email']) ? html::escapeHTML($attr['email']) : '')."'; \n".
			" \$id = \$_ctx->qrcode->encodeData(\$name,\$address,\$phone,\$email); \n".
			"?>\n";
	}
	
	public static function encodeData($qrc,$args)
	{
		$data = '';
		if (count($args) > 3)
		{
			$data = 'MECARD:';
			$data .= 'N:'.QRcodeCore::escape($args[0],true).';';
			$data .= 'ADR:'.QRcodeCore::escape($args[1],true).';';
			
			if (!is_array($args[2])) $args[2] = array($args[2]);
			foreach($args[2] as $param)
			{
				if (!empty($param))
					$data .= 'TEL:'.QRcodeCore::escape($param,true).';';
			}
			
			if (!is_array($args[3])) $args[3] = array($args[3]);
			foreach($args[3] as $param)
			{
				if (!empty($param))
					$data .= 'EMAIL:'.QRcodeCore::escape($param,true).';';
			}
			
			if (!empty($args[4]))
				$data .= 'URL:'.QRcodeCore::escape($args[4],true).';';
			
			if (!empty($args[5]))
				$data .= 'BDAY:'.QRcodeCore::escape($args[5],true).';';
			
			if (!empty($args[6]))
				$data .= 'NOTE:'.QRcodeCore::escape($args[6],true).';';
			
			if (!empty($args[7]))
				$data .= 'NICKNAME:'.QRcodeCore::escape($args[7],true).';';
			
			if (!empty($args[8]))
				$data .= 'TEL-AV:'.QRcodeCore::escape($args[8],true).';';
			
			if (!empty($args[9]))
				$data .= 'SOUND:'.QRcodeCore::escape($args[9],true).';';

			$data .= ';';
		}
		return $data;
	}
}
?>