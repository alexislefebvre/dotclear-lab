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

$core->blog->settings->addNameSpace('agora');

if ($core->blog->settings->agora->agora_flag)
{
	require dirname(__FILE__).'/_widgets.php';
	$__autoload['urlAgora']		= dirname(__FILE__).'/inc/class.agora.urlhandlers.php';
	// Classic URLs
	$core->url->register('agora','agora','^agora(.*)$',array('urlAgora','agora'));	
	$core->url->register('place','place','^place/(.+)$',array('urlAgora','place'));
	$core->url->register('newthread','newthread','^newthread(.*)$',array('urlAgora','newthread'));
	$core->url->register('thread','thread','^thread/(.+)$',array('urlAgora','thread'));	
	$core->url->register('threadpreview','threadpreview','^threadpreview/(.+)$',array('urlAgora','threadpreview'));
	$core->url->register('agora_feed','feed/agora','^feed/agora/(.+)$',array('urlAgora','feed'));	
	//$core->url->register('userlist','agora/userlist','^agora/userlist/(.+)$',array('urlAgora','userlist'));
	
	// Moderation
	$core->url->register('editthread','thread/edit','^thread/edit/(.+)$',array('urlAgora','editthread'));
	$core->url->register('removethread','thread/remove','^thread/remove/(.+)$',array('urlAgora','removethread'));
	$core->url->register('editmessage','message/edit','^message/edit/(.+)$',array('urlAgora','editmessage'));
	$core->url->register('removemessage','message/remove','^message/remove/(.+)$',array('urlAgora','removemessage'));
	
	// user actions
	$core->url->register('register','register','^register$',array('urlAgora','newaccount'));
	$core->url->register('recover','recover','^recover/(.*)$',array('urlAgora','recover'));
	$core->url->register('login','login','^login$',array('urlAgora','login'));
	$core->url->register('logout','logout','^logout$',array('urlAgora','logout'));
	$core->url->register('profile','profile','^profile/(.+)$',array('urlAgora','profile'));

	$core->setPostType('thread','plugin.php?p=agora&act=thread&id=%d',$core->url->getBase('thread').'/%s');
}

$__autoload['agora']			= dirname(__FILE__).'/inc/class.agora.php';
$__autoload['dcPublicAuth']		= dirname(__FILE__).'/inc/class.agora.auth.php';
$__autoload['widgetsAgora']		= dirname(__FILE__).'/inc/class.agora.widgets.php';
$__autoload['agoraTemplate']		= dirname(__FILE__).'/inc/class.agora.template.php';
$__autoload['agoraBehaviors']		= dirname(__FILE__).'/inc/class.agora.behaviors.php';
$__autoload['agorapublicBehaviors']		= dirname(__FILE__).'/inc/class.agora.public.behaviors.php';
$__autoload['agoraTools']		= dirname(__FILE__).'/inc/class.agora.utils.php';
$__autoload['rsExtMessage']		= dirname(__FILE__).'/inc/class.rs.agora.php';
$__autoload['rsExtThread']		= dirname(__FILE__).'/inc/class.rs.agora.php';
$__autoload['rsExtMessagePublic']		= dirname(__FILE__).'/inc/class.rs.public.agora.php';
?>