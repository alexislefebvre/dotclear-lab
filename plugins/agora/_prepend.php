<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2012 Osku and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->blog->settings->addNameSpace('agora');

// Admin pager lists for users and messages.
$__autoload['adminMessageList']	= dirname(__FILE__).'/lib/agora.pager.php';
$__autoload['agoraUserList']		= dirname(__FILE__).'/lib/agora.pager.php';

// Main classes
$__autoload['agora']			= dirname(__FILE__).'/inc/class.agora.php';
$__autoload['mailAgora']			= dirname(__FILE__).'/inc/class.agora.mail.php';
$__autoload['widgetsAgora']		= dirname(__FILE__).'/inc/class.agora.widgets.php';
$__autoload['agoraBehaviors']		= dirname(__FILE__).'/inc/class.agora.behaviors.php';
$__autoload['agorapublicBehaviors']= dirname(__FILE__).'/inc/class.agora.public.behaviors.php';
$__autoload['agoraTools']		= dirname(__FILE__).'/inc/class.agora.utils.php';
$__autoload['mediaAgora']		= dirname(__FILE__).'/inc/class.agora.media.php';
$__autoload['agoraRestMethods']	= dirname(__FILE__).'/inc/class.agora.rest.php';
$__autoload['agoraTemplate']		= dirname(__FILE__).'/inc/class.agora.template.php';

// Recorsets extensions
$__autoload['rsExtMessage']		= dirname(__FILE__).'/inc/class.rs.agora.php';
$__autoload['rsExtThread']		= dirname(__FILE__).'/inc/class.rs.agora.php';
$__autoload['rsExtMember']		= dirname(__FILE__).'/inc/class.rs.agora.php';

// Public messages recordset extension
$__autoload['rsExtMessagePublic']		= dirname(__FILE__).'/inc/class.rs.public.agora.php';

$core->agora = new agora($core);

// Users, posts and messages extensions declaration
$core->addBehavior('agoraGetUsers',array('agoraBehaviors','agoraGetUsers'));
$core->addBehavior('coreBlogGetPosts',array('agoraBehaviors','coreBlogGetPosts'));

$core->addBehavior('agoraGetMessages',array('agoraBehaviors','agoraGetMessages'));

if ($core->blog->settings->agora->agora_flag)
{
	$__autoload['urlAgora']		= dirname(__FILE__).'/inc/class.agora.urlhandlers.php';

	// new ErrorHandler : 401 Unauthorized - need authentification
	$core->url->registerError(array('urlAgora','error401'));

	// Classic URLs
	if ($core->blog->settings->agora->new_post) {
		$core->url->register('new','new','^new(.*)$',array('urlAgora','newpost'));
	}
	
	if ($core->blog->settings->agora->full_flag) {
		// Messages system is active. Beside comments
		$core->url->register('post','post','^post/(.+)$',array('urlAgora','thread'));
	}

	// Feed is rewritten to handle comments/feed mechanism
	$core->url->register('feed','feed','^feed/(.+)$',array('urlAgora','feed'));

	// All users URLs : 
	if ($core->blog->settings->agora->community_flag) {
		$core->url->register('people','people','^people$',array('urlAgora','people'));
		$core->url->register('profile','profile','^profile/(.+)$',array('urlAgora','profile'));
	}

	$core->url->register('preferences','preferences','^preferences$',array('urlAgora','preferences'));

	// Moderation
	$core->url->register('editpost','pedit','^pedit/(.+)$',array('urlAgora','editpost'));
	$core->url->register('editmessage','medit','^medit/(.+)$',array('urlAgora','editmessage'));

	// PUBLISH actions - only for users with contentadmin permission: 
	$core->url->register('publishpost','pubpost','^pubpost/(.+)$',array('urlAgora','publishpost'));
	$core->url->register('unpublishpost','unpubpost','^unpubpost/(.+)$',array('urlAgora','unpublishpost'));
	$core->url->register('publishmessage','pubmsg','^pubmsg/(.+)$',array('urlAgora','publishmessage'));
	$core->url->register('unpublishmessage','unpubmsg','^unpubmsg/(.+)$',array('urlAgora','unpublishmessage'));
	
	// user actions
	$core->url->register('login','login','^login$',array('urlAgora','login'));
	$core->url->register('logout','logout','^logout$',array('urlAgora','logout'));
	
	// Subscription option
	if ($core->blog->settings->agora->register_flag) {
		$core->url->register('register','register','^register$',array('urlAgora','newaccount'));
	}
	// Password recovering handler 
	if ($core->blog->settings->agora->recover_flag && $core->auth->allowPassChange()) {
		$core->url->register('recover','recover','^recover/(.*)$',array('urlAgora','recover'));
	}

	// Specify wiki syntax options for messages
	$core->addBehavior('coreInitWikiComment',array('agoraBehaviors','coreInitWikiComment'));
	
	// Adding new permission to a new user
	$core->addBehavior('publicAfterUserCreate',array('agoraBehaviors','publicAfterUserCreate'));

	// Initialize messages count 
	$core->addBehavior('coreAfterPostCreate',array('agoraBehaviors','initNbMessages'));

	// Cache handler for authentication :
	$core->addBehavior('urlHandlerBeforeGetData',array('agorapublicBehaviors','urlHandlerBeforeGetData'));
	
	// Defines new widgets only if agora is active :
	require dirname(__FILE__).'/_widgets.php';

	if ($core->blog->settings->agora->private_flag)
	{
		#Rewrite Feeds with new URL and representation (obfuscation)
		$feeds_url = new ArrayObject(array('feed','tag_feed','agora_feed'));
		$core->callBehavior('initFeedsPrivatAgora',$feeds_url);
		$privatefeed = $core->blog->uid;

		#Obfuscate all feeds URL
		foreach ($core->url->getTypes() as $k => $v) {
			if (in_array($k,(array)$feeds_url)) {

				$core->url->register(
					$k,
					sprintf('%s/%s',$core->blog->uid,$v['url']),
					sprintf('^%s\/%s/(.+)$',$core->blog->uid,$v['url']),
					$v['handler']
				);
			}
		}

		$core->url->register('fakefeed',
			'feed',
			'^feed/(.+)$',
			array('urlAgora','publicFeed')
		);
	
		#Trick.. 
		$core->url->register('xslt','feed/rss2/xslt','^feed/rss2/xslt$',array('urlAgora','feedXslt'));
	}
}
?>
