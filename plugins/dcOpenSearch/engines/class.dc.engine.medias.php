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

class dcEngineMedias extends dcSearchEngine
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
		$this->type = 'media';
		$this->label = __('Medias');
		$this->description = __('Medias search engine');
	}
	
	public function getResults($q = '')
	{
		$res = array();
		
		$strReq =
		'SELECT M.media_id, M.media_path, M.media_title, '.
		'M.media_file, M.media_meta, M.media_dt, M.media_creadt, '.
		'M.media_upddt, M.media_private, M.user_id, '.
		'U.user_name, U.user_firstname, U.user_displayname, U.user_url '.
		'FROM '.$this->core->prefix.'media M '.
		'INNER JOIN '.$this->core->prefix.'user U ON (M.user_id = U.user_id) '.
		"WHERE (media_title LIKE '%".$q."%' OR ".
		"media_file LIKE '%".$q."%') AND ".
		"media_path = '".$this->core->blog->settings->public_path."'";
		
		if (!$this->core->auth->check('media_admin',$this->core->blog->id))
		{
			$strReq .= 'AND (media_private <> 1 ';
			
			if ($this->core->auth->userID()) {
				$strReq .= "OR user_id = '".$this->con->escape($this->core->auth->userID())."'";
			}
			$strReq .= ') ';
		}
		
		$rs = $this->core->con->select($strReq);
		
		$media = new dcMedia($this->core);
		
		while ($rs->fetch()) {
			$res[] = array(
				'search_id' => $rs->media_id,
				'search_url' => $media->getFile($rs->media_id)->file_url,
				'search_title' => $rs->media_title,
				'search_author_id' => $rs->user_id,
				'search_author_name' => dcUtils::getUserCN($rs->user_id,$rs->user_name,$rs->user_firstname,$rs->user_displayname),
				'search_author_url' => $rs->user_url,
				'search_cat_id' => null,
				'search_cat_title' => null,
				'search_cat_url' => null,
	 			'search_dt' => $rs->media_creadt,
	 			'search_tz' => $this->core->blog->settings->blog_timezone,
				'search_content' => null,
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
		return 'media_item.php?id='.$rs->search_id.'&amp;popup=0&amp;post_id=';
	}
	
	public static function getItemPublicURL($rs)
	{	
		return $rs->search_url;
	}
	
	public static function getItemContent($rs)
	{
		return $rs->search_content;
	}
}

?>