<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Micro-Blogging, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jeremie Patonnier
# jeremie.patonnier@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

/**
 * Cache management class
 * 
 * @author jeremie
 * @package microBlog
 */
class microBlogCache
{
	/**
	 * Path to the cache directory
	 * 
	 * @var string
	 */
	private $cache_dir;
	
	/**
	 * Cache maximum lifetime in second
	 * 
	 * @var int
	 */
	private $cache_max_lifetime;
	
	
	/**
	 * Class constructor
	 * 
	 * @param $maxLifeTime int
	 */
	public function __construct($max_lifetime = 3600)
	{
		$this->cache_dir = DC_TPL_CACHE."/microblog";
		$this->setMaxLifeTime($max_lifetime);
		
		if (!is_dir($this->cache_dir))
			mkdir($this->cache_dir, 0777);
	}
	
	
	/**
	 * Method to set the cache lifetime
	 * 
	 * @param $lifetime int the new lifetime in seconds
	 */
	public function setMaxLifeTime($max_lifetime)
	{
		$this->cache_max_lifetime = (int)$max_lifetime;
	}
	
	
	/**
	 * Build a cache for the value $val
	 * 
	 * @param $key string
	 * @param $val mixed
	 * @return bool
	 */
	public function set($key,$val)
	{
		$path = $this->cache_dir."/".md5($key);
		$val  = serialize($val);
		
		$size = file_put_contents($path, $val);
		chmod($path, 0777);
		clearstatcache();
		
		return $size > 0;
	}
	
	
	/**
	 * Get a cached content if still alive
	 * 
	 * @param $key string
	 * @return mixed
	 */
	public function get($key)
	{
		$path = $this->cache_dir."/".md5($key);
		
		if (!file_exists($path)) return NULL;
		
		$lt = $this->lifetime($key);
		
		if ($lt > $this->cache_max_lifetime) {
			$this->delete($key);
			return NULL;
		}
		
		$val = file_get_contents($path);
		
		return unserialize($val);
	}
	
	
	/**
	 * Delete a cache
	 * 
	 * @param $key
	 * @return bool
	 */
	public function delete($key)
	{
		$path = $this->cache_dir."/".md5($key);
		
		if (!file_exists($path)) return true;
		
		$out = unlink($path);
		clearstatcache();
		
		return $out;
	}
	
	
	/**
	 * Give the lifetime of a cache in seconds
	 * 
	 * @param $key
	 * @return int
	 */
	public function lifetime($key)
	{
		$path = $this->cache_dir."/".md5($key);
		
		if (!file_exists($path)) return 0;
		
		$out = time() - filemtime($path);
	}
}