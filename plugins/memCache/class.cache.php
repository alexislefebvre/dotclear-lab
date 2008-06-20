<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class dcMemCache
{
 	/**
 	 * Memcache connexion
 	 */
	private $con = null;
 	/**
 	 * Memcache host
 	 */	
	protected $cache_host;
 	/**
 	 * Memcache port
 	 */	
	protected $cache_port;	
 	/**
 	 * compression active ?
 	 */		
	const compress = 0;
 	/**
 	 * expiration? see memcached doc
 	 */		
	const expiration = 0;	
	
	public function __construct($cache_host, $cache_port)
	{
		$this->con = new Memcache();
		if (!$this->con->connect($cache_host, $cache_port)) {
			throw new Exception('MemCache: Unable to connect to memcached.');			
		}
	}
	
	public function storePage($key,$content_type,$content,$mtime)
	{
		$val = $mtime."|".$content_type."|".$content;
    	if (!$this->con->set(md5($key), $val, self::compress, self::expiration)) {
    		throw new Exception('MemCache: unable to set a value');
	   	}
	}
	
	public function fetchPage($key,$mtime)
	{		
    	$result = $this->con->get(md5($key));
    	if (empty($result)) {
    		return false;
    	}	
    	$lim = strpos($result, '|');
    	$page_mtime = substr($result, 0, $lim);
    	$result = substr($result, $lim+1, strlen($result));
    	$lim = strpos($result, '|');
    	$content_type = substr($result, 0, $lim);
    	$content = substr($result, $lim+1, strlen($result));
    	

		if ($mtime > $page_mtime) {
			return false;
		}
		
		header('Content-Type: '.$content_type.'; charset=UTF-8');
		header('X-Dotclear-Mem-Cache: true; mtime: '.$page_mtime);
		echo $content;
		return true;
	}
	
	public function dropPage($key)
	{	
		if (!$this->con->delete(md5($key))) {
			throw new Exception('Memcache: unable to drop a page.');
		}
	}
	
	public function getPageMTime($key)
	{		
    	$result = $this->con->get(md5($key));
    	if (empty($result)) {
    		return false;
    	}	
    	$lim = strpos($result, '|');
    	return (int) substr($result, 0, $lim);
	}
}
?>