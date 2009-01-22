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

class pubWidgetGallery
{
	private static function getGalleryLink (&$rsgal,$w) {
		global $core;
		
		return '<a href="'.$rsgal->getURL().'">'.html::escapeHTML($rsgal->post_title).
			'</a>'.($w->gal_count?(' ('.$core->gallery->getGalItemCount($rsgal).')'):"");
	}
	private static function getCategoryLink (&$rscat,$w) {
		global $core;
		
		
		if ($rscat->cat_id == "")
			$cat_title = __('No category');
		else
				$cat_title = $rscat->cat_title;
		if ($w->cat_display == "breadcrumb" && $rscat != "") {
			$par_cat = $core->blog->getCategoryParents($rscat->cat_id);
			$path="";
			while ($par_cat->fetch()) 
				$path .= $par_cat->cat_title."/";
			$title = html::escapeHTML($path.$cat_title);

		} else {
			$title = html::escapeHTML($cat_title);
		}
		return
			'<a href="'.$core->blog->url.$core->url->getBase('galleries').'/category/'.
			$rscat->cat_url.'">'.
			$title.'</a> '.(($w->cat_count &&$rscat->exists('nb_post'))?('('.$rscat->nb_post.')'):"");
	}

	private static function getGalleriesInCategory($cat_id,&$rsgal,$w) {
		$res = "<ul>";
		while (!$rsgal->isEnd() && $rsgal->cat_id == $cat_id) {
			$res .= '<li class="ligal">'.self::getGalleryLink($rsgal,$w);	

			$rsgal->fetch();
		}
		$res .= "</ul>";
		return $res;
	}

	private static function displayCategoryList(&$rscat,&$rsgal,$w,$cur_cat_id=null) {
		global $core;
		$level = $rscat->level;
		$class = '';
		$res = '<ul>';
		if ($rscat->cat_id == $cur_cat_id)
			$class = ' category-current';
		$res .= '<li class="ligalcat'.$class.'">';
		$res .= self::getCategoryLink($rscat, $w);
		$res .= self::getGalleriesInCategory($rscat->cat_id,$rsgal,$w);
		while (!$rscat->isEnd() && $rscat->level >= $level) {
			$rscat->fetch();
			if ($rscat->level > $level) {
				$res .= self::displayCategoryList($rscat,$rsgal,$w,$cur_cat_id);
			} 
			if ($rscat->level == $level) {
				$class='';
				if ($rscat->cat_id == $cur_cat_id)
					$class = ' category-current';
				$res .= '</li><li class="ligalcat'.$class.'">';
				$res .= self::getCategoryLink($rscat, $w);
				$res .= self::getGalleriesInCategory($rscat->cat_id,$rsgal,$w);
			}
		}
		$res .= '</li></ul>';
		return $res;

	}


	# Gallery Widget function
	public static function listgalWidget(&$w)
	{
		global $core,$_ctx;

		if (empty($core->meta)) $core->meta = new dcMeta($core);
		if (empty($core->gallery)) $core->gallery = new dcGallery($core);
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		$title = $w->title ? html::escapeHTML($w->title) : __('Galleries');

		$orderby = $w->orderby;
		$orderdir = $w->orderdir;
		$display_cat = ($w->cat_display !== 'none');
		$display_gal = ($w->gal_display !== 'none');
		$order="";

		if ($display_cat) {
			$order="C.cat_lft asc, ";
		}
		if ($orderby == 'date')
			$order .= 'P.post_dt ';
		else
			$order .= 'P.post_title ';

		$order .= ($orderdir == 'asc') ? 'asc':'desc';
		$rsgal=$rscat=null;
		if ($display_cat) {
			$rscat = $core->blog->getCategories (array('post_type'=>'gal'));
		}
		if ($display_gal) {
			if (((integer)$w->limit) != 0) {
				$rsgal = $core->gallery->getGalleries(array("order" => $order, "limit" => array(0,(integer)$w->limit), "no_content" => true));
			} else {
				$rsgal = $core->gallery->getGalleries(array("order" => $order, "no_content" => true));
			}
		}
		
		$res =
		'<div class="galleries categories"><h2>'.$title.'</h2>';
		
		if ($core->url->type == 'category' && $_ctx->categories instanceof record)
			$cur_cat_id = $_ctx->categories->cat_id;
		elseif ($core->url->type == 'post' && $_ctx->posts instanceof record)
			$cur_cat_id = $_ctx->posts->cat_id;
		else
			$cur_cat_id = null;

		if ($w->cat_display == 'tree') {
			if ($rscat->fetch()) {
				if ($rsgal != null)
					$rsgal->fetch();

				$res .= self::displayCategoryList($rscat,$rsgal,$w,$cur_cat_id);
			}
			
		} else {
			$first=true;
			$current_cat = "dummycategoryblabla";
			$res .= "<ul>";
			while ($rsgal->fetch()) {
				if ($display_cat) {
					if ($current_cat != $rsgal->cat_id) {
						if (!$first) {
							$res .= '</ul></li>';
						} else {
							$first=false;
						}
						$res .= ' <li class="ligalcat'.(($cur_cat_id == $rsgal->cat_id && $cur_cat_id != 0)?" category-current":"").'">'.self::getCategoryLink($rsgal,$w);
						$current_cat = $rsgal->cat_id;
						$res .= '<ul>';
					}
				}
				$res .= '<li class="ligal">'.self::getGalleryLink($rsgal,$w).'</li>';
			}
			$res .= '</ul>';

		}
		if ($display_cat)
			$res .= '</li></ul>';
		$res .= '<p><strong><a href="'.$core->blog->url.$core->url->getBase("galleries").'">'.
			__('All galleries').'</a></strong></p>';
		
		$res .= '</div>';
		
		return $res;
	}

	# Gallery Widget function
	public static function randimgWidget(&$w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		if (empty($core->gallery)) $core->gallery = new dcGallery($core);
		$title = $w->title ? html::escapeHTML($w->title) : __('Random Image');
		$img = $core->gallery->getRandomImage();
		$imglink = $w->imglink == "img";
		$size = in_array($w->size,array('sq','t','m','s'))?$w->size:'t';
		if (!$img->isEmpty()) {
			$media = $core->gallery->readMedia($img);
			$p  = '<div id="randomimage">';
			$p .= '<h2>'.$title.'</h2>';
			$p .= '<a href="'.($imglink?$media->file_url:$img->getURL()).'" title="'.html::escapeHTML($img->post_title).'">'; 
			$p .= '<img src="'.$media->media_thumb[$size].'" alt="'.html::escapeHTML($img->post_title).'" />';
			$p .= '</a>';
			$p .= '</div>';
			return $p;
		} else {
			return '';
		}


	}

	public static function lastimgWidget(&$w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		if (empty($core->gallery)) $core->gallery = new dcGallery($core);
		$title = $w->title ? html::escapeHTML($w->title) : __('Last images');
		$nb_last = $w->limit;
		$display = $w->display;
		$imglink = $w->imglink == "img";
		$params['limit']=$w->limit;
		$params['order']='P.post_dt DESC';
		$img = $core->gallery->getGalImageMedia($params);
		$p  = '<div id="lastimage">';
		$p .= '<h2>'.$title.'</h2>';
		while ($img->fetch()) {
			$media = $core->gallery->readMedia($img);
			$p .= '<a href="'.($imglink?$media->file_url:$img->getURL()).'">'; 
			$p .='<img src="'.$media->media_thumb["sq"].'" style="float:left;"  alt="'.html::escapeHTML($img->post_title).'"/>';
			$p .= '</a>';
		}
		$p .= '<p style="clear: both;"></p></div>';
		return $p;


	}

	public static function imageMetaWidget(&$w)
	{
		global $core,$_ctx;

		if ($core->url->type != 'galitem') {
			return;
		}
		$title = $w->title ? html::escapeHTML($w->title) : __('Image Information');

		$p  = '<div id="imagemeta">'.
			'<h2>'.$title.'</h2>'.
			'<ul>';
		if ($_ctx->media->media_meta instanceof SimpleXMLElement) {
			foreach ($_ctx->media->media_meta as $k => $v)
			{
				if ((string) $v && trim($v) !== "") {
					$p .= '<li><strong>'.__($k).':</strong> '.html::escapeHTML($v).'</li>';
				}
			}
		}
		$p .= '</ul></div>';
		return $p;


	}

}
?>
