<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of cinecturlink2, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets',array('cinecturlink2Widget','adminLinks'));
$core->addBehavior('initWidgets',array('cinecturlink2Widget','adminCats'));

class cinecturlink2Widget
{
	public static function adminLinks($w)
	{
		global $core;

		$C2 = new cinecturlink2($core);

		$categories_combo = array('' => '', __('Uncategorized') => 'null');
		$categories = $C2->getCategories();
		while($categories->fetch())
		{
			$cat_title = html::escapeHTML($categories->cat_title);
			$categories_combo[$cat_title] = $categories->cat_id;
		}

		$sortby_combo = array(
			__('Update date') => 'link_upddt',
			__('My rating') => 'link_note',
			__('Title') => 'link_title',
			__('Random') => 'RANDOM',
			__('Number of views') => 'COUNTER'
		);
		$order_combo = array(
			__('Ascending') => 'asc',
			__('Descending') => 'desc'
		);

		$w->create('cinecturlink2links',
			__('My cinecturlink'),array('cinecturlink2Widget','publicLinks')
		);
		$w->cinecturlink2links->setting('title',
			__('Title:'),__('My cinecturlink'),'text'
		);
		$w->cinecturlink2links->setting('category',
			__('Category:'),'','combo',$categories_combo
		);
		$w->cinecturlink2links->setting('sortby',
			__('Order by:'),'link_upddt','combo',$sortby_combo
		);
		$w->cinecturlink2links->setting('sort',
			__('Sort: (only for date, note and title)'),'desc','combo',$order_combo
		);
		$w->cinecturlink2links->setting('limit',
			__('Limit:'),10,'text'
		);
		$w->cinecturlink2links->setting('withlink',
			__('Enable link'),1,'check'
		);
		$w->cinecturlink2links->setting('showauthor',
			__('Show author'),1,'check'
		);
		$w->cinecturlink2links->setting('shownote',
			__('Show my rating'),0,'check'
		);
		$w->cinecturlink2links->setting('showdesc',
			__('Show description'),0,'check'
		);
		$w->cinecturlink2links->setting('showpagelink',
			__('Show a link to cinecturlink page'),0,'check'
		);
		$w->cinecturlink2links->setting('homeonly',
			__('Home page only'),1,'check'
		);
	}

	public static function adminCats($w)
	{
		$w->create('cinecturlink2cats',
			__('List of categories of cinecturlink'),array('cinecturlink2Widget','publicCats')
		);
		$w->cinecturlink2cats->setting('title',
			__('Title:'),__('My cinecturlink by categories'),'text'
		);
		$w->cinecturlink2cats->setting('shownumlink',
			__('Show number of links'),0,'check'
		);
		$w->cinecturlink2cats->setting('homeonly',
			__('Home page only'),1,'check'
		);
	}

	public static function publicLinks($w)
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
		elseif ($w->sortby == 'COUNTER')
		{
			$params['order'] = 'link_count asc';
			$params['limit'] = $limit;
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
			$count = abs((integer) $rs->link_count);

			$entries[] = 
			'<p style="text-align:center;">'.
			($w->withlink && !empty($url) ? '<a href="'.$url.'"'.$lang.' title="'.$cat.'">' : '').
			'<strong>'.$title.'</strong>'.$note.'<br />'.
			($w->showauthor ? $author.'<br />' : '').'<br />'.
			'<img src="'.$img.'" alt="'.$title.' - '.$author.'"'.$style.' />'.
			$desc.
			($w->withlink && !empty($url) ? '</a>' : '').
			'<br />&nbsp;'.
			self::rateItWidgetAddon('cinecturlink2',$rs->link_id).
			'</p>';

			$C2->updLinkCount($rs->link_id,($count + 1));
		}
		# Tirage aléatoire
		if ($w->sortby == 'RANDOM' || $w->sortby == 'COUNTER')
		{
			shuffle($entries);
			if ($core->blog->settings->cinecturlink2_triggeronrandom)
				$core->blog->triggerBlog();
		}

		return 
		'<div class="cinecturlink2list">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		implode(' ',$entries).
		($w->showpagelink && $core->blog->settings->cinecturlink2_public_active ? 
		'<a href="'.$core->blog->url.$core->url->getBase('cinecturlink2').'" title="'.__('view all links').'">'.__('More links').'</a>' : ''
		).
		'<br />&nbsp;'.
		'</div>';
	}

	public static function publicCats($w)
	{
		global $core; 

		if (!$core->blog->settings->cinecturlink2_active 
		 || !$core->blog->settings->cinecturlink2_public_active 
		 || $w->homeonly && $core->url->type != 'default') return;

		$C2 = new cinecturlink2($core);
		
		$rs = $C2->getCategories(array());

		if ($rs->isEmpty()) return;

		$res = 
		'<li><a href="'.
		$core->blog->url.$core->url->getBase('cinecturlink2').
		'" title="'.__('view all links').'">'.__('all links').
		'</a>';
		if ($w->shownumlink)
		{
			$res .= ' ('.($C2->getLinks(array(),true)->f(0)).')';
		}
		$res .= '</li>';

		while($rs->fetch())
		{
			$res .= 
			'<li><a href="'.
			$core->blog->url.$core->url->getBase('cinecturlink2').'/'.$core->blog->settings->cinecturlink2_public_caturl.'/'.urlencode($rs->cat_title).
			'" title="'.__('view links of this category').'">'.
			html::escapeHTML($rs->cat_title).
			'</a>';
			if ($w->shownumlink)
			{
				$res .= ' ('.($C2->getLinks(array('cat_id'=>$rs->cat_id),true)->f(0)).')';
			}
			$res .= '</li>';
		}

		return 
		'<div class="cinecturlink2cat">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>'.$res.'</ul>'.
		'</div>';
	}

	public static function initCinecturlink2WidgetRateItRank($types)
	{
		$types[] = 'cinecturlink2';
	}

	protected static function rateItWidgetAddon($type,$id)
	{
		global $core;

		if (!$core->blog->settings->rateit_active 
		 || !$core->blog->settings->rateit_cinecturlink2_active
		 || !$core->blog->settings->rateit_cinecturlink2_widget) return;

		$rateIt = new rateIt($core);
		$rateit_voted = $rateIt->voted($type,$id);
		$_rateIt = $rateIt->get($type,$id);

		$res =
		'<div class="rateit">'.
		'<p><span id="rateit-fullnote-'.$_rateIt->type.'-'.$_rateIt->id.'"  class="rateit-fullnote">'.$_rateIt->note."/".$_rateIt->quotient.'</span></p>'.
		'<form class="rateit-linker" id="rateit-linker-'.$_rateIt->type.'-'.$_rateIt->id.'" action="'.
		$core->blog->url.$core->url->getBase('rateItpostform').'/'.$_rateIt->type.'/'.$_rateIt->id.'" method="post"><p>';

		for($i=0;$i<$_rateIt->quotient;$i++)
		{
			$dis = $rateit_voted ? ' disabled="disabled"' : '';
			$chk = $_rateIt->note > $i && $_rateIt->note <= $i+1 ? ' checked="checked"' : '';
			$res .= '<input name="rateit-'.$_rateIt->type.'-'.$_rateIt->id.'" class="rateit-'.$_rateIt->type.'-'.$_rateIt->id.'" type="radio" value="'.($i+1).'"'.$chk.$dis.' />';
		}
		if (!$rateit_voted)
		{
			$res .= '<input class="rateit-submit" name="rateit_submit" type="submit" value="'.__('Vote').'" />';
		}
		$res .= 
		'</p></form>'.
		'</div><br />&nbsp;';

		return $res;
	}
}
?>