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

class widgetsAgora
{
	public static function memberWidget($w)
	{
		global $core;
		
		/*if ($core->url->type != 'place' && $core->url->type != 'thread' 
			&& $core->url->type != 'agora' && $core->url->type != 'agora-page') {
			return;
		}*/
		
		$user_displayname = ($core->auth->getInfo('user_displayname') == '' )? $core->auth->userID() : $core->auth->getInfo('user_displayname');
		
		$content = 
			'<li><a href="'.$core->blog->url.$core->url->getBase("agora").'">'.__('Home').'</a></li>';
		$content .=
			($core->auth->userID() != false && isset($_SESSION['sess_user_id'])) ?
			'<li><a href="'.$core->blog->url.$core->url->getBase("profile").'/'.$core->auth->userID().'"><strong>'.$user_displayname.'</strong></a></li>'.
			'<li><a href="'.$core->blog->url.$core->url->getBase("logout").'">'.__('Logout').'</a></li>'.
			'<li><a href="'.$core->blog->url.$core->url->getBase("newthread").'">'.__('Create a new thread').'</a></li>' : 
			'<li><a href="'.$core->blog->url.$core->url->getBase("login").'">'.__('Login').'</a></li>';
		
		return
		'<div class="agorabox">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>'.
		$content.
		'</ul>'.
		'</div>';
	}
	
	public static function moderateWidget($w)
	{
		global $core, $_ctx;
		
		if ($core->url->type != 'thread') {
			return;
		}
		
		$url = $core->blog->url.$core->url->getBase("thread")."/".$_ctx->posts->post_url;
		$url .= strpos($core->blog->url,'?') !== false ? '&' : '?';
		$openclose = $_ctx->posts->post_open_comment ? 
			'<li><a href="'.$url.'action=close'.'">'.__('Close the thread').'</a></li>' : 
			'<li><a href="'.$url.'action=open'.'">'.__('Open the thread').'</a></li>';
			
		$pinunpin = $_ctx->posts->post_selected ? 
			'<li><a href="'.$url.'action=unpin'.'">'.__('Unpin the thread').'</a></li>' : 
			'<li><a href="'.$url.'action=pin'.'">'.__('Pin the thread').'</a></li>';
		
		$res  = 
			(($core->auth->userID() != false) && $_ctx->agora->isModerator($core->auth->userID()) === true) ?
			'<div class="agoramodobox">'.
			($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
			'<ul>'.
			$openclose.
			$pinunpin.
			'</ul>'.
			'</div>' :'';
		
		return $res;
	}

	public static function categoriesWidget($w)
	{
		global $core, $_ctx;

		if ($core->url->type != 'place' && $core->url->type != 'thread' 
			&& $core->url->type != 'agora' && $core->url->type != 'agora-page') {
			return;
		}
		
		$rs = $core->blog->getCategories(array('post_type'=>'thread'));
		if ($rs->isEmpty()) {
			return;
		}
		
		$res =
		'<div class="categories">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '');
		
		$ref_level = $level = $rs->level-1;
		while ($rs->fetch())
		{
			$class = '';
			if (($core->url->type == 'place' && $_ctx->categories instanceof record && $_ctx->categories->cat_id == $rs->cat_id)
			|| ($core->url->type == 'thread' && $_ctx->posts instanceof record && $_ctx->posts->cat_id == $rs->cat_id)) {
				$class = ' class="category-current"';
			}
			
			if ($rs->level > $level) {
				$res .= str_repeat('<ul><li'.$class.'>',$rs->level - $level);
			} elseif ($rs->level < $level) {
				$res .= str_repeat('</li></ul>',-($rs->level - $level));
			}
			
			if ($rs->level <= $level) {
				$res .= '</li><li'.$class.'>';
			}
			
			$res .=
			'<a href="'.$core->blog->url.$core->url->getBase('place').'/'.
			$rs->cat_url.'">'.
			html::escapeHTML($rs->cat_title).'</a>'.
			($w->postcount ? ' ('.$rs->nb_post.')' : '');
			
			
			$level = $rs->level;
		}
		
		if ($ref_level - $level < 0) {
			$res .= str_repeat('</li></ul>',-($ref_level - $level));
		}
		$res .= '</div>';
		
		return $res;
	}

	public static function bestofWidget($w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'agora') {
			return;
		}
		
		$params = array(
			'post_type' => 'thread',
			'post_selected'=>true,
			'no_content'=>true,
			'order'=>'post_dt desc');
		
		$rs = $core->blog->getPosts($params);
		
		if ($rs->isEmpty()) {
			return;
		}
		
		$res =
		'<div class="selected">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		
		while ($rs->fetch()) {
			$res .= ' <li><a href="'.$rs->getURL().'">'.html::escapeHTML($rs->post_title).'</a></li> ';
		}
		
		$res .= '</ul></div>';
		
		return $res;
	}

	public static function lastthreadsWidget($w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'agora') {
			return;
		}
		
		$params['post_type'] = 'thread';
		$params['limit'] = abs((integer) $w->limit);
		$params['order'] = 'post_id desc';
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
		'<div class="lastthreads">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		
		while ($rs->fetch()) {
			$res .= '<li><a href="'.$rs->getURL().'">'.
			html::escapeHTML($rs->post_title).'</a></li>';
		}
		
		$res .= '</ul></div>';
		
		return $res;
	}

	public static function lastmessagesWidget($w)
	{
		global $core, $_ctx;
		
		if ($w->homeonly && $core->url->type != 'agora') {
			return;
		}
		
		$params['limit'] = abs((integer) $w->limit);
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
		$agora = new agora($core);
		$rs = $agora->getMessages($params);

		if ($rs->isEmpty()) {
			return;
		}
		
		$res = '<div class="lastmessages">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		
		while ($rs->fetch())
		{
			$res .= '<li><a href="'.$rs->getThreadURL().'#m'.$rs->message_id.'">'.
			html::escapeHTML($rs->post_title).' - '.
			html::escapeHTML($rs->getAuthorCN()).
			'</a></li>';
		}
		
		$res .= '</ul></div>';
		
		return $res;
	}

	public static function subscribeWidget($w)
	{
		global $core, $_ctx;

		if ($w->homeonly && $core->url->type != 'agora') {
			return;
		}
		
		$type = ($w->type == 'atom' || $w->type == 'rss2') ? $w->type : 'rss2';
		$mime = $type == 'rss2' ? 'application/rss+xml' : 'application/atom+xml';
		
		$p_title = __('This agora\'s threads %s feed');
		$c_title = __('This agora\'s messages %s feed');
		
		$res =
		'<div class="syndicate">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		
		$res .=
		'<li><a type="'.$mime.'" '.
		'href="'.$core->blog->url.$core->url->getBase('agora_feed').'/'.$type.'" '.
		'title="'.sprintf($p_title,($type == 'atom' ? 'Atom' : 'RSS')).'" class="feed">'.
		__('Threads feed').'</a></li>'.
		'<li><a type="'.$mime.'" '.
		'href="'.$core->blog->url.$core->url->getBase('agora_feed').'/'.$type.'/messages" '.
		'title="'.sprintf($c_title,($type == 'atom' ? 'Atom' : 'RSS')).'" class="feed">'.
		__('Messages feed').'</a></li>';
		
		$res .= '</ul></div>';
		
		return $res;
	}
}
?>