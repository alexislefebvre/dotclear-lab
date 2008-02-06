<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

# Menu template functions

/* require dirname(__FILE__).'/_widgets.php'; */

$core->tpl->addValue('MenuFreshy',array('tplMenuFreshy','menu'));
$core->tpl->addValue('IfCurrentLinkMenu',array('tplMenuFreshy','IfCurrentLinkMenu'));
/*
$core->tpl->addValue('MenuXbelLink',array('tplMenu','menuXbelLink'));
$core->url->register('menuxbel','menuxbel','^menu/xbel(?:/?)$',array('urlMenu','menuxbel'));
*/

class tplMenuFreshy
{
	public static function IfCurrentLinkMenu($attr)
	{
		if ($_SERVER['REQUEST_URI']){}
	}
	public static function menu($attr)
	{
		$category = '<h3>%s</h3>';
		$block = '<ul class="menu">%s</ul>';
		$item = '<li>%s</li>';
		$open_ul = "";
		$close_ul = "";
/*		
		if (isset($attr['category'])) {
			$category = addslashes($attr['category']);
			if ($category == "0") {
				$category = null; $block='%s'; $open_ul = "<ul class=\"menu\">"; $close_ul = "</ul>";
			}
		}
*/		
		if (isset($attr['block'])) {
			$block = addslashes($attr['block']);
		}
		
		if (isset($attr['item'])) {
			$item = addslashes($attr['item']);
		}
		
		return
		$open_ul."\n".
		'<?php '.
		"echo tplMenuFreshy::getList('".$category."','".$block."','".$item."'); ".
		'?>'.
		$close_ul."\n";
	}

/*	
	public static function menuXbelLink($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("menuxbel")').'; ?>';
	}
*/
	
	public static function getList($category='<h3>%s</h3>',$block='<ul class="menu">%s</ul>',$item='<li>%s</li>')
	{
		require_once dirname(__FILE__).'/class.dc.blogmenu.php';
		$menu = new dcBlogMenu($GLOBALS['core']->blog);
		
		try {
			$links = $menu->getLinks();
		} catch (Exception $e) {
			return false;
		}
		
		$res = '';
		
		foreach ($menu->getLinksHierarchy($links) as $k => $v)
		{
			if ($k != '') {
				$res .= sprintf($category,html::escapeHTML($k))."\n";
			}
			
			$res .= self::getLinksList($v,$block,$item);
		}
		
		return $res;
	}
	
	private static function getLinksList($links,$block='<ul class="menu">%s</ul>',$item='<li>%s</li>')
	{
		global $core;  // Pour avoir accès a l'url du blog
		$list = '';
		$url = $_SERVER['REQUEST_URI'];
		
		$first = true;
		foreach ($links as $v)
		{
			$title = $v['link_title'];
			$href  = $v['link_href'];
			$desc = $v['link_desc'];
			$lang  = $v['link_lang'];
			$xfn = $v['link_xfn'];
			

			// Si c'est le premier on lui met une classlien
			if ($first==true){
				$classlien=" class=\"first_menu\" ";
				$first=false;
			} else {
				$classlien="";
			}	
			
			// Si ce doit être le dernier
			if ($xfn=="me"){
				$classlast=" last_menu";
				$classlienlast=" class=\"last_menu\""; 
				$classitem=$classlast;
			} else {
				$classlast="";
				$classlienlast="";
				$classitem="page_item";
			}	
			
			$link =
			'<a href="'.html::escapeHTML($href).'"'.
			((!$lang) ? '' : ' hreflang="'.html::escapeHTML($lang).'"').
			((!$desc) ? '' : ' title="'.html::escapeHTML($desc).'"').
			((!$xfn) ? '' : ' rel="'.html::escapeHTML($xfn).'"').
			$classlien.$classlienlast.
			'>'.
			html::escapeHTML($title).
			'</a>';
			
			// Si il faut tester aussi si page accuei
			if ($xfn=="accueil"){
				// Si nous sommes en accueil
				if ($core->url->type == 'default') {
					$item = '<li class="current_page_item '.$classitem.'">%s</li>';
				} else {
					$item = '<li class="'.$classitem.'">%s</li>';
				}
			} else {	
				if ($url == html::escapeHTML($href)) {
					$item = '<li class="current_page_item '.$classitem.'">%s</li>';
				} else {
					$item = '<li class="'.$classitem.'">%s</li>';			
				}
			}	
			$list .= sprintf($item,$link)."\n";
		}
		
		return sprintf($block,$list)."\n";
	}
	
/*	
	# Widget function
	public static function menuWidget(&$w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		return
		'<div class="menu">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		self::getList('<h3>%s</h3>','<ul>%s</ul>','<li>%s</li>').
		'</div>';
	}
*/	
}

/*
class urlMenu extends dcUrlHandlers
{
	public static function menuxbel($args)
	{
		require dirname(__FILE__).'/class.dc.menu.php';
		$menu = new dcBlogMenu($GLOBALS['core']->blog);
		
		try {
			$links = $menu->getLinks();
		} catch (Exception $e) {
			self::p404();
		}
		
		if ($args) {
			self::p404();
		}
		
		http::cache($GLOBALS['mod_files'],$GLOBALS['mod_ts']);
		
		header('Content-Type: text/xml; charset=UTF-8');
		
		echo
		'<?xml version="1.0" encoding="UTF-8"?>'."\n".
		'<!DOCTYPE xbel PUBLIC "+//IDN python.org//DTD XML Bookmark Exchange '.
		'Language 1.0//EN//XML"'."\n".
		'"http://www.python.org/topics/xml/dtds/xbel-1.0.dtd">'."\n".
		'<xbel version="1.0">'."\n".
		'<title>'.html::escapeHTML($GLOBALS['core']->blog->name)." menu</title>\n";
		
		$i = 1;
		foreach ($menu->getLinksHierarchy($links) as $cat_title => $links)
		{
			if ($cat_title != '') {
				echo
				'<folder>'."\n".
				"<title>".html::escapeHTML($cat_title)."</title>\n";
			}
			
			foreach ($links as $k => $v)
			{
				$lang = $v['link_lang'] ? ' xml:lang="'.$v['link_lang'].'"' : '';
				
				echo
				'<bookmark href="'.$v['link_href'].'"'.$lang.'>'."\n".
				'<title>'.html::escapeHTML($v['link_title'])."</title>\n";
				
				if ($v['link_desc']) {
					echo '<desc>'.html::escapeHTML($v['link_desc'])."</desc>\n";
				}
				
				if ($v['link_xfn']) {
					echo
					"<info>\n".
					'<metadata owner="http://gmpg.org/xfn/">'.$v['link_xfn']."</metadata>\n".
					"</info>\n";
				}
				
				echo
				"</bookmark>\n";
			}
			
			if ($cat_title != '') {
				echo "</folder>\n";
			}
			
			$i++;
		}
		
		echo
		'</xbel>';
		
		exit;
	}
}
*/
?>