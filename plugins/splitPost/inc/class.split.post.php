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
	public function __construct($current,$nb_page_tot,$nb_page_max = 20)
	{
		parent::__construct($current,$nb_page_tot,1,$nb_page_max);
	}
	
	public function init($params = null)
	{
		$this->base_url = $GLOBALS['_ctx']->posts->getURL().'/page/%s';
		$this->html_cur_page = isset($params['current']) ? $params['current'] : $this->html_cur_page;
		$this->html_prev = isset($params['prev']) ? $params['prev'] : $this->html_prev;
		$this->html_next = isset($params['next']) ? $params['next'] : $this->html_next;
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
			$core->blog->settings->splitpost_enable ?
			'<script type="text/javascript" src="index.php?pf=splitPost/js/post.min.js"></script>'.
			'<script type="text/javascript">'."\n".
			"//<![CDATA[\n".
			dcPage::jsVar('jsToolBar.prototype.elements.splitPost.title',__('Post pager')).
			"\n//]]>\n".
			"</script>\n" : '';
	}
	
	public static function adminBlogPreferencesForm($core,$settings)
	{
		echo
		'<fieldset><legend>'.__('SplitPost').'</legend>'.
		'<p><label class="classic">'.
		form::checkbox('splitpost_enable','1',$settings->splitpost_enable).
		__('Enable plugin').'</label></p>'.
		'<p><label class="classic">'.
		form::checkbox('splitpost_auto_insert','1',$settings->splitpost_auto_insert).
		__('Auto insert post pagination').'</label></p>'.
		'</fieldset>';
	}

	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$settings->setNameSpace('splitpost');
		$settings->put('splitpost_enable',!empty($_POST['splitpost_enable']),'boolean');
		$settings->put('splitpost_auto_insert',!empty($_POST['splitpost_auto_insert']),'boolean');
		$settings->setNameSpace('system');
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