<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of dayMode, a plugin for DotClear2.
# Copyright (c) 2006-2008 Pep and contributors. All rights
# reserved.
#
# This plugin is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This plugin is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this plugin; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

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