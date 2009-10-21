<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2004-2008 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

require_once (dirname(__FILE__).'/class.dc.rs.gallery.php');
require_once (dirname(__FILE__).'/class.metaplus.php');

/**
 * Main Gallery class
 *
 * Handles database back-end requests to retrieve or update images as well as posts
 * 
 * @uses dcMedia
 * @package Gallery
 */
class dcGallery extends dcMedia
{
	/** @var array orderby order combo list for galeries */	
	public $orderby;
	/** @var array sortby sort combo list for galeries */	
	public $sortby;

	/** @var boolean without_password Disallow entries password protection */	
	public $without_password = true;

	/**
	 * Constructor
	 *
	 * Create a new gallery manager
	 * 
	 * @param dcCore $core the core object
	 * @access public
	 * @return void
	 */
	public function __construct($core)
	{
		parent::__construct($core);
		$this->orderby= array(__('Date') => 'P.post_dt',
		 __('Title') => 'P.post_title',
		 __('Filename') => 'M.media_file',
		 __('URL') => 'P.post_url'
		);
		$this->sortby = array(__('Ascending') => 'ASC',
		__('Descending') => 'DESC' );
	}
	
	/**
	 * getGalleries 
	 * 
	 * Retrieves galleries from database.
	 *
	 * @param array $params  gallery parameters (see dcBlog->getPosts for available parameters)
	 * @param boolean $count_only  only count results
	 * @access public
	 * @return void
	 */
	public function getGalleries ($params=array(), $count_only=false) {
		$params['post_type']='gal';
		$rs= $this->core->blog->getPosts($params,$count_only);
		$rs->extend('rsExtGallery');
		return $rs;
	}

	/**
	 * getGalItems
	 * 
	 * Retrieves gallery items from database (simple select, no flourishes).
	 * @see getGalImageMedia for enhanced requests
	 *
	 * @param array $params  gallery parameters (see dcBlog->getPosts for available parameters)
	 * @param boolean $count_only  only count results
	 * @access public
	 * @return void
	 */
	public function getGalItems ($params=array(), $count_only=false) {
		$params['post_type']='galitem';
		$rs=$this->core->blog->getPosts($params,$count_only);
		$rs->extend('rsExtImage');
		return $rs;
	}

	/**
	 * getGalTheme
	 * 
	 * Retrieves gallery theme from gallery metadata
	 * Theme can be different according to context
	 * a wished theme can be specified, but it will be checked before
	 *
	 * @param record $gal the gallery record
	 * @param string $context context to fetch theme from (integ, gal) 
	 * @param string $wished wished theme (theme switcher, for instance)
	 * @access public
	 * @return string the gallery theme name
	 */
	public function getGalTheme ($gal,$context='gal',$wished=null) {
		if ($wished != null) {
			if ($this->themeExists($wished))
				return $wished;
		}
		$meta = $this->core->meta->getMetaArray($gal->post_meta);
		if ($context == 'gal') {
			if (isset($meta['galtheme']))
				return $meta['galtheme'][0];
			return $this->core->blog->settings->gallery->gallery_default_theme;
		} else {
			if (isset($meta['galtheme'.$context])) {
				$theme = $meta['galtheme'.$context][0];
			} else {
				$theme =  $this->core->blog->settings->gallery->gallery_default_integ_theme;
			}

			if ($theme == 'sameasgal') {
				if (isset($meta['galtheme']))
					return $meta['galtheme'][0];
				else
					return $this->core->blog->settings->gallery->gallery_default_theme;

			} else {
				return $theme;
			}
		}
	}

	/**
	 * getAllGalleryComments
	 * 
	 * Retrieves number of comments/trackbacks for all images from a gallery.
	 *
	 * @param string $gal_id the gallery id, null to fetch for all galeries
	 * @access public
	 * @return recordSet the retrieved recordset
	 */
	public function getAllGalleryComments($gal_id=null) {
		$prefix=$this->core->prefix;
		$strReq = "SELECT G.post_id, COALESCE(SUM(I.nb_comment),0) as nb_comment, ".
			"COALESCE(SUM(I.nb_trackback),0) as nb_trackback ".
			"FROM ".$prefix."post G ".
			"LEFT JOIN (".$prefix."meta M ";
		if (DC_DBDRIVER == 'pgsql')
			$strReq .= "INNER JOIN ".$prefix."post I ON I.post_id=M.meta_id::bigint AND I.post_type='galitem') ";
		else
			$strReq .= "INNER JOIN ".$prefix."post I ON I.post_id=M.meta_id AND I.post_type='galitem') ";

		$strReq .= "ON M.post_id=G.post_id AND M.meta_type='galitem' ".
			"WHERE G.post_type='gal' and G.blog_id = '".$this->con->escape($this->core->blog->id)."' ";
		if ($gal_id != null)
			$strReq .= "AND G.post_id='".$this->con->escape($gal_id)."' ";
		$strReq .= "GROUP BY G.post_id,G.post_type";
		$rs = $this->con->select($strReq);
		return $rs;
	}

	/**
	 * withoutPassword 
	 * Disallows entries password protection. You need to set it to
	 * <var>false</var> while serving a public blog.
	 * 
	 * @param boolean $v true to disallow password
	 * @access public
	 * @return void
	 */
	public function withoutPassword($v)
	{
		$this->without_password = (boolean) $v;
	}

	/** 
		### GALLERY FILTERS RETRIEVAL ###
	*/

	/**
	 * getGalFilters 
	 *
	 * Retrieve all gallery filters definition from a gallery recordset
	 * 
	 * @param record $rs the record to retrieve filters from
	 * @access public
	 * @return void
	 */
	public function getGalFilters($rs) {
		$meta = $this->core->meta->getMetaArray($rs->post_meta);
		$filters = array();
		$filtered=false;
		if (isset($meta['galrecursedir'])) {
			$filters['recurse_dir']=$meta['galrecursedir'][0];
			$filtered=true;
		}
		if (isset($meta['galsubcat'])) {
			$filters['sub_cat']=$meta['galsubcat'][0];
			$filtered=true;
		}
		if (isset($meta['galmediadir'])) {
			$filters['media_dir']=$meta['galmediadir'];
			$filtered=true;
		}
		if (isset($meta['galcat'])) {
			$filters['cat_id']=$meta['galcat'][0];
			$filtered=true;
		}
		if (isset($meta['galtag'])) {
			$filters['tag']=$meta['galtag'][0];
			$filtered=true;
		}
		if (isset($meta['galuser'])) {
			$filters['user_id']=$meta['galuser'][0];
			$filtered=true;
		}
		$filters['filter_enabled']=$filtered;
		return $filters;
	}

	/**
	 * getGalOrder
	 *
	 * Retrieve all gallery ordering definition from a gallery recordset
	 * 
	 * @param record $rs the record to retrieve filters from
	 * @access public
	 * @return void
	 */
	public function getGalOrder($rs) {
		$meta = $this->core->meta->getMetaArray($rs->post_meta);
		$order = array();
		if (isset($meta['galorderby'])){
			$order['orderby']=$meta['galorderby'][0];
		} else {
			$order['orderby']="P.post_dt";
		}
		if (isset($meta['galsortby'])){
			$order['sortby']=$meta['galsortby'][0];
		} else {
			$order['sortby']="ASC";
		}
		return $order;
	}


	/**
	 * getGalOrder
	 *
	 * Retrieve all gallery filters and ordering definition from a gallery
	 * recordset.
	 * 
	 * @param record $rs the record to retrieve filters from
	 * @access public
	 * @return void
	 */
	public function getGalParams($rs) {
		return array_merge($this->getGalFilters($rs),$this->getGalOrder($rs));
	}


	/** 
		### IMAGES RETRIEVAL ###
	*/

	/**
	 * getGalImageMedia 
	 * 
	 * Retrieve all media & posts associated to a gallery id. 
	 * <b>$params</b> is an array taking one of the following parameters :
	 * - no_content : dont retrieve entry contents (ie image description)	
	 * - post_type: Get only entries with given type (default "post")
	 * - post_id: (integer) Get entry with given post_id
	 * - post_url: Get entry with given post_url field
	 * - user_id: (integer) Get entries belonging to given user ID
	 * - post_status: (integer) Get entries with given post_status
	 * - post_selected: (boolean) Get select flaged entries
	 * - post_year: (integer) Get entries with given year
	 * - post_month: (integer) Get entries with given month
	 * - post_day: (integer) Get entries with given day
	 * - post_lang: Get entries with given language code
	 * - search: Get entries corresponding of the following search string
	 * - sql: Append SQL string at the end of the query
	 * - from: Append SQL string after "FROM" statement in query
	 * - order: Order of results (default "ORDER BY post_dt DES")
	 * - limit: Limit parameter
	 *
	 * @param array $params  parameters (see above for values)
	 * @param mixed $count_only  only count items
	 * @access public
	 * @return record a recordset
	 */
	public function getGalImageMedia ($params=array(),$count_only=false) {
		
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
			'P.post_type, P.post_meta, P.post_status, P.post_selected, '.
			'P.post_open_comment, P.post_open_tb, P.nb_comment, P.nb_trackback, '.
			'U.user_name, U.user_firstname, U.user_displayname, U.user_email, '.
			'U.user_url, '.
			'C.cat_title, C.cat_url ';
			$fields.=", M.media_file, M.media_id, M.media_path, M.media_title, M.media_meta, M.media_dt, M.media_creadt, M.media_upddt, M.media_private, M.media_dir ";
			
			$strReq = 'SELECT '.$fields;
		}
		
		$strReq .=
 		'FROM '.$this->core->prefix.'post P ';

		# Cut asap request with gallery id if requested
		if (!empty($params['gal_id']) || !empty($params['gal_url'])) {
			$strReq .= 'INNER JOIN '.$this->core->prefix.'meta GM ';
			if (DC_DBDRIVER == 'pgsql')
				$strReq .= 'on GM.meta_type=\'galitem\' AND P.post_id=GM.meta_id::bigint ';
			else
				$strReq .= 'on GM.meta_type=\'galitem\' AND P.post_id=GM.meta_id ';
			$strReq .= 'INNER JOIN '.$this->core->prefix.'post G '.
			'on GM.post_id = G.post_id AND G.post_type=\'gal\' ';
			if (!empty($params['gal_id'])) {
				$strReq .= 'AND G.post_id=\''.$this->con->escape($params['gal_id']).'\' '; 
			} else {
				$strReq .= 'AND G.post_url=\''.$this->con->escape($params['gal_url']).'\' '; 
			}
		}


		$strReq .=
		'INNER JOIN '.$this->core->prefix.'user U ON U.user_id = P.user_id and P.post_type=\'galitem\' '.
		'LEFT JOIN '.$this->core->prefix.'category C ON P.cat_id = C.cat_id '.
		'INNER JOIN '.$this->core->prefix.'post_media PM ON P.post_id = PM.post_id '.
		'INNER JOIN '.$this->core->prefix.'media M on M.media_id = PM.media_id ';
	
				
		if (!empty($params['tag'])) {
			$strReq .= 'INNER JOIN '.$this->core->prefix.'meta PT on P.post_id=PT.post_id and PT.meta_type=\'tag\' and PT.meta_id=\''
				.$this->con->escape($params['tag']).'\' ';
		}
		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}
		
		$strReq .=
		"WHERE P.blog_id = '".$this->con->escape($this->core->blog->id)."' ";
		
		if (!empty($params['gal_id']))
			$strReq .= " AND G.post_id='".$this->con->escape($params['gal_id'])."' ";
		if (!empty($params['gal_url']))
			$strReq .= " AND G.post_url='".$this->con->escape($params['gal_url'])."' ";

		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$strReq .= 'AND ((P.post_status = 1 ';
			
			if ($this->without_password) {
				$strReq .= 'AND P.post_password IS NULL ';
			}
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
		if (!empty($params['user_id'])) {
			$strReq .= "AND U.user_id = '".$this->con->escape($params['user_id'])."' ";
		}
		if (!empty($params['media_dir'])) {
			if (!is_array($params['media_dir'])) {
				$params['media_dir'] = array($params['media_dir']);
			}
			if (!empty($params['recurse_dir'])) {
				if ($params['media_dir'][0] != '.') {
					$strReq .= "AND ( M.media_dir = '".$this->con->escape($params['media_dir'][0])."' ";
					$strReq .= "     OR M.media_dir LIKE '".$this->con->escape($params['media_dir'][0])."/%') ";
					}
			} else {
				$strReq .= "AND M.media_dir ".$this->con->in($params['media_dir'])." ";
			}
		}

		/* Categories filters */
		$cat_subcond = '';
		$cat_cond = '';
		$cat_not = false;
		if (!empty($params['cat_id']))
		{
			$cat_not = !empty($params['cat_id_not']);
			
			if (is_array($params['cat_id'])) {
				array_walk($params['cat_id'],create_function('&$v,$k','if($v!==null){$v=(integer)$v;}'));
			} else {
				$params['cat_id'] = array((integer) $params['cat_id']);
			}
			
			if (empty($params['sub_cat'])) {
				$cat_cond = 'P.cat_id '.$this->con->in($params['cat_id']);
			} else {
				$cat_subcond = 'cat_id '.$this->con->in($params['cat_id']);
			}
		}
		elseif (!empty($params['cat_url']))
		{
			$cat_not = !empty($params['cat_url_not']);
			
			if (is_array($params['cat_url'])) {
				array_walk($params['cat_url'],create_function('&$v,$k','$v=(string)$v;'));
			} else {
				$params['cat_url'] = array((string) $params['cat_url']);
			}
			
			if (empty($params['sub_cat'])) {
				$cat_cond = 'C.cat_url '.$this->con->in($params['cat_url']);
			} else {
				$cat_subcond = 'cat_url '.$this->con->in($params['cat_url']);
			}
		}
		
		if ($cat_subcond) # we want posts from given categories and their children
		{
			$rs = $this->con->select(
				'SELECT cat_lft, cat_rgt FROM '.$this->core->prefix.'category '.
				"WHERE blog_id = '".$this->con->escape($this->core->blog->id)."' ".
				'AND '.$cat_subcond
			);
			$cat_borders = array();
			while ($rs->fetch()) {
				$cat_borders[] = '(C.cat_lft BETWEEN '.$rs->cat_lft.' AND '.$rs->cat_rgt.')';
			}
			if (count($cat_borders) > 0) {
				$strReq .= ' AND '.($cat_not ? ' NOT' : '').'(P.cat_id IS NOT NULL AND('.implode(' OR ',$cat_borders).')) ';
			}
		} elseif ($cat_cond) { # without children
			$strReq .= ' AND '.($cat_not ? ' NOT' : '').'(P.cat_id IS NOT NULL AND '.$cat_cond.') ';
		}

		if (isset($params['post_status'])) {
			$strReq .= 'AND post_status = '.(integer) $params['post_status'].' ';
		}
		
		if (isset($params['post_selected'])) {
			$strReq .= 'AND post_selected = '.(integer) $params['post_selected'].' ';
		}
		
		if (!empty($params['post_year'])) {
			$strReq .= 'AND '.$this->con->dateFormat('post_dt','%Y').' = '.
			"'".sprintf('%04d',$params['post_year'])."' ";
		}
		
		if (!empty($params['post_month'])) {
			$strReq .= 'AND '.$this->con->dateFormat('post_dt','%m').' = '.
			"'".sprintf('%02d',$params['post_month'])."' ";
		}
		
		if (!empty($params['post_day'])) {
			$strReq .= 'AND '.$this->con->dateFormat('post_dt','%d').' = '.
			"'".sprintf('%02d',$params['post_day'])."' ";
		}
		
		if (!empty($params['post_lang'])) {
			$strReq .= "AND P.post_lang = '".$this->con->escape($params['post_lang'])."' ";
		}
                if (!empty($params['sql'])) {
                        $strReq .= $params['sql'].' ';
                }


		if (!$count_only)
		{
			if (!empty($params['order'])) {
				$order = $this->con->escape($params['order']);
			} else if (!empty($params['orderby']) && !empty($params['sortby'])) {
				$order = $this->con->escape($params['orderby']).' '.
					$this->con->escape($params['sortby']);
			} else {
				$order = "P.post_dt ASC, P.post_id ASC";
			}

			//$strReq .= 'GROUP BY '.$fields.' ';
			$strReq .= 'ORDER BY '.$order;
			
		}
		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->con->limit($params['limit']);
		}
		if (!empty($params['debug']))
			echo "*** DEBUG SQL : [".$strReq."]";
		$rs = $this->con->select($strReq);
		$rs->core = $this->core;

		$rs->_nb_media = array();
		$rs->extend('rsExtPost');
				
		
		# --BEHAVIOR-- coreBlogGetPosts
		$this->core->callBehavior('coreBlogGetPosts',$rs);
		$rs->extend('rsExtImage');
		return $rs;

	
	}


	/** 
		### IMAGES & MEDIA MAINTENANCE RETRIEVAL ###
	*/

	/**
	 * getMediaWithoutGalItems 
	 *
	 * Retrieve media items not associated to a image post in a given directory
	 * 
	 * @param string $media_dir  mediai directory to parse
	 * @param boolean $subdirs if true, include subdirectories
	 * @access public
	 * @return record the recordset
	 */
	function getMediaWithoutGalItems($media_dir,$subdirs=false) {
		$strReq = 'SELECT M.media_id, M.media_dir, M.media_file '.
			'FROM '.$this->core->prefix.'media M '.
			'LEFT JOIN ('.
				'SELECT PM.post_id,PM.media_id FROM '.$this->core->prefix.'post_media PM '.
				'INNER JOIN '.$this->core->prefix.'post P '.
				'ON PM.post_id = P.post_id '.
				'AND P.blog_id = \''.$this->con->escape($this->core->blog->id).'\' '.
				'AND P.post_type = \'galitem\') PM2 '.
			'ON M.media_id = PM2.media_id '.
			'WHERE PM2.post_id IS NULL ';
		if ($subdirs) {
			if ($media_dir != '.') {
				$strReq .= "AND ( M.media_dir = '".$this->con->escape($media_dir)."' ";
				$strReq .= "     OR M.media_dir LIKE '".$this->con->escape($media_dir)."/%') ";
			}
		} else {
			$strReq .= 'AND media_dir = \''.$this->con->escape($media_dir).'\'';
		}
		$rs = $this->con->select($strReq);
		return $rs;
	}

	/**
	 * getImageFromMedia 
	 * 
	 * Retrieve image from a given media_id
	 *
	 * @param string $media_id the media id
	 * @access public
	 * @return record the recordset
	 */
	function getImageFromMedia($media_id) {
		$strReq =
		'SELECT P.post_id '.
		'FROM '.$this->core->prefix."post P, ".$this->table.' M, '.$this->table_ref.' PM '.
		"WHERE P.post_id = PM.post_id AND M.media_id = PM.media_id ".
		"AND M.media_id = '".$media_id."' AND P.post_type='galitem' ".
		"AND P.blog_id = '".$this->con->escape($this->core->blog->id)."' ";
		
		$rs = $this->con->select($strReq);
		
		$res = array();
		
		while ($rs->fetch()) {
			$res[] = $rs->post_id;
		}
		
		return $res;
	}

	/**
	 * getNewMedia 
	 * 
	 * Retrieve media not yet created in a given directory
	 *
	 * @param mixed $media_dir  the media directory to scan
	 * @access public
	 * @return array list of new media file names
	 */
	function getNewMedia($media_dir) {
		$strReq =
		'SELECT media_file, media_id, media_path, media_title, media_meta, media_dt, '.
		'media_creadt, media_upddt, media_private, user_id '.
		'FROM '.$this->table.' '.
		"WHERE media_path = '".$this->path."' ".
		"AND media_dir = '".$this->con->escape($media_dir)."' ";
		
		if (!$this->core->auth->check('media_admin',$this->core->blog->id))
		{
			$strReq .= 'AND (media_private <> 1 ';
			
			if ($this->core->auth->userID()) {
				$strReq .= "OR user_id = '".$this->con->escape($this->core->auth->userID())."'";
			}
			$strReq .= ') ';
		}
		
		$strReq .= 'ORDER BY LOWER(media_file) ASC';
		
		$rs = $this->con->select($strReq);
		
		$this->chdir($media_dir);
		filemanager::getDir();
		
		$p_dir = $this->dir;
		
		$f_reg = array();
		$res=array();
		
		while ($rs->fetch())
		{
			# File in subdirectory, forget about it!
			if (dirname($rs->media_file) != '.' && dirname($rs->media_file) != $this->relpwd) {
				continue;
			}
			
			if ($this->inFiles($rs->media_file))
			{
				$f_reg[$rs->media_file] = 1;
			}
		}
		
		
		# Check files that don't exist in database and create them
		foreach ($p_dir['files'] as $f)
		{
			if (!isset($f_reg[$f->relname])) {
				$res[]=$f->basename;
			}
		}
		return $res;
	}


	/**
	 * getMediaWithoutThumbs 
	 *
	 * Retrieve media with no thumbnails in the given directory
	 * 
	 * @param mixed $media_dir  the media directory to scan
	 * @access public
	 * @return array the list of media IDs
	 */
	function getMediaWithoutThumbs($media_dir) {
		$this->chdir($media_dir);
		$this->getDir('image');
		$dir =& $this->dir;
		$items=array_values($dir['files']);
		$ids=array();
		foreach ($items as $item) {
			if (empty($item->media_thumb)) {
				$ids[$item->media_id]=$item->basename;
			} else {
				foreach ($this->thumb_sizes as $suffix => $s) {
					if (empty($item->media_thumb[$suffix])) {
						$ids[$item->media_id]=$item->basename;
						continue(2);
					}
				}
			}
		}
		return $ids;
	}

	/** 
		### IMAGES & MEDIA ORPHANS REMOVAL ###
	*/

	/**
	 * deleteOrphanMedia 
	 *
	 * Delete media entries with no physical files associated
	 * 
	 * @param string $media_dir the media directory to scan
	 * @access public
	 * @return void
	 */
	function deleteOrphanMedia($media_dir) {
		$strReq =
		'SELECT media_file, media_id, media_path, media_title, media_meta, media_dt, '.
		'media_creadt, media_upddt, media_private, user_id '.
		'FROM '.$this->table.' '.
		"WHERE media_path = '".$this->path."' ".
		"AND media_dir = '".$this->con->escape($media_dir)."' ";
		
		if (!$this->core->auth->check('media_admin',$this->core->blog->id))
		{
			$strReq .= 'AND (media_private <> 1 ';
			
			if ($this->core->auth->userID()) {
				$strReq .= "OR user_id = '".$this->con->escape($this->core->auth->userID())."'";
			}
			$strReq .= ') ';
		}
		$rs = $this->con->select($strReq);
		while ($rs->fetch())
		{
			if (!file_exists($this->pwd."/".$rs->media_file)) {
				# Physical file does not exist remove it from DB
				# Because we don't want to erase everything on
				# dotclear upgrade, do it only if there are files
				# in directory and directory is root
				$this->con->execute(
					'DELETE FROM '.$this->table.' '.
					"WHERE media_path = '".$this->con->escape($this->path)."' ".
					"AND media_file = '".$this->con->escape($rs->media_file)."' "
				);
			}
		}
	}

	/**
	 * deleteOrphanItems 
	 * 
	 * Delete Items no more associated to media
	 *
	 * @access public
	 * @return void
	 */
	function deleteOrphanItems() {
		if (!$this->core->auth->check('usage',$this->core->blog->id)) {
			return;
		}
		$strReq = 'SELECT P.post_id,P.post_title '.
		'FROM '.$this->core->prefix.'post P '.
			'LEFT JOIN '.$this->core->prefix.'post_media PM ON P.post_id = PM.post_id '.
			'WHERE PM.media_id IS NULL AND P.post_type=\'galitem\' AND P.blog_id = \''.$this->con->escape($this->core->blog->id).'\'';
		$rs = $this->con->select($strReq);
		$count=0;
		while ($rs->fetch()) {
			try {
				$this->core->blog->delPost($rs->post_id);
				$count++;
			} catch (Exception $e) {
				// Ignore rights problems ...
			}
		}
		return $count;
	}

	/** 
		### IMAGES & MEDIA CREATION ###
	*/
	/**
	 * getExifDate 
	 *
	 * Returns the exif date from media metadata
	 * 
	 * @param simpleXML $meta metadata
	 * @param date $default default date to return if exif date is not readable
	 * @access public
	 * @return string the exif date (converted to string)
	 */
	function getExifDate($meta,$default) {
		$post_dt=$default;
		if ($meta !== false){
			if (count($meta->xpath('DateTimeOriginal'))) {
				if ($meta->DateTimeOriginal != '') {
					$media_ts = strtotime($meta->DateTimeOriginal);
					$o = dt::getTimeOffset($this->core->auth->getInfo('user_tz'),$media_ts);
					$post_dt = dt::str('%Y-%m-%d %H:%M:%S',$media_ts+$o);
				}
			}
		}
		return $post_dt;
	}


	/**
	 * fixImageExif 
	 * 
	 * Fix post date to media exif date
	 *
	 * @param string $img_id post-image id
	 * @access public
	 * @return void
	 */
	function fixImageExif($img_id) {
		$rs = $this->getGalImageMedia(array('post_id' => $img_id));
		if ($rs->fetch()) {
			$media=$this->readMedia($rs);
			$new_dt=$this->getExifDate($media->media_meta,$rs->post_dt);
		
			$strReq =
			"UPDATE ".$this->core->prefix."post ".
			"SET post_dt = '".$this->con->escape($new_dt)."' ".
			"WHERE post_id = '".$rs->post_id."'";
			$this->con->execute($strReq);
		}
	}

	/**
	 * createPostForMedia 
	 *
	 * Creates a new Post for a given media
	 * 
	 * @param dcMedia $media the media record
	 * @param boolean $update_timestamp if true, set the post date to the media exif date
	 * @access public
	 * @return string the new post ID
	 */
	function createPostForMedia($media,$update_timestamp=false) {
		$imgref = $this->getImageFromMedia($media->media_id);
		if (sizeof($imgref)!=0)
			return;
		if (!$media->media_image)
			return;
		
		$cur = $this->con->openCursor($this->core->prefix.'post');	
		$cur->post_type='galitem';
		if (trim($media->media_title) != '')
			$cur->post_title = $media->media_title;
		else
			$cur->post_title = basename($media->file);
		
		$cur->cat_id = null;
		$post_dt = $media->media_dtstr;
	
		if ($update_timestamp && $media->type == 'image/jpeg') {
			$post_dt = $this->getExifDate($media->media_meta,$post_dt);
		}
		$cur->post_dt = $post_dt;
		$cur->post_format = 'wiki';
		$cur->post_password = null;
		$cur->post_lang = $this->core->auth->getInfo('user_lang');
		$cur->post_excerpt = '';
		$cur->post_excerpt_xhtml = '';
		$cur->post_content = "///html\n<p></p>\n///";
		$cur->post_content_xhtml = "";
		$cur->post_notes = null;
		$cur->post_status = 1;
		$cur->post_selected = 0;
		$cur->post_open_comment = 1;
		$cur->post_open_tb = 1;
		$cur->post_url = preg_replace('/\.[^.]+$/','',substr($media->file,strlen($this->root)+1));

		
		$cur->user_id = $this->core->auth->userID();
		$return_id=0;
	
		try
		{
			$return_id = $this->core->blog->addPost($cur);
			// Attach media to post
			$this->addPostMedia($return_id,$media->media_id);
			return $return_id;
		}
		catch (Exception $e)
		{
			$this->core->error->add($e->getMessage());
			throw $e;
		}

	}

	/**
	 * removeAllPostMedia 
	 * 
	 * remove all media links from a post
	 *
	 * @param string $post_id the post id
	 * @access public
	 * @return void
	 */
	public function removeAllPostMedia($post_id) {
		$media = $this->getPostMedia($post_id);
		foreach ($media as $medium) {
			$this->removePostMedia($post_id,$medium->media_id);
		}
	}

	/**
	 * createThumbs 
	 * 
	 * creates all thumbs for a given media
	 * given its id
	 *
	 * @param string $media_id  the media id
	 * @access public
	 * @return void
	 */
	public function createThumbs($media_id) {
		$media = $this->getFile($media_id);
		if ($media == null) {
			throw new Exception (__('Media not found'));
		}
		$this->chdir(dirname($media->relname));
		if ($media->media_type == 'image')
			$this->imageThumbCreate($media,$media->basename);
	}


	/**
	 * getNextGallery 
	 * 
	 * Returns a record with post id, title and date for next or previous post
	 * according to the post ID and timestamp given.
	 * $dir can be 1 (next post) or -1 (previous post).
	 *
	 * @param integer $post_id  the post id
	 * @param string $ts  post timestamp
	 * @param integer $dir search direction
	 * @access public
	 * @return record the record
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
		$params['limit'] = 1;
		$params['order'] = 'post_dt '.$order.', P.post_id '.$order;
		$params['sql'] =
		'AND ( '.
		"	(post_dt = '".$this->con->escape($dt)."' AND P.post_id ".$sign." ".$post_id.") ".
		"	OR post_dt ".$sign." '".$this->con->escape($dt)."' ".
		') ';
		
		$rs = $this->getGalleries($params);
		if ($rs->isEmpty()) {
			return null;
		}
		
		return $rs;
	}



	/**
	 * getNextGalleryItem 
	 * 
	 * retrieves next gallery item, optionnaly in a given gallery
	 *
	 * @param record $post current gallery item record
	 * @param integer $dir direction (+1 for next, -1 for previous)
	 * @param string $gal_url gallery url (null for no gallery)
	 * @param integer $nb number of items to retrieve
	 * @access public
	 * @return record the next item(s)
	 */
	public function getNextGalleryItem($post,$dir,$gal_url=null,$nb=1) {
		if ($gal_url != null) {
			$gal = $this->getGalleries(array('post_url' => $gal_url));
			$params = $this->getGalOrder($gal);
			if ($dir < 0) {
				$sign= ($params['sortby']=='ASC') ? '<':'>';
				$params['sortby'] = ($params['sortby']=='ASC') ? 'DESC' : 'ASC';
			} else {
				$sign= ($params['sortby']=='ASC') ? '>':'<';;
			}
		} else {
			if($dir > 0) {
				$sign = '>';
				$params['orderby'] = 'P.post_dt';
				$params['sortby'] = 'ASC';
			}
			else {
				$sign = '<';
				$params['orderby'] = 'P.post_dt';
				$params['sortby'] = 'DESC';
			}
		}
		$params['order'] = $this->con->escape($params['orderby']).' '.
			$this->con->escape($params['sortby']).', P.post_id '.
			$this->con->escape($params['sortby']);
		switch($params['orderby']) {
			case "P.post_dt" : 
				$field_value = $post->post_dt;
				break;
			case "P.post_title" : 
				$field_value = $post->post_title;
				break;
			case "M.media_file" : 
				$field_value = $post->media_file;
				break;
			case "P.post_url" : 
				$field_value = $post->post_url;
				break;
			default:
				$field_value = $post->post_dt;
				break;
		}
		$post_id=$post->post_id;
		$params['sql'] =
			'AND ( '.
			"	(".$params['orderby']." = '".$this->con->escape($field_value).
			"' AND P.post_id ".$sign." ".$post_id.") ".
			"	OR ".$params['orderby']." ".$sign." '".$this->con->escape($field_value)."' ".
			') ';
		$params['post_type'] = 'galitem';
		$params['limit'] = array(0,$nb);
		$params['gal_url'] = $gal_url;
		$rs = $this->getGalImageMedia($params,false);
		if ($rs->isEmpty()) {
			return null;
		}

		return $rs;
	}

	/**
	 * getGalleryItemPage 
	 * 
	 * retrieves gallery page containing an image
	 *
	 * @param string $img_url image url
	 * @param string $gal_url gallery url 
	 * @access public
	 * @return int the page
	 */
	public function getGalleryItemPage($img_url,$gal) {
		$params=$this->getGalOrder($gal);
		$params['gal_url'] = $gal->post_url;
		$params['no_content'] = true;
		//$params['debug'] = true;
		$rs = $this->getGalImageMedia($params,false);
		$pos=0;
		while ($rs->fetch() && $rs->post_url !== $img_url) {
		 	$pos++;
		}
		if ($rs->post_url !== $img_url || $this->core->blog->settings->gallery->gallery_nb_images_per_page == 0)
			return 0;
		return (integer)($pos/$this->core->blog->settings->gallery->gallery_nb_images_per_page)+1;
	}

	/**
	 * getGalItemCount 
	 * 
	 * Returns the number of items from a gallery
	 *
	 * @param record $rs the gallery record
	 * @access public
	 * @return int the image count
	 */
	public function getGalItemCount($rs) {
		$image_ids = $this->core->meta->getMetaArray($rs->post_meta);
		$nb_images=isset($image_ids['galitem'])?sizeof($image_ids['galitem']):0;
		return $nb_images;
	}

	/**
	 * readMedia 
	 * 
	 * Reads a media from a record in database
	 *
	 * @param record $rs  the media record
	 * @access public
	 * @return record the decorated record
	 */
	public function readMedia ($rs) {
		return $this->fileRecord($rs);
	}


	/**
	 * getImageGalleries 
	 * 
	 * Retrives the list of galleries containinng a given image
	 *
	 * @param integer $img_id the image id
	 * @access public
	 * @return record the galleries record
	 */
	public function getImageGalleries($img_id) {
		$params=array();
		$params["meta_id"]=$img_id;
		$params["meta_type"]='galitem';
		$params["post_type"]='gal';
		$gals = $this->core->meta->getPostsByMeta($params);
		$gals->extend('rsExtGallery');
		return $gals;
	}

	// Refresh a gallery with its images (for performance purpose)
	/**
	 * refreshGallery 
	 * 
	 * Refreshes a gallery content, from its filters
	 *
	 * @param integer $gal_id  the gallery id
	 * @access public
	 * @return void
	 */
	public function refreshGallery($gal_id) {

		// Step 1 : retrieve current gallery items
		$params['post_id'] = $gal_id;
		$params['no_content'] = 0;
		$gal = $this->getGalleries($params);
		$metaplus = new MetaPlus ($this->core);
		
		$meta = $this->core->meta->getMetaArray($gal->post_meta);
		if (isset($meta['galitem']))
			$current_ids = $meta['galitem'];
		else
			$current_ids = array();

		// Step 2 : retrieve expected gallery items
		$new_ids=array();
		$params = $this->getGalParams($gal);
		if ($params['filter_enabled']) {
			$params['no_content']=1;
			$rs = $this->getGalImageMedia($params);
			while ($rs->fetch()) {
				$new_ids[]=$rs->post_id;
			}
		}

		sort($current_ids);
		sort($new_ids);

		//print_r($current_ids);
		// Step 3 : find out items to add and items to remove
		$ids_to_add=array_diff($new_ids,$current_ids);
		$ids_to_remove = array_diff($current_ids,$new_ids);

		// Perform database operations (number limited due to php timeouts)
		$template_insert = array(
			'post_id' => (integer) $gal_id,
			'meta_id' => 0,
			'meta_type'=>'galitem');
		$before_insert = '('.(integer)$gal_id.',';
		$after_insert = ",'galitem')";

		$inserts=array();
		foreach ($ids_to_add as $id) {
			$inserts[]=array('post_id' => (integer) $gal_id,
				'meta_id' => $id,
				'meta_type' => 'galitem');
		}
		if (sizeof($inserts) != 0) {
			$metaplus->massSetPostMeta ($inserts);
		}
		if (sizeof($ids_to_remove) != 0) {
			$metaplus->massDelPostMeta($gal_id, 'galitem',$ids_to_remove);
		}
		return false;
	}

	/**
	 * addImage 
	 * 
	 * adds an image to a gallery
	 *
	 * @param integer $gal_id  gallery id
	 * @param integer $img_id image id
	 * @access public
	 * @return void
	 */
	public function addImage($gal_id,$img_id) {
		$this->core->meta->delPostMeta($gal_id,'galitem',$img_id);
		$this->core->meta->setPostMeta($gal_id,'galitem',$img_id);
	}

	/**
	 * unlinkImage 
	 * 
	 * remove an image from a gallery.
	 *
	 * @param integer $gal_id  gallery id
	 * @param integer $img_id image id
	 * @access public
	 * @return void
	 */
	public function unlinkImage($gal_id,$img_id) {
		$this->core->meta->delPostMeta($gal_id,'galitem',$img_id);
	}

	/**
	 * getRandomImage 
	 * 
	 * Retrieves a random image
	 *
	 * @param array $params_in retrieval parameters 
	 *	(see getRandomImage for details)
	 * @access public
	 * @return record The random image record
	 */
	public function getRandomImage($params_in=null) {
		$params=$params_in;
		$count = $this->getGalImageMedia($params,true);
		$offset = rand(0, $count->f(0) - 1);


		$params['limit']=array($offset, 1);
		return $this->getGalImageMedia($params);
	}


	/**
	 * checkThemesDir 
	 *
	 * Checks whether themes dir is valid or not
	 * 
	 * @access public
	 * @return boolean true if themes dir is valid, false otherwise
	 */
	public function checkThemesDir() {
		$themes_dir = path::fullFromRoot($this->core->blog->settings->gallery->gallery_themes_path,DC_ROOT);
		if (!is_dir($themes_dir))
			return false;
		if (!is_dir($themes_dir.'/gal_simple'))
			return false;
		return true;
	}

	/**
	 * getThemes 
	 * 
	 * Retrieves all available gallery themes
	 *
	 * @access public
	 * @return array the themes list
	 */
	public function getThemes() {
		$themes = array();
		$themes_dir = path::fullFromRoot($this->core->blog->settings->gallery->gallery_themes_path,DC_ROOT);
		if ($dh = opendir($themes_dir)) {
			while (($file = readdir($dh)) !== false) {
				if(is_dir($themes_dir.'/'.$file) && (substr($file,0,1) != '.' ) && ($file !== 'gal_feed') && strpos($file,"gal_")===0 ) {
					$name=substr($file,4);
					$themes[$name]=$name;
				}
			}
		}
		return $themes;
	}

	/**
	 * themeExists
	 * 
	 * checks whether a gallery theme exists.
	 *
	 * @param string $theme the theme to check
	 * @access public
	 * @return boolean true if theme exits, false otherwise.
	 */
	public function themeExists($theme) {
		$galtheme=basename($theme);
		if ($galtheme == "gal_feed")
			return false;
		$themes_dir = path::fullFromRoot($this->core->blog->settings->gallery->gallery_themes_path,DC_ROOT);
		$theme_path = $themes_dir.'/gal_'.$galtheme;
		return file_exists($theme_path) && is_dir($theme_path);
	}

	/**
	 * fillGalleryContext 
	 * 
	 * Prefills public context with current gallery
	 *
	 * @param dcContext $_ctx current context
	 * @access public
	 * @return void
	 */
	public function fillGalleryContext($_ctx,$theme_context='gal') {
		$gal_params = $this->core->gallery->getGalOrder($_ctx->posts);
		$gal_params["gal_url"]=$_ctx->posts->post_url;
		$_ctx->gal_params=$gal_params;
		$_ctx->gallery_url=$_ctx->posts->post_url;
		$_ctx->gallery_theme = $this->core->gallery->getGalTheme($_ctx->posts,$theme_context);
		$_ctx->prevent_recursion=true;
	}


	/**
	 * emptyGalleryContext 
	 * 
	 * Empties public context previously filled
	 *
	 * @param dcContext $_ctx current context
	 * @access public
	 * @return void
	 */
	public function emptyGalleryContext($_ctx) {
		$_ctx->prevent_recursion=false;
		$_ctx->gallery_theme=null;
		$_ctx->gallery_url = null; $_ctx->gal_params=null;
	}

	/**
	 * fillItemContext 
	 * 
	 * Prefills public context with current gallery item
	 *
	 * @param dcContext $_ctx current context
	 * @access public
	 * @return void
	 * Retrieves all available gallery themes
	 */
	public function fillItemContext($_ctx) {
		$myparams = array("post_url" => $_ctx->posts->post_url);
		$_ctx->posts = $this->getGalImageMedia($myparams); 
		unset($myparams);
		$_ctx->media = $this->readMedia($_ctx->posts);
		$_ctx->prevent_recursion=true;
	}

	/**
	 * emptyItemContext 
	 * 
	 * Empties public context previously filled
	 *
	 * @param dcContext $_ctx current context
	 * @access public
	 * @return void
	 */
	public function emptyItemContext($_ctx) {
		$_ctx->prevent_recursion=false;
		$_ctx->posts = null;
		$_ctx->media = null;
	}


}
?>
