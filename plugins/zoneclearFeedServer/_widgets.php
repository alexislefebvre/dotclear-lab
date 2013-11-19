<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of zoneclearFeedServer, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {

	return null;
}

$core->addBehavior(
	'initWidgets',
	array('zoneclearFeedServerWidget', 'adminSource')
);
$core->addBehavior(
	'initWidgets',
	array('zoneclearFeedServerWidget', 'adminNumber')
);

/**
 * @ingroup DC_PLUGIN_ZONECLEARFEEDSERVER
 * @brief Mix your blog with a feeds planet - widgets methods.
 * @since 2.6
 */
class zoneclearFeedServerWidget
{
	/**
	 * Widget configuration for sources list.
	 * 
	 * @param  dcWidget $w dcWidget instance
	 */
	public static function adminSource($w)
	{
		$w->create(
			'zcfssource',
			__('Feeds server : sources'),
			array('zoneclearFeedServerWidget', 'publicSource'),
			null,
			__('List sources of feeds')
		);
		$w->zcfssource->setting(
			'title',
			__('Title:'),
			__('Feeds sources'),
			'text'
		);
		$w->zcfssource->setting(
			'sortby',
			__('Order by:'),
			'feed_upd_last',
			'combo',
			array(
				__('Last update')	=> 'feed_upd_last',
				__('Name')		=> 'lowername',
				__('Create date')	=> 'feed_creadt'
			)
		);
		$w->zcfssource->setting(
			'sort',
			__('Sort:'),
			'desc',
			'combo',
			array(
				__('Ascending')	=> 'asc',
				__('Descending')	=> 'desc'
			)
		);
		$w->zcfssource->setting(
			'limit',
			__('Limit:'),
			10,
			'text'
		);
		$w->zcfssource->setting(
			'pagelink',
			__('Add link to feeds page'),
			1,
			'check'
		);
		$w->zcfssource->setting(
			'homeonly',
			__('Display on:'),
			0,
			'combo',
			array(
				__('All pages') => 0,
				__('Home page only') => 1,
				__('Except on home page') => 2
			)
		);
		$w->zcfssource->setting(
			'content_only',
			__('Content only'),
			0,
			'check'
		);
		$w->zcfssource->setting(
			'class',
			__('CSS class:'),
			''
		);
	}

	/**
	 * Widget configuration for feeds info.
	 * 
	 * @param  dcWidget $w dcWidget instance
	 */
	public static function adminNumber($w)
	{
		$w->create(
			'zcfsnumber',
			__('Feeds server : numbers'),
			array('zoneclearFeedServerWidget', 'publicNumber'),
			null,
			__('Show some numbers about feeds')
		);
		$w->zcfsnumber->setting(
			'title',
			__('Title:')
			,__('Feeds numbers'),
			'text'
		);

		# Feed
		$w->zcfsnumber->setting(
			'feed_show',
			__('Show feeds count'),
			1,
			'check'
		);
		$w->zcfsnumber->setting(
			'feed_title',
			__('Title for feeds count:'),
			__('Feeds:'),
			'text'
		);

		# Entry
		$w->zcfsnumber->setting(
			'entry_show',
			__('Show entries count'),
			1,
			'check'
		);
		$w->zcfsnumber->setting(
			'entry_title',
			__('Title for entries count:'),
			__('Entries:'),
			'text'
		);
		
		$w->zcfsnumber->setting(
			'homeonly',
			__('Display on:'),
			0,
			'combo',
			array(
				__('All pages') => 0,
				__('Home page only') => 1,
				__('Except on home page') => 2
			)
		);
		$w->zcfsnumber->setting(
			'content_only',
			__('Content only'),
			0,
			'check'
		);
		$w->zcfsnumber->setting(
			'class',
			__('CSS class:'),
			''
		);
	}

	/**
	 * Widget for sources list.
	 * 
	 * @param  dcWidget $w dcWidget instance
	 */
	public static function publicSource($w)
	{
		global $core; 
		
		if (!$core->blog->settings->zoneclearFeedServer->zoneclearFeedServer_active 
		 || $w->homeonly == 1 && $core->url->type != 'default' 
		 || $w->homeonly == 2 && $core->url->type == 'default'
		) {
			return null;
		}

		$p = array();
		$p['order'] = ($w->sortby && in_array($w->sortby, array('feed_upd_last', 'lowername', 'feed_creadt'))) ? 
			$w->sortby.' ' : 'feed_upd_last ';
		$p['order'] .= $w->sort == 'desc' ? 'DESC' : 'ASC';
		$p['limit'] = abs((integer) $w->limit);
		$p['feed_status'] = 1;

		$zc = new zoneclearFeedServer($core);
		$rs = $zc->getFeeds($p);

		if ($rs->isEmpty()) {

			return null;
		}

		$res = '';
		$i = 1;
		while($rs->fetch()) {
			$res .= 
			'<li>'.
			'<a href="'.$rs->feed_url.'" title="'.$rs->feed_owner.'">'.$rs->feed_name.'</a>'.
			'</li>';
			$i++;
		}

		if ($w->pagelink) {
			$res .= '<li><a href="'.$core->blog->url.$core->url->getBase('zoneclearFeedsPage').'">'.__('All sources').'</a></li>';
		}

		return
		($w->content_only ? '' : '<div class="zoneclear-sources'.
		($w->class ? ' '.html::escapeHTML($w->class) : '').'">').
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>'.$res.'</ul>'.
		($w->content_only ? '' : '</div>');
	}

	/**
	 * Widget for feeds info.
	 * 
	 * @param  dcWidget $w dcWidget instance
	 */
	public static function publicNumber($w)
	{
		global $core;

		if (!$core->blog->settings->zoneclearFeedServer->zoneclearFeedServer_active 
		 || $w->homeonly == 1 && $core->url->type != 'default' 
		 || $w->homeonly == 2 && $core->url->type == 'default'
		) {
			return null;
		}

		$zc = new zoneclearFeedServer($core);
		$content = '';

		# Feed
		if ($w->feed_show) {
			$title = ($w->feed_title ? 
				'<strong>'.html::escapeHTML($w->feed_title).'</strong> ' : '');

			$count = $zc->getFeeds(array(),true)->f(0);

			if ($count == 0) {
				$text = sprintf(__('no sources'),$count);
			}
			elseif ($count == 1) {
				$text = sprintf(__('one source'),$count);
			}
			else {
				$text = sprintf(__('%d sources'),$count);
			}
			if ($core->blog->settings->zoneclearFeedServer->zoneclearFeedServer_pub_active) {
				$text = '<a href="'.$core->blog->url.$core->url->getBase('zoneclearFeedsPage').'">'.$text.'</a>';
			}

			$content .= sprintf('<li>%s%s</li>',$title,$text);
		}

		# Entry
		if ($w->entry_show) {
			$count = 0;
			$feeds = $zc->getFeeds();

			if (!$feeds->isEmpty()) {
				while ($feeds->fetch()) {
					$count += (integer) $zc->getPostsByFeed(array('feed_id' => $feeds->feed_id), true)->f(0);
				}
			}
			$title = ($w->entry_title ? 
				'<strong>'.html::escapeHTML($w->entry_title).'</strong> ' : '');

			if ($count == 0) {
				$text = sprintf(__('no entries'),$count);
			}
			elseif ($count == 1) {
				$text = sprintf(__('one entry'),$count);
			}
			else {
				$text = sprintf(__('%d entries'),$count);
			}

			$content .= sprintf('<li>%s%s</li>',$title,$text);
		}

		# Nothing to display
		if (!$content) {

			return null;
		}

		# Display
		return 
		($w->content_only ? '' : '<div class="oneclear-number'.
		($w->class ? ' '.html::escapeHTML($w->class) : '').'">').
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>'.$content.'</ul>'.
		($w->content_only ? '' : '</div>');
	}
}
