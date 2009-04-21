<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiBlogSearch, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class multiBlogSearch extends dcBlog
{
	protected $core;
	protected $where_clause;

	public function __construct(&$core)
	{
		$this->core =& $core;

		$rs = $this->core->con->select('SELECT * FROM '.$this->core->prefix.'blog WHERE blog_status = "1"');
		$sb = array();
		while ($rs->fetch()) {
			$s = new dcSettings($this->core,$rs->blog_id);
			if ($s->multiblogsearch_enabled) {
				$sb[] = "P.blog_id = '".$rs->blog_id."'";
			}
			unset($s);
		}

		$this->where_clause = count($sb) > 0 ? ' ('.implode(' OR ',$sb).') ' : " P.blog_id = '' ";
	}

	public function getPosts($params = array(),$count_only = false)
	{
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
				'post_excerpt, post_excerpt_xhtml, '.
				'post_content, post_content_xhtml, post_notes, ';
			}

			if (!empty($params['columns']) && is_array($params['columns'])) {
				$content_req .= implode(', ',$params['columns']).', ';
			}

			$strReq =
			'SELECT P.post_id, P.blog_id, P.user_id, P.cat_id, post_dt, '.
			'post_tz, post_creadt, post_upddt, post_format, post_password, '.
			'post_url, post_lang, post_title, '.$content_req.
			'post_type, post_meta, post_status, post_selected, post_position, '.
			'post_open_comment, post_open_tb, nb_comment, nb_trackback, '.
			'U.user_name, U.user_firstname, U.user_displayname, U.user_email, '.
			'U.user_url, '.
			'C.cat_title, C.cat_url, C.cat_desc, '.
			'B.blog_url, B.blog_name ';
		}

		$strReq .=
		'FROM '.$this->core->prefix.'post P '.
		'INNER JOIN '.$this->core->prefix.'user U ON U.user_id = P.user_id '.
		'LEFT OUTER JOIN '.$this->core->prefix.'blog B ON B.blog_id = P.blog_id '.
		'LEFT OUTER JOIN '.$this->core->prefix.'category C ON P.cat_id = C.cat_id ';

		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}

		$strReq .= "WHERE ".$this->where_clause;

		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$strReq .= 'AND ((post_status = 1 ';

			if ($this->core->blog->without_password) {
				$strReq .= 'AND post_password IS NULL ';
			}
			$strReq .= ') ';

			if ($this->core->auth->userID()) {
				$strReq .= "OR P.user_id = '".$this->con->escape($this->core->auth->userID())."')";
			} else {
				$strReq .= ') ';
			}
		}

		# Adding parameters
		if (isset($params['post_type']))
		{
			if (is_array($params['post_type']) && !empty($params['post_type'])) {
				$strReq .= 'AND post_type '.$this->core->con->in($params['post_type']);
			} elseif ($params['post_type'] != '') {
				$strReq .= "AND post_type = '".$this->core->con->escape($params['post_type'])."' ";
			}
		}
		else
		{
			$strReq .= "AND post_type = 'post' ";
		}

		if (!empty($params['post_id'])) {
			if (is_array($params['post_id'])) {
				array_walk($params['post_id'],create_function('&$v,$k','if($v!==null){$v=(integer)$v;}'));
			} else {
				$params['post_id'] = array((integer) $params['post_id']);
			}
			$strReq .= 'AND P.post_id '.$this->core->con->in($params['post_id']);
		}

		if (!empty($params['post_url'])) {
			$strReq .= "AND post_url = '".$this->core->con->escape($params['post_url'])."' ";
		}

		if (!empty($params['user_id'])) {
			$strReq .= "AND U.user_id = '".$this->core->con->escape($params['user_id'])."' ";
		}

		if (!empty($params['cat_id']))
		{
			if (!is_array($params['cat_id'])) {
				$params['cat_id'] = array($params['cat_id']);
			}
			if (!empty($params['cat_id_not'])) {
				array_walk($params['cat_id'],create_function('&$v,$k','$v=$v." ?not";'));
			}
			$strReq .= 'AND '.$this->core->blog->getPostsCategoryFilter($params['cat_id'],'cat_id').' ';
		}
		elseif (!empty($params['cat_url']))
		{
			if (!is_array($params['cat_url'])) {
				$params['cat_url'] = array($params['cat_url']);
			}
			if (!empty($params['cat_url_not'])) {
				array_walk($params['cat_url'],create_function('&$v,$k','$v=$v." ?not";'));
			}
			$strReq .= 'AND '.$this->core->blog->getPostsCategoryFilter($params['cat_url'],'cat_url').' ';
		}

		/* Other filters */
		if (isset($params['post_status'])) {
			$strReq .= 'AND post_status = '.(integer) $params['post_status'].' ';
		}

		if (isset($params['post_selected'])) {
			$strReq .= 'AND post_selected = '.(integer) $params['post_selected'].' ';
		}

		if (!empty($params['post_year'])) {
			$strReq .= 'AND '.$this->core->con->dateFormat('post_dt','%Y').' = '.
			"'".sprintf('%04d',$params['post_year'])."' ";
		}

		if (!empty($params['post_month'])) {
			$strReq .= 'AND '.$this->core->con->dateFormat('post_dt','%m').' = '.
			"'".sprintf('%02d',$params['post_month'])."' ";
		}

		if (!empty($params['post_day'])) {
			$strReq .= 'AND '.$this->core->con->dateFormat('post_dt','%d').' = '.
			"'".sprintf('%02d',$params['post_day'])."' ";
		}

		if (!empty($params['post_lang'])) {
			$strReq .= "AND P.post_lang = '".$this->core->con->escape($params['post_lang'])."' ";
		}

		if (!empty($params['search']))
		{
			$words = text::splitWords($params['search']);
	
			if (!empty($words))
			{
				# --BEHAVIOR-- corePostSearch
				if ($this->core->hasBehavior('corePostSearch')) {
					$this->core->callBehavior('corePostSearch',$this->core,array(&$words,&$strReq,&$params));
				}

				if ($words)
				{
					foreach ($words as $i => $w) {
						$words[$i] = "post_words LIKE '%".$this->core->con->escape($w)."%'";
					}
					$strReq .= 'AND '.implode(' AND ',$words).' ';
				}
			}
		}

		if (!empty($params['sql'])) {
			$strReq .= $params['sql'].' ';
		}

		if (!$count_only)
		{
			if (!empty($params['order'])) {
				$strReq .= 'ORDER BY '.$this->core->con->escape($params['order']).' ';
			} else {
				$strReq .= 'ORDER BY post_dt DESC, blog_name ASC ';
			}
			$strReq .= ', post_dt DESC ';
		}

		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->core->con->limit($params['limit']);
		}

		$rs = $this->core->con->select($strReq);
		$rs->core = $this->core;
		$rs->_nb_media = array();
		$rs->extend('rsExtPost');

		# --BEHAVIOR-- coreBlogGetPosts
		$this->core->callBehavior('coreBlogGetPosts',$rs);

		return $rs;
	}

	public function firstPostOfBlog(&$rs)
	{
		if ($rs->index() == 0) {
			return true;
		}
		$cblog = $rs->blog_id;
		$rs->movePrev();
		$nblog = $rs->blog_id;
		$rs->moveNext();
		return $nblog != $cblog;
	}

	public static function paginationURL($offset = 0)
	{
		global $core;

		$args = $_SERVER['URL_REQUEST_PART'];

		$n = context::PaginationPosition($offset);

		$args = preg_replace('#(^|/)page/([0-9]+)$#','',$args);

		$url = $GLOBALS['core']->blog->url;

		if ($n > 1) {
			$url = preg_replace('#/$#','',$url);
			$url .= '/page/'.$n;
		}

		# If multiblogsearch param
		if (!empty($_GET['qm'])) {
			$s = strpos($url,'?') !== false ? '&amp;' : '?';
			$url .= $s.'qm='.rawurlencode($_GET['qm']);
		}

		return $url;
	}
}

?>