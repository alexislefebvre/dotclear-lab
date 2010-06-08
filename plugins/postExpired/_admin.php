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
	public static function categoriesCombo()
	{
		# Getting categories
		$categories_combo = array(
			__('not changed') => '',
			__('uncategorized') => '.'
		);
		try {
			$categories = $GLOBALS['core']->blog->getCategories(array('post_type'=>'post'));
			while ($categories->fetch())
			{
				$categories_combo[] = new formSelectOption(
					str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.html::escapeHTML($categories->cat_title),
					'.'.$categories->cat_id
				);
			}
		}
		catch (Exception $e) { }
		return $categories_combo;
	}
	
	public static function statusCombo()
	{
		return array(
			__('not changed') => '',
			__('pending') => '.-2',
			__('unpublished') => '.0'
		);
	}
	
	public static function selectedCombo()
	{
		return array(
			__('not changed') => '',
			__('selected') => '.1',
			__('not selected') => '.0'
		);
	}
	
	public static function header($posts_actions=true)
	{
		return ($posts_actions ? dcPage::jsDatePicker() : '').
		dcPage::jsLoad('index.php?pf=postExpired/js/postexpired.js');
	}
	
	public static function form($post)
	{
		global $core;
		$expired_date = $expired_status = $expired_cat = $expired_selected = '';

		if ($post)
		{
			$rs_date = $core->meta->getMetadata(array('meta_type'=>'postexpired','limit'=>1,'post_id'=>$post->post_id));
			if (!$rs_date->isEmpty())
			{
				$expired_date = date('Y-m-d H:i',strtotime($rs_date->meta_id));
				
				$rs_status = $core->meta->getMetadata(array('meta_type'=>'postexpiredstatus','limit'=>1,'post_id'=>$post->post_id));
				$expired_status = $rs_status->isEmpty() ? '' : (string) $rs_status->meta_id;
				
				$rs_cat = $core->meta->getMetadata(array('meta_type'=>'postexpiredcat','limit'=>1,'post_id'=>$post->post_id));
				$expired_cat = $rs_cat->isEmpty() ? '' : (string) $rs_cat->meta_id;
				
				$rs_selected = $core->meta->getMetadata(array('meta_type'=>'postexpiredselected','limit'=>1,'post_id'=>$post->post_id));
				$expired_selected = $rs_selected->isEmpty() ? '' : (string) $rs_selected->meta_id;
			}
		}
		
		echo 
		'<h3 id="postexpired-form-title">'.__('Expired date').'</h3>'.
		'<div id="postexpired-form-content">'.
		'<p><label>'.__('Date:').
		form::field('post_expired_date',16,16,$expired_date,'',3).
		'</label></p>'.
		'<p><label>'.__('Change status on expire:').
		form::combo('post_expired_status',self::statusCombo(),$expired_status,'',3).
		'</label></p>'.
		'<p><label>'.__('Change category on expire:').
		form::combo('post_expired_cat',self::categoriesCombo(),$expired_cat,'',3).
		'</label></p>'.
		'<p><label>'.__('Change selection on expire:').
		form::combo('post_expired_selected',self::selectedCombo(),$expired_selected,'',3).
		'</label></p>';
		
		# --BEHAVIOR-- adminPostExpiredFormSidebar
		$core->callbehavior('adminPostExpiredFormSidebar',$post);
		
		echo '</div>';
	}
	
	public static function set(&$cur,&$post_id)
	{
		global $core;
		if (!isset($_POST['post_expired_date'])) return;
		
		$post_id = (integer) $post_id;
		
		# --BEHAVIOR-- adminBeforePostExpiredSave
		$core->callBehavior('adminBeforePostExpiredSave',$cur,$post_id);
		
		self::del($post_id);
		
		if (!empty($_POST['post_expired_date']) 
		 && (!empty($_POST['post_expired_status']) 
		  || !empty($_POST['post_expired_cat']) 
		  || !empty($_POST['post_expired_selected'])))
		{
			$post_expired_date = date('Y-m-d H:i:00',strtotime($_POST['post_expired_date']));
			$core->meta->setPostMeta($post_id,'postexpired',$post_expired_date);
			
			if (!empty($_POST['post_expired_status']))
			{
				$core->meta->setPostMeta($post_id,'postexpiredstatus',(string) $_POST['post_expired_status']);
			}
			if (!empty($_POST['post_expired_selected']))
			{
				$core->meta->setPostMeta($post_id,'postexpiredcat',(string) $_POST['post_expired_cat']);
			}
			if (!empty($_POST['post_expired_selected']))
			{
				$core->meta->setPostMeta($post_id,'postexpiredselected',(string) $_POST['post_expired_selected']);
			}
		}
		
		# --BEHAVIOR-- adminAfterPostExpiredSave
		$core->callBehavior('adminAfterPostExpiredSave',$cur,$post_id);
	}
	
	public static function del($post_id)
	{
		global $core;
		
		$post_id = (integer) $post_id;
		
		# --BEHAVIOR-- adminBeforePostExpiredDelete
		$core->callBehavior('adminBeforePostExpiredDelete',$post_id);
		
		$core->meta->delPostMeta($post_id,'postexpired');
		$core->meta->delPostMeta($post_id,'postexpiredstatus');
		$core->meta->delPostMeta($post_id,'postexpiredcat');
		$core->meta->delPostMeta($post_id,'postexpiredselected');
	}
	
	public static function combo(&$args)
	{
		if ($GLOBALS['core']->auth->check('usage,contentadmin',$GLOBALS['core']->blog->id))
		{
			$args[0][__('Expired entries')][__('add expired date')] = 'postexpired_add';
		}
		if ($GLOBALS['core']->auth->check('delete,contentadmin',$GLOBALS['core']->blog->id))
		{
			$args[0][__('Expired entries')][__('remove expired date')] = 'postexpired_remove';
		}
	}
	
	public static function action(&$core,$posts,$action,$redir)
	{
		if ($action == 'action_postexpired_add')
		{
			# --BEHAVIOR-- adminPostExpiredActions
			$core->callBehavior('adminPostExpiredActions',$core,$posts,$action,$redir);
			
			if (!$core->auth->check('usage,contentadmin',$core->blog->id) 
			 || empty($_POST['new_post_expired_date']) 
			 || (empty($_POST['new_post_expired_status']) 
			  && empty($_POST['new_post_expired_cat']) 
			  && empty($_POST['new_post_expired_selected'])))
			{
				http::redirect($redir);
			}
			
			try
			{
				$new_post_expired_date = date('Y-m-d H:i:00',strtotime($_POST['new_post_expired_date']));
				
				while ($posts->fetch())
				{
					$rs = $core->meta->getMetadata(array('meta_type'=>'postexpired','limit'=>1,'post_id'=>$posts->post_id));
					if ($rs->isEmpty())
					{
						$core->meta->setPostMeta($posts->post_id,'postexpired',$new_post_expired_date);

						if (!empty($_POST['new_post_expired_status']))
						{
							$core->meta->setPostMeta($posts->post_id,'postexpiredstatus',$_POST['new_post_expired_status']);
						}
						if (!empty($_POST['new_post_expired_cat']))
						{
							$core->meta->setPostMeta($posts->post_id,'postexpiredcat',$_POST['new_post_expired_cat']);
						}
						if (!empty($_POST['new_post_expired_selected']))
						{
							$core->meta->setPostMeta($posts->post_id,'postexpiredselected',$_POST['new_post_expired_selected']);
						}
					}
				}
				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		elseif ($action == 'action_postexpired_remove')
		{
			if (empty($_POST['rmv_post_expired']) 
			 || !$core->auth->check('delete,contentadmin',$core->blog->id))
			{
				http::redirect($redir);
			}

			try
			{
				$posts_ids = array();
				while($posts->fetch())
				{
					$posts_ids[] = $posts->id;
				}
				
				$rs_params['no_content'] = true;
				$rs_params['post_id'] = $posts_ids;
				$rs_params['meta_id'] = 'postexpired';
				$rs = $core->meta->getPostsByMeta($rs_params);
				
				while ($rs->fetch())
				{
					self::del($rs->post_id);
				}
				
				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
	}
	
	public static function content($core,$action,$hidden_fields)
	{
		if ($action == 'postexpired_add')
		{
			echo self::header().
			'<h2>'.__('Add expired date to entries').'</h2>'.
			'<p>'.__('It will be added only if there is no expired date on entry.').'<p>'.
			'<form action="posts_actions.php" method="post">'.
			'<p><label>'.__('Date:').
			form::field('new_post_expired_date',16,16,'','',2).
			'</label></p>'.
			'<p><label>'.__('Change status on expire:').
			form::combo('new_post_expired_status',self::statusCombo(),'','',2).
			'</label></p>'.
			'<p><label>'.__('Change category on expire:').
			form::combo('new_post_expired_cat',self::categoriesCombo(),'','',2).
			'</label></p>'.
			'<p><label>'.__('Change selection on expire:').
			form::combo('new_post_expired_selected',self::selectedCombo(),'','',2).
			'</label></p><p>';
			
			# --BEHAVIOR-- adminPostExpiredActionsContent
			$core->callBehavior('adminPostExpiredActionsContent',$core,$action,$hidden_fields);
			
			echo 
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'action_postexpired_add').
			'<input type="submit" value="'.__('save').'" /></p>'.
			'</form>';
		}
		elseif ($action == 'postexpired_remove')
		{
			$dts = array();

			foreach ($_POST['entries'] as $id)
			{
				$rs = $core->meta->getMetadata(array('meta_type'=>'postexpired','limit'=>1,'post_id'=>$id));
				if ($rs->isEmpty()) continue;
				
				if (isset($dts[$rs->meta_id]))
				{
					$dts[$rs->meta_id]++;
				}
				else
				{
					$dts[$rs->meta_id] = 1;
				}
			}
			
			echo '<h2>'.__('Remove selected expired date from entries').'</h2>';
			
			if (empty($dts))
			{
				echo '<p>'.__('No expired date for selected entries').'</p>';
				return;
			}
			
			$posts_count = count($_POST['entries']);
			
			echo
			'<form action="posts_actions.php" method="post">'.
			'<fieldset><legend>'.__('Following expired date have been found in selected entries:').'</legend>';
			
			foreach ($dts as $k => $n)
			{
				$label = '<label class="classic">%s %s</label>';
				if ($posts_count == $n)
				{
					$label = sprintf($label,'%s','<strong>%s</strong>');
				}
				echo '<p>'.sprintf($label,
					form::checkbox(array('rmv_post_expired[]'),html::escapeHTML($k)),
					date('Y-m-d H:i',strtotime($k))
				).'</p>';
			}
			
			echo
			'<p><input type="submit" value="'.__('ok').'" /></p>'.
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'action_postexpired_remove').
			'</fieldset></form>';
		}
	}
}
?>