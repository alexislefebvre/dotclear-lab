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

class QRcodeTypeBIZCARD extends QRcodeType
{
	public static function getTitle($qrc)
	{
		return __('Business');
	}
	
	public static function getForm($qrc)
	{
		$lastname = empty($_POST['BIZCARD_lastname']) ? '' : html::escapeHTML($_POST['BIZCARD_lastname']);
		$firstname = empty($_POST['BIZCARD_firstname']) ? '' : html::escapeHTML($_POST['BIZCARD_firstname']);
		$job = empty($_POST['BIZCARD_job']) ? '' : html::escapeHTML($_POST['BIZCARD_job']);
		$company = empty($_POST['BIZCARD_company']) ? '' : html::escapeHTML($_POST['BIZCARD_company']);
		$address = empty($_POST['BIZCARD_address']) ? '' : html::escapeHTML($_POST['BIZCARD_address']);
		$phone = empty($_POST['BIZCARD_phone']) ? '' : html::escapeHTML($_POST['BIZCARD_phone']);
		$email = empty($_POST['BIZCARD_email']) ? '' : html::escapeHTML($_POST['BIZCARD_email']);
		
		echo 
		'<p><label for="BIZCARD_lastname" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('Last name:').
		form::field('BIZCARD_lastname',60,255,$lastname).
		'</label></p>'.
		
		'<p><label for="BIZCARD_firstname" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('First name:').
		form::field('BIZCARD_firstname',60,255,$firstname).
		'</label></p>'.
		
		'<p><label for="BIZCARD_job" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('Job:').
		form::field('BIZCARD_job',60,255,$job).
		'</label></p>'.
		
		'<p><label for="BIZCARD_company">'.
		__('Company:').
		form::field('BIZCARD_company',60,255,$company).
		'</label></p>'.
		
		'<p><label for="BIZCARD_address">'.
		__('Address:').
		form::field('BIZCARD_address',60,255,$address).
		'</label></p>'.
		
		'<p><label for="BIZCARD_phone">'.
		__('Phone:').
		form::field('BIZCARD_phone',60,255,$phone).
		'</label></p>'.
		
		'<p><label for="BIZCARD_email">'.
		__('Email:').
		form::field('BIZCARD_email',60,255,$email).
		'</label></p>';
	}
	
	public static function saveForm($qrc)
	{
		$lastname = empty($_POST['BIZCARD_lastname']) ? '' : html::escapeHTML($_POST['BIZCARD_lastname']);
		$firstname = empty($_POST['BIZCARD_firstname']) ? '' : html::escapeHTML($_POST['BIZCARD_firstname']);
		$job = empty($_POST['BIZCARD_job']) ? '' : html::escapeHTML($_POST['BIZCARD_job']);
		$company = empty($_POST['BIZCARD_company']) ? '' : html::escapeHTML($_POST['BIZCARD_company']);
		$address = empty($_POST['BIZCARD_address']) ? '' : html::escapeHTML($_POST['BIZCARD_address']);
		$phone = empty($_POST['BIZCARD_phone']) ? '' : html::escapeHTML($_POST['BIZCARD_phone']);
		$email = empty($_POST['BIZCARD_email']) ? '' : html::escapeHTML($_POST['BIZCARD_email']);
		
		$id = $qrc->encodeData(
			$lastname,
			$firstname,
			$job,
			$company,
			$address,
			$phone,
			$email
		);
		self::returnImg($qrc,$id);
	}
	
	public static function getTemplate($qrc,$attr)
	{
		return empty($attr['lastname']) || empty($attr['firstname']) || empty($attr['job']) ? '' : 
			"<?php \n".
			" \$lastname = '".html::escapeHTML($attr['lastname'])."'; \n".
			" \$firstname = '".html::escapeHTML($attr['firstname'])."'; \n".
			" \$job = '".html::escapeHTML($attr['job'])."'; \n".
			" \$address = '".(isset($attr['address']) ? html::escapeHTML($attr['address']) : '')."'; \n".
			" \$company = '".(isset($attr['company']) ? html::escapeHTML($attr['company']) : '')."'; \n".
			" \$phone = '".(isset($attr['phone']) ? html::escapeHTML($attr['phone']) : '')."'; \n".
			" \$email = '".(isset($attr['email']) ? html::escapeHTML($attr['email']) : '')."'; \n".
			" \$id = \$_ctx->qrcode->encodeData(\$lastname,\$firstname,\$job,\$address,\$company,\$phone,\$email); \n".
			"?>\n";
	}
	
	public static function encodeData($qrc,$args)
	{
		$data = '';
		if (count($args) > 2)
		{
			$data = 'BIZCARD:';
			$data .= 'N:'.QRcodeCore::escape($args[0],true).';'; // lastname
			$data .= 'X:'.QRcodeCore::escape($args[1],true).';'; // firstname
			$data .= 'T:'.QRcodeCore::escape($args[2],true).';'; // job
			
			if (!empty($args[3]))
				$data .= 'C:'.QRcodeCore::escape($args[3],true).';'; // company
			
			if (!empty($args[4]))
				$data .= 'A:'.QRcodeCore::escape($args[4],true).';'; // business address
			
			if (!empty($args[5]))
				$data .= 'B:'.QRcodeCore::escape($args[5],true).';'; // business phone
			
			if (!empty($args[6]))
				$data .= 'E:'.QRcodeCore::escape($args[6],true).';'; // business email
			
			$data .= ';';
		}
		return $data;
	}
}
?>