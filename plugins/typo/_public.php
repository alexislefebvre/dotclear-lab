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
require_once dirname(__FILE__).'/inc/hyphenation.php';

/* Add behavior callback for typo replacement in comments */
$core->addBehavior('coreBeforeCommentCreate',array('dcTypo','updateTypoComments'));
$core->addBehavior('publicBeforeCommentPreview',array('dcTypo','previewTypoComments'));

/* Add behavior callback for hyphenation in entries and comments */
//$core->addBehavior('coreBlogGetPosts',array('dcTypo','prepareTypoEntries'));
//$core->addBehavior('coreBlogGetComments',array('dcTypo','prepareTypoComments'));

/* Add behavior callback for hyphenation in entries and comments */
//$core->addBehavior('publicEntryBeforeContent',array('dcTypo','hyphenateEntry'));
//$core->addBehavior('publicCommentBeforeContent',array('dcTypo','hyphenateComment'));

class dcTypo
{
	public static function hyphenateEntry(&$core,&$_ctx)
	{
		if ($core->blog->settings->typo_active && $core->blog->settings->typo_hyphenation)
		{
			if ($_ctx->posts->exists('post_lang') && ($_ctx->posts->exists('post_excerpt_xhtml') || $_ctx->posts->exists('post_content_xhtml'))) {
				$language = $_ctx->posts->post_lang;
				$hyphenate = preset_hyphenation($language, $path_to_patterns, $dictionary,
					$leftmin, $rightmin, $charmin);
				if ($hyphenate) {
					if ($_ctx->posts->exists('post_excerpt_xhtml')) {
						$_ctx->posts->post_excerpt_xhtml = hyphenation($_ctx->posts->post_excerpt_xhtml, $language, $path_to_patterns, $dictionary,
							$leftmin, $rightmin, $charmin);
					}
					if ($_ctx->posts->exists('post_content_xhtml')) {
						$_ctx->posts->post_content_xhtml = hyphenation($_ctx->posts->post_content_xhtml, $language, $path_to_patterns, $dictionary,
							$leftmin, $rightmin, $charmin);
					}
				}
			}
		}
	}

	public static function hyphenateComment(&$core,&$_ctx)
	{
		if ($core->blog->settings->typo_active && $core->blog->settings->typo_hyphenation_comments)
		{
			if ($_ctx->posts->exists('post_lang') && $_ctx->comments->exists('comment_content')) {
				$language = $_ctx->posts->post_lang;
				$hyphenate = preset_hyphenation($language, $path_to_patterns, $dictionary, $leftmin, $rightmin, $charmin);
				if ($hyphenate) {
					$_ctx->comments->comment_content = hyphenation($_ctx->comments->comment_content, $language, $path_to_patterns, $dictionary,
						$leftmin, $rightmin, $charmin);
				}
			}
		}
	}

	public static function prepareTypoEntries(&$rs)
	{
		global $core;
		
		if ($core->blog->settings->typo_active && $core->blog->settings->typo_hyphenation)
		{
			$rs = $rs->toStatic();
			if ($rs->exists('post_lang') && ($rs->exists('post_excerpt_xhtml') || $rs->exists('post_content_xhtml'))) {
				while ($rs->fetch()) {
					$language = $rs->post_lang;
					$hyphenate = preset_hyphenation($language, $path_to_patterns, $dictionary,
						$leftmin, $rightmin, $charmin);
					if ($hyphenate) {
						if ($rs->exists('post_excerpt_xhtml')) {
							$rs->post_excerpt_xhtml = hyphenation($rs->post_excerpt_xhtml, $language, $path_to_patterns, $dictionary,
								$leftmin, $rightmin, $charmin);
						}
						if ($rs->exists('post_content_xhtml')) {
							$rs->post_content_xhtml = hyphenation($rs->post_content_xhtml, $language, $path_to_patterns, $dictionary,
								$leftmin, $rightmin, $charmin);
						}
					}
				}
			}
		}
	}

	public static function prepareTypoComments(&$rs)
	{
		global $core;
		global $_ctx;
		
		if ($core->blog->settings->typo_active && $core->blog->settings->typo_hyphenation_comments)
		{
			$rs = $rs->toStatic();
			if ($_ctx->posts->exists('post_lang') && $rs->exists('comment_content')) {
				while ($rs->fetch()) {
					$language = $_ctx->posts->post_lang;
					$hyphenate = preset_hyphenation($language, $path_to_patterns, $dictionary, $leftmin, $rightmin, $charmin);
					if ($hyphenate) {
						$rs->comment_content = hyphenation($rs->comment_content, $language, $path_to_patterns, $dictionary,
							$leftmin, $rightmin, $charmin);
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
					if ($core->blog->settings->typo_comments)
						$cur->comment_content = SmartyPants($cur->comment_content);
				}
			}
		}
	}
	public static function previewTypoComments(&$prv)
	{
		global $core;

		if ($core->blog->settings->typo_active && $core->blog->settings->typo_comments)
		{
			/* Transform typo for comment content (XHTML) */
			if ($prv['content'] != null) {
				if ($core->blog->settings->typo_comments)
					$prv['content'] = SmartyPants($prv['content']);
			}
		}
	}
}
?>
