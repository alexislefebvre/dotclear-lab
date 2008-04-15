<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2008 Olivier Meunier and contributors. All rights
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

if (!defined('DC_MC_CACHE_ENABLE')) {
	define('DC_MC_CACHE_ENABLE',true);
}

if (!defined('DC_MC_CACHE_HOST')) {
	define('DC_MC_CACHE_HOST', 'localhost');
}

if (!defined('DC_MC_CACHE_PORT')) {
	define('DC_MC_CACHE_PORT', 11211);
}


if (!DC_MC_CACHE_ENABLE) {
	return;
}

# We need touch function
if (!class_exists('Memcache')) {
	return;
}

$GLOBALS['__autoload']['dcMemCache'] = dirname(__FILE__).'/class.cache.php';

$core->addBehavior('urlHandlerServeDocument',array('dcMemCacheBehaviors','urlHandlerServeDocument'));
$core->addBehavior('publicBeforeDocument',array('dcMemCacheBehaviors','publicBeforeDocument'));

class dcMemCacheBehaviors
{
	public static function urlHandlerServeDocument(&$result)
	{
		try
		{
			$cache = new dcMemCache(DC_MC_CACHE_HOST, DC_MC_CACHE_PORT);
			
			$do_cache = true;
			
			# We have POST data, no cache
			if (!empty($_POST)) {
				$do_cache = false;
			}
			
			# This is a post with a password, no cache
			if ($result['tpl'] == 'post.html' && $GLOBALS['_ctx']->posts->post_password) {
				$do_cache = false;
			}
			
			if ($do_cache)
			{
				# No POST data or COOKIE, do cache
				$cache->storePage($_SERVER['REQUEST_URI'],$result['content_type'],$result['content'],$result['blogupddt']);
			}
			else
			{
				# Remove cache file
				$cache->dropPage($_SERVER['REQUEST_URI']);
			}
		}
		catch (Exception $e) {}
	}
	
	public static function publicBeforeDocument(&$core)
	{
		if (!empty($_POST)) {
			return;
		}
		
		try
		{
			$cache = new dcMemCache(DC_MC_CACHE_HOST, DC_MC_CACHE_PORT);
			$ts = $cache->getPageMTime($_SERVER['REQUEST_URI']);
			
			if ($ts !== false)
			{
				$mod_ts = array_merge(array($ts), $GLOBALS['mod_ts']);
				self::cache($mod_ts);
				if ($cache->fetchPage($_SERVER['REQUEST_URI'],$GLOBALS['core']->blog->upddt)) {
					exit;
				}
			}
		}
		catch (Exception $e) {}
	}
	
	public static function cache($mod_ts=array())
	{	
		rsort($mod_ts);
		$ts = $mod_ts[0];
		
		$since = NULL;
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			$since = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
			$since = preg_replace ('/^(.*)(Mon|Tue|Wed|Thu|Fri|Sat|Sun)(.*)(GMT)(.*)/', '$2$3 GMT', $since);
			$since = strtotime($since);
		}
		
		# Common headers list
		$headers[] = 'Last-Modified: '.gmdate('D, d M Y H:i:s',$ts).' GMT';
		$headers[] = 'Cache-Control: must-revalidate, max-age=0';		
		$headers[] = 'Pragma:';
		
		if ($since >= $ts)
		{
			http::head(304,'Not Modified');
			foreach ($headers as $v) {
				header($v);
			}
			exit;
		}
		else
		{
			header('Date: '.gmdate('D, d M Y H:i:s').' GMT');
			foreach ($headers as $v) {
				header($v);
			}
		}
	}
}
?>