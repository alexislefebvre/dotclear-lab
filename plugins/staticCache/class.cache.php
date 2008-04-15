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

class dcStaticCache
{
	protected $cache_dir;
	protected $cache_key;
	
	public function __construct($cache_dir,$cache_key)
	{
		$cache_dir = path::real($cache_dir,false);
		
		if (!is_dir($cache_dir)) {
			files::makeDir($cache_dir);
		}
		
		if (!is_writable($cache_dir)) {
			throw new Exception('Cache directory is not writable.');
		}
		
		$k = str_split($cache_key,2);
		
		$this->cache_dir = sprintf('%s/%s/%s/%s/%s',$cache_dir,$k[0],$k[1],$k[2],$cache_key);
	}
	
	public function storePage($key,$content_type,$content,$mtime)
	{
		$file = $this->getCacheFileName($key);
		$dir = dirname($file);
		$tmp_file = $dir.'/._'.basename($file);
		
		if (!is_dir($dir)) {
			files::makeDir($dir,true);
		}
		
		$fp = @fopen($tmp_file,'wb');
		if (!$fp) {
			throw new Exception('Unable to create cache file.');
		}
		
		fwrite($fp,$content_type."\n\n");
		fwrite($fp,$content);
		fclose($fp);
		
		if (file_exists($file)) {
			unlink($file);
		}
		rename($tmp_file,$file);
		touch($file,$mtime);
		files::inheritChmod($file);
	}
	
	public function fetchPage($key,$mtime)
	{
		$file = $this->getCacheFileName($key);
		if (!file_exists($file) || !is_readable($file) || !files::isDeletable($file)) {
			return false;
		}
		
		$page_mtime = filemtime($file);
		if ($mtime > $page_mtime) {
			return false;
		}
		
		$fp = @fopen($file,'rb');
		if (!$fp) {
			return false;
		}
		
		$content_type = trim(fgets($fp));
		fgets($fp);
		
		header('Content-Type: '.$content_type.'; charset=UTF-8');
		header('X-Dotclear-Static-Cache: true; mtime: '.$page_mtime);
		fpassthru($fp);
		fclose($fp);
		return true;
	}
	
	public function dropPage($key)
	{
		$file = $this->getCacheFileName($key);
		if (!file_exists($file) || !files::isDeletable($file)) {
			return false;
		}
		
		unlink($file);
	}
	
	public function getPageFile($key)
	{
		$file = $this->getCacheFileName($key);
		if (file_exists($file)) {
			return $file;
		}
		return false;
	}
	
	protected function getCacheFileName($key)
	{
		$key = md5($key);
		$k = str_split($key,2);
		return $this->cache_dir.'/'.sprintf('%s/%s/%s/%s',$k[0],$k[1],$k[2],$key);
	}
}
?>