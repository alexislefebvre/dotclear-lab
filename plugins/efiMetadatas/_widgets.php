<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of efiMetadatas, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
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
		
		# Content lookup
		$text = $_ctx->posts->post_excerpt_xhtml.$_ctx->posts->post_content_xhtml;
		
		# Find source image
		$img = efiMetadatas::imgSource($core,$text,$w->thumbsize);
		
		# No image
		if (!$img['source']) return;
		
		# List metas
		$metas = efiMetadatas::imgMeta($core,$img['source']);
		
		$content = '';
		foreach($metas as $k => $v)
		{
			// keep empty meta if wanted
			if (!$w->showmeta && empty($v[1])) continue;
			$content .= '<li class="efi-'.$k.'"><strong>'.$v[0].':</strong><br />'.$v[1].'</li>';
		}
		
		# No meta
		if (empty($content)) return;
		
		# thumbnail
		if ($img['thumb'])
		{
			$thumb = 
			'<div class="img-box">'.				
			'<div class="img-thumbnail">'.
			'<img alt="'.$img['title'].'" src="'.$img['thumb'].'" />'.
			'</div>'.
			"</div>\n";
		}
		
		return 
		'<div class="entryFirstImageMetas">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		$thumb.
		'<ul>'.$content.'</ul>'.
		'</div>';
	}
}
?>