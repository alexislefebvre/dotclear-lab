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

class QRcodeTypeIAPPLI extends QRcodeType
{
	public static function getTitle($qrc)
	{
		return __('i-appli');
	}
	
	public static function getForm($qrc)
	{
		$url = empty($_POST['IAPPLI_url']) ? '' : html::escapeHTML($_POST['IAPPLI_url']);
		$cmd = empty($_POST['IAPPLI_cmd']) ? '' : html::escapeHTML($_POST['IAPPLI_cmd']);
		$params = array();
		for($i = 0; $i < 16; $i++)
		{
			$params[$i] = empty($_POST['IAPPLI_param_'.$i]) ? '' : html::escapeHTML($_POST['IAPPLI_param_'.$i]);
		}
		
		echo 
		'<p><label for="IAPPLI_url" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('ADF URL:').
		form::field('IAPPLI_url',60,255,$url).
		'</label></p>'.
		
		'<p><label for="IAPPLI_cmd" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('Command:').
		form::field('IAPPLI_cmd',60,255,$cmd).
		'</label></p>';

		foreach($params as $k => $v)
		{
			echo 
			'<p><label for="IAPPLI_param_'.$k.'">'.
			sprintf(__('Param %s'),($k+1)).
			form::field('IAPPLI_param_'.$k,60,255,$v).
			'</label></p>';
		}
		
		echo 
		'<p class="form-note">'.
		__('Designates a text string to be set as the parameter sent to the i-appli to be activated. (1 to 255 bytes)').' <br />'. 
		__('The "name" and "value" are separated by a comma (,).').' <br />'.
		__('16 parameters can be designated within a single LAPL: identifier.').
		'</p>';
	}
	
	public static function saveForm($qrc)
	{
		$url = empty($_POST['IAPPLI_url']) ? '' : html::escapeHTML($_POST['IAPPLI_url']);
		$cmd = empty($_POST['IAPPLI_cmd']) ? '' : html::escapeHTML($_POST['IAPPLI_cmd']);
		$params = array();
		for($i = 0; $i < 16; $i++)
		{
			$params[$i] = empty($_POST['IAPPLI_param_'.$k]) ? '' : html::escapeHTML($_POST['IAPPLI_param_'.$k]);
		}
		
		$id = $qrc->encodeData($summary,$startdate,$enddate);
		self::returnImg($qrc,$id);
	}
	
	public static function getTemplate($qrc,$attr)
	{
		return empty($attr['url']) ||  empty($attr['cmd']) ? '' : 
			"<?php \n".
			" \$url = '".html::escapeHTML($attr['url'])."'; \n".
			" \$cmd = '".html::escapeHTML($attr['cmd'])."'; \n".
			//toto: add params
			" \$id = \$_ctx->qrcode->encodeData(\$url,\$cmd,array()); \n".
			"?>\n";
	}
	
	public static function encodeData($qrc,$args)
	{
		$data = '';
		if (count($args) > 1)
		{
			$data = 'LAPL:';
			$data .= 'ADFURL:'.QRcodeCore::escape($args[0],true).';';
			$data .= 'CMD:'.QRcodeCore::escape($args[1],true).';';
			if (!empty($args[2]) && is_array($args[2]))
			{
				foreach($args[2] as $param)
				{
					if (!empty($param))
						$data .= 'PARAM:'.QRcodeCore::escape($param,true).';';
				}
			}
			$data .= ';';
		}
		return $data;
	}
}
?>