<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of mkcompat, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Dotclear Team and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

class mkcompat {
	
	public static function themeNeedUpgrade ($theme_path)
	{
		if (!is_dir($theme_path.'/tpl'))
			return false;
		
		$dir_contents = files::getDirList($theme_path.'/tpl');
		foreach ($dir_contents['files'] as $file)
			if (self::checkFile($file))
				return true;
		
		return false;
	}
	
	public static function pluginNeedUpgrade ($theme_path)
	{
		$dir_contents = files::getDirList($theme_path);
		
		foreach ($dir_contents['files'] as $file)
			if (self::checkFile($file))
				return true;
		
		return false;
	}
	
	public static function moduleUpgrade ($plugin_path)
	{
		$dir_contents = files::getDirList($plugin_path);
		foreach ($dir_contents['files'] as $file)
			self::updateFile($file);
	}
	
	public static function checkFile_html ($filename)
	{
		$oldTags = array(
			'MetaData','MetaDataHeader','MetaDataFooter','MetaID','MetaPercent',
			'MetaRoundPercent','MetaURL','MetaAllURL','EntryMetaData'
		);
		
		if (basename($filename) == '404.html' &&
			strpos(file_get_contents($filename),
				'The document you are looking for does not exists.') != false)
			return true;
				
		$contents = file_get_contents($filename);
		foreach ($oldTags as $tag)
		{
			if (strpos($contents,'tpl:'.$tag) != false)
				return true;
		}
		return false;
	}
	
	public static function checkFile_php ($filename)
	{
		$pattern = '/(<script[^>]*>(?(?!script).)*)\[@(.*?<\/script>)/s';
		
		if (strpos($contents = file_get_contents($filename),'[@') != false)
			if (preg_match_all($pattern,$contents,$scripts))
				return true;
	}
	
	public static function checkFile_js ($filename)
	{
		if (strpos(file_get_contents($filename),'[@') != false)
			return true;
		return false;
	}
	
	public static function updateFile_html ($filename)
	{
		$oldTags = array(
			'tpl:MetaData','tpl:MetaID','tpl:MetaPercent','tpl:MetaRoundPercent',
			'tpl:MetaURL','tpl:MetaAllURL','tpl:EntryMetaData',
			'The document you are looking for does not exists.'
			);
		$newTags = array(
			'tpl:Tags','tpl:TagID','tpl:TagPercent','tpl:TagRoundPercent',
			'tpl:TagURL','tpl:TagCloudURL','tpl:EntryTags',
			'The document you are looking for does not exist.'
		);
		
		if (!$contents = file_get_contents  ($filename))
			throw new exception (__('cannot read file: ').$filename);
		
		$newcontents = str_replace($oldTags,$newTags,$contents,$count);
		if ($count > 0) files::putContent($filename,$newcontents);
	}
	
	public static function updateFile_php ($filename)
	{
		$pattern = '/(<script[^>]*>(?(?!script).)*)\[@(.*?<\/script>)/s';
		$replace = '$1[$2';
		
		if (!$contents = file_get_contents  ($filename))
			throw new exception (__('cannot read file: ').$filename);
		
		$newcontents = preg_replace($pattern,$replace,$contents,-1,$count);
		if ($count > 0)
		{
			while ($count > 0) $newcontents = preg_replace($pattern,$replace,$newcontents,-1,$count);
			files::putContent($filename,$newcontents);
		}
	}
	
	public static function updateFile_js ($filename)
	{
		if (!$contents = file_get_contents  ($filename))
			throw new exception (__('cannot read file: ').$filename);
		
		$newcontents = str_replace('[@','[',$contents,$count);
		if ($count > 0) files::putContent($filename,$newcontents);
}
	
	public static function checkFile ($filename)
	{
		$file_ext = files::getExtension($filename);
		
		if (method_exists(get_class(),'checkFile_'.$file_ext))
		{
			return call_user_func(array(get_class(),'checkFile_'.$file_ext),$filename);
		}
		else
		{
			return false;
		}		
	}
	
	public static function updateFile ($filename)
	{
		$file_ext = files::getExtension($filename);
		
		if (method_exists(get_class(),'updateFile_'.$file_ext))
			call_user_func(array(get_class(),'updateFile_'.$file_ext),$filename);
	}
}
?>
