<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of templator a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(__('More templates'),
		'plugin.php?p=templator','index.php?pf=templator/icon.png',
		preg_match('/plugin.php\?p=templator(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));

if ($core->blog->settings->templator_flag)
{
	$core->addBehavior('adminPostFormSidebar',array('templatorBehaviors','adminPostFormSidebar'));
	$core->addBehavior('adminPageFormSidebar',array('templatorBehaviors','adminPageFormSidebar'));

	$core->addBehavior('adminAfterPostCreate',array('templatorBehaviors','adminBeforePostUpdate'));
	$core->addBehavior('adminBeforePostUpdate',array('templatorBehaviors','adminBeforePostUpdate'));
	$core->addBehavior('adminAfterPageCreate',array('templatorBehaviors','adminBeforePostUpdate'));
	$core->addBehavior('adminBeforePageUpdate',array('templatorBehaviors','adminBeforePostUpdate'));

	$core->addBehavior('adminPostsActionsCombo',array('templatorBehaviors','adminPostsActionsCombo'));
	$core->addBehavior('adminPostsActions',array('templatorBehaviors','adminPostsActions'));
	$core->addBehavior('adminPostsActionsContent',array('templatorBehaviors','adminPostsActionsContent'));
}

class templatorBehaviors
{
	public static function adminPostFormSidebar($post)
	{
		global $core;

		$meta = new dcMeta($core);

		$setting = unserialize($core->blog->settings->templator_files_active);
		$ressources = unserialize($core->blog->settings->templator_files);
		$tpl = array('&nbsp;' => '');
		$tpl_post = array();
		
		foreach ($setting as $k => $v) {
			if (($ressources[$k]['type'] == 'post') && ($v['used'] == true))
			{
				$tpl_post= array_merge($tpl_post, array($ressources[$k]['title']=> $k));
			}
		}

		if (!empty($tpl_post))
		{
			$tpl  = array_merge($tpl,$tpl_post);
			if ($post)
			{
				$post_meta = $meta->getMeta('template',null,null,$post->post_id);
				$selected = $post_meta->isEmpty()? '' : $post_meta->meta_id  ;
			}
			
			echo
			'<h3><label for="post_tpl">'.__('Specific template:').'</label></h3>'.
			'<div class="p" id="meta-edit-tpl">'.form::combo('post_tpl',$tpl,$selected).'</div>';
		}
	}

	public static function adminPageFormSidebar($post)
	{
		global $core;

		$meta = new dcMeta($core);

		$setting = unserialize($core->blog->settings->templator_files_active);
		$ressources = unserialize($core->blog->settings->templator_files);
		$tpl = array('' => '');
		$tpl_post = array();
		
		foreach ($setting as $k => $v) {
			if (($ressources[$k]['type'] == 'page') && ($v['used'] == true))
			{
				$tpl_post= array_merge($tpl_post, array($ressources[$k]['title']=> $k));
			}
		}

		if (!empty($tpl_post))
		{
			$tpl  = array_merge($tpl,$tpl_post);
			if ($post)
			{
				$post_meta = $meta->getMeta('template',null,null,$post->post_id);
				$selected = $post_meta->isEmpty()? '' : $post_meta->meta_id  ;
			}
			
			echo
			'<h3><label for="post_tpl">'.__('Specific template:').'</label></h3>'.
			'<div class="p" id="meta-edit-tpl">'.form::combo('post_tpl',$tpl,$selected).'</div>';
		}
	}

	public static function adminBeforePostUpdate($cur,$post_id)
	{
		global $core;

		$post_id = (integer) $post_id;
		
		if (isset($_POST['post_tpl'])) {
			$tpl = $_POST['post_tpl'];
			
			$meta = new dcMeta($core);
			
			$meta->delPostMeta($post_id,'template');
			if (!empty($_POST['post_tpl']))
			{
				$meta->setPostMeta($post_id,'template',$tpl);
			}
		}
	}

	public static function adminPostsActionsCombo($args)
	{
		$args[0][__('Appearance')] = array(__('Select template') => 'tpl');
	}
	
	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		if ($action == 'tpl' && isset($_POST['post_tpl']))
		{
			try
			{
				$meta = new dcMeta($core);
				$tpl = $_POST['post_tpl'];
				
				while ($posts->fetch())
				{
					$meta->delPostMeta($posts->post_id,'template');
					if (!empty($_POST['post_tpl']))
					{
						$meta->setPostMeta($posts->post_id,'template',$tpl);
					}
				}
				
				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
	}
	
	public static function adminPostsActionsContent($core,$action,$hidden_fields)
	{
		if ($action == 'tpl')
		{
			$meta = new dcMeta($core);

			$setting = unserialize($core->blog->settings->templator_files_active);
			$ressources = unserialize($core->blog->settings->templator_files);
			$tpl = array('&nbsp;' => '');
			$tpl_post = array();
		
			foreach ($setting as $k => $v) {
				if (($ressources[$k]['type'] == 'post') && ($v['used'] == true))
				{
					$tpl_post= array_merge($tpl_post, array($ressources[$k]['title']=> $k));
				}
			}

			if (!empty($tpl_post))
			{
				$tpl  = array_merge($tpl,$tpl_post);
			
				echo
				'<h2>'.__('Select template for these entries').'</h2>'.
				'<form action="posts_actions.php" method="post">'.
				'<p><label class="classic">'.__('Choose template:').' '.
				form::combo('post_tpl',$tpl).
				'</label> '.
			
				$hidden_fields.
				$core->formNonce().
				form::hidden(array('action'),'tpl').
				'<input type="submit" value="'.__('save').'" /></p>'.
				'</form>';
			}
		}
	}
}
?>
