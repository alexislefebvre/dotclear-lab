<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of xiti, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

require dirname(__FILE__).'/_widgets.php';

$core->addBehavior('publicFooterContent',array('xitiPublic','publicFooterContent'));

class xitiPublic
{
	public static function publicFooterContent($core)
	{
		global $core;
		if (!$core->blog->settings->xiti_active 
		 || !$core->blog->settings->xiti_footer 
		 || '' == $core->blog->settings->xiti_serial) return;
		
		echo 
		'<div class="xiti-footer">'.
		self::xitiLink($core).
		'</div>';
	}

	public static function xitiWidget($w)
	{
		global $core;
		if (!$core->blog->settings->xiti_active 
		 || '' == $core->blog->settings->xiti_serial) return;

		return 
		'<div class="xiti-widget">'.
		(strlen($w->title) > 0 ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		self::xitiLink($core).
		'</div>';
	}

	public static function xitiLink($core)
	{
		$combo_image = array(
			0 => array('w'=>39,'h'=>25,'n'=>'hit'),
			1 => array('w'=>80,'h'=>15,'n'=>'g'),
			2 => array('w'=>80,'h'=>15,'n'=>'bgc'),
			3 => array('w'=>80,'h'=>15,'n'=>'grcg'),
			4 => array('w'=>80,'h'=>15,'n'=>'oco'),
			5 => array('w'=>80,'h'=>15,'n'=>'orcr'),
			6 => array('w'=>80,'h'=>15,'n'=>'rcg'),
			7 => array('w'=>80,'h'=>15,'n'=>'vcg')
		);
		$i = (integer) $core->blog->settings->xiti_image;
		$s = html::escapeHTML($core->blog->settings->xiti_serial);
		
		return 
		"<p>".
		"<a href=\"http://www.xiti.com/xiti.asp?s=".$s."\" title=\"WebAnalytics\" target=\"_top\">\n".
		"<script type=\"text/javascript\">\n".
		"<!--\n".
		"Xt_param = 's=".$s."&p=document.title';\n".
		"try {Xt_r = top.document.referrer;}\n".
		"catch(e) {Xt_r = document.referrer; }\n".
		"Xt_h = new Date();\n".
		"Xt_i = '<img width=\"".$combo_image[$i]['w']."\" height=\"".$combo_image[$i]['h']."\" border=\"0\" alt=\"\" ';\n".
		"Xt_i += 'src=\"http://logv10.xiti.com/".$combo_image[$i]['n'].".xiti?'+Xt_param;\n".
		"Xt_i += '&hl='+Xt_h.getHours()+'x'+Xt_h.getMinutes()+'x'+Xt_h.getSeconds();\n".
		"if(parseFloat(navigator.appVersion)>=4)\n".
		"{Xt_s=screen;Xt_i+='&r='+Xt_s.width+'x'+Xt_s.height+'x'+Xt_s.pixelDepth+'x'+Xt_s.colorDepth;}\n".
		"document.write(Xt_i+'&ref='+Xt_r.replace(/[<>\"]/g, '').replace(/&/g, '$')+'\" title=\"Internet Audience\">');\n".
		"//-->\n".
		"</script>\n".
		"<noscript>\n".
		"<img width=\"".$combo_image[$i]['w']."\" height=\"".$combo_image[$i]['h']."\" src=\"http://logv10.xiti.com/".$combo_image[$i]['n'].".xiti?s=".$s."&p=\" alt=\"WebAnalytics\" />\n".
		"</noscript></a>".
		"</p>\n";
	}
}
?>