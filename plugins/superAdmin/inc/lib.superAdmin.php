<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Super Admin, a plugin for Dotclear 2
# Copyright (C) 2009 Moe (http://gniark.net/)
#
# Super Admin is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Super Admin is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

class superAdmin
{
	/// @name Entries management methods
	//@{
	/**
	Retrieves entries. <b>$params</b> is an array taking the following
	optionnal parameters:
	
	- no_content: Don't retrieve entry content (excerpt and content)
	- post_type: Get only entries with given type (default "post", array for many types and '' for no type)
	- post_id: (integer) Get entry with given post_id
	- post_url: Get entry with given post_url field
	- user_id: (integer) Get entries belonging to given user ID
	- cat_id: (string or array) Get entries belonging to given category ID
	- cat_id_not: deprecated (use cat_id with "id ?not" instead)
	- cat_url: (string or array) Get entries belonging to given category URL
	- cat_url_not: deprecated (use cat_url with "url ?not" instead)
	- post_status: (integer) Get entries with given post_status
	- post_selected: (boolean) Get select flaged entries
	- post_year: (integer) Get entries with given year
	- post_month: (integer) Get entries with given month
	- post_day: (integer) Get entries with given day
	- post_lang: Get entries with given language code
	- search: Get entries corresponding of the following search string
	- columns: (array) More columns to retrieve
	- sql: Append SQL string at the end of the query
	- from: Append SQL string after "FROM" statement in query
	- order: Order of results (default "ORDER BY post_dt DES")
	- limit: Limit parameter
	- blog_id: Blog id
	
	Please note that on every cat_id or cat_url, you can add ?not to exclude
	the category and ?sub to get subcategories.
	
	@param	params		<b>array</b>		Parameters
	@param	count_only	<b>boolean</b>		Only counts results
	@return	<b>record</b>	A record with some more capabilities
	*/
	public static function getPosts($params=array(),$count_only=false)
	{
		global $core;
		
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
			'C.cat_title, C.cat_url, C.cat_desc '.
			', B.blog_name ';
		}
		
		$strReq .=
		'FROM '.$core->prefix.'post P '.
		'INNER JOIN '.$core->prefix.'user U ON U.user_id = P.user_id '.
		'LEFT OUTER JOIN '.$core->prefix.'blog B ON P.blog_id = B.blog_id '.
		'LEFT OUTER JOIN '.$core->prefix.'category C ON P.cat_id = C.cat_id ';
		
		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}
		
		if (!empty($params['blog_id'])) {
			$strReq .=
				"WHERE (P.blog_id ='".$core->con->escape($params['blog_id'])."') ";
		}
		else
		{
			$strReq .= "WHERE (P.blog_id IS NOT NULL) ";
		}
		
		# Adding parameters
		if (isset($params['post_type']))
		{
			if (is_array($params['post_type']) && !empty($params['post_type'])) {
				$strReq .= 'AND post_type '.$core->con->in($params['post_type']);
			} elseif ($params['post_type'] != '') {
				$strReq .= "AND post_type = '".$core->con->escape($params['post_type'])."' ";
			}
		}
		/*else
		{
			$strReq .= "AND post_type = 'post' ";
		}*/
		
		if (!empty($params['post_id'])) {
			if (is_array($params['post_id'])) {
				array_walk($params['post_id'],create_function('&$v,$k','if($v!==null){$v=(integer)$v;}'));
			} else {
				$params['post_id'] = array((integer) $params['post_id']);
			}
			$strReq .= 'AND P.post_id '.$core->con->in($params['post_id']);
		}
		
		if (!empty($params['post_url'])) {
			$strReq .= "AND post_url = '".$core->con->escape($params['post_url'])."' ";
		}
		
		if (!empty($params['user_id'])) {
			$strReq .= "AND U.user_id = '".$core->con->escape($params['user_id'])."' ";
		}
		
		if (!empty($params['cat_id']))
		{
			if (!is_array($params['cat_id'])) {
				$params['cat_id'] = array($params['cat_id']);
			}
			if (!empty($params['cat_id_not'])) {
				array_walk($params['cat_id'],create_function('&$v,$k','$v=$v." ?not";'));
			}
			$strReq .= 'AND '.$core->blog->getPostsCategoryFilter($params['cat_id'],'cat_id').' ';
		}
		elseif (!empty($params['cat_url']))
		{
			if (!is_array($params['cat_url'])) {
				$params['cat_url'] = array($params['cat_url']);
			}
			if (!empty($params['cat_url_not'])) {
				array_walk($params['cat_url'],create_function('&$v,$k','$v=$v." ?not";'));
			}
			$strReq .= 'AND '.$core->blog->getPostsCategoryFilter($params['cat_url'],'cat_url').' ';
		}
		
		/* Other filters */
		if (isset($params['post_status'])) {
			$strReq .= 'AND post_status = '.(integer) $params['post_status'].' ';
		}
		
		if (isset($params['post_selected'])) {
			$strReq .= 'AND post_selected = '.(integer) $params['post_selected'].' ';
		}
		
		if (!empty($params['post_year'])) {
			$strReq .= 'AND '.$core->con->dateFormat('post_dt','%Y').' = '.
			"'".sprintf('%04d',$params['post_year'])."' ";
		}
		
		if (!empty($params['post_month'])) {
			$strReq .= 'AND '.$core->con->dateFormat('post_dt','%m').' = '.
			"'".sprintf('%02d',$params['post_month'])."' ";
		}
		
		if (!empty($params['post_day'])) {
			$strReq .= 'AND '.$core->con->dateFormat('post_dt','%d').' = '.
			"'".sprintf('%02d',$params['post_day'])."' ";
		}
		
		if (!empty($params['post_lang'])) {
			$strReq .= "AND P.post_lang = '".$core->con->escape($params['post_lang'])."' ";
		}
		
		if (!empty($params['search']))
		{
			$words = text::splitWords($params['search']);
			
			if (!empty($words))
			{
				# --BEHAVIOR-- corePostSearch
				if ($core->hasBehavior('corePostSearch')) {
					$core->callBehavior('corePostSearch',$core,array(&$words,&$strReq,&$params));
				}
				
				if ($words)
				{
					foreach ($words as $i => $w) {
						$words[$i] = "post_words LIKE '%".$core->con->escape($w)."%'";
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
				$strReq .= 'ORDER BY '.$core->con->escape($params['order']).' ';
			} else {
				$strReq .= 'ORDER BY post_dt DESC ';
			}
		}
		
		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $core->con->limit($params['limit']);
		}
		
		$rs = $core->con->select($strReq);
		$rs->core = $core;
		$rs->_nb_media = array();
		$rs->extend('rsExtPost');
		
		# --BEHAVIOR-- coreBlogGetPosts
		$core->callBehavior('coreBlogGetPosts',$rs);
		
		return $rs;
	}
	
	/**
	Retrieves different languages and post count on blog, based on post_lang
	field. <var>$params</var> is an array taking the following optionnal
	parameters:
	
	- post_type: Get only entries with given type (default "post", '' for no type)
	- lang: retrieve post count for selected lang
	- order: order statement (default post_lang DESC)
	
	@param	params	<b>array</b>		Parameters
	@return	record
	*/
	public static function getLangs($params=array())
	{
		global $core;
		
		$strReq = 'SELECT COUNT(post_id) as nb_post, post_lang '.
				'FROM '.$core->prefix.'post '.
				"WHERE blog_id IS NOT NULL ".
				"AND post_lang <> '' ".
				"AND post_lang IS NOT NULL ";
		
		$strReq .= 'GROUP BY post_lang ';
		
		$order = 'desc';
		
		$strReq .= 'ORDER BY post_lang '.$order.' ';
		
		return $core->con->select($strReq);
	}
	
	/**
	Returns a record with all distinct blog dates and post count.
	<var>$params</var> is an array taking the following optionnal parameters:
	
	- type: (day|month|year) Get days, months or years
	- year: (integer) Get dates for given year
	- month: (integer) Get dates for given month
	- day: (integer) Get dates for given day
	- cat_id: (integer) Category ID filter
	- cat_url: Category URL filter
	- post_lang: lang of the posts
	- next: Get date following match
	- previous: Get date before match
	- order: Sort by date "ASC" or "DESC"
	
	@param	params	<b>array</b>		Parameters array
	@return	record
	*/
	public static function getDates($params=array())
	{
		global $core;
		
		$dt_f = '%Y-%m-%d';
		$dt_fc = '%Y%m%d';
		
		$dt_f .= ' 00:00:00';
		$dt_fc .= '000000';
		
		$strReq = 'SELECT DISTINCT('.$core->con->dateFormat('post_dt',$dt_f).') AS dt '.
				',COUNT(P.post_id) AS nb_post '.
				'FROM '.$core->prefix.'post P LEFT JOIN '.$core->prefix.'category C '.
				'ON P.cat_id = C.cat_id '.
				"WHERE P.blog_id = '".$core->con->escape($core->blog->id)."' ";
		
		$strReq .= 'GROUP BY dt ';
		
		$order = 'desc';
		
		$strReq .=
		'ORDER BY dt '.$order.' ';
		
		$rs = $core->con->select($strReq);
		$rs->extend('rsExtDates');
		return $rs;
	}
	
	/**
	Creates a new entry. Takes a cursor as input and returns the new entry
	ID.
	
	@param	cur		<b>cursor</b>		Post cursor
	@return	<b>integer</b>		New post ID
	*/
	public static function addPost($cur)
	{
		global $core;
		
		$core->con->writeLock($core->prefix.'post');
		try
		{
			# Get ID
			$rs = $core->con->select(
				'SELECT MAX(post_id) '.
				'FROM '.$core->prefix.'post ' 
				);
			
			$cur->post_id = (integer) $rs->f(0) + 1;
			
			$cur->post_url = $core->blog->getPostURL($cur->post_url,$cur->post_dt,$cur->post_title,$cur->post_id);
			
			$cur->insert();
			$core->con->unlock();
		}
		catch (Exception $e)
		{
			$core->con->unlock();
			throw $e;
		}
		
		$core->blog->triggerBlog();
		
		return $cur->post_id;
	}
	
	/**
	Retrieves all users having posts on current blog.
	
	@param	post_type		<b>string</b>		post_type filter (post)
	@return	record
	*/
	public static function getPostsUsers($post_type='post')
	{
		global $core;
		
		$strReq = 'SELECT P.user_id, user_name, user_firstname, '.
				'user_displayname, user_email '.
				'FROM '.$core->prefix.'post P, '.$core->prefix.'user U '.
				'WHERE P.user_id = U.user_id '.
				"AND blog_id IS NOT NULL ";
		
		if ($post_type) {
			$strReq .= "AND post_type = '".$core->con->escape($post_type)."' ";
		}
		
		$strReq .= 'GROUP BY P.user_id, user_name, user_firstname, user_displayname, user_email ';
		
		return $core->con->select($strReq);
	}
	
	/// @name Comments management methods
	//@{
	/**
	Retrieves comments. <b>$params</b> is an array taking the following
	optionnal parameters:
	
	- no_content: Don't retrieve comment content
	- post_type: Get only entries with given type (default no type, array for many types) 
	- post_id: (integer) Get comments belonging to given post_id
	- cat_id: (integer or array) Get comments belonging to entries of given category ID
	- comment_id: (integer) Get comment with given ID
	- comment_status: (integer) Get comments with given comment_status
	- comment_trackback: (integer) Get only comments (0) or trackbacks (1)
	- comment_ip: (string) Get comments with given IP address
	- post_url: Get entry with given post_url field
	- user_id: (integer) Get entries belonging to given user ID
	- q_author: Search comments by author
	- sql: Append SQL string at the end of the query
	- from: Append SQL string after "FROM" statement in query
	- order: Order of results (default "ORDER BY comment_dt DES")
	- limit: Limit parameter
	- blog_id: Blog id
	
	@param	params		<b>array</b>		Parameters
	@param	count_only	<b>boolean</b>		Only counts results
	@return	<b>record</b>	A record with some more capabilities
	*/
	public static function getComments($params=array(),$count_only=false)
	{
		global $core;
		
		if ($count_only)
		{
			$strReq = 'SELECT count(comment_id) ';
		}
		else
		{
			if (!empty($params['no_content'])) {
				$content_req = '';
			} else {
				$content_req = 'comment_content, ';
			}
			
			$strReq =
			'SELECT C.comment_id, comment_dt, comment_tz, comment_upddt, '.
			'comment_author, comment_email, comment_site, '.
			$content_req.' comment_trackback, comment_status, '.
			'comment_spam_status, comment_spam_filter, comment_ip, '.
			'P.post_title, P.post_url, P.post_id, P.post_password, P.post_type, '.
			'P.post_dt, P.user_id, U.user_email, U.user_url, '.
			'B.blog_name, B.blog_id ';
		}
		
		$strReq .=
		'FROM '.$core->prefix.'comment C '.
		'INNER JOIN '.$core->prefix.'post P ON C.post_id = P.post_id '.
		'INNER JOIN '.$core->prefix.'user U ON P.user_id = U.user_id '.
		'INNER JOIN '.$core->prefix.'blog B ON P.blog_id = B.blog_id ';
		
		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}
		
		if (!empty($params['blog_id'])) {
			$strReq .=
				"WHERE (P.blog_id ='".$core->con->escape($params['blog_id'])."') ";
		}
		else
		{
			$strReq .= "WHERE (P.blog_id IS NOT NULL) ";
		}
		
		if (!empty($params['post_type']))
		{
			if (is_array($params['post_type']) && !empty($params['post_type'])) {
				$strReq .= 'AND post_type '.$core->con->in($params['post_type']);
			} else {
				$strReq .= "AND post_type = '".$core->con->escape($params['post_type'])."' ";
			}
		}
		
		if (!empty($params['post_id'])) {
			$strReq .= 'AND P.post_id = '.(integer) $params['post_id'].' ';
		}
		
		if (!empty($params['cat_id'])) {
			$strReq .= 'AND P.cat_id = '.(integer) $params['cat_id'].' ';
		}
		
		if (!empty($params['comment_id'])) {
			$strReq .= 'AND comment_id = '.(integer) $params['comment_id'].' ';
		}
		
		if (isset($params['comment_status'])) {
			$strReq .= 'AND comment_status = '.(integer) $params['comment_status'].' ';
		}
		
		if (!empty($params['comment_status_not']))
		{
			$strReq .= 'AND comment_status <> '.(integer) $params['comment_status_not'].' ';
		}
		
		if (isset($params['comment_trackback'])) {
			$strReq .= 'AND comment_trackback = '.(integer) (boolean) $params['comment_trackback'].' ';
		}
		
		if (isset($params['comment_ip'])) {
			$strReq .= "AND comment_ip = '".$core->con->escape($params['comment_ip'])."' ";
		}

		if (isset($params['q_author'])) {
			$q_author = $core->con->escape(str_replace('*','%',strtolower($params['q_author'])));
			$strReq .= "AND LOWER(comment_author) LIKE '".$q_author."' ";
		}
		
		if (!empty($params['search']))
		{
			$words = text::splitWords($params['search']);
			
			if (!empty($words))
			{
				# --BEHAVIOR coreCommentSearch
				if ($core->hasBehavior('coreCommentSearch')) {
					$core->callBehavior('coreCommentSearch',$core,array(&$words,&$strReq,&$params));
				}
				
				if ($words)
				{
					foreach ($words as $i => $w) {
						$words[$i] = "comment_words LIKE '%".$core->con->escape($w)."%'";
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
				$strReq .= 'ORDER BY '.$core->con->escape($params['order']).' ';
			} else {
				$strReq .= 'ORDER BY comment_dt DESC ';
			}
		}
		
		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $core->con->limit($params['limit']);
		}
		
		$rs = $core->con->select($strReq);
		$rs->core = $core;
		$rs->extend('rsExtComment');
		
		# --BEHAVIOR-- coreBlogGetComments
		$core->callBehavior('coreBlogGetComments',$rs);
		
		return $rs;
	}
	
	public static function getAllPostTypes()
	{
		global $core;
		
		$array = array('-' => '');
		
		$strReq = 'SELECT post_type AS post_type '.
			',COUNT(P.post_id) AS nb_post '.
			'FROM '.$core->prefix.'post P '.
			"WHERE P.blog_id IS NOT NULL ";
		
		$strReq .= 'GROUP BY post_type ';
		
		$strReq .=
		'ORDER BY post_type DESC ';
		
		$rs = $core->con->select($strReq);
		
		while ($rs->fetch())
		{
			$array[$rs->post_type.' ('.$rs->nb_post.')'] = $rs->post_type;
		}
		
		return($array);
	}
}

?>