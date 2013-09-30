<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 "Include subcats" plugin.
#
# Copyright (c) 2009-2013 Bruno Hondelatte and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets', array('includesubcatsWidgets', 'initWidgets'));

class includesubcatsWidgets
{

  public static function initWidgets($w) {
    $w->create('subcat', __('SubCategories list'), array('includesubcatsWidgets', 'categories'));
	$w->subcat->setting('title',__('Title:'),__('Categories'));
	$w->subcat->setting('postcount',__('With entries counts'),0,'check');
    $w->subcat->setting('subcatscount', __('Include sub cats in count'), false, 'check');
	$w->subcat->setting('with_empty',__('Include empty categories'),0,'check');
	$w->subcat->setting('homeonly',__('Display on:'),0,'combo',
		array(__('All pages') => 0, __('Home page only') => 1, __('Except on home page') => 2));
	$w->subcat->setting('content_only',__('Content only'),0,'check');
	$w->subcat->setting('class',__('CSS class:'),'');    
  }

	public static function categories($w)
	{
		global $core, $_ctx;
		
		if (($w->homeonly == 1 && $core->url->type != 'default') ||
			($w->homeonly == 2 && $core->url->type == 'default')) {
			return;
		}

		$rs = $core->blog->getCategories(array('post_type'=>'post','without_empty'=> !$w->with_empty));
		if ($rs->isEmpty()) {
			return;
		}
		
		$res =
		($w->content_only ? '' : '<div class="categories'.($w->class ? ' '.html::escapeHTML($w->class) : '').'">').
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
			'<a href="'.$core->blog->url.$core->url->getURLFor('category', $rs->cat_url).'">'.
			html::escapeHTML($rs->cat_title).'</a>'.
			($w->postcount ? ' <span>('.($w->subcatscount ? $rs->nb_total : $rs->nb_post).')</span>' : '');
			
			
			$level = $rs->level;
		}
		
		if ($ref_level - $level < 0) {
			$res .= str_repeat('</li></ul>',-($ref_level - $level));
		}
		$res .= ($w->content_only ? '' : '</div>');
		
		return $res;
	}
}
?>
