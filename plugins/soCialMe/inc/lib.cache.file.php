<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMe, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}# Standard class to manage cache file fo soCialMe addonsclass soCialMeCacheFile{	# Test if a cached file exists	public static function exists($word,$ext)	{		$cache_path = self::named($word,$ext);				return !empty($cache_path);	}		# Test if a cached file has expired	public static function expired($word,$ext,$timeout=900)	{		$cache_path = self::named($word,$ext);				if (!$cache_path || !is_readable($cache_path)) return true;				@clearstatcache($cache_path);				return time() > (@filemtime($cache_path) + $timeout);	}		# Read a cached file	public static function read($word,$ext)	{		$cache_path = self::named($word,$ext);				if (!$cache_path || !is_readable($cache_path)) return null;				return file_get_contents($cache_path);	}
	
	# Create a cached file
	public static function write($word,$ext,$content='')
	{
		$cache_path = self::named($word,$ext);
		
		if (empty($cache_path)) return false;
		
		# Make dir
		if (!is_dir(dirname($cache_path)))
		{
			files::makeDir(dirname($cache_path),true);
		}
		
		# Make file
		$fp = @fopen($cache_path,'w');
		if ($fp === false) return false;
		
		fwrite($fp,$content,strlen($content));
		fclose($fp);
		
		return true;
	}
	
	# Update time of a cached file
	public static function touch($word,$ext)
	{
		$cache_path = self::named($word,$ext);
		
		if (empty($cache_path)) return false;
		
		# Make dir
		if (!is_dir(dirname($cache_path)))
		{
			files::makeDir(dirname($cache_path),true);
		}
		
		# Make file
		if (!file_exists($cache_path)) {
			$fp = @fopen($cache_path,'w');
			if ($fp === false) return false;
			
			fwrite($fp,' ',1);
			fclose($fp);
		}
		else {
			files::touch($cache_path);
		}
		
		return true;
	}		# Generate name of a cached file	private static function named($word,$ext)	{		if (empty($word) || empty($ext)) return false;				# Cache writable ?		if (!is_writable(DC_TPL_CACHE)) return false;				# Set file path		$f_md5 = md5($word);		$cache_path = sprintf('%s/%s/%s/%s/%s.'.$ext,			DC_TPL_CACHE,			'socialme',			substr($f_md5,0,2),			substr($f_md5,2,2),			$f_md5		);				# Real path		return path::real($cache_path,false);	}}
?>