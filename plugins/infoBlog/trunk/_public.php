<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of infoBlog, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }
class infoBlogPublic
{
	/**
	 * This function displays the infoBlog widget
	 *
	 * @param	w	Widget object
	 *
	 * @return	string
	 */
	public static function widget($w)
	{
		global $core;

		if (($w->homeonly == 1 && $core->url->type != 'default') ||
			($w->homeonly == 2 && $core->url->type == 'default')) {
			return;
		}

		$mask = '<li class="%1$s">%2$s</li>';

		$title = strlen($w->title) > 0 ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '';
		
		$res = '';

		if ($w->displayentriesnumber) {
			$post_count = $core->blog->getPosts(array(),true)->f(0);
			$str_entries = ($post_count > 1) ? __('entries') : __('entry');
			$res .= sprintf($mask,'entries-number',$post_count.' '.$str_entries);
		}
		if ($w->displayselectedentriesnumber) {
			$post_selected_count = $core->blog->getPosts(array('post_selected' => 1),true)->f(0);
			$str_entries_selected = ($post_selected_count > 1) ? __('selected entries') : __('selected entry');
			$res .= sprintf($mask,'entries-number',$post_selected_count.' '.$str_entries_selected);
		}
		if ($w->displayentrieswaitingnumber) {
			$strReq = "SELECT count(P.post_id) FROM ".$core->prefix.
			"post P INNER JOIN ".$core->prefix.
			"user U ON U.user_id = P.user_id LEFT OUTER JOIN ".$core->prefix.
			"category C ON P.cat_id = C.cat_id WHERE P.blog_id = '".$core->blog->id.
			"' AND post_type = 'post' AND post_status = -2";
			$post_waiting_count = $core->con->select($strReq)->f(0);
			$str_entries_waiting = ($post_waiting_count > 1) ? __('entries waiting') : __('entry waiting');
			$res .= sprintf($mask,'entries-waiting-number',$post_waiting_count.' '.$str_entries_waiting);
		}
		if ($w->displayscheduledentriesnumber) {
			$strReq = "SELECT count(P.post_id) FROM ".$core->prefix.
			"post P INNER JOIN ".$core->prefix.
			"user U ON U.user_id = P.user_id LEFT OUTER JOIN ".$core->prefix.
			"category C ON P.cat_id = C.cat_id WHERE P.blog_id = '".$core->blog->id.
			"' AND post_type = 'post' AND post_status = -1";
			$post_scheduled_count = $core->con->select($strReq)->f(0);
			$str_entries_scheduled = ($post_scheduled_count > 1) ? __('scheduled entries') : __('scheduled entry');
			$res .= sprintf($mask,'entries-scheduled-number',$post_scheduled_count.' '.$str_entries_scheduled);
		}
		if ($w->displayofflineentriesnumber) {
			$strReq = "SELECT count(P.post_id) FROM ".$core->prefix.
			"post P INNER JOIN ".$core->prefix.
			"user U ON U.user_id = P.user_id LEFT OUTER JOIN ".$core->prefix.
			"category C ON P.cat_id = C.cat_id WHERE P.blog_id = '".$core->blog->id.
			"' AND post_type = 'post' AND post_status = -0";
			$post_offline_count = $core->con->select($strReq)->f(0);
			$str_entries_offline = ($post_offline_count > 1) ? __('offline entries') : __('offline entry');
			$res .= sprintf($mask,'entries-offline-number',$post_offline_count.' '.$str_entries_offline);
		}
		if ($w->displaycommentsnumber) {
			$comment_count = $core->blog->getComments(array(),true)->f(0);
			$str_comments = ($comment_count > 1) ? __('comments') : __('comment');
			$res .= sprintf($mask,'comments-number',$comment_count.' '.$str_comments);
		}
		if ($w->displaypingsnumber) {
			$ping_count = $core->blog->getComments(array('comment_trackback' => true),true)->f(0);
			$str_pings = ($comment_count > 1) ? __('pings') : __('ping');
			$res .= sprintf($mask,'pings-number',$ping_count.' '.$str_pings);
		}
		if ($w->displaystartblogdate) {
			$start = $core->blog->getPosts(array('order' => 'post_dt ASC','limit' => '1'));
			$str_start = dt::dt2str($core->blog->settings->date_format,$start->post_dt);
			$str_date = sprintf($w->displaystartblogdatetext,$str_start);
			if (!empty($str_date)) {
				$res .= sprintf($mask,'start-date',$str_date);
			}
		}
		if ($w->displayauthors) {
			$list = '';
			$users = $core->getUsers();
			while ($users->fetch()){
				$str = '<li>%1$s</li>';
				if ($users->user_url) {
					$str = '<li><a href="%2$s">%1$s</a></li>';
				}
				if ($w->displayauthorstats) {
					$post_count = $core->blog->getPosts(array('user_id' => $users->user_id),true)->f(0);
					$str_authors =
						rsExtPost::getAuthorCN($users).' - '.$post_count.' '.
						(($post_count > 1) ? __('entries') : __('entry'));
				}
				else {
					$str_authors = rsExtPost::getAuthorCN($users);
				}
				$list .= sprintf($str,$str_authors,$users->user_url);
			}
			if (!empty($list)) {
				$list = sprintf('<ul class="%1$s">%2$s</ul>','authors-list',$list);
				$res .= sprintf($mask,'authors',__('Authors:').$list);
			}
		}
		
		$res = !empty($res) ? '<ul>'.$res.'</ul>' : '';

		return
			$res = ($w->content_only ? '' : '<div class="info-blog'.($w->class ? ' '.html::escapeHTML($w->class) : '').'">').
			//'<div id="info-blog">'.
			$title.
			$res.
		//	'</div>';
			($w->content_only ? '' : '</div>');
	}
}

?>