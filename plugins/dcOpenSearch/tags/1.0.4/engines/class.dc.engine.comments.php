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

class dcEngineComments extends dcSearchEngine
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
		$this->type = 'comment';
		$this->label = __('Comments');
		$this->description = __('Comments search engine');
	}
	
	public function getResults($q = '')
	{
		$res = array();
		
		$rs = $this->core->blog->getComments(array('search' => $q));
		
		while ($rs->fetch()) {
			$res[] = array(
				'search_id' => $rs->comment_id,
				'search_url' => $rs->post_url,
				'search_title' => $rs->post_title,
				'search_author_id' => $rs->comment_author,
				'search_author_name' => $rs->comment_author,
				'search_author_url' => trim($rs->comment_site),
				'search_cat_id' => null,
				'search_cat_title' => null,
				'search_cat_url' => null,
	 			'search_dt' => $rs->comment_dt,
	 			'search_tz' => $rs->comment_tz,
				'search_content' => $rs->comment_content,
				'search_comment_nb' => null,
				'search_trackback_nb' => null,
				'search_engine' => $this->name,
				'search_lang' => $this->core->blog->settings->lang,
				'search_type' => $this->type
			);
		}
		
		return $res;	
	}
	
	public static function getItemAdminURL($rs)
	{
		return 'comment.php?id='.$rs->search_id;
	}
	
	public static function getItemPublicURL($rs)
	{
		return $GLOBALS['core']->blog->url.$GLOBALS['core']->getPostPublicURL($rs->search_type,$rs->search_url)."#c".$rs->search_id;
	}
	
	public static function getItemContent($rs)
	{
		return $rs->search_content;
	}
}

?>