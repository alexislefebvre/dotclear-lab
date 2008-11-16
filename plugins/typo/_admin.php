<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Typo plugin for Dotclear 2.
#
# Copyright (c) 2008 Franck Paul and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

require_once dirname(__FILE__).'/inc/smartypants.php';

/* Add behavior callback, will be use for all types of posts (standard, page, galery item, ...) */
$core->addBehavior('coreAfterPostContentFormat',array('adminTypo','updateTypoEntries'));
/* Add behavior callbacks, will be use for all comments (not trackbacks) */
$core->addBehavior('coreBeforeCommentCreate',array('adminTypo','updateTypoComments'));
$core->addBehavior('coreBeforeCommentUpdate',array('adminTypo','updateTypoComments'));

/* Add menu item in extension list */
$_menu['Plugins']->addItem(__('Typo'),'plugin.php?p=typo','index.php?pf=typo/icon.png',
		preg_match('/plugin.php\?p=typo(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('contentadmin',$core->blog->id));

class adminTypo
{
	public static function updateTypoEntries($ref)
	{
		global $core;
		if ($core->blog->settings->typo_active)
		{
			if (@is_array($ref)) {
				/* Transform typo for excerpt (XHTML) */
				if (isset($ref['excerpt_xhtml'])) {
					$excerpt = &$ref['excerpt_xhtml'];
					if ($excerpt) {
						$excerpt = SmartyPants($excerpt);
					}
				}
				/* Transform typo for content (XHTML) */
				if (isset($ref['content_xhtml'])) {
					$content = &$ref['content_xhtml'];
					if ($content) {
						$content = SmartyPants($content);
					}
				}
			}
		}
	}
	
	public static function updateTypoComments(&$blog,&$cur)
	{
		global $core;
		if ($core->blog->settings->typo_active && $core->blog->settings->typo_comments)
		{
			/* Transform typo for comment content (XHTML) */
			if (!(boolean)$cur->comment_trackback) {
				if ($cur->comment_content != null) {
					$cur->comment_content = SmartyPants($cur->comment_content);
				}
			}
		}
	}
}
?>
