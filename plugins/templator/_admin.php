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
	$core->addBehavior('adminPostFormSidebar',array('dcTemplatorBehaviors','adminPostFormSidebar'));
	$core->addBehavior('adminPageFormSidebar',array('dcTemplatorBehaviors','adminPageFormSidebar'));

	$core->addBehavior('adminAfterPostCreate',array('dcTemplatorBehaviors','adminBeforePostUpdate'));
	$core->addBehavior('adminBeforePostUpdate',array('dcTemplatorBehaviors','adminBeforePostUpdate'));
	$core->addBehavior('adminAfterPageCreate',array('dcTemplatorBehaviors','adminBeforePostUpdate'));
	$core->addBehavior('adminBeforePageUpdate',array('dcTemplatorBehaviors','adminBeforePostUpdate'));
}
//$t = new dcTemplator($core);
//$core->media->addExclusion($t->publicTemplatorFilesPath());

class dcTemplatorBehaviors
{
	public static function adminPostFormSidebar($post)
	{
		global $core;

		$meta = new dcMeta($core);

		$setting = unserialize($core->blog->settings->templator_files);
		$tpl = array('' => '');
		$tpl_post = array();
		
		foreach ($setting as $k => $v) {
			if (($v['type'] == 'post') && ($v['used'] == true))
			{
				$tpl_post= array_merge($tpl_post, array($v['title']=> $k));
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

		$setting = unserialize($core->blog->settings->templator_files);
		$tpl = array('' => '');
		$tpl_post = array();
		
		foreach ($setting as $k => $v) {
			if (($v['type'] == 'page') && ($v['used'] == true))
			{
				$tpl_post= array_merge($tpl_post, array($v['title']=> $k));
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
		
		if (isset($_POST['post_tpl']) && !empty($_POST['post_tpl'])) {
			$tpl = $_POST['post_tpl'];
			
			$meta = new dcMeta($core);
			
			$meta->delPostMeta($post_id,'template');
			$meta->setPostMeta($post_id,'template',$tpl);
		}
	}
}
?>
