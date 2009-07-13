<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of @ Reply, a plugin for Dotclear 2
# Copyright 2008,2009 Moe (http://gniark.net/) and buns
#
# @ Reply is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# @ Reply is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# Inspired by http://iyus.info/at-reply-petit-plugin-wordpress-inspire-par-twitter/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

$core->addBehavior('adminAfterCommentDesc',
	array('AtReplyAdmin','adminAfterCommentDesc'));
$core->addBehavior('adminBeforeCommentCreate',
	array('AtReplyAdmin','adminBeforeCommentCreate'));

$_menu['Plugins']->addItem(__('@ Reply'),'plugin.php?p=atReply',
	'index.php?pf=atReply/icon.png',preg_match('/plugin.php\?p=atReply(&.*)?$/',
		$_SERVER['REQUEST_URI']),$core->auth->check('admin',$core->blog->id));	

class AtReplyAdmin
{
	/**
	adminAfterCommentDesc behavior
	@param	rs	<b>recordset</b>	Recordset
	*/
	public static function adminAfterCommentDesc($rs)
	{	
		# ignore trackbacks
		if ($rs->comment_trackback == 1) {return;}
		
		# don't display on comment.php, it breaks the <form>
		if (strpos($_SERVER['REQUEST_URI'],'comment.php') !== false) {return;}
		
		global $core;
		
		$comment_content = '<p>@<a href="#c'.
			html::escapeHTML($rs->comment_id).'">'.
			html::escapeHTML($rs->comment_author).'</a> : </p>';
		
		return(
			# from /dotclear/admin/post.php, modified
			'<form action="comment.php" method="post" id="comment-form">'.
			form::hidden('comment_author',html::escapeHTML($core->auth->getInfo('user_cn'))).
			form::hidden('comment_email',html::escapeHTML($core->auth->getInfo('user_email'))).
			form::hidden('comment_site',html::escapeHTML($core->auth->getInfo('user_url'))).
			form::hidden('comment_content',html::escapeHTML($comment_content)).
			form::hidden('comment_atreply_comment_status',-1).
			form::hidden('post_id',$rs->post_id).
			$core->formNonce().
			'<p><strong>'.__('@ Reply').' :</strong> '.
				'<input type="submit" name="add" value="'.
				__('reply to this comment').'" /></p>'.
			'</form>'
			# /from /dotclear/admin/post.php, modified
		);
	}
	
	/**
	adminBeforeCommentCreate behavior
	@param	cur	<b>cursor</b>	Cursor
	*/
	public static function adminBeforeCommentCreate(&$cur)
	{
		if (isset($_POST['comment_atreply_comment_status']))
		{
			$cur->comment_status = (integer) $_POST['comment_atreply_comment_status'];
		}
	}
}

?>