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
		# Tag
		if ($core->plugins->moduleExists('metadata'))
		{
			$w->lastpostsextend->setting(
				'tag',
				__('Tag:'),
				'',
				'text'
			);
		}
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
			__('Excerpt length'),
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

		# Home page only
		if ($w->homeonly && $core->url->type != 'default')
			return;
		# Need posts excerpt
		if ($w->excerpt)
			$params['columns'][] = 'post_excerpt';
		# Updated posts only
		if ($w->updatedonly)
		{
			$params['sql'] = " 
			AND TIMESTAMP(post_creadt ,'DD-MM-YYYY HH24:MI:SS') < TIMESTAMP(post_upddt ,'DD-MM-YYYY HH24:MI:SS') 
			AND TIMESTAMP(post_dt ,'DD-MM-YYYY HH24:MI:SS') < TIMESTAMP(post_upddt ,'DD-MM-YYYY HH24:MI:SS') ";

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
				$params['sql'] = ' AND p.cat_id IS NULL ';
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
			$m = new dcMeta($core);
			$params['meta_id'] = $w->tag;
			$rs = $m->getPostsByMeta($params);
		}
		else
		{
			$rs = $core->blog->getPosts($params);
		}
		# No result
		if ($rs->isEmpty()) return;
		# Return
		$res =
		'<div class="lastpostsextend">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		while ($rs->fetch())
		{
			$res .= '<li><a href="'.$rs->getURL().'" title="'.
			dt::dt2str($core->blog->settings->date_format,$rs->post_upddt).', '.
			dt::dt2str($core->blog->settings->time_format,$rs->post_upddt).'">'.
			html::escapeHTML($rs->post_title).'</a>';
			# Nb comments
			if ($w->commentscount)
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
		
		if (preg_match('/^\.(.+)_(sq|t|s|m)$/',$base,$m)) {
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
		
		if ($res) {
			return $res;
		}
		return false;
	}
}
?>