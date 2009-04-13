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

if (!defined('DC_RC_PATH')) return;

$core->addBehavior('initWidgets',array('eventdataWidget','eventdataInitListWidget'));
$core->addBehavior('initWidgets',array('eventdataWidget','eventdataInitMetaWidget'));
//$core->addBehavior('initWidgets',array('eventdataWidget','eventdataInitCalendardWidget'));

class eventdataWidget
{
	public static function eventdataInitListWidget(&$w)
	{
		global $core;

		# Create widget
		$w->create('eventdatalist',__('Events'),
			array('eventdataWidget','evendataParseListWidget'));
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
		$w->eventdatalist->setting('category',__('Category:'),'','combo',$categories);
		unset($rs,$categories);
		# Tag
		if ($core->plugins->moduleExists('metadata'))
			$w->eventdatalist->setting('tag',__('Tag:'),'');

		# Entries limit
		$w->eventdatalist->setting('limit',__('Entries limit:'),10);
		# Sort type
		$w->eventdatalist->setting('sortby',__('Order by:'),'eventdata_start','combo',array(
			__('Date') => 'post_dt',
			__('Title') => 'post_title',
			__('Event start') => 'eventdata_start',
			__('Event end') => 'eventdata_end'));
		# Sort order
		$w->eventdatalist->setting('sort',__('Sort:'),'asc','combo',array(
			__('Ascending') => 'asc',
			__('Descending') => 'desc'));
		# Selected entries only
		$w->eventdatalist->setting('selectedonly',__('Selected entries only'),0,'check');
		# Period
		$w->eventdatalist->setting('period',__('Period:'),'notfinished','combo',array(
			'' => '',
			__('Not started') => 'notstarted',
			__('Started') => 'started',
			__('Finished') => 'finished',
			__('Not finished') => 'notfinished',
			__('Ongoing') => 'ongoing',
			__('Outgoing') => 'outgoing'));
		# Date format
		$w->eventdatalist->setting('date_format',__('Date format of items:'),
			__('%Y-%m-%d %H:%M'),'text');
		# Item format
		$w->eventdatalist->setting('item_format',__('Text format of items:'),
			__('%T (%C)'),'text');
		# Item mouseover format
		$w->eventdatalist->setting('over_format',__('Mouseover format of items:'),
			__('From %S to %E'),'text');
		# Home only
		$w->eventdatalist->setting('homeonly',__('Home page only'),1,'check');
	}

	public static function evendataParseListWidget(&$w)
	{
		global $core;
		$E = new eventdata($core);

		# Plugin active
		if (!$E->S->eventdata_option_active) return;
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
			# Format items
			$fs = dt::dt2str($w->date_format,$rs->eventdata_start);
			$fe = dt::dt2str($w->date_format,$rs->eventdata_end);
			$fd = eventdata::getReadableDuration(
				(strtotime($rs->eventdata_end) - strtotime($rs->eventdata_start)),null);
			$fl = html::escapeHTML($rs->eventdata_location);
			$fc = html::escapeHTML($rs->cat_title);
			$ft = html::escapeHTML($rs->post_title);
			$over = str_replace(
				array('%S','%E','%D','%L','%C','%T','%%'),
				array($fs,$fe,$fd,$fl,$fc,$ft,'%'),
				$w->over_format);
			$item = str_replace(
				array('%S','%E','%D','%L','%C','%T','%%'),
				array($fs,$fe,$fd,$fl,$fc,$ft,'%'),
				$w->item_format);

			$res .= '<li><a href="'.$rs->getURL().'" title="'.$over.'">'.$item.'</a></li>';
		}
		$res .= '</ul></div>';

		return $res;
	}

	public static function eventdataInitMetaWidget(&$w)
	{
		# Create widget
		$w->create('eventdatameta',__('Post Events'),
			array('eventdataWidget','evendataParseMetaWidget'));
		# Title
		$w->eventdatameta->setting('title',__('Title:'),
			__('Linked events'),'text');
		# Rows number
		$params['limit'] = abs((integer) $w->limit);
		# Date format
		$w->eventdatameta->setting('date_format',__('Date format of items:'),
			__('%Y-%m-%d %H:%M'),'text');
		# Item format
		$w->eventdatameta->setting('item_format',__('Text format of items:'),
			__('From %S to %E'),'text');
	}

	public static function evendataParseMetaWidget(&$w)
	{
		global $core, $_ctx;
		$E = new eventdata($core);

		# Plugin active and on post page
		if (!$E->S->eventdata_option_active
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
			# Format items
			$fs = dt::dt2str($w->date_format,$rs->eventdata_start);
			$fe = dt::dt2str($w->date_format,$rs->eventdata_end);
			$fd = eventdata::getReadableDuration(
				(strtotime($rs->eventdata_end) - strtotime($rs->eventdata_start)),null);
			$fl = html::escapeHTML($rs->eventdata_location);
			$item = str_replace(
				array('%S','%E','%D','%L','%%'),
				array($fs,$fe,$fd,$fl,'%'),
				$w->item_format);

			$res .= '<li>'.html::escapeHTML($item).'</li>';
		}
		$res .= '</ul></div>';

		return $res;
	}
}
?>