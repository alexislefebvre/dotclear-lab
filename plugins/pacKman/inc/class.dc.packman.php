<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pacKman, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

class dcPackman
{
	public static $exclude = array('.','..','__MACOSX','.svn','CVS','.DS_Store','Thumbs.db');

	public static function quote_exclude($exclude)
	{
		foreach($exclude AS $k => $v)
		{
			$exclude[$k] = '#(^|/)('.str_replace(array('.','*'),array('\.','.*?'),trim($v)).')(/|$)#';
		}
		return $exclude;
	}

	public static function getPackages($core,$root)
	{
		$res = array();
		
		$cache = self::getCache().'/';
		if (!is_dir($root) || !is_readable($root)) return $res;
		
		$files = files::scanDir($root);
		$zip_files = array();
		foreach($files AS $file)
		{
			if (!preg_match('#(^|/)(.*?)\.zip(/|$)#',$file)) continue;
			$zip_files[] = $file;
		}
		
		if (empty($zip_files)) return $res;
		
		$modules = new dcModules($core);
		
		$i = 0;
		foreach($zip_files AS $zip_file)
		{
			$zip = new fileUnzip($root.'/'.$zip_file);
			
			$zip_root_dir = $zip->getRootDir();
			
			if ($zip_root_dir != false)
			{
				$define = $zip_root_dir.'/_define.php';
				$has_define = $zip->hasFile($define);
			}
			else
			{
				$define = '_define.php';
				$has_define = $zip->hasFile($define);
			}
			
			if (!$has_define) continue;
			
			$zip->unzip($define,$cache.'/_define.php');
			
			$modules->requireDefine($cache,$zip_root_dir);
			$res[$i] = $modules->getModules($zip_root_dir);
			$res[$i]['id'] = $zip_root_dir;
			$res[$i]['root'] = $root.'/'.$zip_file;
			
			unlink($cache.'_define.php');
			$i++;
		}
		return $res;
	}
	
	public static function pack($info,$root,$files,$overwrite=false,$exclude=array())
	{
		if (!($info = self::getInfo($info))) return false;
		if (!($root = self::getRoot($root))) return false;
		$exclude = self::getExclude($exclude);
		
		foreach($files as $file)
		{
			if (!($file = self::getFile($file,$info))) continue;
			if (!($dest = self::getOverwrite($overwrite,$root,$file))) continue;
			
			@set_time_limit(300);
			$fp = fopen($dest,'wb');
			$zip = new fileZip($fp);
			
			foreach($exclude AS $e)
			{
				$zip->addExclusion($e);
			}
			$zip->addDirectory(path::real($info['root']),$info['id'],true);
			
			$zip->write();
			$zip->close();
			unset($zip);
		}
		return true;
	}
	
	private function getRoot($root)
	{
		$root = path::real($root);
		if (!is_dir($root) || !is_writable($root))
		{
			throw new Exception('Directory is not writable');
			return null;
		}
		return $root;
	}
	
	private function getInfo($info)
	{
		if (!isset($info['root']) || !isset($info['id']) || !is_dir($info['root']))
		{
			throw new Exception('Failed to get module info');
			return null;
		}
		return $info;
	}
	
	private function getExclude($exclude)
	{
		$exclude = array_merge(self::$exclude,$exclude);
		return self::quote_exclude($exclude);
	}
	
	private function getFile($file,$info)
	{
		if (empty($file) || empty($info)) return null;
		
		$file = str_replace(
			array(
				'%type%',
				'%id%',
				'%version%',
				'%author%',
				'%time%'
			),
			array(
				$info['type'],
				$info['id'],
				$info['version'],
				$info['author'],
				time()
			),
			$file
		);
		$parts = explode('/',$file);
		foreach($parts as $i => $part)
		{
			$parts[$i] = files::tidyFileName($part);
		}
		return implode('/',$parts).'.zip';
	}
	
	private function getOverwrite($overwrite,$root,$file)
	{
		$path = $root.'/'.$file;
		if (file_exists($path) && !$overwrite)
		{
			// don't break loop
			//throw new Exception('File already exists');
			return null;
		}
		return $path;
	}
	
	private static function getCache()
	{
		$c = DC_TPL_CACHE.'/packman';
		if (!file_exists($c))
		{
			@mkdir($c);
		}
		if (!is_writable($c))
		{
			throw new Exception('Failed to get temporary directory');
			return null;
		}
		return $c;
	}
}
?>