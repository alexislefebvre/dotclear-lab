<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku ,Tomtom and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraMember'));
$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraModerate'));
$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraCategories'));
$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraBestof'));
$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraLastThreads'));
$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraLastMessages'));
$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraSubscribe'));

class agoraWidgets 
{
	public static function initWidgetsAgoraMember($w)
	{
		$w->create('memberAgoraWidget',__('Agora: navigation'),array('widgetsAgora','memberWidget'));
		$w->memberAgoraWidget->setting('title',__('Title:'),__('Agora navigation'));
		#$widgets->privateblog->setting('text',__('Text:'),'','textarea');
		#$widgets->privateblog->setting('label',__('Button:'),__('Disconnect'));
		#$widgets->privateblog->setting('homeonly',__('Home page only'),0,'check');
	}
	
	public static function initWidgetsAgoraModerate($w)
	{
		$w->create('moderateAgoraWidget',__('Agora: moderation'),array('widgetsAgora','moderateWidget'));
		$w->moderateAgoraWidget->setting('title',__('Title:'),__('Agora moderation'));
		#$widgets->privateblog->setting('text',__('Text:'),'','textarea');
		#$widgets->privateblog->setting('label',__('Button:'),__('Disconnect'));
		#$widgets->privateblog->setting('homeonly',__('Home page only'),0,'check');
	}
	public static function initWidgetsAgoraCategories($w)
	{
		$w->create('categoriesAgoraWidget',__('Agora: categories list'),array('widgetsAgora','categoriesWidget'));
		$w->categoriesAgoraWidget->setting('title',__('Title:'),__('Agora\'s sections'));
		$w->categoriesAgoraWidget->setting('postcount',__('With entries counts'),0,'check');
		#$widgets->privateblog->setting('text',__('Text:'),'','textarea');
		#$widgets->privateblog->setting('label',__('Button:'),__('Disconnect'));
		#$widgets->privateblog->setting('homeonly',__('Home page only'),0,'check');
	}
	public static function initWidgetsAgoraBestof($w)
	{
		$w->create('bestofAgoraWidget',__('Agora: selected threads'),array('widgetsAgora','bestofWidget'));
		$w->bestofAgoraWidget->setting('title',__('Title:'),__('Selected threads'));
		$w->bestofAgoraWidget->setting('homeonly',__('Home page only'),1,'check');
	}
	public static function initWidgetsAgoraLastThreads($w)
	{
		global $core;
		$w->create('lastthreadsAgoraWidget',__('Agora: last threads'),array('widgetsAgora','lastthreadsWidget'));;
		$w->lastthreadsAgoraWidget->setting('title',__('Title:'),__('Last threads'));
		$rs = $core->blog->getCategories(array('post_type'=>'thread'));
		$categories = array('' => '', __('Uncategorized') => 'null');
		while ($rs->fetch()) {
			$categories[str_repeat('&nbsp;&nbsp;',$rs->level-1).'&bull; '.html::escapeHTML($rs->cat_title)] = $rs->cat_id;
		}
		$w->lastthreadsAgoraWidget->setting('category',__('Category:'),'','combo',$categories);
		unset($rs,$categories);
		$w->lastthreadsAgoraWidget->setting('limit',__('Entries limit:'),10);
		$w->lastthreadsAgoraWidget->setting('homeonly',__('Home page only'),1,'check');
	}
	public static function initWidgetsAgoraLastMessages($w)
	{
		global $core;
		$w->create('lastmessagesAgoraWidget',__('Agora: last messages'),array('widgetsAgora','lastmessagesWidget'));
		$w->lastmessagesAgoraWidget->setting('title',__('Title:'),__('Last messages'));
		$rs = $core->blog->getCategories(array('post_type'=>'thread'));
		$categories = array('' => '', __('Uncategorized') => 'null');
		while ($rs->fetch()) {
			$categories[str_repeat('&nbsp;&nbsp;',$rs->level-1).'&bull; '.html::escapeHTML($rs->cat_title)] = $rs->cat_id;
		}
		$w->lastmessagesAgoraWidget->setting('category',__('Category:'),'','combo',$categories);
		unset($rs,$categories);
		$w->lastmessagesAgoraWidget->setting('limit',__('Messages limit:'),10);
		$w->lastmessagesAgoraWidget->setting('homeonly',__('Home page only'),1,'check');
	}

	public static function initWidgetsAgoraSubscribe($w)
	{
		global $core;
		$w->create('subscribeAgoraWidget',__('Agora: subscribe links'),array('widgetsAgora','subscribeWidget'));
		$w->subscribeAgoraWidget->setting('title',__('Title:'),__('Subscribe'));
		$w->subscribeAgoraWidget->setting('type',__('Feeds type:'),'atom','combo',array('Atom' => 'atom', 'RSS' => 'rss2'));
		$w->subscribeAgoraWidget->setting('homeonly',__('Home page only'),0,'check');
	}
}
?>
