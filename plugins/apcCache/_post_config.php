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

# This file needs to be called at the end of your configuration
# file. See README for more details

if (!defined('DC_APC_CACHE_ENABLE')) {
	define('DC_APC_CACHE_ENABLE',(function_exists('apc_cache_info') && @apc_cache_info('user')));
}

if (!defined('DC_APC_CACHE_SCHEDULED')) {
	define('DC_APC_CACHE_SCHEDULED',false);
}

if (!DC_APC_CACHE_ENABLE) return;

define('DC_APC_CACHE_BRUTE',true);

if (defined('DC_BLOG_ID')) // Public area detection
{
	require dirname(__FILE__).'/class.cache.php';
	if (!empty($_POST)) return;
	
	try	{
		$cache = new dcApcCache($_SERVER['REQUEST_URI']);	
		
		if (($blog_ts = $cache->getBlogMTime(DC_BLOG_ID)) === false) {
			throw new Exception();
		}
		
		if (($ts = $cache->getPageMTime()) !== false) {
			dcMemCache::httpCache(array($ts,$blog_ts));
			if ($cache->fetchPage($blog_ts)) {
				exit;
			}
		}
		unset($cache);
	}
	catch (Exception $e) {
		unset($cache);
	}
}
?>