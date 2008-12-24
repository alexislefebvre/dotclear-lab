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
	@param	mymeta	<b>My Meta object</b>	My Meta
	@param	all	<b>boolean</b>	All the settings ?
	@return	<b>recordset</b> Image metadata
	*/
	public static function getMyMeta($mymeta,$all=false)
	{
		global $core,$_ctx;
		
		$array = array();
		
		if (($mymeta === false) || (!$mymeta->hasMeta()))
		{
			return(staticRecord::newFromArray($array));
		}
		
		$mymeta_values = @unserialize(@base64_decode(
			$core->blog->settings->contribute_mymeta_values));
		
		if (!is_array($mymeta_values)) {$mymeta_values = array();}
		
		foreach ($mymeta->getAll() as $k => $v)
		{
			if ($v->enabled)
			{
				$active = in_array($k,$mymeta_values);
				if ($all || $active)
				{
					$array[] = array(
						'id' => $k,
						'type' => $v->type,
						'prompt' => $v->prompt,
						'values' => $v->values,
						'active' => $active
					);
				}
			}
		}
		
		return(staticRecord::newFromArray($array));
	}
	
	/**
	get My Meta values
	@param	values	<b>array</b>	Values
	@return	<b>recordset</b> Values
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