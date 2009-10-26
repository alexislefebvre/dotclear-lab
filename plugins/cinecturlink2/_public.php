<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of cinecturlink2, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

require_once dirname(__FILE__).'/_widgets.php';

class cinecturlink2PublicWidget
{
	public static function links($w)
	{
		global $core; 

		if (!$core->blog->settings->cinecturlink2_active 
		 || $w->homeonly && $core->url->type != 'default') return;

		$C2 = new cinecturlink2($core);

		if ($w->category)
		{
			if ($w->category == 'null') {
				$params['sql'] = ' AND L.cat_id IS NULL ';
			} elseif (is_numeric($w->category)) {
				$params['cat_id'] = (integer) $w->category;
			}
		}

		$limit = abs((integer) $w->limit);

		# Tirage aléatoire
		# Consomme beaucoup de ressources!
		if ($w->sortby == 'RANDOM')
		{
			$big_rs = $C2->getLinks($params);
			
			if ($big_rs->isEmpty()) return;

			$ids= array();
			while($big_rs->fetch())
			{
				$ids[] = $big_rs->link_id;
			}
			shuffle($ids);
			$ids = array_slice($ids,0,$limit);

			$params['link_id'] = array();
			foreach($ids as $id)
			{
				$params['link_id'][] = $id;
			}
		}
		else
		{
			$params['order'] = $w->sortby;
			$params['order'] .= $w->sort == 'asc' ? ' asc' : ' desc';
			$params['limit'] = $limit;
		}

		$rs = $C2->getLinks($params);

		if ($rs->isEmpty()) return;

		$widthmax = (integer) $core->blog->settings->cinecturlink2_widthmax;
		$style = $widthmax ? ' style="width:'.$widthmax.'px;"' : '';

		$entries = array();
		while($rs->fetch())
		{
			$url = $rs->link_url;
			$img = $rs->link_img;
			$title = html::escapeHTML($rs->link_title);
			$author = html::escapeHTML($rs->link_author);
			$cat = html::escapeHTML($rs->cat_title);
			$note = $w->shownote ? ' <em>('.$rs->link_note.'/20)</em>' : '';
			$desc = $w->showdesc ? '<br /><em>'.html::escapeHTML($rs->link_desc).'</em>' : '';
			$lang = $rs->link_lang ? ' hreflang="'.$rs->link_lang.'"' : '';

			$entries[] = 
			'<p style="text-align:center;">'.
			($w->withlink && !empty($url) ? '<a href="'.$url.'"'.$lang.' title="'.$cat.'">' : '').
			'<strong>'.$title.'</strong>'.$note.'<br />'.
			$author.'<br /><br />'.
			'<img src="'.$img.'" alt="'.$title.' - '.$author.'"'.$style.' />'.
			$desc.
			($w->withlink && !empty($url) ? '</a>' : '').
			'<br />&nbsp;'.
			'</p>';
		}
		# Tirage aléatoire
		if ($w->sortby == 'RANDOM')
		{
			shuffle($entries);
			if ($core->blog->settings->cinecturlink2_triggeronrandom)
				$core->blog->triggerBlog();
		}

		return 
		'<div class="cinecturlink2list">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		implode(' ',$entries).
		'</div>';
	}
}
?>