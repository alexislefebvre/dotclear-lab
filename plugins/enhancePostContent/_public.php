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

$core->addBehavior('publicBeforeContentFilter',array('enhancePostContent','filterLinks'));
$core->addBehavior('publicHeadContent',array('enhancePostContent','styleLinks'));

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

		while($tags->fetch())
		{
			$args[0] = self::regularizeTags($args[0],$tags->meta_id,$url);
		}

		return;
	}

	private static function regularizeTags($src,$tag,$url)
	{
		# Mark words
		$src = preg_replace('/(\b)('.preg_quote($tag,'/').')(\b)/','$1ççççç$2ççççç$3',$src,-1,$count);
		# Nothing to parse
		if (!$count) return $src;
		# Remove words that are already links
		$src = preg_replace('/(<a[^>]*?[^<]*?)(ççççç('.preg_quote($tag,'/').')ççççç)([^>]*?<)/','$1$3$4',$src);
		# Remove words inside html tag (class, title, alt, href, ...)
		$src = preg_replace('/(<[^>]*?)(ççççç('.preg_quote($tag,'/').')ççççç)([^<]*?>)/','$1$3$4',$src);
		# Replace words by links
		return str_replace('ççççç'.$tag.'ççççç','<a class="post-tag" href="'.$url.'$2" title="'.__('Tag').'">'.$tag.'</a>',$src);
	}

	public static function styleSearch($core)
	{
		if (!$core->blog->settings->enhancePostContent_filterSearch
		 || !$core->blog->settings->enhancePostContent_styleSearch
		) return;

		echo 
		"\n<!-- CSS for enhancePostContent Search --> \n".
		"<style type=\"text/css\"> ".
		"span.post-search {".
		html::escapeHTML($core->blog->settings->enhancePostContent_styleSearch).
		"} ".
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

		foreach($searchs as $k => $v)
		{
			$args[0] = self::regularizeAcronymes($args[0],$k,$v);
		}
		return;
	}

	private static function regularizeSearch($src,$k,$v)
	{
		# Mark words
		$src = preg_replace('/(\b)('.preg_quote($k,'/').')(\b)/','$1ççççç$2ççççç$3',$src,-1,$count);
		# Nothing to parse
		if (!$count) return $src;
		# Remove words inside html tag (class, title, alt, href, ...)
		$src = preg_replace('/(<[^>]*?)(ççççç('.preg_quote($k,'/').')ççççç)([^<]*?>)/','$1$3$4',$src);
		# Replace words by links
		return str_replace('ççççç'.$k.'ççççç','<span class="post-search" title="'.__('Search').'">'.$k.'</span>',$src);
	}

	public static function styleAcronymes($core)
	{
		if (!$core->blog->settings->enhancePostContent_filterAcronymes
		 || !$core->blog->settings->enhancePostContent_styleAcronymes
		 || !$core->blog->settings->enhancePostContent_listAcronymes
		) return;

		echo 
		"\n<!-- CSS for enhancePostContent Acronymes --> \n".
		"<style type=\"text/css\"> ".
		"span.post-acronyme {".
		html::escapeHTML($core->blog->settings->enhancePostContent_styleAcronymes).
		"} ".
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

		foreach($acronymes as $k => $v)
		{
			$args[0] = self::regularizeAcronymes($args[0],$k,$v);
		}
		return;
	}

	private static function regularizeAcronymes($src,$k,$v)
	{
		# Mark words
		$src = preg_replace('/(\b)('.preg_quote($k,'/').')(\b)/','$1ççççç$2ççççç$3',$src,-1,$count);
		# Nothing to parse
		if (!$count) return $src;
		# Remove words inside html tag (class, title, alt, href, ...)
		$src = preg_replace('/(<[^>]*?)(ççççç('.preg_quote($k,'/').')ççççç)([^<]*?>)/','$1$3$4',$src);
		# Replace words by links
		return str_replace('ççççç'.$k.'ççççç','<span class="post-acronyme" title="'.__($v).'">'.$k.'</span>',$src);
	}

	public static function styleLinks($core)
	{
		if (!$core->blog->settings->enhancePostContent_filterLinks
		 || !$core->blog->settings->enhancePostContent_styleLinks
		 || !$core->blog->settings->enhancePostContent_listLinks
		) return;

		echo 
		"\n<!-- CSS for enhancePostContent Links --> \n".
		"<style type=\"text/css\"> ".
		"a.post-link {".
		html::escapeHTML($core->blog->settings->enhancePostContent_styleLinks).
		"} ".
		"</style> \n";
	}

	public static function filterLinks($core,$tag,$args)
	{
		if (!$core->blog->settings->enhancePostContent_filterLinks //enable
		 || !$core->blog->settings->enhancePostContent_listLinks //list
		 || $tag != 'EntryContent' //tpl value
		 || $args[0] == '' //content
		 || $args[2] // remove html
		) return;

		$links = @unserialize($core->blog->settings->enhancePostContent_listLinks);
		if (!is_array($links) || empty($links)) return;

		foreach($links as $k => $v)
		{
			$args[0] = self::regularizeLinks($args[0],$k,$v);
		}

		return;
	}

	private static function regularizeLinks($src,$k,$v)
	{
		# Mark words
		$src = preg_replace('/(\b)('.preg_quote($k,'/').')(\b)/','$1ççççç$2ççççç$3',$src,-1,$count);
		# Nothing to parse
		if (!$count) return $src;
		# Remove words that are already links
		$src = preg_replace('/(<a[^>]*?[^<]*?)(ççççç('.preg_quote($k,'/').')ççççç)([^>]*?<)/','$1$3$4',$src);
		# Remove words inside html tag (class, title, alt, href, ...)
		$src = preg_replace('/(<[^>]*?)(ççççç('.preg_quote($k,'/').')ççççç)([^<]*?>)/','$1$3$4',$src);
		# Replace words by links
		return str_replace('ççççç'.$k.'ççççç','<a class="post-link" title="'.$v.'" href="'.$v.'">'.$k.'</a>',$src);
	}
}
?>