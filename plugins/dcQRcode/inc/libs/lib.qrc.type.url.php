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

class QRcodeTypeURL extends QRcodeType
{
	public static function getTitle($qrc)
	{
		return __('Bookmark');
	}
	
	public static function getForm($qrc)
	{
		$title = empty($_POST['URL_title']) ? '' : html::escapeHTML($_POST['URL_title']);
		$url = empty($_POST['URL_url']) ? '' : html::escapeHTML($_POST['URL_url']);
		
		echo 
		'<p><label for="URL_title">'.
		__('Title:').
		form::field('URL_title',60,255,$title).
		'</label></p>'.
		
		'<p><label for="URL_url" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('URL:').
		form::field('URL_url',60,255,$url).
		'</label></p>';
	}
	
	public static function saveForm($qrc)
	{
		$title = empty($_POST['URL_title']) ? '' : html::escapeHTML($_POST['URL_title']);
		$url = empty($_POST['URL_url']) ? '' : html::escapeHTML($_POST['URL_url']);
		$mebkm = !empty($title);
		
		$qrc->setMebkm($mebkm);
		$id = $qrc->encodeData($url,$title);
		self::returnImg($qrc,$id);
	}
	
	public static function getTemplate($qrc,$attr)
	{
		return empty($attr['url']) ? '' : 
			"<?php \n".
			" \$_ctx->qrcode->setMebkm(".(!empty($attr['use_mebkm']))."); \n".
			" \$title = '".(isset($attr['title']) ? html::escapeHTML($attr['title']) : '')."'; \n".
			" \$url = '".html::escapeHTML($attr['url'])."'; \n".
			" \$id = \$_ctx->qrcode->encodeData(\$url,\$title); \n".
			"?>\n";
	}
	
	public static function encodeData($qrc,$args)
	{
		$data = '';
		if (count($args) > 0)
		{
			if ($qrc->getParam('mebkm')) {
				$data = 'MEBKM:TITLE:';
				$data .= !empty($args[1]) ? QRcodeCore::escape($args[1],true) : __('URL');
				$data .= ';URL:'.QRcodeCore::escape($args[0],true).';';
			}
			else {
				$data = QRcodeCore::escape($args[0]);
			}
		}
		return $data;
	}
}
?>