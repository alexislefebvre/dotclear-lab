<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2012 Osku contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class agoraRestMethods
{
	public static function getMessageById($core,$get)
	{
		if (empty($get['id'])) {
			throw new Exception('No message ID');
		}
		
		$rs = $core->agora->getMessages(array('message_id' => (integer) $get['id']));
		
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
