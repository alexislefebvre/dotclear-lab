<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of hum, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$core->blog->settings->addNamespace('hum');

# Plugin menu
$_menu['Plugins']->addItem(
	__('Useless comments'),
	'plugin.php?p=hum','index.php?pf=hum/icon.png',
	preg_match('/plugin.php\?p=hum(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id)
);

if ($core->blog->settings->hum->active) {
	# admin/comments.php Add actions on comments combo
	$core->addBehavior('adminCommentsActionsCombo',array('adminHum','adminCommentsActionsCombo'));
	# admin/comments_actions.php Save actions on comments
	$core->addBehavior('adminCommentsActions',array('adminHum','adminCommentsActions'));
	# admin/comment.php Action on comments
	$core->addBehavior('adminBeforeCommentCreate',array('adminHum','adminBeforeCommentSave'));
	$core->addBehavior('adminBeforeCommentUpdate',array('adminHum','adminBeforeCommentSave'));
	$core->addBehavior('adminAfterCommentDesc',array('adminHum','adminAfterCommentDesc'));
	# inc/blog.php Extend core blog getComments()
	$core->addBehavior('coreBlogGetComments',array('adminHum','coreBlogGetComments'));
	$core->addBehavior('coreBeforeCommentCreate',array('adminHum','coreBeforeCommentCreate'));
	# admin/posts.php Add actions on comments combo of entries
	$core->addBehavior('adminPostsActionsCombo',array('adminHum','adminPostsActionsCombo'));
	# admin/posts_actions.php Save actions on comments of entries
	$core->addBehavior('adminPostsActions',array('adminHum','adminPostsActions'));
}

# Extends getComments() only on admin side
class rsExtHum extends rsExtComment
{
	public static function is_selected($rs)
	{
		# On bloc "Comments" on public side we have it.
		if ($rs->exists('comment_selected')) {
			return (boolean) $rs->comment_selected;
		}
		# Or create a memory array (to prevent multiple requests on same id)
		if (!$rs->exists('comment_selected_memory')) {
			$rs->comment_selected_memory = array();
		}
		# Check memory array to see if it has it
		if (empty($rs->comment_selected_memory[$rs->comment_id])) {
			$res = $rs->core->con->select(
				'SELECT comment_selected FROM '.$rs->core->prefix.'comment '.
				'WHERE comment_id = '.$rs->comment_id.' '.
				'LIMIT 1'
			);
			# Put info into memory array
			if ($res->isEmpty()) {
				$rs->comment_selected_memory[$rs->comment_id] = 0;
			}
			else {
				$rs->comment_selected_memory[$rs->comment_id] = $res->f(0);
			}
		}
		# Then return info
		return (boolean) $rs->comment_selected_memory[$rs->comment_id];
	}
}

class adminHum
{
	public static function coreBlogGetComments($rs)
	{
		$rs->extend('rsExtHum');
	}
	
	public static function coreBeforeCommentCreate($blog,$cur)
	{
		if (null === $cur->comment_selected) {
			$cur->comment_selected = (integer) $blog->settings->hum->comment_selected;
		}
	}
	
	public static function adminCommentsActionsCombo($args)
	{
		if ($GLOBALS['core']->auth->check('publish,contentadmin',$GLOBALS['core']->blog->id))
		{
			$args[0][__('Mark as selected')] = 'selected';
			$args[0][__('Mark as unselected')] = 'unselected';
		}
	}
	
	public static function adminCommentsActions($core,$co,$action,$redir)
	{
		if (preg_match('/^(selected|unselected)$/',$action))
		{
			switch ($action) {
				case 'selected' : $selected = 1; break;
				default : $selected = 0; break;
			}
			
			while ($co->fetch())
			{
				try {
					$cur = $core->con->openCursor($core->prefix.'comment');
					$cur->comment_selected = (integer) $selected;
					$core->blog->updComment($co->comment_id,$cur);
				} catch (Exception $e) {
					$core->error->add($e->getMessage());
				}
			}
			
			if (!$core->error->flag()) {
				http::redirect($redir);
			}
		}
	}
	
	public static function adminBeforeCommentSave($cur,$comment_id=null)
	{
		$cur->comment_selected = (integer) !empty($_POST['comment_selected']);
	}
	
	public static function adminAfterCommentDesc($rs)
	{
		return 
		'<p><label class="classic">'.form::checkbox('comment_selected',1,$rs->is_selected(),'',3).' '.
		__('Selected comment').'</label></p>';
	
	}
	
	public static function adminPostsActionsCombo($args)
	{
		if ($GLOBALS['core']->auth->check('publish,contentadmin',$GLOBALS['core']->blog->id))
		{
			$args[0][__('Comments')][__('Mark as selected')] = 'commentselected';
			$args[0][__('Comments')][__('Mark as unselected')] = 'commentunselected';
		}
	}
	
	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		if (preg_match('/^(commentselected|commentunselected)$/',$action))
		{
			switch ($action) {
				case 'commentselected' : $selected = 1; break;
				default : $selected = 0; break;
			}
			
			while ($posts->fetch())
			{
				try {
					$co_params['post_id'] = $posts->post_id;
					$co_params['comment_trackback'] = 0;
					$co = $core->blog->getComments($co_params);
					while($co->fetch()) {
						$cur = $core->con->openCursor($core->prefix.'comment');
						$cur->comment_selected = (integer) $selected;
						$core->blog->updComment($co->comment_id,$cur);
					}
				} catch (Exception $e) {exit(1);
					$core->error->add($e->getMessage());
				}
			}
			
			if (!$core->error->flag()) {
				http::redirect($redir);
			}
		}
	}
}
?>