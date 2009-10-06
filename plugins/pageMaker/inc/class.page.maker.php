<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pageMaker, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class pageMakerPager extends pager
{	
	public function __construct($current,$nb_page_tot,$nb_page_max = 20)
	{
		parent::__construct($current,$nb_page_tot,1,$nb_page_max);
	}
	
	public function init($params = null)
	{
		$this->base_url = $GLOBALS['_ctx']->posts->getURL();
		if ($params['type'] === 'post') {
			$this->base_url .= '/page/%s';
		}
		elseif ($params['type'] === 'comment') {
			$this->base_url .= '/c/%s#comments';
		}
		$this->html_cur_page = isset($params['current']) ? $params['current'] : $this->html_cur_page;
		$this->html_prev = isset($params['prev']) ? $params['prev'] : $this->html_prev;
		$this->html_next = isset($params['next']) ? $params['next'] : $this->html_next;
	}
}

class pageMakerBehaviors
{
	public static function coreBlogGetPosts($rs)
	{
		$rs->extend('rsExtPostPageMaker');
	}
	
	public static function postHeaders()
	{
		global $core;

		return
			$core->blog->settings->pagemaker_enable ?
			'<script type="text/javascript" src="index.php?pf=pageMaker/js/post.min.js"></script>'.
			'<script type="text/javascript">'."\n".
			"//<![CDATA[\n".
			dcPage::jsVar('jsToolBar.prototype.elements.pageMaker.title',__('Post pager')).
			"\n//]]>\n".
			"</script>\n" : '';
	}
	
	public static function adminBlogPreferencesForm($core,$settings)
	{
		echo
		'<fieldset><legend>'.__('pageMaker').'</legend>'.
		'<div class="two-cols"><div class="col">'.
		'<p><label class="classic">'.
		form::checkbox('pagemaker_post_enable','1',$settings->pagemaker_post_enable).
		__('Enable post pagination').'</label></p>'.
		'<p><label class="classic">'.
		form::checkbox('pagemaker_post_auto_insert','1',$settings->pagemaker_post_auto_insert).
		__('Auto insert post pagination').'</label></p>'.
		'</div><div class="col">'.
		'<p><label class="classic">'.
		form::checkbox('pagemaker_comment_enable','1',$settings->pagemaker_comment_enable).
		__('Enable comment pagination').'</label></p>'.
		'<p><label>'.__('Comment number per page:').
		form::field('pagemaker_comment_nb_per_page',3,3,($settings->pagemaker_comment_nb_per_page ? $settings->pagemaker_comment_nb_per_page : '20')).
		'</label></p>'.
		'<p><label class="classic">'.
		form::checkbox('pagemaker_comment_auto_insert','1',$settings->pagemaker_comment_auto_insert).
		__('Auto insert comment pagination').'</label></p>'.
		'</div></div>'.
		'</fieldset>';
	}

	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$settings->setNameSpace('pagemaker');
		$settings->put('pagemaker_post_enable',!empty($_POST['pagemaker_post_enable']),'boolean');
		$settings->put('pagemaker_comment_enable',!empty($_POST['pagemaker_comment_enable']),'boolean');
		$settings->put('pagemaker_post_auto_insert',!empty($_POST['pagemaker_post_auto_insert']),'boolean');
		$settings->put('pagemaker_comment_auto_insert',!empty($_POST['pagemaker_comment_auto_insert']),'boolean');
		$settings->put('pagemaker_comment_nb_per_page',$_POST['pagemaker_comment_nb_per_page'],'integer');
		$settings->setNameSpace('system');
	}
}

class rsExtPostPageMaker
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