<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of DL Manager.
# Copyright 2008 Moe (http://gniark.net/) and Tomtom (http://blog.zenstyle.fr)
#
# DL Manager is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# DL Manager is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

/**
@ingroup Download manager
@brief General class
*/
class dlManager
{
	/**
	return list of subdirectories
	@return	<b>array</b> Subdirectories
	*/
	public static function listDirs($in_jail=false)
	{
		global $core;

		# empty default value
		$dirs = array(''=> '');
		
		try
		{
			if (!is_object($core->media))
			{
				$core->media = new dcMedia($core);
			}
			# from gallery/gal.php
			foreach ($core->media->getRootDirs() as $v)
			{
				$path = $v->relname;
				if (($in_jail) && (self::inJail($path)))
					$dirs[$path] = $path;
				}
				else
				{
					$dirs[$path] = $path;
				}
			}
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
		
		return($dirs);
	}

	/**
	get sort values
	@param	empty_value	<b>boolean</b>	Add an empty value in the array
	@return	<b>array</b> sort values
	*/
	public static function getSortValues($empty_value=false)
	{
		$array = (($empty_value === true) ? array('' => '') : array());
		
		# from /dotclear/admin/media.php
		return(array_merge($array,array(
			__('By names, ascendant') => 'name-asc',
			__('By names, descendant') => 'name-desc',
			__('By dates, ascendant') => 'date-asc',
			__('By dates, descendant') => 'date-desc'
		)));
	}

	/**
	return DL Manager URL
	@return	<b>string</b> URL
	*/
	public static function pageURL()
	{
		global $core;

		return ($core->blog->url.$core->url->getBase('media'));
	}
	
	/**
	make BreadCrumb
	@param	dir	<b>string</b>	path directory
	@return	<b>array</b> BreadCrumb
	*/
	public static function breadCrumb($dir)
	{
		# BreadCrumb
		$base_url = self::pageURL().'/';
		$dirs = explode('/',$dir);
		$path = '';
		
		foreach ($dirs as $dir)
		{
			$dir = trim($dir);
			
			# check
			if (($dir == '.') OR ($dir == '..')) {self::p404();}
			
			if (!empty($dir))
			{
				$path = (($path == '') ? $dir : $path.'/'.$dir); 
				$breadCrumb[$dir] = $base_url.$path;
			}
		}
		
		if (empty($breadCrumb)) {$breadCrumb = array();}
		
		return($breadCrumb);
	}
	
	/**
	test if a file or a directory is in "jail"
	@param	path	<b>string</b>	path
	@return	<b>boolean</b> BreadCrumb
	*/
	public static function inJail($path)
	{
		global $core;
		
		$root = $core->blog->settings->dlmanager_root;
		
		if (!empty($root) && (strpos($path,$root) !== 0))
		{
			return false;
		}
		
		return true;
	}
	
	/**
	find entries containing this media
	@param	path	<b>string</b>	path
	@return	<b>boolean</b> BreadCrumb
	*/
	public static function findPosts($id)
	{
		global $core;
		
		$file = $core->media->getFile($id);
		
		# from /dotclear/admin/media_item.php
		$params = array(
			'post_type' => '',
			'from' => 'LEFT OUTER JOIN '.$core->prefix.'post_media PM ON P.post_id = PM.post_id ',
			'sql' => 'AND ('.
				'PM.media_id = '.(integer) $id.' '.
				"OR post_content_xhtml LIKE '%".$core->con->escape($file->relname)."%' ".
				"OR post_excerpt_xhtml LIKE '%".$core->con->escape($file->relname)."%' "
		);
		
		if ($file->media_image)
		{ # We look for thumbnails too
			$media_root = $core->blog->host.path::clean($core->blog->settings->public_url).'/';
			foreach ($file->media_thumb as $v) {
				$v = preg_replace('/^'.preg_quote($media_root,'/').'/','',$v);
				$params['sql'] .= "OR post_content_xhtml LIKE '%".$core->con->escape($v)."%' ";
				$params['sql'] .= "OR post_excerpt_xhtml LIKE '%".$core->con->escape($v)."%' ";
			}
		}
		
		$params['sql'] .= ') ';
		
		$rs = $core->blog->getPosts($params);
		
		return $rs;
	}
}

?>