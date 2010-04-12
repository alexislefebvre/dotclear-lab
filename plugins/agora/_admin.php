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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

if ($core->blog->settings->agora_flag)
{
	$core->addBehavior('adminDashboardIcons','agora_dashboard');

	$_menu['Blog']->addItem(__('Threads'),
			'plugin.php?p=agora','index.php?pf=agora/icon-small.png',
			preg_match('/plugin.php\?p=agora(&.*)?$/',$_SERVER['REQUEST_URI']),
			$core->auth->check('admin',$core->blog->id));
}

function agora_dashboard($core,$icons)
{
	$agora = new agora($core,false);
	$thread_count = $core->blog->getPosts(array('post_type'=>'thread'),true)->f(0);
	$str_threads = ($thread_count > 1) ? __('%d threads') : __('%d thread');

	$message_count = $agora->getMessages(array(),true)->f(0);
	$str_messages = ($message_count > 1) ? __('%d messages') : __('%d message');
	$icons['agora'] = new ArrayObject(array(sprintf($str_threads,$thread_count),'plugin.php?p=agora','index.php?pf=agora/icon.png'));
	$icons['agora'][0] .= '</a> <br /><a href="plugin.php?p=agora&amp;act=messages">'.sprintf($str_messages,$message_count);
}

$_menu['Plugins']->addItem(__('agora:config'),
		'plugin.php?p=agora&amp;act=options','index.php?pf=agora/icon-small.png',
		preg_match('/plugin.php\?p=agora(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));



$core->auth->setPermissionType('member',__('is an agora member'));
$core->auth->setPermissionType('moderator',__('can moderate the agora'));

# Rest methods
$core->rest->addFunction('getMessageById',array('agoraRestMethods','getMessageById'));

class agoraRestMethods
{
	public static function getMessageById($core,$get)
	{
		//global $core;

		if (empty($get['id'])) {
			throw new Exception('No message ID');
		}
		
		$rs = $core->blog->agora->getMessages(array('message_id' => (integer) $get['id']));
		
		if ($rs->isEmpty()) {
			throw new Exception('No message for this ID');
		}
		
		$rsp = new xmlTag('post');
		$rsp->id = $rs->message_id;
		
		$rsp->message_dt($rs->message_dt);
		$rsp->message_creadt($rs->message_creadt);
		$rsp->message_upddt($rs->message_upddt);
		$rsp->user_id($rs->user_id);

		$rsp->message_content($rs->message_content);

		$rsp->message_status($rs->message_status);
		$rsp->post_title($rs->post_title);
		$rsp->post_url($rs->post_url);
		$rsp->post_id($rs->post_id);
		$rsp->post_dt($rs->post_dt);
		$rsp->user_id($rs->user_id);
		$rsp->user_name($rs->user_name);
		$rsp->user_firstname($rs->user_firstname);
		$rsp->user_displayname($rs->user_displayname);
		$rsp->user_email($rs->user_email);
		$rsp->user_url($rs->user_url);
		$rsp->cat_title($rs->cat_title);
		$rsp->cat_url($rs->cat_url);
		
		$rsp->message_display_content($rs->getContent(true));
		
		if ($core->auth->userID()) {
			# --BEHAVIOR-- adminAfterCommentDesc
			$rsp->message_spam_disp($core->callBehavior('adminAfterMessageDesc', $rs));
		}
		
		return $rsp;
	}
}
?>
