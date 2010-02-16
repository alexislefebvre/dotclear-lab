<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of fac, a plugin for Dotclear 2.
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

# Plugin menu
$_menu['Plugins']->addItem(
	__('fac'),
	'plugin.php?p=fac','index.php?pf=fac/icon.png',
	preg_match('/plugin.php\?p=fac(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id)
);

# Admin behaviors
if ($core->blog->settings->fac_active)
{
	$core->addBehavior('adminPostFormSidebar',array('facAdmin','facField'));
	$core->addBehavior('adminAfterPostCreate',array('facAdmin','setFac'));
	$core->addBehavior('adminAfterPostUpdate',array('facAdmin','setFac'));
	$core->addBehavior('adminBeforePostDelete',array('facAdmin','delFac'));
	$core->addBehavior('adminPostsActionsCombo',array('facAdmin','adminPostsActionsCombo'));
	$core->addBehavior('adminPostsActions',array('facAdmin','adminPostsActions'));
	$core->addBehavior('adminPostsActionsContent',array('facAdmin','adminPostsActionsContent'));
}

# Admin behaviors class
class facAdmin
{
	public static function facField($post)
	{
		if ($post) {
			$f = new fac($GLOBALS['core']);
			$rs = $f->getFac($post->post_id,null,1);
			$fac_url = $rs->isEmpty() ? '' : $rs->meta_id;
		} else {
			$fac_url = '';
		}

		echo 
		'<h3 class="clear">'.__('fac').'</h3>'.
		'<p class="classic">'.
		'<label for="fac_url">'.
		($fac_url ? __('change RSS/ATOM feed:') : __('Add RSS/Atom feed:')).
		'</label>'.
		form::field('fac_url',32,32,$fac_url).
		'</p>';

		if ($fac_url) {
			echo 
			'<p><a href="'.$fac_url.'" title="'.$fac_url.'">'.__('view feed').'</a></p>';
		}
	}
	
	public static function setFac(&$cur,&$post_id)
	{
		if (!isset($_POST['fac_url'])) return;

		$post_id = (integer) $post_id;
		$f = new fac($GLOBALS['core']);
		$f->delFac($post_id);

		if (!empty($_POST['fac_url'])) {
			$f->setFac($post_id,$_POST['fac_url']);
		}
	}

	public static function delFac($post_id)
	{
		$post_id = (integer) $post_id;
		$f = new fac($GLOBALS['core']);
		$f->delFac($post_id);
	}
	
	public static function adminPostsActionsCombo(&$args)
	{
		$args[0][__('add fac')] = 'fac_add';
		
		if ($GLOBALS['core']->auth->check('delete,contentadmin',$GLOBALS['core']->blog->id)) {
			$args[0][__('remove fac')] = 'fac_remove';
		}
	}
	
	public static function adminPostsActions(&$core,$posts,$action,$redir)
	{
		if ($action == 'fac_add' && !empty($_POST['new_fac_url']))
		{
			try {
				$f = new fac($core);
				$new_fac_url = $_POST['new_fac_url'];
				
				while ($posts->fetch())
				{
					$rs = $f->getFac($posts->post_id,null,1);
					if (!$rs->isEmpty()) {
						if ($new_fac_url != $rs->meta_id) {
							$f->setFac($posts->post_id,$new_fac_url);
						}
					}
				}
				http::redirect($redir);
			}
			catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
		elseif ($action == 'fac_remove' && !empty($_POST['meta_id']) && $core->auth->check('delete,contentadmin',$core->blog->id))
		{
			try {
				$f = new fac($core);
				while ($posts->fetch())
				{
					foreach ($_POST['meta_id'] as $v)
					{
						$f->delFac($posts->post_id,$v);
					}
				}
				http::redirect($redir);
			}
			catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
	}
	
	public static function adminPostsActionsContent($core,$action,$hidden_fields)
	{
		if ($action == 'fac_add')
		{
			echo
			'<h2>'.__('Add fac to entries').'</h2>'.
			'<form action="posts_actions.php" method="post">'.
			'<p><label class="area">'.__('fac to add:').' '.
			form::textarea('new_fac_url',60,3).
			'</label> '.
			
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'fac_add').
			'<input type="submit" value="'.__('save').'" /></p>'.
			'</form>';
		}
		elseif ($action == 'fac_remove')
		{
			$f = new fac($core);
			$facs = array();
			
			foreach ($_POST['entries'] as $id) {
				$rs = $f->getFac($id);
				if ($rs->isEmpty()) continue;
				
				if (isset($facs[$rs->meta_id])) {
					$facs[$rs->meta_id]++;
				} else {
					$facs[$rs->meta_id] = 1;
				}
			}
			
			echo '<h2>'.__('Remove selected facs from entries').'</h2>';
			
			if (empty($facs)) {
				echo '<p>'.__('No fac for selected entries').'</p>';
				return;
			}
			
			$posts_count = count($_POST['entries']);
			
			echo
			'<form action="posts_actions.php" method="post">'.
			'<fieldset><legend>'.__('Following facs have been found in selected entries:').'</legend>';
			
			foreach ($facs as $k => $n) {
				$label = '<label class="classic">%s %s</label>';
				if ($posts_count == $n) {
					$label = sprintf($label,'%s','<strong>%s</strong>');
				}
				echo '<p>'.sprintf($label,
						form::checkbox(array('meta_id[]'),html::escapeHTML($k)),
						html::escapeHTML($k)).
					'</p>';
			}
			
			echo
			'<p><input type="submit" value="'.__('ok').'" /></p>'.
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'fac_remove').
			'</fieldset></form>';
		}
	}
}
?>