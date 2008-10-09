<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Public Media.
# Copyright 2008 Moe (http://gniark.net/)
#
# Public Media is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Public Media is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

/**
@ingroup Public Media
@brief General class
*/
class publicMedia
{
	/**
	return list of subdirectories
	@return	<b>array</b> Subdirectories
	*/
	public static function listDirs()
	{
		global $core;

		$dirs = array(''=> '');

		$public_path = path::real($core->blog->public_path);
		if (!is_dir($public_path)) {return(array());}

		# +1 : remove slash at the beginning of the path when using substr()
		$len_public_path = strlen($public_path)+1;

		$dirList = files::getDirList($public_path);

		foreach ($dirList['dirs'] as $dir)
		{
			if ($dir != $public_path)
			{
				$dir = substr($dir,$len_public_path);
				$dirs[$dir] = $dir;
			}
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
	return Media Page URL
	@return	<b>string</b> URL
	*/
	public static function pageURL()
	{
		global $core;

		return ($core->blog->url.$core->url->getBase('media'));
	}
}

?>