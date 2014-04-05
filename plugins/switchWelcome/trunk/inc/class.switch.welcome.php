<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of switchWelcome, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) {return;}
class switchWelcome
{
	public static function getHostReferer($ref = null)
	{
		$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $ref;
		$host = isset($ref) ? parse_url($ref) : $ref;
		
		return isset($host) ? $host['host'] : $ref;
	}
	
	public static function getHostRefererLink()
	{
		$res = '';
		
		if (($host = self::getHostReferer()) !== null) {
			$res = sprintf('<a href="%1$s">%2$s</a>',$_SERVER['HTTP_REFERER'],$host);
		}
		else {
			$res = __('middle of nowhere');
		}
		
		return $res;
	}
	
	public static function getSearchWordsList($block = '%s',$item = ' <span class="keyword">%s</span> ')
	{
		$list = array();
		
		foreach (self::getSearchWords() as $k => $v) {
			$list[] = sprintf($item,$v);
		}
		
		return !empty($list) ? sprintf($block,implode(__('and'),$list)) : '';
	}
	
	public static function getRelatedPosts($block = '<ul>%s</ul>',$item = '<li>%s</li>')
	{
		$list = '';
		$kw = self::getSearchWords();
		$search = implode(' ',$kw);
		
		if (count($kw) > 0) {
			$rs = $GLOBALS['core']->blog->getPosts(array('search' => $search));
			while ($rs->fetch()) {
				$link = sprintf('<a href="%1$s">%2$s</a>',$rs->getURL(),$rs->post_title);
				$list .= sprintf($item,$link);
			}
		}
		
		$list = empty($list) ? sprintf($item,__('No result')) : $list;
		
		return sprintf($block,$list);
	}
	
	public static function getSearchWords()
	{
		$kw = array();
		
		$url = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : null;
		
		if ($url !== null && isset($url['query'])) {
			parse_str($url['query'],$var);
		}
		
		if (isset($var['q'])) {
			$str = preg_replace('/\s{1,}/','+',strtolower(urldecode($var['q'])));
			$kw = explode('+',$str);
		}

		return $kw;
	}
}