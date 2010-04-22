<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of zoneclearFeedServer, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis, BG and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets',array('zoneclearFeedServerWidget','adminSource'));
$core->addBehavior('initWidgets',array('zoneclearFeedServerWidget','adminNumber'));

class zoneclearFeedServerWidget
{
	public static function adminSource($w)
	{
		global $core;

		$w->create('zcfssource',
			__('Feeds server : sources'),
			array('zoneclearFeedServerWidget','publicSource')
		);
		$w->zcfssource->setting('title',
			__('Title:'),__('Feeds sources'),'text'
		);
		$w->zcfssource->setting('sortby',
			__('Order by:'),'lowername','combo',array(
				__('name')=> 'lowername',
				__('creation date') => 'feed_creadt'
			)
		);
		$w->zcfssource->setting('sort',
			__('Sort:'),'desc','combo',array(
				__('Ascending') => 'asc',
				__('Descending') => 'desc'
			)
		);
		$w->zcfssource->setting('limit',
			__('Limit:'),10,'text'
		);
		$w->zcfssource->setting('pagelink',
			__('Add link to feeds page'),1,'check'
		);
		$w->zcfssource->setting('homeonly',
			__('Home page only'),1,'check'
		);
	}

	public static function adminNumber($w)
	{
		$w->create('zcfsnumber',__('Feeds server : numbers'),array('zoneclearFeedServerWidget','publicNumber'));
		$w->zcfsnumber->setting('title',__('Title:'),__('Feeds numbers'),'text');

		# Feed
		$w->zcfsnumber->setting('feed_show',__('Show feeds count'),1,'check');
		$w->zcfsnumber->setting('feed_title',__('Title for feeds count:'),__('Feeds:'),'text');

		# Entry
		$w->zcfsnumber->setting('entry_show',__('Show entries count'),1,'check');
		$w->zcfsnumber->setting('entry_title',__('Title for entries count:'),__('Entries:'),'text');

		$w->zcfsnumber->setting('homeonly',__('Home page only'),1,'check');
	}

	public static function publicSource($w)
	{
		global $core; 
		$s = zoneclearFeedServer::settings($core);

		if (!$s->zoneclearFeedServer_active 
		 || !$core->plugins->moduleExists('metadata') 
		 || $w->homeonly && $core->url->type != 'default') return;

		$p = array();
		$p['order'] = ($w->sortby && in_array($w->sortby,array('lowername','feed_creadt'))) ? 
			$w->sortby.' ' : 'lowername ';

		$p['order'] .= $w->sort == 'desc' ? 'DESC' : 'ASC';

		$p['limit'] = abs((integer) $w->limit);
		$p['feed_status'] = 1;

		$zc = new zoneclearFeedServer($core);
		$rs = $zc->getFeeds($p);

		if ($rs->isEmpty()) return;
		
		$res = '';
		$i = 1;
		while($rs->fetch())
		{
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
		'<div class="zoneclear-sources">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>'.$res.'</ul>'.
		'</div>';
	}

	public static function publicNumber($w)
	{
		global $core;
		$s = zoneclearFeedServer::settings($core);

		if (!$s->zoneclearFeedServer_active 
		 || !$core->plugins->moduleExists('metadata') 
		 || $w->homeonly && $core->url->type != 'default') return;
		
		$zc = new zoneclearFeedServer($core);
		$content = '';

		# Feed
		if ($w->feed_show)
		{
			$title = ($w->feed_title ? 
				'<strong>'.html::escapeHTML($w->feed_title).'</strong> ' : '');

			$count = $zc->getFeeds(array(),true)->f(0);

			if ($count == 0) {
				$text = sprintf(__('none'),$count);
			}
			elseif ($count == 1) {
				$text = sprintf(__('one source'),$count);
			}
			else {
				$text = sprintf(__('%s sources'),$count);
			}
			if ($s->zoneclearFeedServer_pub_active) {
				$text = '<a href="'.$core->blog->url.$core->url->getBase('zoneclearFeedsPage').'">'.$text.'</a>';
			}

			$content .= sprintf('<li>%s%s</li>',$title,$text);
		}

		# Entry
		if ($w->entry_show)
		{
			$count = 0;
			$feeds = $zc->getFeeds();
			if (!$feeds->isEmpty())
			{
				while ($feeds->fetch())
				{
					$count += (integer) $zc->getPostsByFeed(array('feed_id'=>$feeds->feed_id),true)->f(0);
				}
			}
			$title = ($w->entry_title ? 
				'<strong>'.html::escapeHTML($w->entry_title).'</strong> ' : '');

			if ($count == 0) {
				$text = sprintf(__('none'),$count);
			}
			elseif ($count == 1) {
				$text = sprintf(__('one entry'),$count);
			}
			else {
				$text = sprintf(__('%s entries'),$count);
			}

			$content .= sprintf('<li>%s%s</li>',$title,$text);
		}

		# Nothing to display
		if (!$content) return;

		# Display
		return 
		'<div class="zoneclear-number">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>'.$content.'</ul>'.
		'</div>';
	}
}
?>