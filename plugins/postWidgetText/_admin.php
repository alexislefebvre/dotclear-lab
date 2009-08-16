<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of postWidgetText a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

if (!$core->blog->settings->postwidgettext_active) return;

require dirname(__FILE__).'/_widgets.php';

$core->addBehavior('adminPostHeaders',
	array('postWidgetTextBehaviors','adminPostHeaders'));
$core->addBehavior('adminPostForm',
	array('postWidgetTextBehaviors','adminPostForm'));
$core->addBehavior('adminAfterPostUpdate',
	array('postWidgetTextBehaviors','adminAfterPostCreate'));
$core->addBehavior('adminAfterPostCreate',
	array('postWidgetTextBehaviors','adminAfterPostCreate'));
$core->addBehavior('adminBeforePostDelete',
	array('postWidgetTextBehaviors','adminBeforePostDelete'));
$core->addBehavior('pluginsBeforeDelete',
	array('postWidgetTextInstall','pluginsBeforeDelete'));
$core->addBehavior('exportFull',
	array('postWidgetTextBackup','exportFull'));
$core->addBehavior('exportSingle',
	array('postWidgetTextBackup','exportSingle'));
$core->addBehavior('importInit',
	array('postWidgetTextBackup','importInit'));
$core->addBehavior('importSingle',
	array('postWidgetTextBackup','importSingle'));
$core->addBehavior('importFull',
	array('postWidgetTextBackup','importFull'));

class postWidgetTextBehaviors
{
	public static function adminPostHeaders()
	{
		return dcPage::jsLoad('index.php?pf=postWidgetText/js/post.js');
	}

	public static function adminPostForm($post)
	{
		$content = empty($_POST['post_wtext']) ? '' : $_POST['post_wtext'];

		if ($post) {
			$post_id = (integer) $post->post_id;
			$postWidgetText = new postWidgetText($GLOBALS['core']);
			$rs = $postWidgetText->get($post_id,'postwidgettext');
			$content = $rs->wtext_content;
		}
		echo
		'<p class="area" id="post-wtext"><label>'.__('Wigdet text:').'</label>'.
		form::textarea('post_wtext',50,5,html::escapeHTML($content),'',2).
		'</p>';
	}

	public static function adminAfterPostCreate($cur,$post_id)
	{
		$post_id = (integer) $post_id;
		$content = isset($_POST['post_wtext']) && !empty($_POST['post_wtext']) ? 
			$_POST['post_wtext'] : '';

		$postWidgetText = new postWidgetText($GLOBALS['core']);
		$postWidgetText->del($post_id,'postwidgettext');

		if (!empty($content)){
			$postWidgetText->set(
				$post_id,$content,'postwidgettext',$cur->post_format,$cur->post_lang
			);
		}
	}

	public static function adminBeforePostDelete($post_id)
	{
		$post_id = (integer) $post_id;
		$postWidgetText = new postWidgetText($GLOBALS['core']);
		$postWidgetText->del($post_id,'postwidgettext');
	}
}
?>