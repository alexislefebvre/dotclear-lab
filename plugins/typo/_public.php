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

/* Add behavior callback for typo replacement in comments */
$core->addBehavior('coreBeforeCommentCreate',array('dcTypo','updateTypoComments'));
$core->addBehavior('publicBeforeCommentPreview',array('dcTypo','previewTypoComments'));

class dcTypo
{
	public static function updateTypoComments($blog,$cur)
	{
		global $core;

		if ($core->blog->settings->typo->typo_active && $core->blog->settings->typo->typo_comments)
		{
			/* Transform typo for comment content (XHTML) */
			if (!(boolean)$cur->comment_trackback) {
				if ($cur->comment_content != null) {
					if ($core->blog->settings->typo->typo_comments)
						$cur->comment_content = SmartyPants($cur->comment_content);
				}
			}
		}
	}
	public static function previewTypoComments($prv)
	{
		global $core;

		if ($core->blog->settings->typo->typo_active && $core->blog->settings->typo->typo_comments)
		{
			/* Transform typo for comment content (XHTML) */
			if ($prv['content'] != null) {
				if ($core->blog->settings->typo->typo_comments)
					$prv['content'] = SmartyPants($prv['content']);
			}
		}
	}
}
?>