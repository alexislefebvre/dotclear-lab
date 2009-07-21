<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pacKman, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) return;

class dcPackman
{
	public static $exclude = array('.','..','__MACOSX','.svn','CVS','.DS_Store','Thumbs.db');

	public static function quote_exclude(&$exclude)
	{
		foreach($exclude AS $k => $v) {
			$exclude[$k] = '#(^|/)('.str_replace(array('.','*'),array('\.','.*?'),trim($v)).')(/|$)#';
		}
	}

	public static function path($root,$filename,$module)
	{
		$filename = str_replace(
			array('%type%','%id%','%version%','%author%','%time%'),
			array($module['type'],$module['id'],$module['version'],$module['author'],time()),
			$filename
		);
		return $root.'/'.$filename.'.zip';
	}

	public static function pack($module,$dest,$exclude=array())
	{
		$exclude = array_merge(self::$exclude,$exclude);
		self::quote_exclude($exclude);

		@set_time_limit(300);
		$fp = fopen($dest,'wb');
		$zip = new fileZip($fp);

		foreach($exclude AS $k => $e) {
			$zip->addExclusion($e);
		}

		$zip->addDirectory(path::real($module['root']),$module['id'],true);

		$zip->write();
		$zip->close();
		unset($zip);

		return true;
	}

	public static function getPackages($core,$root)
	{
		if (!is_dir($root) || !is_readable($root)) return;

		$files = files::scanDir($root);
		$zip_files = array();
		foreach($files AS $file) {
			if (!preg_match('#(^|/)(.*?)\.zip(/|$)#',$file)) continue;

			$zip_files[] = $file;
		}

		if (empty($zip_files)) return;

		$modules = new dcModules($core);

		$res = array();
		foreach($zip_files AS $zip_file) {

			$zip = new fileUnzip($root.'/'.$zip_file);

			$zip_root_dir = $zip->getRootDir();

			if ($zip_root_dir != false) {
				$define = $zip_root_dir.'/_define.php';
				$has_define = $zip->hasFile($define);
			} else {
				$define = '_define.php';
				$has_define = $zip->hasFile($define);
			}

			if (!$has_define) continue;

			$zip->unzip($define,DC_TPL_CACHE.'/packman/_define.php');

			$modules->requireDefine(DC_TPL_CACHE.'/packman',$zip_root_dir);
			$res[$zip_root_dir] = $modules->getModules($zip_root_dir);
			$res[$zip_root_dir]['root'] = $root.'/'.$zip_file;

			unlink(DC_TPL_CACHE.'/packman/_define.php');
		}
		return $res;
	}
}

?>