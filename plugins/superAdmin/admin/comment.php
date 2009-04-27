<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Super Admin, a plugin for Dotclear 2
# Copyright (C) 2009 Moe (http://gniark.net/)
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

$tab = 'comment';

if (!empty($_GET['comment_updated']))
{
	$msg = __('Comment has been successfully updated.');
}

$comment_id = null;
$comment_dt = '';
$comment_author = '';
$comment_email = '';
$comment_site = '';
$comment_content = '';
$comment_ip = '';
$comment_status = '';
$comment_trackback = 0;
$comment_spam_status = '';

if ((!isset($_REQUEST['comment_id'])) OR (!is_numeric($_REQUEST['comment_id'])))
{
	throw new Exception(__('Invalid comment ID.'));
	exit;
}
else
{
	# load comment
	$rs = superAdmin::getComments(
		array('comment_id' => $_REQUEST['comment_id']));
	
	# switch blog
	$core->setBlog($rs->blog_id);
	
	unset($rs);
}

# Status combo
foreach ($core->blog->getAllCommentStatus() as $k => $v)
{
	$status_combo[$v] = (string) $k;
}

# Adding comment
if (!empty($_POST['add']) && !empty($_POST['post_id']))
{
	try
	{
		$rs = superAdmin::getPosts(array('post_id' => $_POST['post_id']));
		
		if ($rs->isEmpty()) {
			throw new Exception(__('Entry does not exist.'));
		}
		
		$cur = $core->con->openCursor($core->prefix.'comment');
		
		$cur->comment_author = $_POST['comment_author'];
		$cur->comment_email = html::clean($_POST['comment_email']);
		$cur->comment_site = html::clean($_POST['comment_site']);
		$cur->comment_content = $core->HTMLfilter($_POST['comment_content']);
		$cur->post_id = (integer) $_POST['post_id'];
		
		# --BEHAVIOR-- adminBeforeCommentCreate
		$core->callBehavior('adminBeforeCommentCreate',$cur);
		
		$comment_id = $core->blog->addComment($cur);
		
		# --BEHAVIOR-- adminAfterCommentCreate
		$core->callBehavior('adminAfterCommentCreate',$cur,$comment_id);
		
		http::redirect($p_url.'&file=post&id='.$rs->post_id.'&co=1&creaco=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$comment_id = '';

if (!empty($_REQUEST['comment_id']))
{
	try
	{
		$rs = superAdmin::getComments(
			array('comment_id' => $_REQUEST['comment_id']));
		
		$rs->core = $core;
		$rs->extend('rsExtComment');
		
		# --BEHAVIOR-- coreBlogGetComments
		$core->callBehavior('coreBlogGetComments',$rs);
		
		if (!$rs->isEmpty()) {
			$comment_id = $rs->comment_id;
			$post_id = $rs->post_id;
			$post_type = $rs->post_type;
			$post_title = $rs->post_title;
			$comment_dt = $rs->comment_dt;
			$comment_author = $rs->comment_author;
			$comment_email = $rs->comment_email;
			$comment_site = $rs->comment_site;
			$comment_content = $rs->comment_content;
			$comment_ip = $rs->comment_ip;
			$comment_status = $rs->comment_status;
			$comment_trackback = (boolean) $rs->comment_trackback;
			$comment_spam_status = $rs->comment_spam_status;
			
			$comment_blog_id = $rs->blog_id;
		}
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

if (!$comment_id && !$core->error->flag())
	{
		$core->error->add(__('No comment'));
	}

if (!$core->error->flag() && isset($rs))
{
	$can_edit = $can_delete = $can_publish = $core->auth->check('contentadmin',$core->blog->id);
	
	if (!$core->auth->check('contentadmin',$core->blog->id) && $core->auth->userID() == $rs->user_id) {
		$can_edit = true;
		if ($core->auth->check('delete',$core->blog->id)) {
			$can_delete = true;
		}
		if ($core->auth->check('publish',$core->blog->id)) {
			$can_publish = true;
		}
	}
	
	# update comment
	if (!empty($_POST['update']) && $can_edit)
	{
		$cur = $core->con->openCursor($core->prefix.'comment');
		
		$cur->comment_author = $_POST['comment_author'];
		$cur->comment_email = html::clean($_POST['comment_email']);
		$cur->comment_site = html::clean($_POST['comment_site']);
		$cur->comment_content = $core->HTMLfilter($_POST['comment_content']);
		
		if (isset($_POST['comment_status'])) {
			$cur->comment_status = (integer) $_POST['comment_status'];
		}
		
		try
		{
			# --BEHAVIOR-- adminBeforeCommentUpdate
			$core->callBehavior('adminBeforeCommentUpdate',$cur,$comment_id);
			
			$core->blog->updComment($comment_id,$cur);
			
			# --BEHAVIOR-- adminAfterCommentUpdate
			$core->callBehavior('adminAfterCommentUpdate',$cur,$comment_id);
			
			http::redirect($p_url.'&file=comment&comment_id='.$comment_id.
				'&comment_updated=1');
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
	
	if (!empty($_POST['delete']) && $can_delete)
	{
		try {
			$core->blog->delComment($comment_id);
			http::redirect($p_url.'&file=comments&comment_deleted=1');
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
	
	if (!$can_edit) {
		$core->error->add(__("You can't edit this comment."));
	}
}

dcPage::open(__('Edit comment'),
	dcPage::jsPageTabs($tab).
	dcPage::jsConfirmClose('comment-form').
	dcPage::jsToolBar().
	dcPage::jsLoad('js/_comment.js')
);

echo('<h2>'.__('Super Admin').' &rsaquo; '.__('Edit comment').'</h2>');

if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';}

echo '<p><a href="'.$p_url.'&amp;file=comments" class="multi-part">'.
	__('Comments').'</a></p>';
	
if ($comment_id)
{
	echo('<div class="multi-part" id="comment" title="'.__('Edit comment').'">');
	
	$comment_mailto = '';
	if ($comment_email)
	{
		$comment_mailto = '<a href="mailto:'.html::escapeHTML($comment_email)
		.'?subject='.rawurlencode(sprintf(__('Your comment on my blog %s'),$core->blog->name))
		.'&body='
		.rawurlencode(sprintf(__("Hi!\n\nYou wrote a comment on:\n%s\n\n\n"),$rs->getPostURL()))
		.'">'.__('Send an e-mail').'</a>';
	}
	
	echo
	'<form action="'.http::getSelfURI().'" method="post" id="comment-form">'.
	'<p><label>'.__('IP address:').'</label> '.
	'<a href="'.$p_url.'&amp;file=comments&amp;ip='.$comment_ip.'">'.$comment_ip.'</a></p>'.
	
	'<p><label>'.__('Date:').'</label> '.
	dt::dt2str(__('%Y-%m-%d %H:%M'),$comment_dt).'</p>'.
	
	'<p><label class="required" title="'.__('Required field').'">'.__('Author:').
	form::field('comment_author',30,255,html::escapeHTML($comment_author)).
	'</label></p>'.
	
	'<p><label>'.__('Email:').
	form::field('comment_email',30,255,html::escapeHTML($comment_email)).
	$comment_mailto.
	'</label></p>'.
	
	'<p><label>'.__('Web site:').
	form::field('comment_site',30,255,html::escapeHTML($comment_site)).
	'</label></p>'.
	
	'<p><label>'.__('Status:').
	form::combo('comment_status',$status_combo,$comment_status,'','',!$can_publish).
	'</label></p>'.
	
	# --BEHAVIOR-- adminAfterCommentDesc
	$core->callBehavior('adminAfterCommentDesc', $rs).
	
	'<p class="area"><label for="comment_content">'.__('Comment:').'</label> '.
	form::textarea('comment_content',50,10,html::escapeHTML($comment_content)).
	'</p>'.
	
	'<p>'.form::hidden('comment_id',$comment_id).
	$core->formNonce().
	'<input type="submit" accesskey="s" name="update" value="'.__('save').'" /> ';
	
	if ($can_delete) {
		echo '<input type="submit" name="delete" value="'.__('delete').'" />';
	}
	echo
	'</p>'.
	'</form>'.
	'</div>';
	
	echo '<p><a href="'.$p_url.'&amp;file=posts" class="multi-part">'.
	__('Entries').'</a></p>';
}

dcPage::helpBlock('core_comments');
dcPage::close();
?>