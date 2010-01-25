<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of yafr, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
$core->addBehavior('initWidgets',array('yafrWidgets','initWidgets'));

class yafrWidgets
{
	public static function initWidgets($w)
	{
		global $core;

		$w->create('feed',__('Feed reader'),array('publicyafr','Widget'));
		$w->feed->setting('title',__('Title:'),__('Feed reader'));
		$w->feed->setting('url', __('Feed URL:'),'');
		$w->feed->setting('showtitle',__('Display feed main title'),0,'check');	
		$w->feed->setting('showdesc',__('Display feed description'),0,'check');	
		$w->feed->setting('f_limit',__('Entries limit:'),5);
		$w->feed->setting('t_limit',__('Entry title length limit:'),40);
		$w->feed->setting('fe_limit',__('Entry content length limit:'),80);
		$w->feed->setting('cleancontent',__('Remove HTML content (warning!)'),1,'check');	
		$w->feed->setting('dateformat',
			__('Date format (leave empty to use default blog format):'),
			$core->blog->settings->date_format.','.$core->blog->settings->time_format);
		$w->feed->setting('stringformat',
			__('String format (%1$s = date | %2$s = title | %3$s = author | %4$s = content | %5$s = URL | %6$s = description):'),
			'<a href="%5$s" title="%4$s(%3$s)">%2$s<br/>%1$s</a>');
		$w->feed->setting('CSS', __('Supplementary CSS Class:'),'');
		$w->feed->setting('homeonly',
			__('Home page only'),0,'check');
	}
}
?>