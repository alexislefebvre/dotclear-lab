<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of infoEntry, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class infoEntryPublic
{
	/**
	 * This function displays the infoEntry widget
	 *
	 * @param	w	Widget object
	 *
	 * @return	string
	 */
	public static function widget($w)
	{
		global $core, $_ctx;

		if ($w->offline)
			return;

		if ($core->url->type != 'post') { return; }

		$res = '<ul>';

		$pmask = '<li class="%1$s">%2$s</li>';
		$amask = '<a href="%1$s">%2$s</a>';

		if ($w->displayauthor) {
			$res .= sprintf($pmask,'info-author',$_ctx->posts->getAuthorLink());
		}
		if ($w->displaydate) {
			$res .= sprintf($pmask,'info-date',$_ctx->posts->getDate($w->dateformat));
		}
		if ($w->displaycategory) {
			$link = sprintf($amask,$_ctx->posts->getCategoryURL(),$_ctx->posts->cat_title);
			$res .= sprintf($pmask,'info-category',$link);
		}
		if ($w->displayfeed) {
			$url = $core->blog->url.$core->url->getBase('feed').'/'.$w->feedformat.'/comments/'.$_ctx->posts->post_id;
			$link = sprintf($amask,$url,__("This post's comments feed"));
			$res .= sprintf($pmask,'info-feed',$link);
		}
		if ($w->displaytags) {
			$tags = new dcMeta($core);
			$_ctx->meta = $tags->getMetaRecordset($_ctx->posts->post_meta,'tag');
			$_ctx->meta->sort($w->sortby,$w->orderby);

			while ($_ctx->meta->fetch())
			{
				$url = $core->blog->url.$core->url->getBase('tag').'/'.rawurlencode($_ctx->meta->meta_id);
				$link = sprintf($amask,$url,html::escapeHTML($_ctx->meta->meta_id));
				$res .= '<li class="info-tags">'.$link.'</li>';
			}

			//$res .= '</ul>';
		}
		if ($w->displaynextentry) {
			$res .= infoEntryPublic::getRelatedPost(__('Next entry: %s'),'info-next-entry',1);
		}
		if ($w->displaypreventry) {
			$res .= infoEntryPublic::getRelatedPost(__('Previous entry: %s'),'info-prev-entry',-1);
		}
		if ($w->displaynextentrycat) {
			$res .= infoEntryPublic::getRelatedPost(__('Next entry in category: %s'),'info-next-entry',1,true);
		}
		if ($w->displaypreventrycat) {
			$res .= infoEntryPublic::getRelatedPost(__('Previous entry in category: %s'),'info-prev-entry',-1,true);
		}

    $res .= '</ul>';

		$res =
		($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').
    $res;
    
		return $w->renderDiv($w->content_only,'info-entry '.$w->class,'',$res);
	}

	/**
	 * Returns link to related posts
	 *
	 * @param	text			string
	 * @param	class			string
	 * @param	direction 		int
	 * @param	restrict_to_cat	boolean
	 *
	 * @return	string
	 */
	public static function getRelatedPost($text = '%s',$class = '',$direction = 1,$restrict_to_cat = false)
	{
		global $core, $_ctx;

		$pmask = '<li class="%1$s">%2$s</li>';
		$amask = '<a href="%1$s">%2$s</a>';
		$res = '';

		$post = $core->blog->getNextPost($_ctx->posts,$direction,$restrict_to_cat);
		if ($post !== null) {
			while ($post->fetch())
			{
				$link = sprintf($amask,$post->getURL(),html::escapeHTML($post->post_title));
				$item = sprintf($text,$link);
				$res = sprintf($pmask,$class,$item);
			}
		}

		return $res;
	}
}