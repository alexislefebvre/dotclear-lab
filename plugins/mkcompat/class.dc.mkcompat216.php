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
		$oldTags = array(
			'MetaData','MetaDataHeader','MetaDataFooter','MetaID','MetaPercent',
			'MetaRoundPercent','MetaURL','MetaAllURL','EntryMetaData'
			);

		
		if (!is_dir($theme_path.'/tpl'))
			return false;
		
		$dir_contents = files::getDirList($theme_path.'/tpl');
		foreach ($dir_contents['files'] as $file)
		{
			if (basename($file) == '404.html' &&
				strpos(file_get_contents($file),
					'The document you are looking for does not exists.') != false)
				return true;
			if (files::getExtension($file) == 'html')
			{
				$contents = file_get_contents($file);
				foreach ($oldTags as $tag)
				{
					if (strpos($contents,'tpl:'.$tag) != false)
						return true;
				}
			}
		}
		
		return false;
	}
	
	public static function themeUpgrade ($theme_path)
	{
		$dir_contents = files::getDirList($theme_path);
		foreach ($dir_contents['files'] as $file) self::themeFileUpdate($file);	
	}
	
	public static function themeFileUpdate($filename)
	{
		$oldTags = array(
			'MetaData','MetaDataHeader','MetaDataFooter','MetaID','MetaPercent',
			'MetaRoundPercent','MetaURL','MetaAllURL','EntryMetaData',
			'The document you are looking for does not exists.'
			);
		$newTags = array(
			'Tags','TagsHeader','TagsFooter','TagID','TagPercent',
			'TagRoundPercent','TagURL','TagCloudURL','EntryTags',
			'The document you are looking for does not exist.'
		);
		
		if (!$contents = file_get_contents  ($filename))
			throw new exception (__('cannot read file: ').$filename);
		
		$newcontents = str_replace($oldTags,$newTags,$contents,$count);
		if ($count > 0) files::putContent($filename,$newcontents);
	}
	
	public static function pluginNeedUpgrade ($theme_path)
	{
		$dir_contents = files::getDirList($theme_path);
		foreach ($dir_contents['files'] as $file)
		{
			if (files::getExtension($file) == 'js')
				if (strpos(file_get_contents($file),'[@') != false)
					return true;
			if (files::getExtension($file) == 'php')
				if (strpos($contents = file_get_contents($file),'[@') != false)
					if (preg_match_all('/<script(.*?)<\/script>/s',$contents,$scripts))
					{
						foreach ($scripts[0] as $script)
							if (strpos($script,'[@') != false)
								return true;
					}
		}
		return false;
	}
	
	public static function pluginUpgrade ($plugin_path)
	{
		$dir_contents = files::getDirList($plugin_path);
		foreach ($dir_contents['files'] as $file)
		{
			if (files::getExtension($file) == 'js') self::pluginFileUpdateJS($file);
			if (files::getExtension($file) == 'php') self::pluginFileUpdatePHP($file);
		}
	}
	
	public static function pluginFileUpdateJS($filename)
	{
		if (!$contents = file_get_contents  ($filename))
			throw new exception (__('cannot read file: ').$filename);
		
		$newcontents = str_replace('[@','[',$contents,$count);
		if ($count > 0) files::putContent($filename,$newcontents);
	}
	
	public static function pluginFileUpdatePHP($file_path)
	{
	}
}
?>
