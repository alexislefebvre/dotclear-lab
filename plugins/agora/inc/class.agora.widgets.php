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

class widgetsAgora
{
	public static function memberWidget($w)
	{
		global $core;
		
		//TO DO : choix de libellés pour les boutons 
		
		$user_displayname = ($core->auth->getInfo('user_displayname') == '' )? $core->auth->userID() : $core->auth->getInfo('user_displayname');
		$label_login = $w->label_login ? $w->label_login : __('Login');
		$label_logout = $w->label_logout ? $w->label_logout : __('Logout');
		$label_preferences = $w->label_preferences ? $w->label_preferences : __('My preferences');
		$avatar ='';

		$login = '<li><a href="'.$core->blog->url.$core->url->getURLFor("login").'">'.$label_login.'</a></li>';
		$content = '';
			//'<li><a href="'.$core->blog->url.$core->url->getBase("agora").'">'.__('Home').'</a></li>';
		$content .=
			($core->auth->userID() != false && isset($_SESSION['sess_user_id'])) ? //&& isset($_SESSION['sess_user_id'])) ?
			'<li><a href="'.$core->blog->url.$core->url->getURLFor("preferences").'">'.$label_preferences.' (<strong>'.$user_displayname.'</strong>)</a></li>'.
			'<li><a href="'.$core->blog->url.$core->url->getURLFor("logout").'">'.$label_logout.'</a></li>':
			//$new_post : 
			$login ;
		
		if ($core->auth->userID() && $w->avatar) {
			if (mediaAgora::myAvatar('sq')) {
				$avatar = '<p>'.mediaAgora::myAvatar('sq','avatar').'</p>';
			} elseif (mediaAgora::defaultAvatarExists()) {
				$avatar = '<p><img class="avatar" 
					src="'.mediaAgora::imagesURL().'/.avatar_sq.jpg" 
					alt="'.html::escapeHTML($core->auth->getInfo('user_cn')).'" /></p>';
			}
		}
		
		return
		'<div class="agorabox">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		$avatar.
		'<ul>'.
		$content.
		'</ul>'.
		'</div>';
	}
	public static function newentryWidget($w)
	{
		global $core, $_ctx;
		
		$new_post = ($w->label_new_entry != '') ? '<a href="'.$core->blog->url.$core->url->getURLFor("new").'">'.$w->label_new_entry.'</a>' : __('New entry');
		
		$res = ($core->auth->userID() != false && isset($_SESSION['sess_user_id'])) ?
		'<div class="agoranew">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<p>'.
		$new_post.
		'</p>'.
		'</div>' : '';
		
		return $res;
	}

	public static function lastPostsWidget($w)
	{
		global $core;

		if (((integer)$w->display == 2  && $core->auth->userID() == false) 
			|| ((integer)$w->display == 1 && $core->auth->userID() == true)
			|| (integer)$w->display == 0)
		{
			if ($w->homeonly && $core->url->type != 'default') {
				return;
			}
			
			$params['post_type'] = 'post';
			$params['limit'] = abs((integer) $w->limit);
			$params['order'] = $w->sortby.' '.$w->ordering;
			$params['post_status'] = 1;
			$params['no_content'] = true;
			
			if ($w->category)
			{
				if ($w->category == 'null') {
					$params['sql'] = ' AND p.cat_id IS NULL ';
				} elseif (is_numeric($w->category)) {
					$params['cat_id'] = (integer) $w->category;
				} else {
					$params['cat_url'] = $w->category;
				}
			}
			
			$rs = $core->blog->getPosts($params);
			
			if ($rs->isEmpty()) {
				return;
			}
			
			$res =
			'<div class="lastposts">'.
			($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
			'<ul>';
			
			while ($rs->fetch()) {
				$messages_count = (integer) $core->meta->getMetaRecordset($rs->post_meta,'nb_message')->meta_id - 1 ;
				$comments_count = (integer) $rs->nb_comment;
				$count = $core->blog->settings->agora->full_flag ? $messages_count : $comments_count;
				$res .= '<li><a href="'.$rs->getURL().'">'.
				html::escapeHTML($rs->post_title).
				($w->showauthor ? ' - '.html::escapeHTML($rs->getAuthorCN()) : '').
				'</a>'.($w->messagecount ? ' ('.$count.')' : '').
				'</li>';
			}
			
			$res .= '</ul></div>';
			
			return $res;
		}
		return;
	}

	public static function lastmessagesWidget($w)
	{
		global $core, $_ctx;

		if (((integer)$w->display == 2  && $core->auth->userID() == false) 
			|| ((integer)$w->display == 1 && $core->auth->userID() == true)
			|| (integer)$w->display == 0)
		{
			
			// NEW
			if ($w->homeonly && $core->url->type != 'default') {
				return;
			}
			
			$params['limit'] = abs((integer) $w->limit);
			//$params['sql'] = 'AND message_status IN (1,-1)';
			$params['message_status'] = 1;
			$params['post_status'] = 1;
			$params['order'] = 'message_id desc';
			if ($w->category)
			{
				if ($w->category == 'null') {
					$params['sql'] = ' AND p.cat_id IS NULL ';
				} elseif (is_numeric($w->category)) {
					$params['cat_id'] = (integer) $w->category;
				} else {
					$params['cat_url'] = $w->category;
				}
			}
			//$agora = new agora($core);
			$rs = $core->agora->getMessages($params);

			if ($rs->isEmpty()) {
				return;
			}
			
			$res = '<div class="lastmessages">'.
			($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
			'<ul>';
			
			while ($rs->fetch())
			{
				$res .= '<li><a href="'.$rs->getPostURL().'#m'.$rs->message_id.'">'.
				html::escapeHTML($rs->post_title).' - '.
				html::escapeHTML($rs->getAuthorCN()).
				'</a></li>';
			}
			
			$res .= '</ul></div>';
			
			return $res;
		}
		return;
	}

	public static function subscribeWidget($w)
	{
		global $core;

		if ($core->blog->settings->agora->private_flag && ($core->auth->userID() == false)) {
			return;
		}
		
		return defaultWidgets::subscribe($w);
	}

	public static function textWidget($w)
	{
		global $core;

		if (((integer)$w->display == 2  && $core->auth->userID() == false) 
			|| ((integer)$w->display == 1 && $core->auth->userID() == true)
			|| (integer)$w->display == 0)
		{
			return defaultWidgets::text($w);
		} 
		
		return;
	}

	public static function connectedWidget($w)
	{
		global $core, $_ctx;

		if (((integer)$w->display == 2  && $core->auth->userID() == false) 
			|| ((integer)$w->display == 1 && $core->auth->userID() == true)
			|| (integer)$w->display == 0)
		{
			// NEW 
			if ($w->homeonly && $core->url->type != 'default') {
				return;
			}

			$res = '<div class="connected">'.
				($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '');

			$params = $core->agora->getConnectedUsers();
			
			$rs = $core->agora->getUsers($params);
			if (!$rs->isEmpty()) {
				$res .= '<p>';
			} else {
				$res .= '<p>'.__('Nobody.').'</p>';
			}
				
			while ($rs->fetch())
			{
				$res .= $rs->getMemberProfile();
				$res .= $rs->isModerator() ? '<span title="'.__('Moderator').'">*</span>' : '';
				if (!$rs->isEnd()) {
					$res .= ', ';
				}
			}

			if (!empty($params) && !$rs->isEmpty()) {
				$res .= '</p>';
			}

			if (empty($params)) {
				$res = '<div class="connected">'.
					($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2><p>'.html::escapeHTML($w->nobodymessage).'</p>' : '');
			}
			
			if ($core->url->getURLFor('people') && !is_null($w->alluserslinktitle) && $w->alluserslinktitle !== '')
			{
				$res .=
				'<p><strong><a href="'.$core->blog->url.$core->url->getURLFor("people").'">'.
				html::escapeHTML($w->alluserslinktitle).'</a></strong></p>';
			}
			$res .= '</div>';
			
			return $res;
		}
		return;
	}
	
	public static function userSearchWidget($w)
	{
		global $core;

		if ($core->url->type != 'people') {
			return;
		}
		
		$value = isset($GLOBALS['_user_search']) ? html::escapeHTML($GLOBALS['_user_search']) : '';
		
		return
		'<div id="usersearch">'.
		($w->title ? '<h2><label for="q">'.html::escapeHTML($w->title).'</label></h2>' : '').
		'<form action="'.$core->blog->url.$core->url->getURLFor('people').'" method="get">'.
		'<fieldset>'.
		'<p><input type="text" size="10" maxlength="255" id="qu" name="qu" value="'.$value.'" /> '.
		'<input type="submit" class="submit" value="ok" /></p>'.
		'</fieldset>'.
		'</form>'.
		'</div>';
	}
}
?>
