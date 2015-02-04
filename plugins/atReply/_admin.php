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
# Big icon (icon-big.png) come from Dropline Neu! :
# <http://art.gnome.org/themes/icon?sort=popularity>
#
# Inspired by @ Reply for WordPress :
# <http://iyus.info/at-reply-petit-plugin-wordpress-inspire-par-twitter/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/admin');

$core->addBehavior('adminAfterCommentDesc',
	array('AtReplyAdmin','adminAfterCommentDesc'));

# from hum plugin
# admin/comments.php Add actions on comments combo
$core->addBehavior('adminCommentsActionsCombo',
	array('AtReplyAdmin','adminCommentsActionsCombo'));
# admin/comments_actions.php Save actions on comments
$core->addBehavior('adminCommentsActions',
	array('AtReplyAdmin','adminCommentsActions'));
# /from hum plugin

$core->addBehavior('adminBeforeCommentCreate',
	array('AtReplyAdmin','adminBeforeCommentCreate'));

$core->addBehavior('adminAfterCommentCreate',
	array('AtReplyAdmin','adminAfterCommentCreate'));

$_menu['Blog']->addItem(__('@ Reply'),'plugin.php?p=atReply',
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
		}
	}

	# from hum plugin
	public static function adminCommentsActionsCombo($args)
	{
		if ($GLOBALS['core']->auth->check('publish,contentadmin',
			$GLOBALS['core']->blog->id))
		{
			$args[0][__('Create a new comment in reply to this comment')] = 'atreply_reply';
		}
	}

	public static function adminCommentsActions($core,$co,$action,$redir)
	{
		# ignore trackbacks
		if ($co->comment_trackback == 1) {return;}
		
		if ($action == 'atreply_reply')
		{
			# reply to one comment, everything is alright
			# Adding comment
			if ($co->count() == 1)
			{
				$post_id = $co->post_id;
				
				try
				{
					$rs = $core->blog->getPosts(
						array('post_id' => $post_id,
							'post_type' => ''));
					
					if ($rs->isEmpty()) {
						throw new Exception(__('Entry does not exist.'));
					}
					
					$cur = $core->con->openCursor($core->prefix.'comment');
					
					$cur->comment_author = $core->auth->getInfo('user_cn');
					$cur->comment_email = html::clean($core->auth->getInfo('user_email'));
					$cur->comment_site = html::clean($core->auth->getInfo('user_url'));
					$cur->comment_content = $core->HTMLfilter(
						'<p>'.
							sprintf(__('@%s:'),'<a href="'.
							$rs->getURL().'#c'.
							html::escapeHTML($co->comment_id).'">'.
							html::escapeHTML($co->comment_author).'</a>').
						' </p>'
					);
					
					$cur->comment_status = -1;
					
					$cur->post_id = (integer) $post_id;
					
					# --BEHAVIOR-- adminBeforeCommentCreate
					$core->callBehavior('adminBeforeCommentCreate',$cur);
					
					$comment_id = $core->blog->addComment($cur);
					
					# --BEHAVIOR-- adminAfterCommentCreate
					$core->callBehavior('adminAfterCommentCreate',$cur,$comment_id);
					
					if (($core->blog->settings->atreply_subscribe_replied_comment == true)
						&& ($core->plugins->moduleExists('subscribeToComments')))
					{
						# subscribe the email address of the replied comment
						$subscriber = new subscriber($co->comment_email);
						$subscriber->subscribe($cur->post_id);
					}
					
					http::redirect('comment.php?id='.$comment_id.'&at_reply_creaco=1');
				} catch (Exception $e) {
					$core->error->add($e->getMessage());
				}
			}
			
			if (!$core->error->flag()) {
				http::redirect($redir);
			}
		}
	}
	# /from hum plugin
}

$core->addBehavior('adminDashboardFavorites','atReplyDashboardFavorites');

function atReplyDashboardFavorites($core,$favs)
{
	$favs->register('atReply', array(
		'title' => __('@ Reply'),
		'url' => 'plugin.php?p=atReply',
		'small-icon' => 'index.php?pf=atReply/icon.png',
		'large-icon' => 'index.php?pf=atReply/icon-big.png',
		'permissions' => 'usage,contentadmin'
	));
}