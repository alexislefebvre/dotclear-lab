<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of @ Reply, a plugin for Dotclear 2
# Copyright (C) 2008,2009,2010,2011 Moe (http://gniark.net/) and buns
#
# @ Reply is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# @ Reply is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
#
# Inspired by @ Reply for WordPress :
# <http://iyus.info/at-reply-petit-plugin-wordpress-inspire-par-twitter/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/admin');

$core->addBehavior('adminAfterCommentDesc',
	array('AtReplyAdmin','adminAfterCommentDesc'));

$core->addBehavior('adminBeforeCommentCreate',
	array('AtReplyAdmin','adminBeforeCommentCreate'));

$core->addBehavior('adminAfterCommentCreate',
	array('AtReplyAdmin','adminAfterCommentCreate'));

$_menu['Plugins']->addItem(__('@ Reply'),'plugin.php?p=atReply',
	'index.php?pf=atReply/icon.png',preg_match('/plugin.php\?p=atReply(&.*)?$/',
		$_SERVER['REQUEST_URI']),$core->auth->check('admin',$core->blog->id));	

class AtReplyAdmin
{
	/**
	adminAfterCommentDesc behavior
	display information on the admin comment form
	@param	rs	<b>recordset</b>	Recordset
	@return	<b>string</b>	String
	*/
	public static function adminAfterCommentDesc($rs)
	{	
		# ignore trackbacks
		if ($rs->comment_trackback == 1) {return;}
		
		# on comment.php, tell the user what to do
		if (strpos($_SERVER['REQUEST_URI'],'comment.php') !== false)
		{
			if (isset($_GET['at_reply_creaco']))
			{
				return('<p class="message">'.__('@ Reply:').' '.
				__('Comment has been successfully created.').' '.
				__('Please edit and publish it.').
				'</p>');
			}
			# don't display the form on comment.php, it would break the <form>
			return;
		}
		
		global $core;
		
		$comment_content = '<p>'.
			sprintf(__('@%s:'),'<a href="#c'.
			html::escapeHTML($rs->comment_id).'">'.
			html::escapeHTML($rs->comment_author).'</a>').' </p>';
		
		return(
			# from /dotclear/admin/post.php, modified
			'<form action="comment.php" method="post">'.
			form::hidden(array('comment_author'),html::escapeHTML($core->auth->getInfo('user_cn'))).
			form::hidden(array('comment_email'),html::escapeHTML($core->auth->getInfo('user_email'))).
			form::hidden(array('comment_site'),html::escapeHTML($core->auth->getInfo('user_url'))).
			form::hidden(array('comment_content'),html::escapeHTML($comment_content)).
			form::hidden(array('comment_atreply_comment_status'),-1).
			form::hidden(array('post_id'),$rs->post_id).
			form::hidden(array('at_reply'),1).
			form::hidden(array('at_reply_email_address'),html::escapeHTML($rs->comment_email)).
			$core->formNonce().
			'<p><strong>'.__('@ Reply:').'</strong> '.
				'<input type="submit" name="add" value="'.
				__('Reply to this comment').'" /></p>'.
			'</form>'
			# /from /dotclear/admin/post.php, modified
		);
	}
	
	/**
	adminBeforeCommentCreate behavior
	@param	cur	<b>cursor</b>	Cursor
	*/
	public static function adminBeforeCommentCreate($cur)
	{
		if (isset($_POST['comment_atreply_comment_status']))
		{
			$cur->comment_status = (integer) $_POST['comment_atreply_comment_status'];
		}
	}

	/**
	adminAfterCommentCreate behavior
	directly edit the comment
	@param	cur	<b>cursor</b>	Cursor
	@param	comment_id	<b>integer</b>	Comment id
	*/
	public static function adminAfterCommentCreate($cur,$comment_id)
	{
		global $core;

		if (isset($_POST['at_reply']))
		{
			if ($core->blog->settings->atreply_subscribe_replied_comment == true)
			{
				if ($core->plugins->moduleExists('subscribeToComments'))
				{
					# subscribe the email address of the replied comment
					$subscriber = new subscriber($_POST['at_reply_email_address']);
					$subscriber->subscribe($cur->post_id);
				}
			}
			
			http::redirect('comment.php?id='.$comment_id.'&at_reply_creaco=1');
		}
	}
}

?>