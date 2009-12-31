<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of lastpostsExtend, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets',array('lastpostsextendWidget','initWidget'));

class lastpostsextendWidget
{
	public static function initWidget($w)
	{
		global $core;

		# Create widget
		$w->create(
			'lastpostsextend',
			__('Last entries (Extended)'),
			array('lastpostsextendWidget','parseWidget')
		);
		# Title
		$w->lastpostsextend->setting(
			'title',
			__('Title:'),
			__('Last entries'),
			'text'
		);
		# type
		$w->lastpostsextend->setting(
			'posttype',
			__('Type:'),
			'post',
			'combo',
			array(
				__('Post') => 'post',
				__('Page') => 'page',
				__('Gallery') => 'galitem'
			)
		);
		# Category (post and page have same category)
		$rs = $core->blog->getCategories(array('post_type'=>'post'));
		$categories = array('' => '', __('Uncategorized') => 'null');
		while ($rs->fetch())
		{
			$categories[str_repeat('&nbsp;&nbsp;',$rs->level-1).'&bull; '.
			html::escapeHTML($rs->cat_title)] = $rs->cat_id;
		}
		$w->lastpostsextend->setting(
			'category',
			__('Category:'),
			'',
			'combo',
			$categories
		);
		unset($rs,$categories);
		# Pasworded
		$w->lastpostsextend->setting(
			'passworded',
			__('Protection:'),
			'no',
			'combo',
			array(
				__('all') => 'all',
				__('only without password') => 'no',
				__('only with password') => 'yes'
			)
		);
		# Status
		$w->lastpostsextend->setting(
			'status',
			__('Status:'),
			'1',
			'combo',
			array(
				__('all') => 'all',
				__('pending') => '-2',
				__('scheduled') => '-1',
				__('unpublished') => '0',
				__('published') => '1'
			)
		);
		# Selected entries only
		$w->lastpostsextend->setting(
			'selectedonly',
			__('Selected entries only'),
			0,
			'check'
		);
		# Updated entries only
		$w->lastpostsextend->setting(
			'updatedonly',
			__('Updated entries only'),
			0,
			'check'
		);
		# Tag
		if ($core->plugins->moduleExists('metadata'))
		{
			$w->lastpostsextend->setting(
				'tag',
				__('Limit to tags:'),
				'',
				'text'
			);
		}
		# Search
		$w->lastpostsextend->setting(
			'search',
			__('Limit to words:'),
			'',
			'text'
		);
		# Entries limit
		$w->lastpostsextend->setting(
			'limit',
			__('Entries limit:'),
			10,
			'text'
		);
		# Sort type
		$w->lastpostsextend->setting(
			'sortby',
			__('Order by:'),
			'date',
			'combo',
			array(
				__('Date') => 'date',
				__('Title') => 'title'
			)
		);
		# Sort order
		$w->lastpostsextend->setting(
			'sort',
			__('Sort:'),
			'desc',
			'combo',
			array(
				__('Ascending') => 'asc',
				__('Descending') => 'desc'
			)
		);
		# First image
		$w->lastpostsextend->setting(
			'firstimage',
			__('Show entries first image:'),
			'',
			'combo',
			array(
				__('no') => '',
				__('square') => 'sq',
				__('thumbnail') => 't',
				__('small') => 's',
				__('medium') => 'm',
				__('original') => 'o'
			)
		);
		# With excerpt
		$w->lastpostsextend->setting(
			'excerpt',
			__('Show entries excerpt'),
			0,
			'check'
		);
		# Excerpt length
		$w->lastpostsextend->setting(
			'excerptlen',
			__('Excerpt length:'),
			100,
			'text'
		);
		# Comment count
		$w->lastpostsextend->setting(
			'commentscount',
			__('Show comments count'),
			0,
			'check'
		);
		# Home only
		$w->lastpostsextend->setting(
			'homeonly',
			__('Home page only'),
			1,
			'check'
		);
	}

	public static function parseWidget($w)
	{
		global $core;

		$params = array('sql' => '', 'columns' => array(), 'from' => '');

		# Home page only
		if ($w->homeonly && $core->url->type != 'default')
		{
			return;
		}
		# Need posts excerpt
		if ($w->excerpt)
		{
			$params['columns'][] = 'post_excerpt';
		}
		# Passworded
		if ($w->passworded == 'yes')
		{
			$params['sql'] .= 'AND post_password IS NOT NULL ';
		}
		elseif ($w->passworded == 'no')
		{
			$params['sql'] .= 'AND post_password IS NULL ';
		}
		# Status
		if ($w->status != 'all')
		{
			$params['post_status'] = $w->status;
		}
		# Search words
		if ('' != $w->search)
		{
			$params['search'] = $w->search;
		}
		# Updated posts only
		if ($w->updatedonly)
		{
			$params['sql'] .= 
			"AND TIMESTAMP(post_creadt ,'DD-MM-YYYY HH24:MI:SS') < TIMESTAMP(post_upddt ,'DD-MM-YYYY HH24:MI:SS') ".
			"AND TIMESTAMP(post_dt ,'DD-MM-YYYY HH24:MI:SS') < TIMESTAMP(post_upddt ,'DD-MM-YYYY HH24:MI:SS') ";

			$params['order'] = $w->sortby == 'title' ? 'post_title ' : 'post_upddt ';
		}
		else
		{
			$params['order'] = $w->sortby == 'title' ? 'post_title ' : 'post_dt ';
		}
		$params['order'] .= $w->sort == 'asc' ? 'asc' : 'desc';
		$params['limit'] = abs((integer) $w->limit);
		$params['no_content'] = true;
		# Selected posts only
		if ($w->selectedonly)
		{
			$params['post_selected'] = 1;
		}
		# Type
		$params['post_type'] = $w->posttype;
		# Category
		if ($w->category)
		{
			if ($w->category == 'null')
			{
				$params['sql'] .= ' AND p.cat_id IS NULL ';
			}
			elseif (is_numeric($w->category))
			{
				$params['cat_id'] = (integer) $w->category;
			}
			else
			{
				$params['cat_url'] = $w->category;
			}
		}
		# Tags
		if ($core->plugins->moduleExists('metadata') && $w->tag)
		{
			$tags = explode(',',$w->tag);
			foreach($tags as $i => $tag) { $tags[$i] = trim($tag); }
			$params['from'] .= ', '.$core->prefix.'meta META ';
			$params['sql'] .= 'AND META.post_id = P.post_id ';
			$params['sql'] .= "AND META.meta_id ".$core->con->in($tags)." ";
			$params['sql'] .= "AND META.meta_type = 'tag' ";
		}

		$rs = self::getPosts($params);

		# No result
		if ($rs->isEmpty()) return;
		# Return
		$res =
		'<div class="lastpostsextend">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		while ($rs->fetch())
		{
			$res .= '<li>'.
			'<'.($rs->post_status == 1 ? 'a href="'.$rs->getURL().'"' : 'span').
			' title="'.
			dt::dt2str($core->blog->settings->date_format,$rs->post_upddt).', '.
			dt::dt2str($core->blog->settings->time_format,$rs->post_upddt).'">'.
			html::escapeHTML($rs->post_title).
			'</'.($rs->post_status == 1 ? 'a' : 'span').'>';
			# Nb comments
			if ($w->commentscount && $rs->post_status == 1)
			{
				$res .= ' ('.$rs->nb_comment.')';
			}
			# First image
			if ($w->firstimage != '')
			{
				$res .= self::entryFirstImage($core,$w->posttype,$rs->post_id,$w->firstimage);
			}
			# Excerpt
			if ($w->excerpt)
			{
				$excerpt = $rs->post_excerpt;
				if ($rs->post_format == 'wiki')
				{
					$core->initWikiComment();
					$excerpt = $core->wikiTransform($excerpt);
					$excerpt = $core->HTMLfilter($excerpt);
				}
				if (strlen($excerpt) > 0)
				{
					$cut = text::cutString($excerpt,abs((integer) $w->excerptlen));
					$res .= ' : '.$cut.(strlen($cut) < strlen($excerpt) ? '...' : '');
					unset($cut);
				}
			}
			$res .= '</li>';
		}
		$res .= '</ul></div>';

		return $res;
	}

	private static function entryFirstImage($core,$type,$id,$size='s')
	{
		if (!in_array($type,array('post','page','galitem'))) return '';

		$rs = $core->blog->getPosts(array('post_id'=>$id,'post_type'=>$type));

		if ($rs->isEmpty()) return '';

		if (!preg_match('/^sq|t|s|m|o$/',$size))
		{
			$size = 's';
		}

		$p_url = $core->blog->settings->public_url;
		$p_site = preg_replace('#^(.+?//.+?)/(.*)$#','$1',$core->blog->url);
		$p_root = $core->blog->public_path;

		$pattern = '(?:'.preg_quote($p_site,'/').')?'.preg_quote($p_url,'/');
		$pattern = sprintf('/<img.+?src="%s(.*?\.(?:jpg|gif|png))"[^>]+/msu',$pattern);

		$src = '';
		$alt = '';

		$subject = $rs->post_excerpt_xhtml.$rs->post_content_xhtml.$rs->cat_desc;
		if (preg_match_all($pattern,$subject,$m) > 0)
		{
			foreach ($m[1] as $i => $img)
			{
				if (($src = self::ContentFirstImageLookup($p_root,$img,$size)) !== false)
				{
					$src = $p_url.(dirname($img) != '/' ? dirname($img) : '').'/'.$src;
					if (preg_match('/alt="([^"]+)"/',$m[0][$i],$malt))
					{
						$alt = $malt[1];
					}
					break;
				}
			}
		}
		if (!$src) return '';

		return 
		'<div class="img-box">'.				
		'<div class="img-thumbnail">'.
		'<a title="'.html::escapeHTML($rs->post_title).'" href="'.$rs->getURL().'">'.
		'<img alt="'.$alt.'" src="'.$src.'" />'.
		'</a></div>'.
		"</div>\n";
	}
	
	private static function ContentFirstImageLookup($root,$img,$size)
	{
		# Get base name and extension
		$info = path::info($img);
		$base = $info['base'];
		
		if (preg_match('/^\.(.+)_(sq|t|s|m)$/',$base,$m))
		{
			$base = $m[1];
		}
		
		$res = false;
		if ($size != 'o' && file_exists($root.'/'.$info['dirname'].'/.'.$base.'_'.$size.'.jpg'))
		{
			$res = '.'.$base.'_'.$size.'.jpg';
		}
		else
		{
			$f = $root.'/'.$info['dirname'].'/'.$base;
			if (file_exists($f.'.'.$info['extension'])) {
				$res = $base.'.'.$info['extension'];
			} elseif (file_exists($f.'.jpg')) {
				$res = $base.'.jpg';
			} elseif (file_exists($f.'.png')) {
				$res = $base.'.png';
			} elseif (file_exists($f.'.gif')) {
				$res = $base.'.gif';
			}
		}

		return $res ? $res : false;
	}

	public static function getPosts($params=array(),$count_only=false)
	{
		global $core;
		
		$content_req = '';
		if (!empty($params['columns']) && is_array($params['columns'])) {
			$content_req .= implode(', ',$params['columns']).', ';
		}

		$strReq =
		'SELECT P.post_id, P.blog_id, P.user_id, P.cat_id, post_dt, '.
		'post_tz, post_creadt, post_upddt, post_format, post_password, '.
		'post_url, post_lang, post_title, '.
		$content_req.
		'post_type, post_meta, post_status, post_selected, '.
		'nb_comment, '.
		'U.user_name, U.user_firstname, U.user_displayname, U.user_email, '.
		'U.user_url, '.
		'C.cat_title, C.cat_url, C.cat_desc '.
		'FROM '.$core->prefix.'post P '.
		'INNER JOIN '.$core->prefix.'user U ON U.user_id = P.user_id '.
		'LEFT OUTER JOIN '.$core->prefix.'category C ON P.cat_id = C.cat_id ';
		
		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}
		
		$strReq .=
		"WHERE P.blog_id = '".$core->con->escape($core->blog->id)."' ";

		if (isset($params['post_type']))
		{
			if (is_array($params['post_type']) && !empty($params['post_type'])) {
				$strReq .= 'AND post_type '.$core->con->in($params['post_type']);
			} elseif ($params['post_type'] != '') {
				$strReq .= "AND post_type = '".$core->con->escape($params['post_type'])."' ";
			}
		}
		else
		{
			$strReq .= "AND post_type = 'post' ";
		}

		if (!empty($params['cat_id']))
		{
			if (!is_array($params['cat_id'])) {
				$params['cat_id'] = array($params['cat_id']);
			}
			if (!empty($params['cat_id_not'])) {
				array_walk($params['cat_id'],create_function('&$v,$k','$v=$v." ?not";'));
			}
			$strReq .= 'AND '.self::getPostsCategoryFilter($params['cat_id'],'cat_id').' ';
		}
		elseif (!empty($params['cat_url']))
		{
			if (!is_array($params['cat_url'])) {
				$params['cat_url'] = array($params['cat_url']);
			}
			if (!empty($params['cat_url_not'])) {
				array_walk($params['cat_url'],create_function('&$v,$k','$v=$v." ?not";'));
			}
			$strReq .= 'AND '.self::getPostsCategoryFilter($params['cat_url'],'cat_url').' ';
		}

		if (isset($params['post_status'])) {
			$strReq .= 'AND post_status = '.(integer) $params['post_status'].' ';
		}
		
		if (isset($params['post_selected'])) {
			$strReq .= 'AND post_selected = '.(integer) $params['post_selected'].' ';
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

		$strReq .= 'GROUP BY P.post_id ';
		
		if (!empty($params['order'])) {
			$strReq .= 'ORDER BY '.$core->con->escape($params['order']).', P.post_id ';
		} else {
			$strReq .= 'ORDER BY post_dt, P.post_id DESC ';
		}

		if (!empty($params['limit'])) {
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
	
	public static function getPostsCategoryFilter($arr,$field='cat_id')
	{
		global $core;
		$field = $field == 'cat_id' ? 'cat_id' : 'cat_url';
		
		$sub = array();
		$not = array();
		$queries = array();
		
		foreach ($arr as $v)
		{
			$v = trim($v);
			$args = preg_split('/\s*[?]\s*/',$v,-1,PREG_SPLIT_NO_EMPTY);
			$id = array_shift($args);
			$args = array_flip($args);
			
			if (isset($args['not'])) { $not[$id] = 1; }
			if (isset($args['sub'])) { $sub[$id] = 1; }
			if ($field == 'cat_id') {
				$queries[$id] = 'P.cat_id = '.(integer) $id;
			} else {
				$queries[$id] = "C.cat_url = '".$core->con->escape($id)."' ";
			}
		}
		
		if (!empty($sub)) {
			$rs = $core->con->select(
				'SELECT cat_id, cat_url, cat_lft, cat_rgt FROM '.$core->prefix.'category '.
				"WHERE blog_id = '".$core->con->escape($core->blog->id)."' ".
				'AND '.$field.' '.$core->con->in(array_keys($sub))
			);
			
			while ($rs->fetch()) {
				$queries[$rs->f($field)] = '(C.cat_lft BETWEEN '.$rs->cat_lft.' AND '.$rs->cat_rgt.')';
			}
		}
		
		# Create queries
		$sql = array(
			0 => array(), # wanted categories
			1 => array()  # excluded categories
		);
		
		foreach ($queries as $id => $q) {
			$sql[(integer) isset($not[$id])][] = $q;
		}
		
		$sql[0] = implode(' OR ',$sql[0]);
		$sql[1] = implode(' OR ',$sql[1]);
		
		if ($sql[0]) {
			$sql[0] = '('.$sql[0].')';
		} else {
			unset($sql[0]);
		}
		
		if ($sql[1]) {
			$sql[1] = '(P.cat_id IS NULL OR NOT('.$sql[1].'))';
		} else {
			unset($sql[1]);
		}
		
		return implode(' AND ',$sql);
	}
}
?>