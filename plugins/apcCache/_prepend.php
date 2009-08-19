<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of ApcCache, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Pep and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

if (!defined('DC_APC_CACHE_ENABLE')) {
	define('DC_APC_CACHE_ENABLE',(function_exists('apc_cache_info') && @apc_cache_info('user')));
}

if (!defined('DC_APC_CACHE_SCHEDULED')) {
	define('DC_APC_CACHE_SCHEDULED',false);
}

if (!DC_APC_CACHE_ENABLE) return;

$GLOBALS['__autoload']['dcApcCache'] = dirname(__FILE__).'/class.cache.php';

$core->addBehavior('urlHandlerServeDocument',	array('dcApcCacheBehaviors','urlHandlerServeDocument'));
$core->addBehavior('publicBeforeDocument',		array('dcApcCacheBehaviors','publicBeforeDocument'));
$core->addBehavior('coreBlogAfterTriggerBlog',	array('dcApcCacheBehaviors','coreBlogAfterTriggerBlog'));

class dcApcCacheBehaviors
{
	public static function urlHandlerServeDocument(&$result)
	{
		try
		{
			$cache = new dcApcCache($_SERVER['REQUEST_URI']);
			
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
			$cache = new dcApcCache($_SERVER['REQUEST_URI']);
			$ts = $cache->getPageMTime();
			
			if ($ts !== false) {
				if (DC_APC_CACHE_SCHEDULED) {
					if ($core->blog->url == http::getSelfURI()) {
						$core->blog->publishScheduledEntries();
					}
				}
				$blog_ts = $GLOBALS['core']->blog->upddt;
				dcApcCache::httpCache(array($ts,$blog_ts));
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
			$cache = new dcApcCache(null);
			$host = preg_replace('#^(http://(?:.+?))/(.*)$#','$1',$core->blog->url);
			$cache->setBlogMTime($host,$core->blog->id);
		}
		catch (Exception $e) {}
	}
}
?>