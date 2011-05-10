<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of postWidgetText, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$core->blog->settings->addNamespace('postwidgettext');

require dirname(__FILE__).'/_widgets.php';

# Admin menu
$_menu['Plugins']->addItem(
	__('Post widget text'),
	'plugin.php?p=postWidgetText','index.php?pf=postWidgetText/icon.png',
	preg_match('/plugin.php\?p=postWidgetText(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('contentadmin',$core->blog->id)
);

# Post
$core->addBehavior('adminPostHeaders',array('postWidgetTextAdminBehaviors','headers'));
$core->addBehavior('adminPostForm',array('postWidgetTextAdminBehaviors','form'));
$core->addBehavior('adminAfterPostUpdate',array('postWidgetTextAdminBehaviors','save'));
$core->addBehavior('adminAfterPostCreate',array('postWidgetTextAdminBehaviors','save'));
$core->addBehavior('adminBeforePostDelete',array('postWidgetTextAdminBehaviors','delete'));

# Plugin "pages"
$core->addBehavior('adminPageHeaders',array('postWidgetTextAdminBehaviors','headers'));
$core->addBehavior('adminPageForm',array('postWidgetTextAdminBehaviors','form'));
$core->addBehavior('adminAfterPageUpdate',array('postWidgetTextAdminBehaviors','save'));
$core->addBehavior('adminAfterPageCreate',array('postWidgetTextAdminBehaviors','save'));
$core->addBehavior('adminBeforePageDelete',array('postWidgetTextAdminBehaviors','delete'));

# Plugin "importExport"
if ($core->blog->settings->postwidgettext->postwidgettext_importexport_active)
{
	$core->addBehavior('exportFull',array('postWidgetTextBackupBehaviors','exportFull'));
	$core->addBehavior('exportSingle',array('postWidgetTextBackupBehaviors','exportSingle'));
	$core->addBehavior('importInit',array('postWidgetTextBackupBehaviors','importInit'));
	$core->addBehavior('importSingle',array('postWidgetTextBackupBehaviors','importSingle'));
	$core->addBehavior('importFull',array('postWidgetTextBackupBehaviors','importFull'));
}

class postWidgetTextAdminBehaviors
{
	public static function headers()
	{
		return dcPage::jsLoad('index.php?pf=postWidgetText/js/post.js');
	}
	
	public static function form($post)
	{
		# _POST fields
		$title = empty($_POST['post_wtitle']) ? '' : $_POST['post_wtitle'];
		$content = empty($_POST['post_wtext']) ? '' : $_POST['post_wtext'];
		# Existing post
		if ($post)
		{
			$post_id = (integer) $post->post_id;
			
			$pwt = new postWidgetText($GLOBALS['core']);
			$w = $pwt->getWidgets(array('post_id'=>$post_id));
			# Existing widget
			if (!$w->isEmpty())
			{
				$title = $w->option_title;
				$content = $w->option_content;
			}
		}
		# Form
		$res = 
		'<div id="post-wtext-form"><p id="post-wtext-head">'.__('Widget').'</p>'.
		'<p class="col"><label>'.__('Widget title:').
		form::field('post_wtitle',20,255,html::escapeHTML($title),'maximal',2).
		'</label></p>'.
		'<p class="area" id="post-wtext"><label>'.__('Wigdet text:').'</label>'.
		form::textarea('post_wtext',50,5,html::escapeHTML($content),'',2).
		'</p>'.
		'</div><hr />';
		# Place widget on plugin "pages" on old Dotclear version
		if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2.3','<') 
		 && preg_match('/plugin.php\?p=pages(&.*)?$/',$_SERVER['REQUEST_URI']))
		{
			return $res;
		}
		else
		{
			echo $res;
		}
	}
	
	public static function save($cur,$post_id)
	{
		$post_id = (integer) $post_id;
		# _POST fields
		$title = isset($_POST['post_wtitle']) && !empty($_POST['post_wtitle']) ? 
			$_POST['post_wtitle'] : '';
		$content = isset($_POST['post_wtext']) && !empty($_POST['post_wtext']) ? 
			$_POST['post_wtext'] : '';
		# Object
		$pwt = new postWidgetText($GLOBALS['core']);
		# Get existing widget
		$w = $pwt->getWidgets(array('post_id'=>$post_id));
		# If new content is empty, delete old existing widget
		if (empty($title) && empty($content) && !$w->isEmpty())
		{
			$pwt->delWidget($w->option_id);
		}
		# If new content is not empty
		if (!empty($title) || !empty($content))
		{
			$wcur = $pwt->openCursor();
			$wcur->post_id = $post_id;
			$wcur->option_type = 'postwidgettext';
			$wcur->option_lang = $cur->post_lang;
			$wcur->option_format = $cur->post_format;
			$wcur->option_title = $title;
			$wcur->option_content = $content;
			
			# Create widget
			if ($w->isEmpty())
			{
				$id = $pwt->addWidget($wcur);
			}
			# Upddate widget
			else
			{
				$pwt->updWidget($w->option_id,$wcur);
			}
		}
	}
	
	public static function delete($post_id)
	{
		$post_id = (integer) $post_id;
		# Object
		$pwt = new postWidgetText($GLOBALS['core']);
		# Get existing widget
		$w = $pwt->getWidgets(array('post_id'=>$post_id));
		# If new content is empty, delete old existing widget
		if (!$w->isEmpty())
		{
			$pwt->delWidget($w->option_id);
		}
	}
}

class postWidgetTextBackupBehaviors
{
	public static function exportSingle($core,$exp,$blog_id)
	{
		$exp->export('postwidgettext',
			'SELECT option_type, option_content, option_content_xhtml, W.post_id '.
			'FROM '.$core->prefix.'post_option W '.
			'LEFT JOIN '.$core->prefix.'post P ON P.post_id = W.post_id '.
			"WHERE P.blog_id = '".$blog_id."' AND W.option_type = 'postwidgettext' "
		);
	}
	
	public static function exportFull($core,$exp)
	{
		$exp->export('postwidgettext',
			'SELECT option_type, option_content, option_content_xhtml, W.post_id '.
			'FROM '.$core->prefix.'post_option W '.
			'LEFT JOIN '.$core->prefix.'post P ON P.post_id = W.post_id '.
			"WHERE W.option_type = 'postwidgettext' "
		);
	}
	
	public static function importInit($bk,$core)
	{
		$bk->cur_postwidgettext = $core->con->openCursor($core->prefix.'post_option');
		$bk->postwidgettext = new postWidgetText($core);
	}
	
	public static function importSingle($line,$bk,$core)
	{
		if ($line->__name == 'postwidgettext' && isset($bk->old_ids['post'][(integer) $line->post_id]))
		{
			$line->post_id = $bk->old_ids['post'][(integer) $line->post_id];
			
			$exists = $bk->postwidgettext->getWidgets(array('post_id'=>$line->post_id));
			if ($exists->isEmpty())
			{
				$bk->cur_postwidgettext->clean();
				
				$bk->cur_postwidgettext->post_id = (integer) $line->post_id;
				$bk->cur_postwidgettext->option_type = (string) $line->option_type;
				$bk->cur_postwidgettext->option_lang = (string) $line->option_lang;
				$bk->cur_postwidgettext->option_format = (string) $line->option_format;
				$bk->cur_postwidgettext->option_content = (string) $line->option_content;
				$bk->cur_postwidgettext->option_content_xhtml = (string) $line->option_content_xhtml;
				
				$bk->postwidgettext->addWidget($bk->cur_postwidgettext);
			}
		}
	}
	
	public static function importFull($line,$bk,$core)
	{
		if ($line->__name == 'postwidgettext')
		{
			$exists = $bk->postwidgettext->getWidgets(array('post_id'=>$line->post_id));
			if ($exists->isEmpty())
			{
				$bk->cur_postwidgettext->clean();
				
				$bk->cur_postwidgettext->post_id = (integer) $line->post_id;
				$bk->cur_postwidgettext->option_type = (string) $line->option_type;
				$bk->cur_postwidgettext->option_format = (string) $line->option_format;
				$bk->cur_postwidgettext->option_content = (string) $line->option_content;
				$bk->cur_postwidgettext->option_content = (string) $line->option_content;
				$bk->cur_postwidgettext->option_content_xhtml = (string) $line->option_content_xhtml;
				
				$bk->postwidgettext->addWidget($bk->cur_postwidgettext);
			}
		}
	}
}
?>