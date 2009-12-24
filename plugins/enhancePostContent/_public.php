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
		 || !self::testTag($core,$tag,$args) 
		) return;

		$url = $core->blog->url.$core->url->getBase('tag').'/';
		$html_list = self::decodeTags($core->blog->settings->enhancePostContent_notagTags);

		$meta = new dcMeta($core);
		$tags = $meta->getMeta('tag');

		while($tags->fetch())
		{
			$k = $tags->meta_id;
			$args[0] = self::regularizeString($k,'<a class="post-tag" href="'.$url.$k.'" title="'.__('Tag').'">'.$k.'</a>',$args[0],$html_list);
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
		 || !self::testTag($core,$tag,$args) 
		) return;

		$html_list = self::decodeTags($core->blog->settings->enhancePostContent_notagSearch);
		$searchs = explode(' ',$GLOBALS['_search']);

		foreach($searchs as $k => $v)
		{
			$args[0] = self::regularizeString($k,'<span class="post-search" title="'.__('Search').'">'.$k.'</span>',$args[0],$html_list);
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
		 || !self::testTag($core,$tag,$args) 
		) return;

		$acronymes = @unserialize($core->blog->settings->enhancePostContent_listAcronymes);
		if (!is_array($acronymes) || empty($acronymes)) return;
		
		$html_list = self::decodeTags($core->blog->settings->enhancePostContent_notagAcronymes);

		foreach($acronymes as $k => $v)
		{
			$args[0] = self::regularizeString($k,'<span class="post-acronyme" title="'.__($v).'">'.$k.'</span>',$args[0],$html_list);
		}
		return;
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
		 || !self::testTag($core,$tag,$args) 
		) return;

		$links = @unserialize($core->blog->settings->enhancePostContent_listLinks);
		if (!is_array($links) || empty($links)) return;
		
		$html_list = self::decodeTags($core->blog->settings->enhancePostContent_notagLinks);

		foreach($links as $k => $v)
		{
			$args[0] = self::regularizeString($k,'<a class="post-link" title="'.$v.'" href="'.$v.'">'.$k.'</a>',$args[0],$html_list);
		}

		return;
	}

	private static function testTag($core,$tag,$args)
	{
		return (
		   ($tag == 'EntryExcerpt' && $core->blog->settings->enhancePostContent_onEntryExcerpt //tpl value EntryExcerpt
		 || $tag == 'EntryContent' && $core->blog->settings->enhancePostContent_onEntryContent) //tpl value EntryConent
		 && $args[0] != '' //content
		 && !$args[2] // remove html
		);
	}

	private static function regularizeString($p,$r,$s,$remove_tags=array(),$quote=true)
	{
		# Quote search
		if ($quote) {
			$p = self::quote($p);
		}
		# Mark words
		$s = preg_replace('#(\b)('.$p.')(\b)#s','$1ççççç$2ççççç$3',$s,-1,$count);
		# Nothing to parse
		if (!$count) return $s;
		# Remove words that are into unwanted html tags
		$tags = '';
		if (is_array($remove_tags) && !empty($remove_tags)) {
			$tags = implode('|',$remove_tags);
		}
		if (!empty($tags)) {
			$s = preg_replace_callback('#(<('.$tags.')[^>]*?>)(.*?)(</\\2>)#s',array('enhancePostContent','removeTags'),$s);
		}
		# Remove words inside html tag (class, title, alt, href, ...)
		$s = preg_replace('#(ççççç('.$p.')ççççç)(?=[^<]+>)#s','$2$3',$s);
		# Replace words by what you want
		return preg_replace('#ççççç'.$p.'ççççç#s',$r,$s);
	}

	private static function quote($s)
	{
		return preg_quote($s,'#');
	}

	public static function removeTags($m)
	{
		return $m[1].preg_replace('#ççççç(?!ççççç)#s','$1',$m[3]).$m[4];
	}

	private static function decodeTags($t)
	{
		return preg_match_all('#([A-Za-z0-9]+)#',(string) $t, $m) ? $m[1] : array();
	}
}
?>