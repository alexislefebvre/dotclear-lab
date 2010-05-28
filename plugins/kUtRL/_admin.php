<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

require_once dirname(__FILE__).'/_widgets.php';

# Plugin menu
$_menu['Plugins']->addItem(
	__('Links shortener'),
	'plugin.php?p=kUtRL','index.php?pf=kUtRL/icon.png',
	preg_match('/plugin.php\?p=kUtRL(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id)
);

$s = kutrlSettings($core);

# Admin behaviors
if ($s->kutrl_active)
{
	$core->addBehavior('adminPostHeaders',array('adminKutrl','adminPostHeaders'));
	$core->addBehavior('adminPostFormSidebar',array('adminKutrl','adminPostFormSidebar'));
	$core->addBehavior('adminAfterPostUpdate',array('adminKutrl','adminAfterPostUpdate')); // update existing short url
	$core->addBehavior('adminAfterPostUpdate',array('adminKutrl','adminAfterPostCreate')); // create new short url
	$core->addBehavior('adminAfterPostCreate',array('adminKutrl','adminAfterPostCreate'));
	$core->addBehavior('adminBeforePostDelete',array('adminKutrl','adminBeforePostDelete'));
	$core->addBehavior('adminPostsActionsCombo',array('adminKutrl','adminPostsActionsCombo'));
	$core->addBehavior('adminPostsActions',array('adminKutrl','adminPostsActions'));
}

# Import/export
if ($s->kutrl_extend_importexport)
{
	$core->addBehavior('exportFull',array('backupKutrl','exportFull'));
	$core->addBehavior('exportSingle',array('backupKutrl','exportSingle'));
	$core->addBehavior('importInit',array('backupKutrl','importInit'));
	$core->addBehavior('importSingle',array('backupKutrl','importSingle'));
	$core->addBehavior('importFull',array('backupKutrl','importFull'));
}

# Admin behaviors class
class adminKutrl
{
	public static function adminPostHeaders()
	{
		return dcPage::jsLoad('index.php?pf=kUtRL/js/admin.js');
	}

	public static function adminPostFormSidebar($post)
	{
		global $core;
		$s = kutrlSettings($core);

		if (!$s->kutrl_active || !$s->kutrl_admin_service
		 || !isset($core->kutrlServices[$s->kutrl_admin_service])) return;

		try {
			$kut = new $core->kutrlServices[$s->kutrl_admin_service]($core,$s->kutrl_limit_to_blog);
		}
		catch (Exception $e) {
			return;
		}

		if ($post)
		{
			$post_url = $post->getURL();
			$rs = $kut->isKnowUrl($post_url);
		}
		else
		{
			$post_url = '';
			$rs = false;
		}

		echo 
		'<h3 id="kutrl-form-title" class="clear">'.__('Short link').'</h3>'.
		'<div id="kutrl-form-content">'.
		form::hidden(array('kutrl_old_post_url'),$post_url);

		if (!$rs)
		{
			if (empty($_POST['kutrl_old_post_url']) && $s->kutrl_admin_entry_default)
			{
				$chk = true;
			}
			else
			{
				$chk = !empty($_POST['kutrl_create']);
			}
			echo 
			'<p><label class="classic">'.
			form::checkbox('kutrl_create',1,$chk,'',3).' '.
			__('Create short link').'</label></p>';
			
			if ($kut->allow_customized_hash)
			{
				echo 
				'<p class="classic">'.
				'<label for="custom">'.__('Custom short link:').' '.
				form::field('kutrl_create_custom',32,32,'',3).
				'</label></p>';
			}
			echo
			'<p><label class="classic">'.
			form::checkbox('kutrl_twit_send',1,0,'',3).' '.
			__('Send to Twitter status').'</label></p>';
		}
		else
		{
			$count = $rs->counter;
			if ($count == 0)
			{
				$title = __('never followed');
			}
			elseif ($count == 1)
			{
				$title = __('followed one time');
			}
			else
			{
				$title = sprintf(__('followed %s times'),$count);
			}
			$href = $kut->url_base.$rs->hash;

			echo 
			'<p><label class="classic">'.
			form::checkbox('kutrl_delete',1,!empty($_POST['kutrl_delete']),'',3).' '.
			__('delete short link').'</label></p>'.
			'<p><a href="'.$href.'" '.'title="'.$title.'">'.$href.'</a></p>';
		}
		echo '</div>';
	}

	public static function adminAfterPostUpdate($cur,$post_id)
	{
		global $core;
		$s = kutrlSettings($core);

		# Create: see adminAfterPostCreate
		if (!empty($_POST['kutrl_create'])
		 || !$s->kutrl_active || !$s->kutrl_admin_service 
		 || !isset($core->kutrlServices[$s->kutrl_admin_service])) return;

		try {
			$kut = new $core->kutrlServices[$s->kutrl_admin_service]($core,$s->kutrl_limit_to_blog);
		}
		catch (Exception $e) {
			return;
		}

		if (empty($_POST['kutrl_old_post_url'])) return;

		$old_post_url = $_POST['kutrl_old_post_url'];

		if (!($rs = $kut->isKnowUrl($old_post_url))) return;

		$rs = $core->blog->getPosts(array('post_id'=>$post_id));

		if ($rs->isEmpty()) return;

		$new_post_url = $rs->getURL();

		# Delete
		if (!empty($_POST['kutrl_delete']))
		{
			$kut->remove($old_post_url);
		}
		# Update
		else
		{
			if ($old_post_url == $new_post_url) return;

			$kut->remove($old_post_url);

			$rs = $kut->hash($new_post_url,$custom); // better to update (not yet implemented)
			$url = $kut->url_base.$rs->hash;

			# Send new url by libDcTwitter
			if (!empty($rs) && !empty($_POST['kutrl_twit_send'])) {
				$twit = kutrlLibDcTwitter::getMessage('kUtRL');
				$twit = str_replace(
					array('%L','%B','%U'),
					array($url,$core->blog->name,$core->auth->getInfo('user_cn'))
					,$twit
				);
				kutrlLibDcTwitter::sendMessage('kUtRL',$twit);
			}
		}
	}

	public static function adminAfterPostCreate($cur,$post_id)
	{
		global $core;
		$s = kutrlSettings($core);

		if (empty($_POST['kutrl_create']) 
		 || !$s->kutrl_active || !$s->kutrl_admin_service 
		 || !isset($core->kutrlServices[$s->kutrl_admin_service])) return;

		try {
			$kut = new $core->kutrlServices[$s->kutrl_admin_service]($core,$s->kutrl_limit_to_blog);
		}
		catch (Exception $e) {
			return;
		}

		$rs = $core->blog->getPosts(array('post_id'=>$post_id));

		if ($rs->isEmpty()) return;

		$custom = !empty($_POST['kutrl_create_custom']) && $kut->allow_customized_hash ?
			$_POST['kutrl_create_custom'] : null;

		$rs = $kut->hash($rs->getURL(),$custom);
		$kut->url_base.$rs->hash;

		# Send new url by libDcTwitter
		if (!empty($rs) && !empty($_POST['kutrl_twit_send'])) {
			$twit = kutrlLibDcTwitter::getMessage('kUtRL');
			$twit = str_replace(
				array('%L','%B','%U'),
				array($url,$core->blog->name,$core->auth->getInfo('user_cn'))
				,$twit
			);
			kutrlLibDcTwitter::sendMessage('kUtRL',$twit);
		}
	}

	public static function adminBeforePostDelete($post_id)
	{
		global $core;
		$s = kutrlSettings($core);

		if (!$s->kutrl_active || !$s->kutrl_admin_service 
		 || !isset($core->kutrlServices[$s->kutrl_admin_service])) return;

		try {
			$kut = new $core->kutrlServices[$s->kutrl_admin_service]($core,$s->kutrl_limit_to_blog);
		}
		catch (Exception $e) {
			return;
		}

		$rs = $core->blog->getPosts(array('post_id'=>$post_id));

		if ($rs->isEmpty()) return;

		$kut->remove($rs->getURL());
	}

	public static function adminPostsActionsCombo($args)
	{
		global $core;
		$s = kutrlSettings($core);

		if (!$s->kutrl_active || !$s->kutrl_admin_service
		 || !$core->auth->check('admin',$core->blog->id) 
		 || !isset($core->kutrlServices[$s->kutrl_admin_service])) return;

		$args[0][__('kUtRL')][__('create short link')] = 'kutrl_create';
		$args[0][__('kUtRL')][__('delete short link')] = 'kutrl_delete';
	}

	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		if ($action != 'kutrl_create' 
		 && $action != 'kutrl_delete') return;

		$s = kutrlSettings($core);

		if (!$s->kutrl_active || !$s->kutrl_admin_service 
		 || !isset($core->kutrlServices[$s->kutrl_admin_service])) return;

		try {
			$kut = new $core->kutrlServices[$s->kutrl_admin_service]($core,$s->kutrl_limit_to_blog);
		}
		catch (Exception $e) {
			return;
		}

		while ($posts->fetch())
		{
			$url = $posts->getURL();

			if ($action == 'kutrl_create')
			{
				$kut->hash($url);
			}

			if ($action == 'kutrl_delete')
			{
				$kut->remove($url);
			}
		}
		$core->blog->triggerBlog();
		http::redirect($redir.'&done=1');
	}
}

# Import/export behaviors for Import/export plugin
class backupKutrl
{
	public static function exportSingle($core,$exp,$blog_id)
	{
		$exp->export('kutrl',
			'SELECT kut_id, blog_id, kut_service, kut_type, '.
			'kut_hash, kut_url, kut_dt, kut_password, kut_counter '.
			'FROM '.$core->prefix.'kutrl '.
			"WHERE blog_id = '".$blog_id."' "
		);
	}

	public static function exportFull($core,$exp)
	{
		$exp->exportTable('kutrl');
	}

	public static function importInit($bk,$core)
	{
		$bk->cur_kutrl = $core->con->openCursor($core->prefix.'kutrl');
		$bk->kutrl = new kutrlLog($core);
	}

	public static function importSingle($line,$bk,$core)
	{
		if ($line->__name == 'kutrl')
		{
			# Do nothing if str/type exists !
			if (false === $bk->kutrl->select($line->kut_url,$line->kut_hash,$line->kut_type,$line->kut_service))
			{
				$bk->kutrl->insert($line->kut_url,$line->kut_hash,$line->kut_type,$line->kut_service);
			}
		}
	}

	public static function importFull($line,$bk,$core)
	{
		if ($line->__name == 'kutrl')
		{
			$bk->cur_kutrl->clean();

			$bk->cur_kutrl->kut_id = (integer) $line->kut_id;
			$bk->cur_kutrl->blog_id = (string) $line->blog_id;
			$bk->cur_kutrl->kut_service = (string) $line->kut_service;
			$bk->cur_kutrl->kut_type = (string) $line->kut_type;
			$bk->cur_kutrl->kut_hash = (string) $line->kut_hash;
			$bk->cur_kutrl->kut_url = (string) $line->kut_url;
			$bk->cur_kutrl->kut_dt = (string) $line->miniurl_dt;
			$bk->cur_kutrl->kut_counter = (integer) $line->kut_counter;
			$bk->cur_kutrl->kut_password = (string) $line->kut_password;

			$bk->cur_kutrl->insert();
		}
	}
}
?>