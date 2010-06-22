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

class dcMemCache
{
	private 	$mc = null;
	protected $memcache_host;
	protected $memcache_port;
	protected $memcache_key;
	protected $memcache_slot;

	public function __construct($uri, $cache_host = 'localhost', $cache_port = 11211, $persist = false)
	{
		$this->mc = new Memcache();
		$mc_con_func = ($persist) ? 'pconnect' : 'connect';
		if (!$this->mc->$mc_con_func($cache_host, $cache_port)) {
			throw new Exception('cmpCache: Unable to connect to memcached.');
		}
		$this->memcache_host = $cache_host;
		$this->memcache_port = $cache_port;
		$more_key = '';
		if (!defined('DC_MC_CACHE_BRUTE') && isset($GLOBALS['core'])) {
			$more_key = $GLOBALS['core']->blog->settings->theme;
		}
		$this->memcache_key	 = md5(http::getHost().$more_key.$uri);
	}

	public function setBlogMTime($host,$blogid)
	{
		$key = md5('bmt:'.$host.':'.$blogid);
		return $this->mc->set($key,time());
	}
	
	public function getBlogMTime($blogid)
	{
		$key = md5('bmt:'.http::getHost().':'.$blogid);
		return $this->mc->get($key);
	}
	
	public function storePage($content_type,$content,$mtime)
	{
		$val = array($mtime,$content_type,$content);
    		if (!$this->mc->set($this->memcache_key,serialize($val),false,604800)) {
    			throw new Exception('memCache: unable to set a value');
	   	}
	}

	public function fetchPage($mtime)
	{
    		if ($result = $this->getSlotData()) {
    			$content_mtime = (integer)$result[0];
    			$content_type  = $result[1];
    			$content		= $result[2];

    			if ($mtime > $content_mtime) {
    				return false;
    			}

			header('Content-Type: '.$content_type.'; charset=UTF-8');
			header('X-Dotclear-Mem-Cache: true; mtime: '.date('c',$content_mtime));
			echo $content;
			return true;
		}
		return false;
	}

	public function dropPage()
	{
		$this->memcache_slot = null;
		if (!$this->mc->delete($this->memcache_key,0)) {
			throw new Exception('memCache: unable to drop a page.');
		}
	}

	public function getPageMTime()
	{
    		if ($result = $this->getSlotData()) {
    			return (integer)$result[0];
    		}
    		return false;
	}

	protected function getSlotData()
	{
		if (!$this->memcache_slot) {
			$this->memcache_slot = @unserialize($this->mc->get($this->memcache_key));
		}
		if (!$this->memcache_slot || !(is_array($this->memcache_slot) && count($this->memcache_slot) == 3)) {
			return false;
		}
		return $this->memcache_slot;
	}

	public static function httpCache($mod_ts=array())
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