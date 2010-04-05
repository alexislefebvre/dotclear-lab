<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of periodical, a plugin for Dotclear 2.
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

$_menu['Plugins']->addItem(
	__('Periodical'),
	'plugin.php?p=periodical','index.php?pf=periodical/icon.png',
	preg_match('/plugin.php\?p=periodical(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id)
);

$core->addBehavior('adminBeforePostDelete',array('adminPeriodical','adminBeforePostDelete'));
$core->addBehavior('adminPostsActionsCombo',array('adminPeriodical','adminPostsActionsCombo'));
$core->addBehavior('adminPostsActionsContent',array('adminPeriodical','adminPostsActionsContent'));
$core->addBehavior('adminPostsActions',array('adminPeriodical','adminPostsActions'));
$core->addBehavior('adminPostFormSidebar',array('adminPeriodical','adminPostFormSidebar'));
$core->addBehavior('adminAfterPostUpdate',array('adminPeriodical','adminAfterPostSave'));
$core->addBehavior('adminAfterPostCreate',array('adminPeriodical','adminAfterPostSave'));

class adminPeriodical
{
	public static function adminBeforePostDelete($post_id)
	{
		global $core;

		if ($post_id === null) return;

		$obj = new periodical($core);
		$obj->delPost($post_id);
	}
	
	public static function adminPostsActionsCombo(&$args)
	{
		global $core;
		if (!$core->blog->settings->periodical_active 
		 || !$core->auth->check('admin',$core->blog->id)) return;

		$args[0][__('Periodical')][__('remove from periodical')] = 'remove_post_periodical';
		$args[0][__('Periodical')][__('add to periodical')] = 'add_post_periodical';
	}

	public static function adminPostsActionsContent($core,$action,$hidden_fields)
	{
		if (!in_array($action,array('remove_post_periodical','add_post_periodical'))) return;

		try
		{
			foreach ($_POST['entries'] as $k => $v) {
				$entries[$k] = (integer) $v;
			}

			if ($action == 'remove_post_periodical') {
				echo '<h2>'.__('remove selected entries from periodical').'</h2>';
			}
			elseif ($action == 'add_post_periodical') {
				echo '<h2>'.__('add selected entries to periodical').'</h2>';
			}

			$obj = new periodical($core);
			$periods = $obj->getPeriods();
			
			if ($periods->isEmpty())
			{
				echo '<p>'.__('There is no periodical').'</p>';
			}
			else
			{
				$params = array();
				$params['post_status'] = -2;
				$params['post_id'] = $entries;
				$posts = $core->blog->getPosts($params);
				
				if ($posts->isEmpty())
				{
					echo '<p>'.__('There is no pending post').'</p>';
				}
				else
				{
					echo 
					'<form action="posts_actions.php" method="post">'.
					'<h3>'.__('Entries').'</h3><ul>';

					while($posts->fetch())
					{
						echo
						'<li><label class="classic">'.
						form::checkbox(array('periodical_entries[]'),$posts->post_id,1).' '.
						$posts->post_title.
						'</label></li>';
					}
					
					if ($action == 'add_post_periodical') {
						echo '</ul><h3>'.__('Periods').'</h3><ul>';

						$sel = true;
						while ($periods->fetch())
						{
							echo 
							'<li><label class="classic" for="'.$periods->periodical_id.'">'.
							form::radio(array('periods',$periods->periodical_id),$periods->periodical_id,$sel).' '.
							html::escapeHTML($periods->periodical_title).'</label></li>';
							$sel = false;
						}
					}

					echo 
					'</ul><p>'.
					$hidden_fields.
					$core->formNonce().
					form::hidden(array('action'),$action).
					'<input type="submit" value="'.__('save').'" /></p>'.
					'</form>';
				}
			}
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}

	public static function adminPostsActions(&$core,$posts,$action,$redir)
	{
		if (!in_array($action,array('remove_post_periodical','add_post_periodical')) 
		 || empty($_POST['periodical_entries'])) return;

		try {
			$obj = new periodical($core);

			while($posts->fetch())
			{
				if (in_array($posts->post_id,$_POST['periodical_entries']))
				{
					if ($action == 'remove_post_periodical') {
						$obj->delPost($posts->post_id);
					}
					elseif ($action == 'add_post_periodical' 
					 && $posts->post_status == '-2') {
						$obj->addPost($_POST['periods'],$posts->post_id);
					}
				}
			}
			http::redirect($redir);
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
	
	public static function adminPostFormSidebar($post)
	{
		global $core;

		if (!$core->auth->check('contentadmin',$core->blog->id)) return;

		$obj = new periodical($core);
		$periods = $obj->getPeriods();
		if ($periods->isEmpty()) return;

		$default = '';
		if ($post->post_id !== null) {
			$rs = $obj->getPosts(array('post_id'=>$post->post_id));
			$default = $rs->isEmpty() ? '' : $rs->periodical_id;
		}

		$combo = array('-'=>'');
		while ($periods->fetch())
		{
			$combo[html::escapeHTML($periods->periodical_title)] = $periods->periodical_id;
		}
		echo 
		'<div id="periodical-sidebar">'.
		'<h3 class="clear">'.__('Periodical').'</h3>'.
		'<p><label for="new_periodical">'.__('Link to a period:').' '.
		form::combo('new_periodical',$combo,$default).'</p>'.
		'</div>';
	}

	public static function adminAfterPostSave($cur,$post_id)
	{
		global $core;
		# Not contentadmin
		if (!$core->auth->check('contentadmin',$core->blog->id) || $post_id === null) return;
		# Period object
		$obj = new periodical($core);
		# Delete relation
		$obj->delPost($post_id);
		# Not pending post
		if ($cur->post_status != -2) return;
		# No period to add
		if (empty($_POST['new_periodical'])) return;
		# Get periods
		$period = $obj->getPeriods(array('periodical_id'=>$_POST['new_periodical']));
		# No period
		if ($period->isEmpty()) return;
		# Add relation
		$obj->addPost($period->periodical_id,$post_id);
	}
}
?>