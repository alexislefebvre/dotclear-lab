<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2012- Osku and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

// New ones:
$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraMember'));
if ($core->blog->settings->agora->new_post) {
	$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraNewEntry'));
}
if ($core->blog->settings->agora->community_flag) {
	$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraConnected'));
}

if ($core->blog->settings->agora->full_flag) {
	$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraLastMessages'));
// Replace existing ones: 
	$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraSubscribe'));
}
$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraLastPosts'));
$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraText'));
// Need review :
//$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraUserSearch'));

class agoraWidgets 
{
	public static function initWidgetsAgoraMember($w)
	{
		$w->create('memberAgoraWidget',__('Agora › User navigation'),array('widgetsAgora','memberWidget'));
		$w->memberAgoraWidget->setting('title',__('Title:'),__('Agora - navigation'));
		$w->memberAgoraWidget->setting('avatar',__('Display avatar'),0,'check');
		$w->memberAgoraWidget->setting('label_login',__('Login label:'),__('Login'));
		$w->memberAgoraWidget->setting('label_preferences',__('My preferences label:'),__('My preferences'));
		$w->memberAgoraWidget->setting('label_logout',__('Logout label:'),__('Logout'));
		
		// NEW 
		/*if ($GLOBALS['core']->blog->settings->agora->new_post) {
			$w->memberAgoraWidget->setting('label_new_post',__('New post label:'),__('New post'));
		}*/
	}

	public static function initWidgetsAgoraNewEntry($w)
	{
		$w->create('newentryAgoraWidget',__('Agora › New entry'),array('widgetsAgora','newentryWidget'));
		$w->newentryAgoraWidget->setting('title',__('Title:'),__('New entry'));
		$w->newentryAgoraWidget->setting('label_new_entry',__('New entry label:'),__('New entry'));
	}

	public static function initWidgetsAgoraLastPosts($w)
	{
		global $core;
		$w->create('lastposts',__('Agora › Last entries'),array('widgetsAgora','lastPostsWidget'));;
		$w->lastposts->setting('title',__('Title:'),__('Last entries'));
		$rs = $core->blog->getCategories(array('post_type'=>'post'));
		$categories = array('' => '', __('Uncategorized') => 'null');
		while ($rs->fetch()) {
			$categories[str_repeat('&nbsp;&nbsp;',$rs->level-1).'&bull; '.html::escapeHTML($rs->cat_title)] = $rs->cat_id;
		}
		$w->lastposts->setting('category',__('Category:'),'','combo',$categories);
		unset($rs,$categories);
		$w->lastposts->setting('limit',__('Entries limit:'),10);
		$w->lastposts->setting('homeonly',__('Home page only'),1,'check');
		$w->lastposts->setting('messagecount',__('With comments/messages counts'),0,'check');
		$w->lastposts->setting('showauthor',__('Display author'),0,'check');
		$sortby = array(
			__('Creation date') => 'post_creadt',
			__('Update date') => 'post_dt'
		);
		$order = array(
			__('Descending') => 'desc',
			__('Ascending') => 'asc'
		);
		$w->lastposts->setting('sortby',__('Sort by:'),'post_creadt','combo',$sortby);
		$display = array(
			'' => '0',
			__('Connected users') => '1',
			__('Non connected users') => '2'
		);
		$w->lastposts->setting('display',__('Display:'),'','combo',$display);
		$w->lastposts->setting('ordering',__('Order:'),'desc','combo',$order);
		
		unset($sortby,$order);
	}

	
	public static function initWidgetsAgoraLastMessages($w)
	{
		global $core;
		$w->create('lastmessagesAgoraWidget',__('Agora › Last messages'),array('widgetsAgora','lastmessagesWidget'));
		$w->lastmessagesAgoraWidget->setting('title',__('Title:'),__('Last messages'));
		$rs = $core->blog->getCategories(array('post_type'=>'thread'));
		$categories = array('' => '', __('Uncategorized') => 'null');
		while ($rs->fetch()) {
			$categories[str_repeat('&nbsp;&nbsp;',$rs->level-1).'&bull; '.html::escapeHTML($rs->cat_title)] = $rs->cat_id;
		}
		$w->lastmessagesAgoraWidget->setting('category',__('Category:'),'','combo',$categories);
		unset($rs,$categories);
		$w->lastmessagesAgoraWidget->setting('limit',__('Messages limit:'),10);
		$display = array(
			'' => '0',
			__('Connected users') => '1',
			__('Non connected users') => '2'
		);
		$w->lastmessagesAgoraWidget->setting('display',__('Display:'),'','combo',$display);
		$w->lastmessagesAgoraWidget->setting('homeonly',__('Home page only'),1,'check');
	}

	public static function initWidgetsAgoraText($w)
	{
		global $core;
		$w->create('text',__('Agora › Text'),array('widgetsAgora','textWidget'));
		$w->text->setting('title',__('Title:'),'');
		$w->text->setting('text',__('Text:'),'','textarea');
		$display = array(
			'' => '0',
			__('Connected users') => '1',
			__('Non connected users') => '2'
		);
		$w->text->setting('display',__('Display:'),'','combo',$display);
		$w->text->setting('homeonly',__('Home page only'),0,'check');
	}

	public static function initWidgetsAgoraSubscribe($w)
	{
		global $core;
		$w->create('subscribe',__('Agora › Subscribe links'),array('widgetsAgora','subscribeWidget'));
		$w->subscribe->setting('title',__('Title:'),__('Subscribe'));
		$w->subscribe->setting('type',__('Feeds type:'),'atom','combo',array('Atom' => 'atom', 'RSS' => 'rss2'));
		$w->subscribe->setting('homeonly',__('Home page only'),0,'check');
	}

	public static function initWidgetsAgoraConnected($w)
	{
		global $core;
		$w->create('connectedAgoraWidget',__('Agora › Connected users'),array('widgetsAgora','connectedWidget'));
		$w->connectedAgoraWidget->setting('title',__('Title:'),__('Connected users'));
		$w->connectedAgoraWidget->setting('alluserslinktitle',__('Link to all users:'),__('All users'));
		$w->connectedAgoraWidget->setting('nobodymessage',__('Message if nobody connected:'),__('Nobody is connected.'));


		$display = array(
			'' => '0',
			__('Connected users') => '1',
			__('Non connected users') => '2'
		);
		$w->connectedAgoraWidget->setting('display',__('Display:'),'','combo',$display);
		$w->connectedAgoraWidget->setting('homeonly',__('Home page only'),0,'check');
	}

	public static function initWidgetsAgoraUserSearch($w)
	{
		global $core;
		$w->create('userSearchAgoraWidget',__('Agora › User search'),array('widgetsAgora','userSearchWidget'));
		$w->userSearchAgoraWidget->setting('title',__('Title:'),__('User search'));
	
	}
}
?>
