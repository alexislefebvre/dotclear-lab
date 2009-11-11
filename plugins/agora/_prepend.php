<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku , Tomtom and contributors
## Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__).'/_widgets.php';
if ($core->blog->settings->agora_flag)
{
	$core->url->register('agora','agora','^agora(.*)$',array('urlAgora','forum'));
	$core->url->register('subforum','agora/sub','^agora/sub/(.+)$',array('urlAgora','subforum'));
	$core->url->register('newthread','agora/newthread','^agora/newthread(.*)$',array('urlAgora','newthread'));
	$core->url->register('thread','agora/thread','^agora/thread/(.+)$',array('urlAgora','thread'));
	//$core->url->register('answer','agora/answer','^agora/answer/(.+)$',array('urlAgora','answer'));
	$core->url->register('editthread','agora/edit/thread','^agora/edit/thread/(.+)$',array('urlAgora','editthread'));
	$core->url->register('removethread','agora/remove/tread','^agora/remove/thread/(.+)$',array('urlAgora','removethread'));
	$core->url->register('editmessage','agora/edit/message','^agora/edit/message/(.+)$',array('urlAgora','editmessage'));
	$core->url->register('removemessage','agora/remove/message','^agora/remove/message/(.+)$',array('urlAgora','removemessage'));
	$core->url->register('register','agora/register','^agora/register$',array('urlAgora','register'));
	$core->url->register('login','agora/login','^agora/login$',array('urlAgora','login'));
	$core->url->register('logout','agora/logout','^agora/logout$',array('urlAgora','logout'));
	$core->url->register('profile','agora/profile','^agora/profile/(.+)$',array('urlAgora','profile'));
	//$core->url->register('userlist','agora/userlist','^agora/userlist/(.+)$',array('urlAgora','userlist'));
	//$core->url->register('recovery','agora/recovery','^agora/recovery(.*)$',array('urlAgora','recovery'));
	$core->url->register('agofeed','agora/feed','^agora/feed/(.+)$',array('urlAgora','feed'));
}

$core->setPostType('threadpost','plugin.php?p=agora&act=thread&id=%d',$core->url->getBase('thread').'/%s');

$__autoload['agora']			= dirname(__FILE__).'/inc/class.agora.php';
$__autoload['dcPublicAuth']		= dirname(__FILE__).'/inc/class.agora.auth.php';
$__autoload['dcLog']			= dirname(__FILE__).'/inc/class.agora.log.php';
$__autoload['agoraTemplate']		= dirname(__FILE__).'/inc/class.agora.template.php';
$__autoload['agoraBehaviors']		= dirname(__FILE__).'/inc/class.agora.behaviors.php';
$__autoload['mail']				= CLEARBRICKS_PATH.'/mail/class.mail.php';
$__autoload['agoraTools']		= dirname(__FILE__).'/inc/class.agora.utils.php';
$__autoload['rsExtMessage']		= dirname(__FILE__).'/inc/class.rs.agora.php';

?>