<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

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