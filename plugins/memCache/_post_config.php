<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of MemCache, a plugin for Dotclear 2.
#
# Copyright (c) 2008-2010  Pep, Alain Vagner and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

# This file needs to be called at the end of your configuration
# file. See README for more details

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

define('DC_MC_CACHE_BRUTE',true);

if (defined('DC_BLOG_ID')) // Public area detection
{
	require dirname(__FILE__).'/class.cache.php';
	if (!empty($_POST)) return;
	
	try	{
		$cache = new dcMemCache($_SERVER['REQUEST_URI'],DC_MC_CACHE_HOST,DC_MC_CACHE_PORT,DC_MC_CACHE_PERSISTENT);	
		
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