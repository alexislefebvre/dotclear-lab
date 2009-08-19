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

class dcApcCache
{
	private 	$mc = null;
	protected $apc_key;
	protected $apc_slot;

	public function __construct($uri)
	{
		$more_key = '';
		if (!defined('DC_APC_CACHE_BRUTE') && isset($GLOBALS['core'])) {
			$more_key = $GLOBALS['core']->blog->settings->theme;
		}
		$this->apc_key = md5(http::getHost().$more_key.$uri);
	}

	public function setBlogMTime($host,$blogid)
	{
		$key = md5('bmt:'.$host.':'.$blogid);
		return apc_store($key,time());
	}
	
	public function getBlogMTime($blogid)
	{
		$key = md5('bmt:'.http::getHost().':'.$blogid);
		return apc_fetch($key);
	}
	
	public function storePage($content_type,$content,$mtime)
	{
		$val = array($mtime,$content_type,$content);
    		if (!apc_store($this->apc_key,$val,604800)) {
    			throw new Exception('apcCache: unable to set a value');
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
			header('X-Dotclear-Apc-Cache: true; mtime: '.date('c',$content_mtime));
			echo $content;
			return true;
		}
		return false;
	}

	public function dropPage()
	{
		$this->apc_slot = null;
		if (!apc_delete($this->memcache_key)) {
			throw new Exception('apcCache: unable to drop a page.');
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
		if (!$this->apc_slot) {
			$this->apc_slot = apc_fetch($this->apc_key);
		}
		if (!$this->apc_slot || !(is_array($this->apc_slot) && count($this->apc_slot) == 3)) {
			return false;
		}
		return $this->apc_slot;
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