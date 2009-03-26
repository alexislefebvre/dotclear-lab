<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dayMode, a plugin for Dotclear 2.
#
# Copyright (c) 2006-2009 Pep and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('widgetsDayMode','init'));

class widgetsDayMode
{
	public static function calendar(&$w)
	{
		global $core;

		if (!$core->blog->settings->daymode_active) return;
		
		if ($w->archiveonly && $core->url->type != 'archive') {
			return;
		}

		$calendar = new dcCalendar($GLOBALS['core'], $GLOBALS['_ctx']);
		$calendar->weekstart = $w->weekstart;

		$res =
		'<div id="calendar">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		$calendar->draw().
		'</div>';
		return $res;
	}

	public static function init(&$w)
	{
	    $w->create('calendar',__('Calendar'),array('widgetsDayMode','calendar'));
	    $w->calendar->setting('title',__('Title:'),__('Calendar'));
	    $w->calendar->setting(
	    	'weekstart',
	    	__('Week start'),
	    	0,
	    	'combo',
	    	array_flip(array(
	    		__('Sunday'),
	    		__('Monday'),
	    		__('Tuesday'),
	    		__('Wednesday'),
	    		__('Thursday'),
	    		__('Friday'),
	    		__('Saturday')
	    	))
	    );
	    $w->calendar->setting('archiveonly',__('Archives only'),1,'check');
	}
}
?>