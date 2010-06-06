<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of muppet, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class muppet
{
	public static function setting()
	{
		global $core;
		return muppetSettings($core);
	}
	
	public static function getPostTypes()
	{
		$s = self::setting();
		return @unserialize($s->muppet_types);
	}

	public static function getExcludePostTypes()
	{
		$s = self::setting();
		return @unserialize($s->muppet_excludes);
	}

	public static function typeIsExcluded($type)
	{
		$s = self::setting();
		$excluded = self::getExcludePostTypes();
		if (array_key_exists($type,array_flip($excluded)))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public static function typeExists($type)
	{
		$s = self::setting();
		$my_types = self::getPostTypes();
		if (array_key_exists($type,$my_types))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public static function setNewPostType($type,$values)
	{
		$s = self::setting();
		$current_types = self::getPostTypes();
		$current_types[$type] = array(
			'name' => $values['name'],
			'plural' => $values['plural'],
			'icon' => $values['icon'],
			'perm' => 'manage'.$type
		);
		$s->put('muppet_types',serialize($current_types),'string','My supplementary post types');
	}
	
	public static function updateAllPostType($types)
	{
		$s = self::setting();
		$current_types = array();
		
		foreach ($types as $k => $v)
		{
			if (!empty($v['name']))
			{
				$current_types[$k] = array(
					'name' => $v['name'],
					'plural' => $v['plural'],
					'icon' => $v['icon'],
					'perm' => 'manage'.$k
				);
			}
		}
		
		$s->put('muppet_types',serialize($current_types),'string','My supplementary post types');
	}


	public static function removePostType($type)
	{
		global $core;
		$s = muppetSettings($core);
		$current_types = self::getPostTypes();
		unset($current_types[$type]);
		$s->put('muppet_types',serialize($current_types),'string','My supplementary post types');
	}

	public static function getInBasePostTypesCounter()
	{
		global $core;

		$strReq =
		'SELECT P.post_type, COUNT(P.post_id) AS nb_post '.
		'FROM '.$core->prefix.'post P '.
		"WHERE P.blog_id = '".$core->con->escape($core->blog->id)."' ".
		'GROUP BY P.post_type ';

		$rs = $core->con->select($strReq);
		$counters = array();
		while ($rs->fetch()) {
			$counters[$rs->post_type] = $rs->nb_post;
		}
		return $counters;
	}
}
?>
