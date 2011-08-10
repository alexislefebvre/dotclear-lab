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

class QRcodeTypeICAL extends QRcodeType
{
	public static function getTitle($qrc)
	{
		return __('Calendar');
	}
	
	public static function getForm($qrc)
	{
		$summary = empty($_POST['ICAL_summary']) ? '' : html::escapeHTML($_POST['ICAL_summary']);
		$startdate = empty($_POST['ICAL_startdate']) ? '' : html::escapeHTML($_POST['ICAL_startdate']);
		$enddate = empty($_POST['ICAL_enddate']) ? '' : html::escapeHTML($_POST['ICAL_enddate']);
		
		echo 
		'<p><label for="ICAL_summary" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('Summary').
		form::field('ICAL_summary',60,255,$summary).
		'</label></p>'.
		
		'<p><label for="ICAL_startdate" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('Start date:').
		form::field('ICAL_startdate',60,255,$startdate).
		'</label></p>'.
		
		'<p><label for="ICAL_enddate" class="required">'.
		'<abbr title="'.__('Required field').'">*</abbr> '.
		__('End date:').
		form::field('ICAL_enddate',60,255,$enddate).
		'</label></p>';
	}
	
	public static function saveForm($qrc)
	{
		$summary = empty($_POST['ICAL_summary']) ? '' : html::escapeHTML($_POST['ICAL_summary']);
		$startdate = empty($_POST['ICAL_startdate']) ? '' : html::escapeHTML($_POST['ICAL_startdate']);
		$enddate = empty($_POST['ICAL_enddate']) ? '' : html::escapeHTML($_POST['ICAL_enddate']);
		
		$id = $qrc->encodeData($summary,$startdate,$enddate);
		self::returnImg($qrc,$id);
	}
	
	public static function getTemplate($qrc,$attr)
	{
		return empty($attr['summary']) ||  empty($attr['startdate']) || empty($attr['enddate']) ? '' : 
			"<?php \n".
			" \$summary = '".html::escapeHTML($attr['summary'])."'; \n".
			" \$startdate = '".html::escapeHTML($attr['startdate'])."'; \n".
			" \$enddate = '".html::escapeHTML($attr['enddate'])."'; \n".
			" \$id = \$_ctx->qrcode->encodeData(\$summary,\$startdate,\$enddate); \n".
			"?>\n";
	}
	
	public static function encodeData($qrc,$args)
	{
		$data = '';
		if (count($args) == 3)
		{
			$data = 'BEGIN:VEVENT'."\n";
			$data .= 'SUMMARY:'.QRcodeCore::escape($args[0])."\n";
			$data .= 'DTSTART:'.$args[1]."\n";
			$data .= 'DTEND:'.$args[2]."\n";
			$data .= 'END:VEVENT'."\n";
		}
		return $data;
	}
}
?>