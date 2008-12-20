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

if (!defined('DC_RC_PATH')) {return;}

/**
@ingroup Contribute
@brief General class
*/
class contribute
{
	/**
	get My Meta values
	@param	array	<b>fileItem</b>	File item
	@return	<b>record</b> Image metadata
	*/
	public static function getMyMeta()
	{
		global $core,$_ctx;
		
		if (!$core->blog->settings->contribute_allow_mymeta) {return;}
		
		$array = array();
		
		if (!$_ctx->contribute->mymeta->hasMeta())
		{
			return(staticRecord::newFromArray($array));
		}
		
		foreach ($_ctx->contribute->mymeta->getAll() as $k => $v)
		{
			if ($v->enabled)
			{
				$array[] = array(
					'id' => $k,
					'type' => $v->type,
					'prompt' => $v->prompt,
					'values' => $v->values
				);
			}
		}
		
		return(staticRecord::newFromArray($array));
	}
	
	/**
	get My Meta values
	@param	array	<b>fileItem</b>	File item
	@return	<b>record</b> Image metadata
	*/
	public static function getMyMetaValues($values)
	{
		global $core;
		
		if (!$core->blog->settings->contribute_allow_mymeta) {return;}
		
		$array = array();
		
		if (empty($values))
		{
			return(staticRecord::newFromArray($array));
		}
				
		foreach ($values as $k => $v)
		{
			$array[] = array(
				'id' => $v,
				'description' => $k
			);
		}
		
		return(staticRecord::newFromArray($array));
	}
}

?>