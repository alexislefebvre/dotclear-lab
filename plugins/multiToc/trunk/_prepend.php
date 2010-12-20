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
		$s = unserialize($GLOBALS['core']->blog->settings->multiToc->multitoc_settings);
		
		if (isset($s['post']['enable']) && $s['post']['enable']) {
			$rs->extend('rsMultiTocPost');
		}
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
}

?>