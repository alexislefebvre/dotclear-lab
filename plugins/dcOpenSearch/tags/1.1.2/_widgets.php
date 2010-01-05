<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcOpenSearch, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('dcOpenSearchWidgets','initWidgets'));
$core->addBehavior('initDefaultWidgets',array('dc0penSearchWidgets','initDefaultWidgets'));

class dcOpenSearchWidgets
{
	public static function initWidgets($w)
	{
		global $core;
		
		$w->create('dcOpenSearch',__('Advanced search engine'),array('dcOpenSearchWidgets','publicWidget'));
		$w->dcOpenSearch->setting('title',__('Title:'),__('Search'));
	}
	
	public static function initDefaultWidgets($w,$d)
	{
		$d['nav']->append($w->dcOpenSearch);
	}
	
	public static function publicWidget($w)
	{
		global $core;
		
		dcOpenSearch::initEngines();
		$engines = dcOpenSearch::$engines->getEngines();
		
		$f = array();
		foreach ($engines as $eid => $e) {
			if ($e->active) {
				$f[] = '<li>'.form::checkbox(array('f[]'),$e->name,in_array($e->name,$GLOBALS['_filter'])).' '.$e->label.'</li>';
			}
		}
		$list = count($f) > 0 ? sprintf('<ul id="search-filter">%s</ul>',implode('',$f)) : '';
		
		$box =
			!empty($list) ?
			sprintf('<h3>%s</h3><div id="search-filters"><p>%s</p>%s</div>',
			__('Filters'),
			__('Exclude results come from:'),
			$list) :
			'';
		
		$value = isset($GLOBALS['_search']) ? html::escapeHTML($GLOBALS['_search']) : '';
		
		return
		'<div id="search">'.
		($w->title ? '<h2><label for="qos">'.html::escapeHTML($w->title).'</label></h2>' : '').
		'<form action="'.$core->blog->url.'" method="get">'.
		'<fieldset><p>'.
		form::field('qos',10,255,$value).
		'<input class="submit" type="submit" value="ok" />'.
		'</p>'.$box.
		'</fieldset>'.
		'</form>'.
		'</div>';
	}
}

?>