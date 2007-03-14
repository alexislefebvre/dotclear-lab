<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Gallery plugin.
# Copyright (c) 2007 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
require_once (dirname(__FILE__).'/class.dc.rs.gallery.php');

class dcGallery
{
	private $core;
	private $con;
	private $table;
	
	public function __construct(&$core)
	{
		$this->core =& $core;
		$this->con =& $this->core->con;
		$this->table = $this->core->prefix.'meta';
	}
	
	public function getGalleries ($params=array()) {
		$params['post_type']='gal';
		return $core->blog->getPosts($params);
	}

	public function getGalItems ($params=array()) {
		$params['post_type']='galitem';
		return $core->blog->getPosts($params);
	}


	/**
	Retrieve all media & posts associated to a gallery id. 
	<b>$params</b> is an array taking one of the following parameters :
	- no_content : dont retrieve entry contents (ie image description)	
	- post_type: Get only entries with given type (default "post")
	- post_id: (integer) Get entry with given post_id
	- post_url: Get entry with given post_url field
	- user_id: (integer) Get entries belonging to given user ID
	- post_status: (integer) Get entries with given post_status
	- post_selected: (boolean) Get select flaged entries
	- post_year: (integer) Get entries with given year
	- post_month: (integer) Get entries with given month
	- post_day: (integer) Get entries with given day
	- post_lang: Get entries with given language code
	- search: Get entries corresponding of the following search string
	- sql: Append SQL string at the end of the query
	- from: Append SQL string after "FROM" statement in query
	- order: Order of results (default "ORDER BY post_dt DES")
	- limit: Limit parameter
	
	@param	type		<b>string</b>		gallery id
	@param	type		<b>boolean</b>		count_only
	*/
	public function getGalImageMedia ($params=array(),$gal_id=null,$count_only=false) {
		
		if ($count_only)
		{
			$strReq = 'SELECT count(P.post_id) ';
		}
		else
		{
			if (!empty($params['no_content'])) {
				$content_req = '';
			} else {
				$content_req =
				'P.post_excerpt, P.post_excerpt_xhtml, '.
				'P.post_content, P.post_content_xhtml, P.post_notes, ';
			}
			
			$fields =
			'P.post_id, P.blog_id, P.user_id, P.cat_id, P.post_dt, '.
			'P.post_tz, P.post_creadt, P.post_upddt, P.post_format, P.post_password, '.
			'P.post_url, P.post_lang, P.post_title, '.$content_req.
			'P.post_meta, P.post_status, P.post_selected, '.
			'P.post_open_comment, P.post_open_tb, P.nb_comment, P.nb_trackback, '.
			'U.user_name, U.user_firstname, U.user_displayname, U.user_email, '.
			'U.user_url, '.
			'C.cat_title, C.cat_url ';
			$fields.=", M.media_file, M.media_id, M.media_path, M.media_title, M.media_meta, M.media_dt, M.media_creadt, M.media_upddt, M.media_private, M.media_dir ";
			
			$strReq = 'SELECT '.$fields;
		}
		
		$strReq .=
 		'FROM '.$this->core->prefix.'post G ';

		$strReq .= 
			'INNER JOIN '.$this->core->prefix.'meta GM '.
			'on GM.post_id = G.post_id and GM.meta_type=\'galitem\' '.
			'INNER JOIN '.$this->core->prefix.'post  P '.
			'on GM.meta_id = P.post_id ';
		/*if (!is_null($gal_id))
			$strReq .= ', '.$this->core->prefix.'post G ';*/
		$strReq .=
		'INNER JOIN '.$this->core->prefix.'user U ON U.user_id = P.user_id '.
		'LEFT JOIN '.$this->core->prefix.'category C ON P.cat_id = C.cat_id '.
		'INNER JOIN '.$this->core->prefix.'post_media PM ON P.post_id = PM.post_id '.
		'INNER JOIN '.$this->core->prefix.'media M on M.media_id = PM.media_id ';
		/*if (!is_null($gal_id))
			$strReq .= ', '.$this->core->prefix.'post G '.
				'INNER JOIN '.$this->core->prefix.'meta GM '.
				'on GM.post_id = G.post_id AND GM.meta_id=P.post_id '
				.'and GM.meta_type="galitem" ';*/
		
		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}
		
		$strReq .=
		"WHERE P.blog_id = '".$this->core->con->escape($this->core->blog->id)."' "
		. ' AND G.post_type=\'gal\' ';
		
		if (!is_null($gal_id))
			$strReq .= " AND G.post_id='".$gal_id."' ";		

		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$strReq .= 'AND ((P.post_status = 1 ';
			
			/*if ($this->without_password) {
				$strReq .= 'AND P.post_password IS NULL ';
			}*/
			$strReq .= ') ';
			
			if ($this->core->auth->userID()) {
				$strReq .= "OR P.user_id = '".$this->con->escape($this->core->auth->userID())."')";
			} else {
				$strReq .= ') ';
			}
		}
		
		#Adding parameters
			$strReq .= "AND P.post_type = 'galitem' ";
		
		if (!empty($params['post_id'])) {
			$strReq .= 'AND P.post_id = '.(integer) $params['post_id'].' ';
		}
		
		if (!empty($params['post_url'])) {
			$strReq .= "AND P.post_url = '".$this->con->escape($params['post_url'])."' ";
		}

		if (!$count_only)
		{
			//$strReq .= 'GROUP BY '.$fields.' ';
			
			if (!empty($params['order'])) {
				$strReq .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			} else {
				$strReq .= 'ORDER BY P.post_title ASC ';
			}
		}
		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->con->limit($params['limit']);
		}
		
		$rs = $this->con->select($strReq);
		$rs->core = $this->core;

		$rs->extend('rsExtPost');
				
		
		# --BEHAVIOR-- coreBlogGetPosts
		$this->core->callBehavior('coreBlogGetPosts',$rs);
		$rs->extend('rsExtImage');
		return $rs;

	
	}


	// Retrieve media items not associated to a image post in a given directory
	function getMediaWithoutGalItems($media_dir) {
		$strReq = 'SELECT M.media_id, M.media_dir, M.media_file, P.post_id '.
		'FROM '.$this->core->prefix.'media M '.
			'LEFT JOIN '.$this->core->prefix.'post_media PM ON M.media_id = PM.media_id '.
			'LEFT JOIN '.$this->core->prefix.'post P ON (PM.post_id = P.post_id AND P.blog_id = \''.$this->core->con->escape($this->core->blog->id).'\') '.
			'WHERE PM.post_id IS NULL AND media_dir = \''.$media_dir.'\'';
		$rs = $this->con->select($strReq);
		return $rs;
	}

	function getItemsWithoutGal($media_dir,$gal_id) {
		$strReq = 'SELECT P.post_id,M.media_id '.
		'FROM '.$this->core->prefix.'post P INNER JOIN '.
			$this->core->prefix.'post_media PM ON P.post_type=\'galitem\' and P.post_id=PM.post_id '.
		 	'INNER JOIN '.$this->core->prefix.'media M on PM.media_id=M.media_id '.
			'and M.media_dir = \''.$media_dir.'=\' '.
			' LEFT JOIN ('.$this->core->prefix.'post P2 INNER JOIN '.
			$this->core->prefix.'meta PMeta on P2.post_id=PMeta.post_id and P2.post_type=\'gal\' '.
			'and P2.post_id='.$gal_id.') on P.post_id=PMeta.meta_id and PMeta.meta_type=\'galitem\' '.
			'where P2.post_id IS NULL';
		
		$rs = $this->con->select($strReq);
		return $rs;

	}

	function createPostForMedia($media) {
		
		$cur = $this->core->con->openCursor($this->core->prefix.'post');	
		$cur->post_type='galitem';
		$cur->post_title = $media->media_title;
		$cur->cat_id = null;
		$cur->post_dt = $media->media_dtstr;
		$cur->post_format = $this->core->auth->getOption('post_format');
		$cur->post_password = '';
		$cur->post_lang = $this->core->auth->getInfo('user_lang');
		$cur->post_excerpt = '';
		$cur->post_excerpt_xhtml = '';
		$cur->post_content = $media->media_title;
		$cur->post_content_xhtml = '';
		$cur->post_notes = null;
		$cur->post_status = 1;
		$cur->post_selected = 0;
		$cur->post_open_comment = 1;
		$cur->post_open_tb = 1;
		$cur->post_url = substr($media->file,strlen($this->core->media->root)+1);

		
		$cur->user_id = $this->core->auth->userID();
		$return_id=0;
	
		try
		{
			$return_id = $this->core->blog->addPost($cur);
			// Attach media to post
			$this->core->media->addPostMedia($return_id,$media->media_id);
			return $return_id;
		}
		catch (Exception $e)
		{
			$this->core->error->add($e->getMessage());
			echo $this->core->error->toHTML();
			throw $e;
		}

	}


	/**
	Returns a record with post id, title and date for next or previous post
	according to the post ID and timestamp given.
	$dir could be 1 (next post) or -1 (previous post).
	
	@param	post_id	<b>integer</b>		Post ID
	@param	ts		<b>string</b>		Post timestamp
	@param	dir		<b>integer</b>		Search direction
	@return	record
	*/
	public function getNextGallery($post_id,$ts,$dir)
	{
		$dt = date('Y-m-d H:i:s',(integer) $ts);
		$post_id = (integer) $post_id;
		
		if($dir > 0) {
			$sign = '>';
			$order = 'ASC';
		}
		else {
			$sign = '<';
			$order = 'DESC';
		}
		$params['post_type'] = 'gal';
		$params['limit'] = 1;
		$params['order'] = 'post_dt '.$order.', P.post_id '.$order;
		$params['sql'] =
		'AND ( '.
		"	(post_dt = '".$this->con->escape($dt)."' AND P.post_id ".$sign." ".$post_id.") ".
		"	OR post_dt ".$sign." '".$this->con->escape($dt)."' ".
		') ';
		
		$rs = $this->core->blog->getPosts($params);
		$rs->extend('rsExtGallery');
		if ($rs->isEmpty()) {
			return null;
		}
		
		return $rs;
	}

}
?>
