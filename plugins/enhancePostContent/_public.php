<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of enhancePostContent, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('publicBeforeContentFilter',array('enhancePostContent','filterTags'));
$core->addBehavior('publicHeadContent',array('enhancePostContent','styleTags'));

$core->addBehavior('publicBeforeContentFilter',array('enhancePostContent','filterSearch'));
$core->addBehavior('publicHeadContent',array('enhancePostContent','styleSearch'));

$core->addBehavior('publicBeforeContentFilter',array('enhancePostContent','filterAcronymes'));
$core->addBehavior('publicHeadContent',array('enhancePostContent','styleAcronymes'));

class enhancePostContent
{
	public static function styleTags($core)
	{
		if (!$core->blog->settings->enhancePostContent_filterTags
		 || !$core->blog->settings->enhancePostContent_styleTags
		) return;

		echo 
		"\n<!-- CSS for enhancePostContent Tags --> \n".
		"<style type=\"text/css\"> \n".
		"a.post-tag {".
		html::escapeHTML($core->blog->settings->enhancePostContent_styleTags).
		"} \n".
		"</style> \n";
	}

	public static function filterTags($core,$tag,$args)
	{
		if (!$core->plugins->moduleExists('metadata') // plugin metadata
		 || !$core->blog->settings->enhancePostContent_filterTags //enable
		 || $tag != 'EntryContent' //tpl value
		 || $args[0] == '' //content
		 || $args[2] // remove html
		) return;

		$url = $core->blog->url.$core->url->getBase('tag').'/';

		$meta = new dcMeta($core);
		$tags = $meta->getMeta('tag');

		$res = array();
		while($tags->fetch())
		{
			$tag = $tags->meta_id;
			$res[0][] = '/(\A|[\s]+)('.preg_quote($tag,'/').')([\s|\.]+|\Z)/ms';
			$res[1][] = '$1<a class="post-tag" href="'.$url.'$2" title="'.__('Tag').'">$2</a>$3';
		}
		if (!empty($res))
		{
			$args[0] = preg_replace($res[0],$res[1],$args[0]);
		}
		return;
	}

	public static function styleSearch($core)
	{
		if (!$core->blog->settings->enhancePostContent_filterSearch
		 || !$core->blog->settings->enhancePostContent_styleSearch
		) return;

		echo 
		"\n<!-- CSS for enhancePostContent Search --> \n".
		"<style type=\"text/css\"> \n".
		"span.post-search {".
		html::escapeHTML($core->blog->settings->enhancePostContent_styleSearch).
		"} \n".
		"</style> \n";
	}

	public static function filterSearch($core,$tag,$args)
	{
		if (!isset($GLOBALS['_search']) // search page
		 || !$core->blog->settings->enhancePostContent_filterSearch //enable
		 || $tag != 'EntryContent' //tpl value
		 || $args[0] == '' //content
		 || $args[2] // remove html
		) return;

		$searchs = explode(' ',$GLOBALS['_search']);

		$res = array();
		foreach($searchs as $search)
		{
			$res[0][] = '/(>[^<]*)('.preg_quote($search,'/').')([^<]*<)/i';
			$res[1][] = '$1<span class="post-search" title="'.__('Search').'">$2</span>$3';
		}
		if (!empty($res))
		{
			$args[0] = preg_replace($res[0],$res[1],$args[0]);
		}
		return;
	}

	public static function styleAcronymes($core)
	{
		if (!$core->blog->settings->enhancePostContent_filterAcronymes
		 || !$core->blog->settings->enhancePostContent_styleAcronymes
		 || !$core->blog->settings->enhancePostContent_listAcronymes
		) return;

		echo 
		"\n<!-- CSS for enhancePostContent Acronymes --> \n".
		"<style type=\"text/css\"> \n".
		"span.post-acronyme {".
		html::escapeHTML($core->blog->settings->enhancePostContent_styleAcronymes).
		"} \n".
		"</style> \n";
	}

	public static function filterAcronymes($core,$tag,$args)
	{
		if (!$core->blog->settings->enhancePostContent_filterAcronymes //enable
		 || !$core->blog->settings->enhancePostContent_listAcronymes //list
		 || $tag != 'EntryContent' //tpl value
		 || $args[0] == '' //content
		 || $args[2] // remove html
		) return;

		$acronymes = @unserialize($core->blog->settings->enhancePostContent_listAcronymes);
		if (!is_array($acronymes) || empty($acronymes)) return;

		$res = array();
		foreach($acronymes as $acro_key => $acro_val)
		{
			$res[0][] = '/(>[^<]*)('.preg_quote($acro_key,'/').')([^<]*<)/';
			$res[1][] = '$1<span class="post-acronyme" title="'.__($acro_val).'">$2</span>$3';
		}
		if (!empty($res))
		{
			$args[0] = preg_replace($res[0],$res[1],$args[0]);
		}
		return;
	}
}
?>