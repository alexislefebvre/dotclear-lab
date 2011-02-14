<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Super Admin, a plugin for Dotclear 2
# Copyright (C) 2009, 2011 Moe (http://gniark.net/)
#
# Super Admin is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Super Admin is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

dcPage::checkSuper();

$core->rest->addFunction('getPostById',array('dcRestMethods','getPostById'));
$core->rest->addFunction('getCommentById',array('dcRestMethods','getCommentById'));
$core->rest->serve();

/* Common REST methods */
class dcRestMethods
{
	public static function getPostById($core,$get)
	{
		if (empty($get['id'])) {
			throw new Exception('No post ID');
		}
		
		$params = array('post_id' => (integer) $get['id']);
		
		if (isset($get['post_type'])) {
			$params['post_type'] = $get['post_type'];
		}
		
		$rs = superAdmin::getPosts($params);
		
		if ($rs->isEmpty()) {
			throw new Exception('No post for this ID');
		}
		
		$rsp = new xmlTag('post');
		$rsp->id = $rs->post_id;
		
		$rsp->blog_id($rs->blog_id);
		$rsp->user_id($rs->user_id);
		$rsp->cat_id($rs->cat_id);
		$rsp->post_dt($rs->post_dt);
		$rsp->post_creadt($rs->post_creadt);
		$rsp->post_upddt($rs->post_upddt);
		$rsp->post_format($rs->post_format);
		$rsp->post_url($rs->post_url);
		$rsp->post_lang($rs->post_lang);
		$rsp->post_title($rs->post_title);
		$rsp->post_excerpt($rs->post_excerpt);
		$rsp->post_excerpt_xhtml($rs->post_excerpt_xhtml);
		$rsp->post_content($rs->post_content);
		$rsp->post_content_xhtml($rs->post_content_xhtml);
		$rsp->post_notes($rs->post_notes);
		$rsp->post_status($rs->post_status);
		$rsp->post_selected($rs->post_selected);
		$rsp->post_open_comment($rs->post_open_comment);
		$rsp->post_open_tb($rs->post_open_tb);
		$rsp->nb_comment($rs->nb_comment);
		$rsp->nb_trackback($rs->nb_trackback);
		$rsp->user_name($rs->user_name);
		$rsp->user_firstname($rs->user_firstname);
		$rsp->user_displayname($rs->user_displayname);
		$rsp->user_email($rs->user_email);
		$rsp->user_url($rs->user_url);
		$rsp->cat_title($rs->cat_title);
		$rsp->cat_url($rs->cat_url);
		
		$rsp->post_display_content($rs->getContent(true));
		$rsp->post_display_excerpt($rs->getExcerpt(true));
		
		$metaTag = new xmlTag('meta');
		if (($meta = @unserialize($rs->post_meta)) !== false)
		{
			foreach ($meta as $K => $V)
			{
				foreach ($V as $v) {
					$metaTag->$K($v);
				}
			}
		}
		$rsp->post_meta($metaTag);
		
		return $rsp;
	}
	
	public static function getCommentById($core,$get)
	{
		if (empty($get['id'])) {
			throw new Exception('No comment ID');
		}
		
		$rs = superAdmin::getComments(array('comment_id' => (integer) $get['id']));
		
		if ($rs->isEmpty()) {
			throw new Exception('No comment for this ID');
		}
		
		$rsp = new xmlTag('post');
		$rsp->id = $rs->comment_id;
		
		$rsp->comment_dt($rs->comment_dt);
		$rsp->comment_upddt($rs->comment_upddt);
		$rsp->comment_author($rs->comment_author);
		$rsp->comment_site($rs->comment_site);
		$rsp->comment_content($rs->comment_content);
		$rsp->comment_trackback($rs->comment_trackback);
		$rsp->comment_status($rs->comment_status);
		$rsp->post_title($rs->post_title);
		$rsp->post_url($rs->post_url);
		$rsp->post_id($rs->post_id);
		$rsp->post_dt($rs->post_dt);
		$rsp->user_id($rs->user_id);
		
		$rsp->comment_display_content($rs->getContent(true));
		
		if ($core->auth->userID()) {
			$rsp->comment_ip($rs->comment_ip);
			$rsp->comment_email($rs->comment_email);
			# --BEHAVIOR-- adminAfterCommentDesc
			$rsp->comment_spam_disp($core->callBehavior('adminAfterCommentDesc', $rs));
		}
		
		return $rsp;
	}
}
?>