<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pollsFactory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets',array('widgetPollsFactory','adminPollsList'));
$core->addBehavior('initWidgets',array('widgetPollsFactory','adminPollsQuery'));
$core->addBehavior('initWidgets',array('widgetPollsFactory','adminPollsPost'));

class widgetPollsFactory
{
	public static function adminPollsList($w)
	{
		global $core;

		$w->create('pollfactlist',
			__('Polls list'),
			array('widgetPollsFactory','publicPollsList')
		);
		# Title
		$w->pollfactlist->setting('title',
			__('Title:'),__('Polls list'),'text'
		);
		# Selected entries only
		$w->pollfactlist->setting('selectedonly',
			__('Selected entries only'),0,'check'
		);
		# Limit
		$w->pollfactlist->setting('limit',
			__('Polls limit:'),10,'text'
		);
		# Sort type
		$w->pollfactlist->setting('sortby',
			__('Order by:'),'poll_strdt','combo',
			array(
				__('Entry date') => 'post_dt',
				__('Start date') => 'poll_strdt',
				__('End date') => 'poll_enddt'
			)
		);
		# Sort order
		$w->pollfactlist->setting('sort',
			__('Sort:'),'desc','combo',
			array(
				__('Ascending') => 'asc',
				__('Descending') => 'desc'
			)
		);
		# Period
		$w->pollfactlist->setting('period',
			__('Period:'),'desc','combo',
			array(
				__('All') => '-',
				__('Ongoing') => 'ongoing',
				__('Coming') => 'coming'
			)
		);
		# Home only
		$w->pollfactlist->setting('homeonly',
			__('Home page only'),1,'check'
		);
	}

	public static function adminPollsPost($w)
	{
		global $core;

		$w->create('pollfactpost',
			__('Poll of an entry'),
			array('widgetPollsFactory','publicPollsPost')
		);
		$w->pollfactpost->setting('title',
			__('Title:'),__('Poll'),'text'
		);
		$w->pollfactpost->setting('usegraph',
			__('Use graphic results'),0,'check'
		);
	}

	public static function adminPollsQuery($w)
	{
		global $core;

		$params = array();
		$params['sql'] = "AND poll_enddt > TIMESTAMP '".date('Y-m-d H:i:s')."' ";
		$fact = new pollsFactory($core);
		$rs = $fact->getPolls($params);

		$list = array('-'=>'');
		if (!$rs->isEmpty()) {
			while($rs->fetch()) {
				$list[$rs->post_title] = $rs->post_id;
			}
		}

		$w->create('pollfactquery',
			__('Polls selection'),
			array('widgetPollsFactory','publicPollsQuery')
		);
		$w->pollfactquery->setting('title',
			__('Title:'),__('Poll'),'text'
		);
		$w->pollfactquery->setting('showposttitle',
			__('Show title of entry below title of widget'),0,'check'
		);
		$w->pollfactquery->setting('post',
			__('Entry:'),'-','combo',$list
		);
		$w->pollfactquery->setting('usegraph',
			__('Use graphic results'),0,'check'
		);
		$w->pollfactquery->setting('homeonly',
			__('Home page only'),1,'check'
		);
	}

	public static function publicPollsList($w)
	{
		global $core; 

		if (!$core->blog->settings->pollsFactory_active 
		 || $w->homeonly && $core->url->type != 'default') return;

		$params = array('sql'=>'');
		# Selected post
		if ($w->selectedonly) {
			$params['post_selected'] = 1;
		}
		# order
		$params['order'] = in_array($w->sortby,array('post_dt','poll_strdt','poll_enddt')) ?
			$w->sortby : 'post_dt ';
		$params['order'] .= $w->sort == 'asc' ? ' asc' : ' desc';
		# limit
		$params['limit'] = abs((integer) $w->limit);
		# period
		if ($w->period == 'ongoing') {
			$params['sql'] .= "AND poll_strdt < TIMESTAMP '".date('Y-m-d H:i:s')."' ";
			$params['sql'] .= "AND poll_enddt > TIMESTAMP '".date('Y-m-d H:i:s')."' ";
		}
		else {
			$params['sql'] .= "AND poll_strdt > TIMESTAMP '".date('Y-m-d H:i:s')."' ";
		}
		# skip content
		$params['no_content'] = true;

		# Find polls
		$fact = new pollsFactory($core);
		$polls = $fact->getPolls($params);
		if ($polls->isEmpty()) return;

		$res = '';
		while($polls->fetch())
		{
			$res .= '<li><a href="'.$polls->getURL().'">'.html::escapeHTML($polls->post_title).'</a></li>';
		}

		return 
		'<div class="pollsfactory poll-widget-query">'.
		($w->title ? '<h2>'.$w->title.'</h2>' : '').
		'<ul>'.$res.'</ul>'.
		'</div>';
	}

	public static function publicPollsPost($w)
	{
		global $core, $_ctx; 

		# Is enabled
		if (!$core->blog->settings->pollsFactory_active 
		 || $core->url->type != 'post') return;

		return publicPollsFactoryForm($core,$_ctx->posts->post_id,$w->title,false,$w->usegraph);
	}

	public static function publicPollsQuery($w)
	{
		global $core; 

		if (!$core->blog->settings->pollsFactory_active 
		 || $w->homeonly && $core->url->type != 'default'
		 || '' == $w->post) return;

		return publicPollsFactoryForm($core,$w->post,$w->title,$w->showposttitle,$w->usegraph);
	}
}
?>