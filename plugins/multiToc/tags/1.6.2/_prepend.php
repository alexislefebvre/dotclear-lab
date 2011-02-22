<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiToc, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom and contributors
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$__autoload['multiTocPost'] = dirname(__FILE__).'/inc/class.multi.toc.php';
$__autoload['multiTocUi'] = dirname(__FILE__).'/inc/class.multi.toc.php';

$core->addBehavior('publicBeforeDocument',array('multiTocBehaviors','addTplPath'));
$core->addBehavior('coreBlogGetPosts',array('multiTocBehaviors','coreBlogGetPosts'));
$core->addBehavior('initStacker',array('multiTocBehaviors','initStacker'));

$core->url->register('multitoc','multitoc','^multitoc/(.*)$',array('multiTocUrl','multiToc'));

require dirname(__FILE__).'/_widgets.php';

class multiTocBehaviors
{
	public static function addTplPath()
	{
		$GLOBALS['core']->tpl->setPath($GLOBALS['core']->tpl->getPath(), dirname(__FILE__).'/default-templates');
	}
	
	public static function coreBlogGetPosts($rs)
	{
		$rs->extend('rsMultiTocPost');
	}
	
	public static function postHeaders()
	{
		$s = unserialize($GLOBALS['core']->blog->settings->multiToc->multitoc_settings);
		
		return
			(isset($s['post']['enable']) && $s['post']['enable']) ?
			'<script type="text/javascript" src="index.php?pf=multiToc/js/post.js"></script>'.
			'<script type="text/javascript">'."\n".
			"//<![CDATA[\n".
			dcPage::jsVar('jsToolBar.prototype.elements.multiToc.title',__('Table of content')).
			"\n//]]>\n".
			"</script>\n" : '';
	}
	
	public static function initStacker($core)
	{
		$core->stacker->addFilter(
			'multiTocFilter',
			'multiTocBehaviors',
			'multiTocFilter',
			'any',
			100,
			'multiToc',
			__('Add post TOC')
		);
	}
	
	public static function multiTocFilter($rs,$text,$absolute_urls = false)
	{
		if ($rs->hasToc()) {
			$toc = new multiTocPost($rs);
			$text = $toc->process($text);
			unset($toc);
		}
		
		return $text;
	}
}

class rsMultiTocPost
{
	public static function hasToc($rs)
	{
		if (preg_match('/<p>::TOC::<\/p>/',$rs->post_excerpt_xhtml.$rs->post_content_xhtml)) {
			return true;
		}
		else {
			return false;
		}
	}
}

?>