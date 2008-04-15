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