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

# Plugin menu
$_menu['Plugins']->addItem(
	__('fac'),
	'plugin.php?p=fac','index.php?pf=fac/icon.png',
	preg_match('/plugin.php\?p=fac(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id)
);

# Admin behaviors
$core->blog->settings->addNamespace('fac');
if ($core->blog->settings->fac->fac_active)
{
	$core->addBehavior('adminPostHeaders',array('facAdmin','adminPostHeaders'));
	$core->addBehavior('adminPostFormSidebar',array('facAdmin','adminPostFormSidebar'));
	$core->addBehavior('adminAfterPostCreate',array('facAdmin','adminAfterPostSave'));
	$core->addBehavior('adminAfterPostUpdate',array('facAdmin','adminAfterPostSave'));
	$core->addBehavior('adminBeforePostDelete',array('facAdmin','adminBeforePostDelete'));
	$core->addBehavior('adminPostsActionsCombo',array('facAdmin','adminPostsActionsCombo'));
	$core->addBehavior('adminPostsActions',array('facAdmin','adminPostsActions'));
	$core->addBehavior('adminPostsActionsContent',array('facAdmin','adminPostsActionsContent'));
}

# Admin behaviors class
class facAdmin
{
	public static function adminPostHeaders()
	{
		return dcPage::jsLoad('index.php?pf=fac/js/admin.js');
	}
	
	public static function adminPostFormSidebar($post)
	{
		global $core;
		
		$fac_formats = self::facFormatsCombo($core);
		
		$fac_url = $fac_format = '';
		if ($post)
		{
			$params = array('meta_type'=>'fac','post_id'=>$post->post_id,'limit'=>1);
			$rs = $core->meta->getMetadata($params);
			$fac_url = $rs->isEmpty() ? '' : $rs->meta_id;
			
			$params = array('meta_type'=>'facformat','post_id'=>$post->post_id,'limit'=>1);
			$rs = $core->meta->getMetadata($params);
			$fac_format = $rs->isEmpty() ? '' : $rs->meta_id;
		}
		
		echo 
		'<h3 id="fac-form-title" class="clear">'.__('fac').'</h3>'.
		'<div id="fac-form-content">'.
		'<p class="classic">'.
		'<label for="fac_url">'.
		($fac_url ? __('change RSS/ATOM feed:') : __('Add RSS/Atom feed:')).
		'<br />'.form::field('fac_url',10,255,$fac_url,'maximal',3).'</label>'.
		($fac_url ? __('change format of feed:') : __('Choose format of feed:')).
		'<br />'.form::combo('fac_format',$fac_formats,$fac_format,'maximal',3).'</label>'.
		'</p>';
		
		if ($fac_url)
		{
			echo 
			'<p><a href="'.$fac_url.'" title="'.$fac_url.'">'.__('view feed').'</a></p>';
		}
		echo '</div>';
	}
	
	public static function adminAfterPostSave($cur,$post_id)
	{
		global $core;
		
		if (!isset($_POST['fac_url']) || !isset($_POST['fac_format'])) return;
		
		$post_id = (integer) $post_id;
		$core->meta->delPostMeta($post_id,'fac');
		$core->meta->delPostMeta($post_id,'facformat');
		
		if (!empty($_POST['fac_url']) && !empty($_POST['fac_format']))
		{
			$core->meta->setPostMeta($post_id,'fac',$_POST['fac_url']);
			$core->meta->setPostMeta($post_id,'facformat',$_POST['fac_format']);
		}
	}
	
	public static function adminBeforePostDelete($post_id)
	{
		global $core;
		
		$post_id = (integer) $post_id;
		$core->meta->delPostMeta($post_id,'fac');
		$core->meta->delPostMeta($post_id,'facformat');
	}
	
	public static function adminPostsActionsCombo($args)
	{
		global $core;
		
		if ($core->auth->check('usage,contentadmin',$core->blog->id))
		{
			$args[0][__('fac')][__('add fac')] = 'fac_add';
		}
		if ($core->auth->check('delete,contentadmin',$core->blog->id))
		{
			$args[0][__('fac')][__('remove fac')] = 'fac_remove';
		}
	}
	
	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		if ($action == 'fac_add' 
		 && !empty($_POST['new_fac_url']) && !empty($_POST['new_fac_format']) 
		 && $core->auth->check('usage,contentadmin',$core->blog->id))
		{
			try
			{
				$new_fac_url = $_POST['new_fac_url'];
				$new_fac_format = $_POST['new_fac_format'];
				
				while ($posts->fetch())
				{
					$params = array('meta_type'=>'fac','post_id'=>$posts->post_id,'limit'=>1);
					$rs = $core->meta->getMetadata($params);
					
					if ($rs->isEmpty())
					{
						$core->meta->setPostMeta($posts->post_id,'fac',$new_fac_url);
						$core->meta->setPostMeta($posts->post_id,'facformat',$new_fac_format);
					}
				}
				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		elseif ($action == 'fac_remove' && !empty($_POST['meta_id']) 
		 && $core->auth->check('delete,contentadmin',$core->blog->id))
		{
			try
			{
				while ($posts->fetch())
				{
					foreach ($_POST['meta_id'] as $v)
					{
						$core->meta->delPostMeta($posts->post_id,'fac',$v);
						$core->meta->delPostMeta($posts->post_id,'facformat',$v);
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
		if ($action == 'fac_add')
		{
			echo
			'<h2>'.__('Add fac to entries').'</h2>'.
			'<form action="posts_actions.php" method="post">'.
			'<p>'.__('It will be added only if there is no feed on entry.').'</p>'.
			'<p class="classic"><label for="new_fac_url">'.__('feed to add:').'<br />'.
			form::field('new_fac_url',60,255,'',2).'</label></p>'.
			'<p class="classic"><label for="new_fac_format">'.__('format:').'<br />'.
			form::combo('new_fac_format',self::facFormatsCombo($core),'',2).'</label></p>'.
			'<p>'.
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'fac_add').
			'<input type="submit" value="'.__('save').'" /></p>'.
			'</form>';
		}
		elseif ($action == 'fac_remove')
		{
			$facs = array();
			
			foreach ($_POST['entries'] as $id)
			{
				$params = array('meta_type'=>'fac','post_id'=>$id,'limit'=>1);
				$rs = $core->meta->getMetadata($params);
				
				if ($rs->isEmpty()) continue;
				
				if (isset($facs[$rs->meta_id]))
				{
					$facs[$rs->meta_id]++;
				}
				else
				{
					$facs[$rs->meta_id] = 1;
				}
			}
			
			echo '<h2>'.__('Remove selected facs from entries').'</h2>';
			
			if (empty($facs))
			{
				echo '<p>'.__('No fac for selected entries').'</p>';
				return;
			}
			
			$posts_count = count($_POST['entries']);
			
			echo
			'<form action="posts_actions.php" method="post">'.
			'<fieldset><legend>'.__('Following facs have been found in selected entries:').'</legend>';
			
			foreach ($facs as $k => $n)
			{
				$label = '<label class="classic">%s %s</label>';
				if ($posts_count == $n)
				{
					$label = sprintf($label,'%s','<strong>%s</strong>');
				}
				echo 
				'<p>'.sprintf($label,
				form::checkbox(array('meta_id[]'),html::escapeHTML($k),'',2),
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
	
	public static function facFormatsCombo($core)
	{
		$formats = @unserialize($core->blog->settings->fac->fac_formats);
		if (!is_array($formats) || empty($formats)) return array();
		foreach($formats as $uid => $f)
		{
			$fac_formats[$f['name']] = $uid;
		}
		return $fac_formats;
	}
}
?>