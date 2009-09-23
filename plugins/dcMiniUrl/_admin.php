<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcMiniUrl, a plugin for Dotclear 2.
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

if ($core->blog->settings->miniurl_active === null) return;

# Plugin menu
$_menu['Plugins']->addItem(
	__('Links shortener'),
	'plugin.php?p=dcMiniUrl','index.php?pf=dcMiniUrl/icon.png',
	preg_match('/plugin.php\?p=dcMiniUrl(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));

# Admin behaviors
$core->addBehavior('adminPostFormSidebar',array('adminMiniUrl','adminPostFormSidebar'));
$core->addBehavior('adminAfterPostUpdate',array('adminMiniUrl','adminAfterPostUpdate')); // update existing miniurl
$core->addBehavior('adminAfterPostUpdate',array('adminMiniUrl','adminAfterPostCreate')); // create new miniurl
$core->addBehavior('adminAfterPostCreate',array('adminMiniUrl','adminAfterPostCreate'));
$core->addBehavior('adminBeforePostDelete',array('adminMiniUrl','adminBeforePostDelete'));
$core->addBehavior('adminPostsActionsCombo',array('adminMiniUrl','adminPostsActionsCombo'));
$core->addBehavior('adminPostsActions',array('adminMiniUrl','adminPostsActions'));

# Import/export
if ($core->blog->settings->miniurl_importexport_active) {

	$core->addBehavior('exportFull',array('backupMiniUrl','exportFull'));
	$core->addBehavior('exportSingle',array('backupMiniUrl','exportSingle'));
	$core->addBehavior('importInit',array('backupMiniUrl','importInit'));
	$core->addBehavior('importSingle',array('backupMiniUrl','importSingle'));
	$core->addBehavior('importFull',array('backupMiniUrl','importFull'));
}

# Admin behaviors class
class adminMiniUrl
{
	public static function adminPostFormSidebar($post)
	{
		global $core;
		$O = new dcMiniUrl($core);

		$id = -1;
		$post_url = '';
		if ($post) {
			$post_url = $core->blog->url.$core->url->getBase('post').'/'.$post->post_url;

			$type = 'customurl';
			if (-1 == ($id = $O->id($type,$post_url))) {
				$type = 'miniurl';
				$id = $O->id($type,$post_url);
			}
		}

		echo 
		'<h3 class="clear">'.__('Short link').'</h3>'.
		form::hidden(array('mini_old_post_url'),$post_url);

		if (-1 == $id) {
			echo 
			'<p><label class="classic">'.form::checkbox('miniurl_create',1,!empty($_POST['miniurl_create']),'',3).' '.
			__('Create short link').'</label></p>';
		} else {
			$count = $O->counter($type,$id);
			if ($count == 0)
				$follow = __('never followed');
			elseif ($count == 1)
				$follow = __('followed one time');
			else
				$follow = sprintf(__('followed %s times'),$count);

			echo 
			'<p><a href="'.$core->blog->url.$core->url->getBase('miniUrl').'/'.$id.
			'" '.'title="'.$follow.'">'.
			$core->blog->url.$core->url->getBase('miniUrl').'/'.$id.'</a></p>';
		}
	}

	public static function adminAfterPostUpdate($cur,$post_id)
	{
		global $core;
		$O = new dcMiniUrl($core);

		# Create: see adminAfterPostCreate
		if (!empty($_POST['miniurl_create'])) return;

		# Update
		if (!empty($_POST['miniurl_old_post_url'])) {
			$old_post_url = $_POST['miniurl_old_post_url'];

			$type = 'customurl';
			if (-1 == ($id = $O->id($type,$old_post_url))) {
				$type = 'miniurl';
				if (-1 == ($id = $O->id($type,$old_post_url)))
					return;
			}

			$rs = $core->con->select(
				'SELECT post_url FROM '.$core->prefix.'post '.
				"WHERE post_id='".$post_id."' ".$core->con->limit(1));

			if ($rs->isEmpty())	return;

			$new_post_url = $core->blog->url.$core->url->getBase('post').'/'.$rs->post_url;

			if ($old_post_url == $new_post_url) return;

			$O->update($type,$id,$type,$id,$new_post_url);
		}
	}

	public static function adminAfterPostCreate($cur,$post_id)
	{
		global $core;
		$O = new dcMiniUrl($core);

		if (empty($_POST['miniurl_create'])) return;

		$rs = $core->con->select(
			'SELECT post_url FROM '.$core->prefix.'post '.
			"WHERE post_id='".$post_id."' ".$core->con->limit(1));

		if ($rs->isEmpty())	return;

		$new_post_url = $core->blog->url.$core->url->getBase('post').'/'.$rs->post_url;

		$O->create('miniurl',$new_post_url);
	}

	public static function adminBeforePostDelete($post_id)
	{
		global $core;
		$O = new dcMiniUrl($core);

		$rs = $core->con->select(
			'SELECT post_url FROM '.$core->prefix.'post '.
			"WHERE post_id='".$post_id."' ".$core->con->limit(1));

		if ($rs->isEmpty()) return;

		$post_url = $core->blog->url.$core->url->getBase('post').'/'.$rs->post_url;

		$type = 'customurl';
		if (-1 == ($id = $O->id($type,$post_url))) {
			$type = 'miniurl';
			if (-1 == ($id = $O->id($type,$post_url)))
				return;
		}

		$O->delete($type,$id);
	}
//?&
	public static function adminPostsActionsCombo($args)
	{
		global $core;

		if ($core->blog->settings->miniurl_active && $core->auth->check('admin',$core->blog->id)) {
			$args[0][__('create short link')] = 'miniurl_create';
			$args[0][__('delete short link')] = 'miniurl_delete';
			$args[0][__('reset short link counter')] = 'miniurl_counter_reset';
		}
	}

	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		if ($action != 'miniurl_create' 
		 && $action != 'miniurl_delete' 
		 && $action != 'miniurl_counter_reset') return;

		try {
			$O = new dcMiniUrl($core);

			while ($posts->fetch()) {
				$post_url = $core->blog->url.$core->url->getBase('post').'/'.$posts->post_url;

				$type = 'customurl';
				if (-1 == ($id = $O->id($type,$post_url))) {
					$type = 'miniurl';
					if (-1 == ($id = $O->id($type,$post_url)))
						$type = '';
				}

				if ($type != '') {

					if ($action == 'miniurl_delete')
						$O->delete($type,$id);
						
					elseif ($action == 'miniurl_counter_reset')
						$O->counter($type,$id,'reset');
				}

				if ($type == '' && $action == 'miniurl_create')
					$O->create('miniurl',$post_url);
			}
			$core->blog->triggerBlog();
			http::redirect($redir.'&done=1');
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
}

# Import/export behaviors for Import/export plugin
class backupMiniUrl
{
	public static function exportSingle($core,$exp,$blog_id)
	{
		$exp->export('miniurl',
			'SELECT blog_id, miniurl_type, miniurl_id, miniurl_str, '.
			'miniurl_dt, miniurl_counter, miniurl_password '.
			'FROM '.$core->prefix.'miniurl '.
			"WHERE blog_id = '".$blog_id."' "
		);
	}

	public static function exportFull($core,$exp)
	{
		$exp->exportTable('miniurl');
	}

	public static function importInit($bk,$core)
	{
		$bk->cur_miniurl = $core->con->openCursor($core->prefix.'miniurl');
		$bk->miniurl = new dcMiniUrl($core);
	}

	public static function importSingle($line,$bk,$core)
	{
		if ($line->__name == 'miniurl'){

			# Do nothing if str/type exists !
			$bk->miniurl->create($line->miniurl_type,$line->miniurl_str,$line->miniurl_id);
		}
	}

	public static function importFull($line,$bk,$core)
	{
		if ($line->__name == 'miniurl') {

			$bk->cur_miniurl->clean();

			$bk->cur_miniurl->blog_id   = (string) $line->blog_id;
			$bk->cur_miniurl->miniurl_type   = (string) $line->miniurl_type;
			$bk->cur_miniurl->miniurl_id   = (string) $line->miniurl_id;
			$bk->cur_miniurl->miniurl_str   = (string) $line->miniurl_str;
			$bk->cur_miniurl->miniurl_dt   = (string) $line->miniurl_dt;
			$bk->cur_miniurl->miniurl_counter   = (integer) $line->miniurl_counter;
			$bk->cur_miniurl->miniurl_password   = (string) $line->miniurl_password;
			
			$bk->cur_miniurl->insert();
		}
	}
}
?>