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

$core->url->register('forum','forum','^forum(.*)$',array('urlAgora','forum'));
$core->url->register('subforum','forum/sub','^forum/sub/(.+)$',array('urlAgora','subforum'));
$core->url->register('thread','forum/thread','^forum/thread/(.+)$',array('urlAgora','thread'));
$core->url->register('register','forum/register','^forum/register(.*)$',array('urlAgora','register'));
$core->url->register('login','forum/login','^forum/login(.*)$',array('urlAgora','login'));
$core->url->register('logout','forum/logout','^forum/logout(.*)$',array('urlAgora','logout'));
//$core->url->register('profile','forum/profile','^forum/profile/(.+)$',array('urlAgora','profile'));
//$core->url->register('recovery','forum/recovery','^forum/recovery(.*)$',array('urlAgora','recovery'));

$__autoload['agora']			= dirname(__FILE__).'/inc/class.dc.agora.php';
$__autoload['dcPublicAuth']		= dirname(__FILE__).'/inc/class.dc.agora.auth.php';
$__autoload['dcLog']			= dirname(__FILE__).'/inc/class.dc.agora.log.php';
$__autoload['agoraTemplate']		= dirname(__FILE__).'/inc/class.dc.agora.template.php';
$__autoload['agoraBehaviors']		= dirname(__FILE__).'/inc/class.dc.agora.behaviors.php';
$__autoload['mail']				= CLEARBRICKS_PATH.'/mail/class.mail.php';

?>
