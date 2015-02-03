<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dayMode, a plugin for Dotclear 2.
#
# Copyright (c) 2006-2010 Pep and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) return;

$core->addBehavior('initWidgets',array('widgetsDayMode','init'));

class widgetsDayMode
{
	public static function calendar($w)
	{
		global $core;

		if (!$core->blog->settings->daymode->daymode_active) return;

		if ($w->offline)
			return;

		if (($w->homeonly == 1 && $core->url->type != 'default') ||
			($w->homeonly == 2 && $core->url->type == 'default') ||
      ($w->homeonly == 3 && $core->url->type != 'archive'))
			return;

		$calendar = new dcCalendar($GLOBALS['core'], $GLOBALS['_ctx']);
		$calendar->weekstart = $w->weekstart;

		$res =
		($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').
		$calendar->draw();

		return $w->renderDiv($w->content_only,'calendar '.$w->class,'',$res);
	}

	public static function init($w)
	{
	    $w->create('calendar',__('DayMode: calendar'),array('widgetsDayMode','calendar'),
			null,
			__('Tickets calendar'));
	    $w->calendar->setting('title',__('Title:'),__('Calendar'));
	    $w->calendar->setting(
	    	'weekstart',
	    	__('First day:'),
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
  		$w->calendar->setting('homeonly',__('Display on:'),3,'combo',
  			array(
  				__('All pages') => 0,
  				__('Home page only') => 1,
  				__('Except on home page') => 2,
  				__('Archives only') => 3
  				)
  		);
  		$w->calendar->setting('content_only',__('Content only'),0,'check');
  		$w->calendar->setting('class',__('CSS class:'),'');
  		$w->calendar->setting('offline',__('Offline'),0,'check');
	}
}