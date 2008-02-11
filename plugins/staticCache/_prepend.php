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

if (!defined('DC_SC_CACHE_ENABLE')) {
	define('DC_SC_CACHE_ENABLE',true);
}

if (!defined('DC_SC_CACHE_DIR')) {
	define('DC_SC_CACHE_DIR',DC_TPL_CACHE.'/dcstaticcache');
}

if (!DC_SC_CACHE_ENABLE) {
	return;
}

# We need touch function
if (!function_exists('touch')) {
	return;
}

$GLOBALS['__autoload']['dcStaticCache'] = dirname(__FILE__).'/class.cache.php';

$core->addBehavior('urlHandlerServeDocument',array('dcStaticCacheBehaviors','urlHandlerServeDocument'));
$core->addBehavior('publicBeforeDocument',array('dcStaticCacheBehaviors','publicBeforeDocument'));

class dcStaticCacheBehaviors
{
	public static function urlHandlerServeDocument(&$result)
	{
		try
		{
			$cache = new dcStaticCache(DC_SC_CACHE_DIR,md5(http::getHost()));
			
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
			$cache = new dcStaticCache(DC_SC_CACHE_DIR,md5(http::getHost()));
			$file = $cache->getPageFile($_SERVER['REQUEST_URI']);
			
			if ($file !== false)
			{
				http::cache(array($file),$GLOBALS['mod_ts']);
				if ($cache->fetchPage($_SERVER['REQUEST_URI'],$GLOBALS['core']->blog->upddt)) {
					exit;
				}
			}
		}
		catch (Exception $e) {}
	}
}
?>