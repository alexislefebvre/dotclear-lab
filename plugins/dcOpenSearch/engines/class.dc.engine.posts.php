<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcOpenSearch, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcEnginePosts extends dcSearchEngine
{
	public $type;
	public $name;
	public $label;
	public $description;
	public $active = true;
	
	protected $core;
	protected $has_gui = false;
	protected $gui_url = null;
	
	public function __construct($core)
	{
		parent::__construct($core);
	}
	
	protected function setInfo()
	{
		$this->type = 'post';
		$this->label = __('Posts');
		$this->description = __('Posts search engine');
	}
	
	public function getResults($q = '')
	{
		$res = array();
		
		$rs = $this->core->blog->getPosts(array('post_type' => 'post', 'search' => $q));
		
		while ($rs->fetch()) {
			$res[] = array(
				'search_id' => $rs->post_id,
				'search_url' => $rs->post_url,
				'search_title' => $rs->post_title,
				'search_author_id' => $rs->user_id,
				'search_author_name' => $rs->getAuthorCN(),
				'search_author_url' => $rs->user_url,
				'search_cat_id' => $rs->cat_id,
				'search_cat_title' => $rs->cat_title,
				'search_cat_url' => $rs->cat_url,
	 			'search_dt' => $rs->post_creadt,
	 			'search_tz' => $rs->post_tz,
				'search_content' => (empty($rs->post_excerpt_xhtml) ? $rs->post_content_xhtml : $rs->post_excerpt_xhtml),
				'search_comment_nb' => $rs->nb_trackback,
				'search_trackback_nb' => $rs->nb_comment,
				'search_engine' => $this->name,
				'search_lang' => $rs->post_lang,
				'search_type' => $this->type
			);
		}
		
		return $res;	
	}
	
	public static function getItemAdminURL($rs)
	{
		return $GLOBALS['core']->getPostAdminURL($rs->search_type,$rs->search_id);
	}
	
	public static function getItemPublicURL($rs)
	{
		return $GLOBALS['core']->blog->url.$GLOBALS['core']->getPostPublicURL($rs->search_type,$rs->search_url);
	}
	
	public static function getItemContent($rs)
	{
		return $rs->search_content;
	}
}

?>