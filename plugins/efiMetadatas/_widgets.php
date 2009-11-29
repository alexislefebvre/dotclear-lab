<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of efiMetadatas, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets',array('efiMetadatasWidget','adminEFIM'));

class efiMetadatasWidget
{
	public static function adminEFIM($w)
	{
		global $core;

		$categories_combo = array('-' => '', __('Uncategorized') => 'null');
		$categories = $core->blog->getCategories();
		while($categories->fetch())
		{
			$cat_title = html::escapeHTML($categories->cat_title);
			$categories_combo[$cat_title] = $categories->cat_id;
		}

		$thumbnail_combo = array(
			'-' => '',
			__('square') => 'sq',
			__('thumbnail') => 't',
			__('small') => 's',
			__('medium') => 'm'
		);

		$w->create('efim',
			__('Entry first image metadatas'),array('efiMetadatasWidget','publicEFIM')
		);
		$w->efim->setting('title',
			__('Title:'),__('Image infos'),'text'
		);
		$w->efim->setting('category',
			__('Category limit:'),'','combo',$categories_combo
		);
		$w->efim->setting('thumbsize',
			__('Thumbnail size:'),'','combo',$thumbnail_combo
		);
		$w->efim->setting('showmeta',
			__('Show empty metadatas'),0,'check'
		);
	}

	public static function publicEFIM($w)
	{
		global $core, $_ctx; 

		# Not in post context
		if (!$_ctx->exists('posts') || !$_ctx->posts->post_id) return;

		# Not supported post type
		if (!in_array($_ctx->posts->post_type,array('post','gal','galitem'))) return '';

		# Category limit
		if ($w->category == 'null' && $_ctx->posts->cat_id !== null) return;
		if ($w->category != 'null' && $w->category != '' && $w->category != $_ctx->posts->cat_id) return;

		# Path and url
		$p_url = $core->blog->settings->public_url;
		$p_site = preg_replace('#^(.+?//.+?)/(.*)$#','$1',$core->blog->url);
		$p_root = $core->blog->public_path;

		# Image pattern
		$pattern = '(?:'.preg_quote($p_site,'/').')?'.preg_quote($p_url,'/');
		$pattern = sprintf('/<img.+?src="%s(.*?\.(?:jpg|gif|png))"[^>]+/msu',$pattern);

		# Content lookup
		$subject = $_ctx->posts->post_excerpt_xhtml.$_ctx->posts->post_content_xhtml;

		# No image
		if (!preg_match_all($pattern,$subject,$m)) return;

		$src = false;
		$size = $w->thumbsize;
		$alt = $metas = $thumb = '';
		$allowed_ext = array('.jpg','.JPG','.png','.PNG','.gif','.GIF');

		# Loop through images
		foreach ($m[1] as $i => $img)
		{
			$src = false;
			$info = path::info($img);
			$base = $info['base'];
			$ext = $info['extension'];

			# Not original
			if (preg_match('/^\.(.+)_(sq|t|s|m)$/',$base,$mbase))
			{
				$base = $mbase[1];
			}

			# Full path
			$f = $p_root.'/'.$info['dirname'].'/'.$base;

			# Find extension
			foreach($allowed_ext as $end)
			{
				if (file_exists($f.$end))
				{
					$src = $f.$end;
					break;
				}
			}

			# No file
			if (!$src) continue;

			# Find thumbnail
			if (!empty($size))
			{
				$t = $p_root.'/'.$info['dirname'].'/.'.$base.'_'.$size.'.jpg';
				if (file_exists($t))
				{
					$thb = $p_url.(dirname($img) != '/' ? dirname($img) : '').'/.'.$base.'_'.$size.'.jpg';
				}
			}

			# Find image description
			if (preg_match('/alt="([^"]+)"/',$m[0][$i],$malt))
			{
				$alt = $malt[1];
			}
			break;
		}

		# No image
		if (!$src || !file_exists($src)) return;

		# Image info		
		$metas_array = imageMeta::readMeta($src);

		# List metas
		foreach($metas_array as $k => $v)
		{
			if (!$w->showmeta && !$v) continue;
			$metas .= '<li><strong>'.__($k.':').'</strong><br />'.$v.'</li>';
		}

		# No meta
		if (empty($metas)) return;


		# thumbnail
		if (!empty($thb))
		{
			$thumb = 
			'<div class="img-box">'.				
			'<div class="img-thumbnail">'.
			'<img alt="'.$alt.'" src="'.$thb.'" />'.
			'</div>'.
			"</div>\n";
		}

		return 
		'<div class="entryFirstImageMetas">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		$thumb.
		'<ul>'.$metas.'</ul>'.
		'</div>';
	}
}
?>