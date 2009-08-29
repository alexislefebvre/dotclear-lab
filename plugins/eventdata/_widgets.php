<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventdata, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets',array('eventdataAdminWidget','events'));
$core->addBehavior('initWidgets',array('eventdataAdminWidget','meta'));
$core->addBehavior('initWidgets',array('eventdataAdminWidget','calendar'));

class eventdataAdminWidget
{
	public static function events($w)
	{
		global $core;

		# Create widget
		$w->create('eventdatalist',__('Events'),
			array('eventdataPublicWidget','events'));
		# Title
		$w->eventdatalist->setting('title',__('Title:'),
			__('Events'),'text');
		# Category
		$rs = $core->blog->getCategories(array('post_type'=>'post'));
		$categories = array('' => '', __('Uncategorized') => 'null');
		while ($rs->fetch()) {
			$categories[str_repeat('&nbsp;&nbsp;',$rs->level-1).'&bull; '.
			html::escapeHTML($rs->cat_title)] = $rs->cat_id;
		}
		$w->eventdatalist->setting('category',
			__('Category:'),'','combo',$categories);
		unset($rs,$categories);
		# Tag
		if ($core->plugins->moduleExists('metadata'))
			$w->eventdatalist->setting('tag',__('Tag:'),'');

		# Entries limit
		$w->eventdatalist->setting('limit',__('Entries limit:'),10);
		# Sort type
		$w->eventdatalist->setting('sortby',
			__('Order by:'),'eventdata_start','combo',array(
			__('Date') => 'post_dt',
			__('Title') => 'post_title',
			__('Event start') => 'eventdata_start',
			__('Event end') => 'eventdata_end'));
		# Sort order
		$w->eventdatalist->setting('sort',
			__('Sort:'),'asc','combo',array(
			__('Ascending') => 'asc',
			__('Descending') => 'desc'));
		# Selected entries only
		$w->eventdatalist->setting('selectedonly',
			__('Selected entries only'),0,'check');
		# Period
		$w->eventdatalist->setting('period',
			__('Period:'),'notfinished','combo',array(
			'' => '',
			__('Not started') => 'notstarted',
			__('Started') => 'started',
			__('Finished') => 'finished',
			__('Not finished') => 'notfinished',
			__('Ongoing') => 'ongoing',
			__('Outgoing') => 'outgoing'));
		# Date format
		$w->eventdatalist->setting('date_format',
			__('Date format of events:'),
			__('%Y-%m-%d'),'text');
		# Time format
		$w->eventdatalist->setting('time_format',
			__('Time format of events:'),
			__('%H:%M'),'text');
		# Item format
		$w->eventdatalist->setting('item_format',
			__('Text format of events:'),
			__('%T (%C)'),'text');
		# Item format
		$w->eventdatalist->setting('item_day_format',
			__('Text format of events on one day:'),
			__('%T (%C)'),'text');
		# Item mouseover format
		$w->eventdatalist->setting('over_format',
			__('Mouseover format of events:'),
			__('From %sd %st to %ed %et'),'text');
		# Item mouseover format
		$w->eventdatalist->setting('over_day_format',
			__('Mouseover format of events on one day:'),
			__('On %sd from %st to %et'),'text');
		# Home only
		$w->eventdatalist->setting('homeonly',
			__('Home page only'),1,'check');
	}

	public static function meta($w)
	{
		# Create widget
		$w->create('eventdatameta',__('Post Events'),
			array('eventdataPublicWidget','meta'));
		# Title
		$w->eventdatameta->setting('title',
			__('Title:'),
			__('Linked events'),'text');
		# Rows number
		$params['limit'] = abs((integer) $w->limit);
		# Date format
		$w->eventdatameta->setting('date_format',
			__('Date format of events:'),
			__('%Y-%m-%d'),'text');
		# Time format
		$w->eventdatameta->setting('time_format',
			__('Time format of events:'),
			__('%H:%M'),'text');
		# Item format
		$w->eventdatameta->setting('item_format',
			__('Text format of events:'),
			__('From %sd %st to %ed %et'),'text');
		# Item format
		$w->eventdatameta->setting('item_day_format',
			__('Text format of events on one day:'),
			__('On %sd from %st to %et'),'text');
	}

	public static function calendar($w)
	{
		global $core;

		# Create widget
		$w->create('eventdatacalendar',
			__('Events calendar'),
			array('eventdataPublicWidget','calendar'));
		# Title
		$w->eventdatacalendar->setting('title',
			__('Title:'),
			__('Events calendar'),'text');
		# Home only
		$w->eventdatacalendar->setting('homeonly',
			__('Home page only'),1,'check');
	}
}

class eventdataPublicWidget
{
	public static function events($w)
	{
		global $core;
		$E = new dcEventdata($core);

		# Plugin active
		if (!$core->blog->settings->eventdata_active) return;
		# Home only
		if ($w->homeonly && $core->url->type != 'default') return;
		$params['sql'] = '';
		# Period
		$params['period'] = $w->period;
		# Sort field
		$params['order'] = ($w->sortby && in_array($w->sortby,array('post_title','post_dt','eventdata_start','eventdata_end'))) ? 
			$w->sortby.' ' : 'post_dt ';
		# Sort order
		$params['order'] .= $w->sort == 'asc' ? 'asc' : 'desc';
		# Rows number
		$params['limit'] = abs((integer) $w->limit);
		# No post content
		$params['no_content'] = true;
		# Event type
		$params['eventdata_type'] = 'eventdata';
		# Post type
		$params['post_type'] = '';
		# Selected post only
		if ($w->selectedonly) {	$params['post_selected'] = 1; }
		# Category
		if ($w->category) {
			if ($w->category == 'null')
				$params['sql'] .= ' AND P.cat_id IS NULL ';
			elseif (is_numeric($w->category))
				$params['cat_id'] = (integer) $w->category;
			else
				$params['cat_url'] = $w->category;
		# If no paricular category is selected, remove unlisted categories
		} else {
			$cats_unlisted = @unserialize($E->S->eventdata_no_cats);
			if (is_array($cats_unlisted) && !empty($cats_unlisted)) {
				foreach($cats_unlisted AS $k => $cat_id) {
					$params['sql'] .= " AND P.cat_id != '$cat_id' ";
				}
			}
		}
		# Tag
		if ($core->plugins->moduleExists('metadata') && $w->tag)
			$params['meta_id'] = $w->tag;
		# Get posts
		$rs = $E->getPostsByEventdata($params);
		# No result
		if ($rs->isEmpty()) return;
		# Display
		$res =
		'<div class="eventdataslist">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		while ($rs->fetch()) {

			# If same day
			$start_day = dt::dt2str('%Y%m%d',$rs->eventdata_start);
			$end_day = dt::dt2str('%Y%m%d',$rs->eventdata_end);

			$over_format = ($start_day == $end_day && $w->over_day_format) ?
					$w->over_day_format : $w->over_format;

			$item_format = ($start_day == $end_day && $w->item_day_format) ?
					$w->item_day_format : $w->item_format;

			# Format items
			$fsd = dt::dt2str($w->date_format,$rs->eventdata_start);
			$fst = dt::dt2str($w->time_format,$rs->eventdata_start);
			$fs = $fsd.' '.$fst;
			$fed = dt::dt2str($w->date_format,$rs->eventdata_end);
			$fet = dt::dt2str($w->time_format,$rs->eventdata_end);
			$fe = $fed.' '.$fet;
			$duration = strtotime($rs->eventdata_end) - strtotime($rs->eventdata_start);
			$fd = eventdata::getReadableDuration($duration,null);
			$fl = html::escapeHTML($rs->eventdata_location);
			$fc = html::escapeHTML($rs->cat_title);
			$ft = html::escapeHTML($rs->post_title);

			# Replacement
			$over = str_replace(
				array('%S','%sd','%st','%E','%ed','%et','%D','%L','%C','%T','%%'),
				array($fs,$fsd,$fst,$fe,$fed,$fet,$fd,$fl,$fc,$ft,'%'),
				$over_format);
			$item = str_replace(
				array('%S','%sd','%st','%E','%ed','%et','%D','%L','%C','%T','%%'),
				array($fs,$fsd,$fst,$fe,$fed,$fet,$fd,$fl,$fc,$ft,'%'),
				$w->item_format);

			$res .= '<li><a href="'.$rs->getURL().'" title="'.$over.'">'.$item.'</a></li>';
		}
		$res .= '</ul></div>';

		return $res;
	}

	public static function meta($w)
	{
		global $core, $_ctx;
		$E = new dcEventdata($core);

		# Plugin active and on post page
		if (!$core->blog->settings->eventdata_active
		 || 'post.html' != $_ctx->current_tpl 
		 || !$_ctx->posts->post_id) return;

		# Rows number
		$limit = $w->limit ? abs((integer) $w->limit) : null;
		# Get posts
		$rs = $E->getEventdata('eventdata',$limit,null,null,$_ctx->posts->post_id);
		# No result
		if ($rs->isEmpty()) return;
		# Display
		$res =
		'<div class="eventdataslist">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		while ($rs->fetch()) {

			# If same day
			$start_day = dt::dt2str('%Y%m%d',$rs->eventdata_start);
			$end_day = dt::dt2str('%Y%m%d',$rs->eventdata_end);

			$item_format = ($start_day == $end_day && $w->item_day_format) ?
					$w->item_day_format : $w->item_format;

			# Format items
			$fsd = dt::dt2str($w->date_format,$rs->eventdata_start);
			$fst = dt::dt2str($w->time_format,$rs->eventdata_start);
			$fs = $fsd.' '.$fst;
			$fed = dt::dt2str($w->date_format,$rs->eventdata_end);
			$fet = dt::dt2str($w->time_format,$rs->eventdata_end);
			$fe = $fed.' '.$fet;
			$duration = strtotime($rs->eventdata_end) - strtotime($rs->eventdata_start);
			$fd = eventdata::getReadableDuration($duration,null);
			$fl = html::escapeHTML($rs->eventdata_location);

			# Replacement
			$item = str_replace(
				array('%S','%sd','%st','%E','%ed','%et','%D','%L','%%'),
				array($fs,$fsd,$fst,$fe,$fed,$fet,$fd,$fl,'%'),
				$item_format);

			$res .= '<li>'.html::escapeHTML($item).'</li>';
		}
		$res .= '</ul></div>';

		return $res;
	}

	public static function calendar($w)
	{
		global $core;

		# Generic calendar Object
		$res = eventdata::arrayCalendar($core);

		return 
		'<div class="eventdatacalendar">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		# Events calendar
		eventdata::drawCalendar($core,$res).
		'</div>';
	}
}
?>