<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of ExtendCategorie, a plugin for Dotclear.
# 
# Copyright (c) 2009 Rocky Horror
# rockyhorror@divingislife.net
# 
# Licensed under the GPL version 3.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/gpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {return;}

class PublicExtendCategorie
{
	public static function extendcategorieslist(&$w)
	{
		global $core, $_ctx;
		
		/*
		$rs = $core->blog->getCategories(array('post_type'=>'post'));
		//*/

		$rs = $core->blog->getCategories(array('post_type'=>''));
		if ($rs->isEmpty()) {
			return;
		}
		
		$res =
		'<div class="categories">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '');
		
		$ref_level = $level = $rs->level-1;
		while ($rs->fetch())
		{
			$class = '';
			if (($core->url->type == 'category' && $_ctx->categories instanceof record && $_ctx->categories->cat_id == $rs->cat_id)
			|| ($core->url->type == 'post' && $_ctx->posts instanceof record && $_ctx->posts->cat_id == $rs->cat_id)) {
				$class = ' class="category-current"';
			}
			
			if ($rs->level > $level) {
				$res .= str_repeat('<ul><li'.$class.'>',$rs->level - $level);
			} elseif ($rs->level < $level) {
				$res .= str_repeat('</li></ul>',-($rs->level - $level));
			}
			
			if ($rs->level <= $level) {
				$res .= '</li><li'.$class.'>';
			}
			
			$res .=
			'<a href="'.$core->blog->url.$core->url->getBase('category').'/'.
			$rs->cat_url.'">'.
			html::escapeHTML($rs->cat_title).'</a>'.
			($w->postcount ? ' ('.$rs->nb_post.')' : '');
			
			
			$level = $rs->level;
		}
		
		if ($ref_level - $level < 0) {
			$res .= str_repeat('</li></ul>',-($ref_level - $level));
		}
		$res .= '</div>';
		
		return $res;
	}
}


class CustomCategoryURL extends dcUrlHandlers
{
        public static function CustomCategory($args)
        {
           $_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
		
		$n = self::getPageNumber($args);
		
		if ($args == '' && !$n) {
			self::p404();
		}
		
		$params['cat_url'] = $args;

		/*
		$params['post_type'] = 'post';
		//*/
		
		$_ctx->categories = $core->blog->getCategories($params);
		
		if ($_ctx->categories->isEmpty()) {
			self::p404();
		} else {
			if ($n) {
				$GLOBALS['_page_number'] = $n;
			}
			self::serveDocument('category.html');
			exit;
		}     
        }
}
?>
