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

if ($core->blog->settings->enhancePostContent_active)
{
	$core->addBehavior('publicHeadContent',array('enhancePostContent','publicHeadContent'));
	$core->addBehavior('publicBeforeContentFilter',array('enhancePostContent','filterTag'));
	$core->addBehavior('publicBeforeContentFilter',array('enhancePostContent','filterSearch'));
	$core->addBehavior('publicBeforeContentFilter',array('enhancePostContent','filterAcronym'));
	$core->addBehavior('publicBeforeContentFilter',array('enhancePostContent','filterLink'));
	$core->addBehavior('publicBeforeContentFilter',array('enhancePostContent','filterWord'));
}

class enhancePostContent
{
	public static function publicHeadContent($core)
	{
		$css = array(
			'Tag' => 'a.epc-tag',
			'Search' => 'span.epc-search',
			'Acronym' => 'acronym.epc-acronym',
			'Link' => 'a.epc-link',
			'Word' => 'span.epc-word'
		);
		foreach($css as $k => $v)
		{
			$s = self::getSettings($core,$k);
			if (empty($s['style'])) continue;

			echo 
			"\n<!-- CSS for enhancePostContent ".$k." --> \n".
			"<style type=\"text/css\"> \n".
			$v." {".html::escapeHTML($s['style'])."} \n".
			"</style> \n";
		}
	}

	public static function filterTag($core,$tag,$args)
	{
		$s = self::getSettings($core,'Tag');

		if (!$core->plugins->moduleExists('metadata') || !self::testTag($tag,$args,$s)) return;

		$meta = new dcMeta($core);
		$metas = $meta->getMeta('tag');

		while($metas->fetch())
		{
			$k = $metas->meta_id;
			$args[0] = self::regularizeString(
				$k,
				'<a class="epc-tag" href="'.$core->blog->url.$core->url->getBase('tag').'/'.$k.'" title="'.__('Tag').'">\\1</a>',
				$args[0],
				self::decodeTags($s['notag']),
				$s['nocase'],
				$s['plural']
			);
		}
		return;
	}

	public static function filterSearch($core,$tag,$args)
	{
		$s = self::getSettings($core,'Search');

		if (!isset($GLOBALS['_search']) || !self::testTag($tag,$args,$s)) return;

		$searchs = explode(' ',$GLOBALS['_search']);

		foreach($searchs as $k => $v)
		{
			$args[0] = self::regularizeString(
				$v,
				'<span class="epc-search" title="'.__('Search').'">\\1</span>',
				$args[0],
				self::decodeTags($s['notag']),
				$s['nocase'],
				$s['plural']
			);
		}
		return;
	}

	public static function filterAcronym($core,$tag,$args)
	{
		$s = self::getSettings($core,'Acronym');

		if (empty($s['list']) || !self::testTag($tag,$args,$s)) return;

		foreach($s['list'] as $k => $v)
		{
			$args[0] = self::regularizeString(
				$k,
				'<acronym class="epc-acronym" title="'.__($v).'">\\1</acronym>',
				$args[0],
				self::decodeTags($s['notag']),
				$s['nocase'],
				$s['plural']
			);
		}
		return;
	}

	public static function filterLink($core,$tag,$args)
	{
		$s = self::getSettings($core,'Link');

		if (empty($s['list']) || !self::testTag($tag,$args,$s)) return;

		foreach($s['list'] as $k => $v)
		{
			$args[0] = self::regularizeString(
				$k,
				'<a class="epc-link" title="'.$v.'" href="'.$v.'">\\1</a>',
				$args[0],
				self::decodeTags($s['notag']),
				$s['nocase'],
				$s['plural']
			);
		}
		return;
	}

	public static function filterWord($core,$tag,$args)
	{
		$s = self::getSettings($core,'Word');

		if (empty($s['list']) || !self::testTag($tag,$args,$s)) return;

		foreach($s['list'] as $k => $v)
		{
			$args[0] = self::regularizeString(
				$k,
				'<span class="epc-word">'.$v.'\\2</span>',
				$args[0],
				self::decodeTags($s['notag']),
				$s['nocase'],
				$s['plural']
			);
		}
		return;
	}

	private static function testTag($tag,$args,$opt)
	{
		return (
		(
			$tag == 'EntryExcerpt' && $opt['onEntryExcerpt'] 
		 || $tag == 'EntryContent' && $opt['onEntryContent'] 
		 || $tag == 'CommentContent' && $opt['onCommentContent'] 
		)
		 && $args[0] != '' //content
		 && !$args[2] // remove html
		);
	}

	private static function regularizeString($p,$r,$s,$remove_tags=array(),$insensitive=false,$plural=false,$quote=true)
	{
		# Quote search
		if ($quote) {
			$p = self::quote($p);
		}
		# Case sensitive
		$i = $insensitive ? 'i' : '';
		# Plural
		$x = $plural ? $p.'s|'.$p : $p;
		# Mark words
		$s = preg_replace('#(\b)('.$x.')(\b)#s'.$i,'$1ççççç$2ççççç$3',$s,-1,$count);
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
		$s = preg_replace('#(ççççç('.$p.'(s|))ççççç)(?=[^<]+>)#s'.$i,'$2$4',$s);
		# Replace words by what you want
		return preg_replace('#ççççç('.$p.'(s|))ççççç#s'.$i,$r,$s);
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

	private function getSettings($core,$name)
	{
		$name = 'enhancePostContent_'.$name;
		$s = @unserialize($core->blog->settings->$name);
		if (!is_array($s)) $s = array();
		$s = array(
			'onEntryExcerpt' => isset($s['onEntryExcerpt']) ? (boolean) $s['onEntryExcerpt'] : false,
			'onEntryContent' => isset($s['onEntryContent']) ? (boolean) $s['onEntryContent'] : false,
			'onCommentContent' => isset($s['onCommentContent']) ? (boolean) $s['onCommentContent'] : false,
			'nocase' => isset($s['nocase']) ? (boolean) $s['nocase'] : false,
			'plural' => isset($s['plural']) ? (boolean) $s['plural'] : false,
			'style' => isset($s['style']) ? (string) $s['style'] : '',
			'notag' => isset($s['notag']) ? (string) $s['notag'] : ''
		);

		$nameList = $name.'List';
		$list = @unserialize($core->blog->settings->$nameList);
		$s['list'] = !is_array($list) ? array() : $list;

		return $s;
	}
}
?>