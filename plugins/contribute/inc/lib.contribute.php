<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Contribute, a plugin for Dotclear 2
# Copyright (C) 2008,2009,2010 Moe (http://gniark.net/)
#
# Contribute is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Contribute is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
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
	get Tags
	@return	<b>array</b> Tags array
	\see /dotclear/plugins/metadata/class.dc.meta.php > getMeta()
	\note $meta->getMeta('tag') break the login when adding a post, we avoid it
	*/
	public static function getTags()
	{
		global $core;
		
		$strReq = 'SELECT meta_id, COUNT(M.post_id) as count '.
		'FROM '.$core->prefix.'meta M '.
		'LEFT JOIN '.$core->prefix.'post P ON M.post_id = P.post_id '.
		"WHERE P.blog_id = '".$core->con->escape($core->blog->id)."' ";
		
		$strReq .= " AND meta_type = 'tag' ";
		
		$strReq .= 'AND ((post_status = 1) AND (post_password IS NULL)) ';
		
		$strReq .=
		'GROUP BY meta_id';
		
		$rs = $core->con->select($strReq);

		$tags = array();
		
		while ($rs->fetch())
		{
			$tags[] = $rs->meta_id;		
		}
		
		return $tags;
	}
	
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
			$core->blog->settings->contribute->contribute_mymeta_values));
		
		if (!is_array($mymeta_values)) {$mymeta_values = array();}
		
		foreach ($mymeta->getAll() as $meta)
		{
			# section
			if ($meta instanceof myMetaSection)
			{
				$active = in_array($meta->id,$mymeta_values);
				
				$array[] = array(
					'id' => $meta->id,
					'type' => 'section',
					'prompt' => $meta->prompt,
					'active' => $active
				);
			}
			elseif ($meta->enabled)
			{
				$active = in_array($meta->id,$mymeta_values);
				
				if ($meta->getMetaTypeId() == 'list')
				{
					$values = $meta->values;
				}
				else
				{
					$values = '';
				}
				
				if ($all || $active)
				{
					$array[] = array(
						'id' => $meta->id,
						'type' => $meta->getMetaTypeId(),
						'prompt' => $meta->prompt,
						'default' => $meta->default,
						'values' => $values,
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
		
		if (!$core->blog->settings->contribute->contribute_allow_mymeta) {return;}
		
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
	
	/**
	filter HTML
	@param	str	<b>string</b>	String to filter
	@return	<b>string</b> Filtered string
	\see /dotclear/inc/core/class.dc.core.php
	*/
	public static function HTMLfilter($str)
	{
		$filter = new htmlFilter;
		$str = trim($filter->apply($str));
		return $str;
	}
}

?>