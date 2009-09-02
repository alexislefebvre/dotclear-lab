<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of splitPost, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class splitPostPager extends pager
{
	private $current;
	private $nb_page_tot;
	private $nb_page_max;
	
	public function __construct($current,$nb_page_tot,$nb_page_max = 20)
	{
		parent::__construct($current,$nb_page_tot,1,$nb_page_max);
	}
	
	public function setBaseUrl()
	{
		$this->base_url = $GLOBALS['_ctx']->posts->getURL().'/page/%s';
	}
}

class splitPostBehaviors
{
	public static function coreBlogGetPosts($rs)
	{
		$rs->extend('rsExtPostSplitPost');
	}
	
	public static function postHeaders()
	{
		global $core;

		return
		'<script type="text/javascript" src="index.php?pf=splitPost/js/post.min.js"></script>'.
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		dcPage::jsVar('jsToolBar.prototype.elements.splitPost.title',__('Post pager')).
		"\n//]]>\n".
		"</script>\n";
	}
}

class rsExtPostSplitPost
{
	public static function getContent($rs,$absolute_urls=false)
	{
		$_ctx = $GLOBALS['_ctx'];
		$core = $GLOBALS['core'];
		
		$res = rsExtPost::getContent($rs,$absolute_urls);
		
		$part = preg_split($core->post_page_pattern,$res);
		
		$page = $_ctx->post_page_current !== null ? $_ctx->post_page_current - 1 : 0;
		
		return isset($part[$page]) ? $part[$page] : '';
	}
}

?>