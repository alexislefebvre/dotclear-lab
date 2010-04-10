<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of postExpired, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}
if (!$core->plugins->moduleExists('metadata')){return;}

# Admin behaviors
$core->addBehavior('adminPostHeaders',array('postExpiredAdmin','header'));
$core->addBehavior('adminPostFormSidebar',array('postExpiredAdmin','form'));
$core->addBehavior('adminAfterPostCreate',array('postExpiredAdmin','set'));
$core->addBehavior('adminAfterPostUpdate',array('postExpiredAdmin','set'));
$core->addBehavior('adminBeforePostDelete',array('postExpiredAdmin','del'));
$core->addBehavior('adminPostsActionsCombo',array('postExpiredAdmin','combo'));
$core->addBehavior('adminPostsActions',array('postExpiredAdmin','action'));
$core->addBehavior('adminPostsActionsContent',array('postExpiredAdmin','content'));

# Admin behaviors class
class postExpiredAdmin
{
	public static function header()
	{
		return
		dcPage::jsDatePicker().
		dcPage::jsLoad('index.php?pf=postExpired/js/postexpired.js');
	}

	public static function form($post)
	{
		$post_expired = '';
		if ($post) {
			$meta = new dcMeta($GLOBALS['core']);
			$rs = $meta->getMeta('postexpired',1,null,$post->post_id);
			$post_expired = $rs->isEmpty() ? '' : date('Y-m-d H:i',strtotime($rs->meta_id));
		}

		echo 
		'<h3 class="clear">'.__('Expired date').'</h3>'.
		'<p><label>'.
		form::field('post_expired',16,16,$post_expired,'',3).
		'</label></p>'.
		'<p class="form-note">'.__('Leave it empty for no expired date.').'</p>';
	}

	public static function set(&$cur,&$post_id)
	{
		if (!isset($_POST['post_expired'])) return;

		$post_id = (integer) $post_id;
		$meta = new dcMeta($GLOBALS['core']);
		$meta->delPostMeta($post_id,'postexpired');

		if (!empty($_POST['post_expired'])) {
			$post_expired = date('Y-m-d H:i:00',strtotime($_POST['post_expired']));
			$meta->setPostMeta($post_id,'postexpired',$post_expired);
		}
	}

	public static function del($post_id)
	{
		$post_id = (integer) $post_id;
		$meta = new dcMeta($GLOBALS['core']);
		$meta->delPostMeta($post_id,'postexpired');
	}

	public static function combo(&$args)
	{
		if ($GLOBALS['core']->auth->check('usage,contentadmin',$GLOBALS['core']->blog->id)) {
			$args[0][__('Expired entries')][__('add expired date')] = 'postexpired_add';
		}
		if ($GLOBALS['core']->auth->check('delete,contentadmin',$GLOBALS['core']->blog->id)) {
			$args[0][__('Expired entries')][__('remove expired date')] = 'postexpired_remove';
		}
	}

	public static function action(&$core,$posts,$action,$redir)
	{
		if ($action == 'postexpired_add' && !empty($_POST['new_post_expired']) 
		 && $core->auth->check('usage,contentadmin',$core->blog->id))
		{
			try {
				$meta = new dcMeta($core);
				$new_post_expired = date('Y-m-d H:i:00',strtotime($_POST['new_post_expired']));

				while ($posts->fetch())
				{
					$rs = $meta->getMeta('postexpired',1,null,$posts->post_id);
					if ($rs->isEmpty()) {
						$meta->setPostMeta($posts->post_id,'postexpired',$new_post_expired);
					}
				}
				http::redirect($redir);
			}
			catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
		elseif ($action == 'postexpired_remove' && !empty($_POST['meta_id']) 
		 && $core->auth->check('delete,contentadmin',$core->blog->id))
		{
			try {
				$meta = new dcMeta($core);
				while ($posts->fetch())
				{
					foreach ($_POST['meta_id'] as $v)
					{
						$meta->delPostMeta($posts->post_id,'postexpired',$v);
					}
				}
				http::redirect($redir);
			}
			catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
	}
	
	public static function content($core,$action,$hidden_fields)
	{
		if ($action == 'postexpired_add')
		{
			echo
			'<h2>'.__('Add expired date to entries').'</h2>'.
			'<form action="posts_actions.php" method="post">'.
			'<p><label>'.__('Expired date:').
			form::field('new_post_expired',16,16,'').
			'</label></p>'.
			'<p class="form-note">'.__('It will be added only if there is no expired date on entry.').'<p>'.
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'postexpired_add').
			'<input type="submit" value="'.__('save').'" /></p>'.
			'</form>';
		}
		elseif ($action == 'postexpired_remove')
		{
			$meta = new dcMeta($core);
			$dts = array();

			foreach ($_POST['entries'] as $id) {
				$rs = $meta->getMeta('postexpired',1,null,$id);
				if ($rs->isEmpty()) continue;
				
				if (isset($dts[$rs->meta_id])) {
					$dts[$rs->meta_id]++;
				} else {
					$dts[$rs->meta_id] = 1;
				}
			}

			echo '<h2>'.__('Remove selected expired date from entries').'</h2>';

			if (empty($dts)) {
				echo '<p>'.__('No expired date for selected entries').'</p>';
				return;
			}

			$posts_count = count($_POST['entries']);

			echo
			'<form action="posts_actions.php" method="post">'.
			'<fieldset><legend>'.__('Following expired date have been found in selected entries:').'</legend>';

			foreach ($dts as $k => $n) {
				$label = '<label class="classic">%s %s</label>';
				if ($posts_count == $n) {
					$label = sprintf($label,'%s','<strong>%s</strong>');
				}
				echo '<p>'.sprintf($label,
						form::checkbox(array('meta_id[]'),html::escapeHTML($k)),
						date('Y-m-d H:i',strtotime($k))
					).'</p>';
			}

			echo
			'<p><input type="submit" value="'.__('ok').'" /></p>'.
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'postexpired_remove').
			'</fieldset></form>';
		}
	}
}
?>