<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
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
	__('kUtRL'),
	'plugin.php?p=kUtRL','index.php?pf=kUtRL/icon.png',
	preg_match('/plugin.php\?p=kUtRL(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id)
);

# Admin behaviors
if ($core->blog->settings->kutrl_active)
{
	$core->addBehavior('adminPostFormSidebar',array('adminKutrl','adminPostFormSidebar'));
	$core->addBehavior('adminAfterPostUpdate',array('adminKutrl','adminAfterPostUpdate')); // update existing short url
	$core->addBehavior('adminAfterPostUpdate',array('adminKutrl','adminAfterPostCreate')); // create new short url
	$core->addBehavior('adminAfterPostCreate',array('adminKutrl','adminAfterPostCreate'));
	$core->addBehavior('adminBeforePostDelete',array('adminKutrl','adminBeforePostDelete'));
	$core->addBehavior('adminPostsActionsCombo',array('adminKutrl','adminPostsActionsCombo'));
	$core->addBehavior('adminPostsActions',array('adminKutrl','adminPostsActions'));
}

# Import/export
if ($core->blog->settings->kutrl_extend_importexport)
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
	public static function adminPostFormSidebar($post)
	{
		global $core;

		$_active = (boolean) $core->blog->settings->kutrl_active;
		$_admin = (string) $core->blog->settings->kutrl_admin_service;
		$_limit_to_blog = (boolean) $core->blog->settings->kutrl_limit_to_blog;

		if (!$_active || !$_admin) return;
		if (!isset($core->kutrlServices[$_admin])) return;

		try
		{
			$kut = new $core->kutrlServices[$_admin]($core,$_limit_to_blog);
		}
		catch (Exception $e)
		{
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
		'<h3 class="clear">'.__('Short link').'</h3>'.
		form::hidden(array('kutrl_old_post_url'),$post_url);

		if (!$rs)
		{
			echo 
			'<p><label class="classic">'.form::checkbox('kutrl_create',1,!empty($_POST['kutrl_create']),'',3).' '.
			__('Create short link').'</label></p>';
			
			if ($kut->allow_customized_hash)
			{
				echo 
				'<p class="classic">'.
				'<label for="custom">'.__('Custom short link:').'</label>'.
				form::field('kutrl_create_custom',32,32,'').
				'</p>';
			}
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
			'<p><label class="classic">'.form::checkbox('kutrl_delete',1,!empty($_POST['kutrl_delete']),'',3).' '.
			__('delete short link').'</label></p>'.
			'<p><a href="'.$href.'" '.'title="'.$title.'">'.$href.'</a></p>';
		}
	}

	public static function adminAfterPostUpdate($cur,$post_id)
	{
		global $core;

		# Create: see adminAfterPostCreate
		if (!empty($_POST['kutrl_create'])) return;

		$_active = (boolean) $core->blog->settings->kutrl_active;
		$_admin = (string) $core->blog->settings->kutrl_admin_service;
		$_limit_to_blog = (boolean) $core->blog->settings->kutrl_limit_to_blog;

		if (!$_active || !$_admin) return;
		if (!isset($core->kutrlServices[$_admin])) return;

		try
		{
			$kut = new $core->kutrlServices[$_admin]($core,$_limit_to_blog);
		}
		catch (Exception $e)
		{
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

			$kut->hash($new_post_url,$custom); // better to update (not yet implemented)
		}
	}

	public static function adminAfterPostCreate($cur,$post_id)
	{
		global $core;

		if (empty($_POST['kutrl_create'])) return;

		$_active = (boolean) $core->blog->settings->kutrl_active;
		$_admin = (string) $core->blog->settings->kutrl_admin_service;
		$_limit_to_blog = (boolean) $core->blog->settings->kutrl_limit_to_blog;

		if (!$_active || !$_admin) return;
		if (!isset($core->kutrlServices[$_admin])) return;

		try
		{
			$kut = new $core->kutrlServices[$_admin]($core,$_limit_to_blog);
		}
		catch (Exception $e)
		{
			return;
		}

		$rs = $core->blog->getPosts(array('post_id'=>$post_id));

		if ($rs->isEmpty()) return;

		$custom = !empty($_POST['kutrl_create_custom']) && $kut->allow_customized_hash ?
			$_POST['kutrl_create_custom'] : null;

		$kut->hash($rs->getURL(),$custom);
	}

	public static function adminBeforePostDelete($post_id)
	{
		global $core;

		$_active = (boolean) $core->blog->settings->kutrl_active;
		$_admin = (string) $core->blog->settings->kutrl_admin_service;
		$_limit_to_blog = (boolean) $core->blog->settings->kutrl_limit_to_blog;

		if (!$_active || !$_admin) return;
		if (!isset($core->kutrlServices[$_admin])) return;

		try
		{
			$kut = new $core->kutrlServices[$_admin]($core,$_limit_to_blog);
		}
		catch (Exception $e)
		{
			return;
		}

		$rs = $core->blog->getPosts(array('post_id'=>$post_id));

		if ($rs->isEmpty()) return;

		$kut->remove($rs->getURL());
	}

	public static function adminPostsActionsCombo($args)
	{
		global $core;

		$_active = (boolean) $core->blog->settings->kutrl_active;
		$_admin = (string) $core->blog->settings->kutrl_admin_service;
		$_auth_check = $core->auth->check('admin',$core->blog->id);

		if (!$_active || !$_admin || !$_auth_check) return;
		if (!isset($core->kutrlServices[$_admin])) return;

		$args[0][__('create short link')] = 'kutrl_create';
		$args[0][__('delete short link')] = 'kutrl_delete';
	}

	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		if ($action != 'kutrl_create' 
		 && $action != 'kutrl_delete') return;


		$_active = (boolean) $core->blog->settings->kutrl_active;
		$_admin = (string) $core->blog->settings->kutrl_admin_service;
		$_limit_to_blog = (boolean) $core->blog->settings->kutrl_limit_to_blog;

		if (!$_active || !$_admin) return;
		if (!isset($core->kutrlServices[$_admin])) return;

		try
		{
			$kut = new $core->kutrlServices[$_admin]($core,$_limit_to_blog);
		}
		catch (Exception $e)
		{
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