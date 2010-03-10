<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Mystique Config plugin.
#
# Copyright (c) 2010 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('initWidgets',array('mystiqueWidgets','initWidgets'));


class mystiqueWidgets 
{
	public static function initWidgets($widgets)
	{
		$widgets->create('mystiqueSearch',__('[Mystique]Search'),array('pubWidgetMystique','searchWidget'));
		$widgets->mystiqueSearch->setting('title',__('Title:'),'');
		
		$widgets->create('mystiqueGroup',__('[Mystique]Group'),array('pubWidgetMystique','mystiqueGroup'));
		$widgets->mystiqueGroup->setting('title',__('Title:'),'');
		$widgets->mystiqueGroup->setting('id',__('ID:'),'');
		$widgets->mystiqueGroup->setting('tab_cats',__('Categories tab'),1,'check');
		$widgets->mystiqueGroup->setting('tab_tags',__('Tags tab'),1,'check');
		$widgets->mystiqueGroup->setting('tab_archives',__('Archives tab'),1,'check');
		$widgets->mystiqueGroup->setting('tab_popular',__('Popular posts tab'),1,'check');
		$widgets->mystiqueGroup->setting('tab_comments',__('Last comments tab'),1,'check');
		$widgets->mystiqueGroup->setting('show_counts',__('Show counts'),1,'check');
		$widgets->mystiqueGroup->setting('nb_posts',__('Number of selected posts'),10);
		$widgets->mystiqueGroup->setting('nb_comments',__('Number of last comments'),10);		

		$widgets->create('mystiqueTwitter',__('[Mystique]Tweets'),array('pubWidgetMystique','mystiqueTwitter'));
		$widgets->mystiqueTwitter->setting('title',__('Title'),'');
		$widgets->mystiqueTwitter->setting('user',__('User name'),'');
		$widgets->mystiqueTwitter->setting('nb_tweets',__('Number of tweets'),10);

	}
}
?>