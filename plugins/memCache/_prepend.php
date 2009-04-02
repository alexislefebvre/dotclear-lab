<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of MemCache, a plugin for Dotclear 2.
#
# Copyright (c) 2008-2009 Alain Vagner, Pep and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

if (!defined('DC_MC_CACHE_ENABLE')) {
	define('DC_MC_CACHE_ENABLE',class_exists('Memcache'));
}

if (!defined('DC_MC_CACHE_HOST')) {
	define('DC_MC_CACHE_HOST', 'localhost');
}

if (!defined('DC_MC_CACHE_PORT')) {
	define('DC_MC_CACHE_PORT', 11211);
}

if (!defined('DC_MC_CACHE_PERSISTENT')) {
	define('DC_MC_CACHE_PERSISTENT', false);
}

if (!defined('DC_MC_CACHE_SCHEDULED')) {
	define('DC_MC_CACHE_SCHEDULED',false);
}

if (!DC_MC_CACHE_ENABLE) return;

$GLOBALS['__autoload']['dcMemCache'] = dirname(__FILE__).'/class.cache.php';

$core->addBehavior('urlHandlerServeDocument',	array('dcMemCacheBehaviors','urlHandlerServeDocument'));
$core->addBehavior('publicBeforeDocument',		array('dcMemCacheBehaviors','publicBeforeDocument'));
$core->addBehavior('coreBlogAfterTriggerBlog',	array('dcMemCacheBehaviors','coreBlogAfterTriggerBlog'));

class dcMemCacheBehaviors
{
	public static function urlHandlerServeDocument(&$result)
	{
		try
		{
			$cache = new dcMemCache($_SERVER['REQUEST_URI'],DC_MC_CACHE_HOST,DC_MC_CACHE_PORT,DC_MC_CACHE_PERSISTENT);
			
			$do_cache = true;
			
			# We have POST data, no cache
			if (!empty($_POST)) $do_cache = false;
			
			# This is a post with a password, no cache
			if ($result['tpl'] == 'post.html' && $GLOBALS['_ctx']->posts->post_password) {
				$do_cache = false;
			}
			
			if ($do_cache)	{
				# No POST data or COOKIE, do cache
				$cache->storePage($result['content_type'],$result['content'],$result['blogupddt']);
			}
			else	{
				# Remove cache file
				$cache->dropPage();
			}
		}
		catch (Exception $e) {}
	}
	
	public static function publicBeforeDocument(&$core)
	{
		if (!empty($_POST)) return;
		
		try {
			$cache = new dcMemCache($_SERVER['REQUEST_URI'],DC_MC_CACHE_HOST,DC_MC_CACHE_PORT,DC_MC_CACHE_PERSISTENT);
			$ts = $cache->getPageMTime();
			
			if ($ts !== false) {
				if (DC_MC_CACHE_SCHEDULED) {
					if ($core->blog->url == http::getSelfURI()) {
						$core->blog->publishScheduledEntries();
					}
				}
				$blog_ts = $GLOBALS['core']->blog->upddt;
				dcMemCache::httpCache(array($ts,$blog_ts));
				if ($cache->fetchPage($blog_ts)) {
					exit;
				}
			}
		}
		catch (Exception $e) {}
	}
	
	public static function coreBlogAfterTriggerBlog($cur)
	{
		global $core;
		
		try {
			$cache = new dcMemCache(null,DC_MC_CACHE_HOST,DC_MC_CACHE_PORT,DC_MC_CACHE_PERSISTENT);
			$host = preg_replace('#^(http://(?:.+?))/(.*)$#','$1',$core->blog->url);
			$cache->setBlogMTime($host,$core->blog->id);
		}
		catch (Exception $e) {}
	}
}
?>