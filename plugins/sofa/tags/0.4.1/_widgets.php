<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of sofa, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

if ($core->blog->settings->sofa->enable) {
	$core->addBehavior('initWidgets',array('sofaWidgets','initWidgets'));
}

class sofaWidgets
{	
	public static function initWidgets($w)
	{
		# Sort widget
		$w->create('sortby',__('Sort by'),array('sofaWidgets','sortWidget'));
		$w->sortby->setting('title',__('Title:'),__('Sort by'));
		foreach (self::getSortByList() as $k => $v) {
			$w->sortby->setting('sortby_'.$k,sprintf(__('Show sort by "%s"'),$v),true,'check');
		}
		$w->sortby->setting('homeonly',__('Home page only'),true,'check');
		# Filter widget
		$w->create('filterby',__('Filter by'),array('sofaWidgets','filterWidget'));
		$w->filterby->setting('title',__('Title:'),__('Filter by'));
		foreach (self::getFilterByList() as $k => $v) {
			$w->filterby->setting('filterby_'.$k,sprintf(__('Show filter by "%s"'),$v),true,'check');
		}
		$w->filterby->setting('homeonly',__('Home page only'),true,'check');
	}
	
	public static function sortWidget($w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		if (!$core->blog->settings->sofa->enable) { return; }
		
		$p_sort = '#(/sort/([^/]+)/(asc|desc))?(/page/([0-9]+))?$#i';
		
		preg_match($p_sort,http::getSelfURI(),$sort);
		
		$sort_by = count($sort) > 2 && $sort[2] !== '' ? $sort[2] : null;
		$order_by = count($sort) > 3 && $sort[3] !== '' ? $sort[3] : null;
		$page = count($sort) > 5 && $sort[5] !== '' ? $sort[5] : null;
		
		$url = preg_replace($p_sort,'',http::getSelfURI());
		$url .= '/sort/%1$s/%2$s';
		if (is_null($order_by)) {
			$order_by = 'desc';
		}
		if (!is_null($page)) {
			$url .= sprintf('/page/%d',$page);
		}
		
		$amask = '<a href="%1$s">%2$s</a>';
		$limask = '<li%1$s>%2$s</li>';

		$title = (strlen($w->title) > 0) ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '';

		$res = '';
		
		foreach (self::getSortByList() as $k => $v)
		{
			if ($w->{'sortby_'.$k}) {
				$class = '';
				$label = $v;
				if ($k === $sort_by) {
					$class = ' class="'.$order_by.'"';
					$label .= ' ('.($order_by === 'asc' ? __('ascending') : __('descending')).')';
					$order_by = $order_by === 'asc' ? 'desc' : 'asc';
				}
				$link = sprintf($amask,sprintf($url,$k,$order_by),$label);
				$res .= sprintf($limask,$class,$link);
			}
		}
		
		$res = !empty($res) ? '<ul>'.$res.'</ul>' : '';
		
		return
			'<div id="sortby">'.
			$title.
			$res.
			'</div>';
	}
	
	public static function filterWidget($w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		if (!$core->blog->settings->sofa->enable) { return; }
		
		$p_filter = '#(/filter/([^/]+)/(\w+))?((/sort/([^/]+)/(asc|desc))?(/page/([0-9]+))?)?$#i';
		
		preg_match($p_filter,http::getSelfURI(),$filter);
		
		$filter_by = count($filter) > 2 && $filter[2] !== '' ? $filter[2] : null;
		$filter_id = count($filter) > 3 && $filter[3] !== '' ? $filter[3] : null;
		$end = count($filter) > 4 && $filter[4] !== '' ? $filter[4] : null;
		
		$url = preg_replace($p_filter,'',http::getSelfURI());
		$url .= '/filter/%1$s/%2$s';
		if (!is_null($end)) {
			$url .= $end;
		}
		
		$amask = '<a href="%1$s">%2$s</a>';
		$limask = '<li%1$s>%2$s</li>';
		$ulmask = '%1$s<ul>%2$s</ul>';

		$title = (strlen($w->title) > 0) ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '';

		$res = '';
		
		foreach (self::getFilterByList() as $k => $v)
		{
			$list = $class = '';
			
			if ($w->{'filterby_'.$k}) {
				if ($k === 'selected') {
					if ($k === $filter_by) {
						$class = ' class="active"';
					}
					$link = sprintf($amask,sprintf($url,$k,'1'),$v);
					$list .= $link;
				}
				if ($k === 'category') {
					$rs = $core->blog->getCategories();
					while ($rs->fetch()) {
						$class = '';
						if ($rs->cat_url === $filter_id) {
							$class = ' class="active"';
						}
						$link = sprintf($amask,sprintf($url,$k,$rs->cat_url),$rs->cat_title);
						$list .= sprintf($limask,$class,$link);
					}
					$class = '';
					$list = sprintf($ulmask,$v,$list);
				}
				if ($k === 'author') {
					$rs = $core->blog->getPostsUsers();
					while ($rs->fetch()) {
						$class = '';
						if ($rs->user_id === $filter_id) {
							$class = ' class="active"';
						}
						$link = sprintf($amask,sprintf($url,$k,$rs->user_id),dcUtils::getUserCN($rs->user_id,$rs->user_name,$rs->user_firstname, $rs->user_displayname));
						$list .= sprintf($limask,$class,$link);
					}
					$class = '';
					$list = sprintf($ulmask,$v,$list);
				}
			}
			
			$res .= sprintf($limask,$class,$list);
		}
		
		$res = !empty($res) ? '<ul>'.$res.'</ul>' : '';
		
		return
			'<div id="filterby">'.
			$title.
			$res.
			'</div>';
	}
	
	public static function getSortByList()
	{
		return array(
			'title' => __('Title'),
			'selected' => __('Selected entry'),
			'author' => __('Author'),
			'date' => __('Date'),
			'id' => __('Entry ID'),
			'comment' => __('Comments number'),
			'trackback' => __('Trackbacks number')
		);
	}
	
	public static function getFilterByList()
	{
		return array(
			'selected' => __('Selected entry'),
			'category' => __('Category'),
			'author' => __('Author')
		);
	}
}

?>