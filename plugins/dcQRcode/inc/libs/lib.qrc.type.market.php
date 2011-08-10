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

class QRcodeTypeMARKET extends QRcodeType
{
	public static function getTitle($qrc)
	{
		return __('Android market');
	}
	
	public static function getForm($qrc)
	{
		$combo_cats = array(__('Publisher')=>'pub',__('Package')=>'pname');
		$cat = empty($_POST['MARKET_cat']) ? '' : $_POST['MARKET_cat'];
		$cat = 'pub' == $cat ? 'pub' : 'pname';
		$search = empty($_POST['MARKET_search']) ? '' : html::escapeHTML($_POST['MARKET_search']);
		
		echo 
		'<p><label for="MARKET_cat">'.
		__('Category:').
		form::combo('MARKET_cat',$combo_cats,$cat).
		'</label></p>'.
		
		'<p><label for="MARKET_search" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('Search:').
		form::field('MARKET_search',60,255,$search).
		'</label></p>';
	}
	
	public static function saveForm($qrc)
	{
		$combo_cats = array(__('Publisher')=>'pub',__('Package')=>'pname');
		$cat = empty($_POST['MARKET_cat']) ? '' : $_POST['MARKET_cat'];
		$cat = 'pub' == $cat ? 'pub' : 'pname';
		$search = empty($_POST['MARKET_search']) ? '' : html::escapeHTML($_POST['MARKET_search']);
		
		$id = $qrc->encodeData($cat,$search);
		self::returnImg($qrc,$id);
	}
	
	public static function getTemplate($qrc,$attr)
	{
		return empty($attr['cat']) || empty($attr['search']) ? '' : 
			"<?php \n".
			" \$cat = '".html::escapeHTML($attr['cat'])."'; \n".
			" \$search = '".html::escapeHTML($attr['search'])."'; \n".
			" \$id = \$_ctx->qrcode->encodeData(\$cat,\$search); \n".
			"?>\n";
	}
	
	public static function encodeData($qrc,$args)
	{
		$data = '';
		if (count($args) == 2)
		{
			$data = 'market://search?q=';
			$data .= $args[0].'%3A';
			$data .=  $args[0] == 'pub' ?
				'%22'.QRcodeCore::escape($args[1]).'%22' : QRcodeCore::escape($args[1]);
// note:  pub & pname seem to be outdated !
//			$data .= QRcodeCore::escape($args[1]); // new market search
		}
		return $data;
	}
}
?>