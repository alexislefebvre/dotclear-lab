<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of yafr, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class publicyafr
{
	public static function Widget($w)
	{
		global $core;
		if (!$w->url) {
			return;
		}
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$limit = abs((integer) $w->f_limit);
		
		try {
			$feed = feedReader::quickParse($w->url,DC_TPL_CACHE);
			if ($feed == false || count($feed->items) == 0) {
				return;
			}
		} catch (Exception $e) {
			return;
		}

		$url = $date = $title = $author = $content = $description = '';
		$res =
		'<div class="feed '.html::escapeHTML($w->CSS).'">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		($w->showtitle ? '<h3>'.html::escapeHTML($feed->title).'</h3>' : '').
		($w->showdesc ? '<p>'.html::escapeHTML($feed->description).'</p>' : '').
		'<ul>';

		$s_url = strpos($w->stringformat,'%5$s') === false ? false : true;
		$s_date = strpos($w->stringformat,'%1$s') === false ? false : true;
		$s_title = strpos($w->stringformat,'%2$s') === false ? false : true;
		$s_author = strpos($w->stringformat,'%3$s') === false ? false : true;
		$s_content = strpos($w->stringformat,'%4$s') === false ? false : true;
		$s_desc= strpos($w->stringformat,'%6$s') === false ? false : true;

		$i = 0;
		foreach ($feed->items as $item) {
			if ($s_url) {
				$url = $item->link;
			}
			if ($s_date) {
				$date = dt::dt2str($w->dateformat,$item->pubdate);
			}
			if ($s_title) {
				$title = self::truncateclean($item->title,$w->t_limit,false);
			}
			if ($s_author) {
				$author = html::escapeHTML($item->creator);
			}
			if ($s_content) {
				$content = ($w->cleancontent)? self::truncateclean($item->content,$w->fe_limit) : $item->content;
			}
			if ($s_desc) {
				$description = ($w->cleancontent)? self::truncateclean($item->description,$w->fe_limit) : $item->description;
			}


			$res .= '<li>'.sprintf($w->stringformat,$date,$title,$author,$content,$url,$description).'</li>';

			$i++;
			if ($i >= $limit) {
				break;
			}
		}
		
		$res .= '</ul></div>';
		
		return $res;
	}

	public static function truncateclean($str,$maxlength,$html=true)
	{
		# On rend la chaîne lisible
		if ($html) {
			$str = html::decodeEntities(html::clean($str));
		}
		
		if ($maxlength > 0)
		{
			# On coupe la chaîne si elle est trop longue
			if (mb_strlen($str) > $maxlength) {
				$str = text::cutString($str,$maxlength).'…';
			}
		}
		
		# On encode la chaîne pour pouvoir l'insérer dans un document HTML
		return html::escapeHTML($str);
	}
}
?>