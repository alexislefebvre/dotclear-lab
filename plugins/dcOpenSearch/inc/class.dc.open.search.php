<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcOpenSearch, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcOpenSearch
{
	public static $engines;
	
	public static function initEngines()
	{
		global $core;
		
		if (!isset($core->searchengines) || !is_array($core->searchengines)) {
			return;
		}
		
		self::$engines = new dcSearchEngines($core);
		self::$engines->init($core->searchengines);
	}
	
	public static function search($q,$filters = null,$count_only = false,$limit = null)
	{
		self::initEngines();
		if ($filters !== null && is_array($filters)) {
			self::$engines->init($filters);
		}
		$search = self::$engines->search($q,$count_only);
		$res = array();
		
		if ($limit !== null && is_array($limit)) {
			for ($i = $limit[0]; $i < $limit[0]+$limit[1]; $i++) {
				if (isset($search[$i])) { $res[$i] = $search[$i]; }
			}
		}
		else {
			$res = $search;
		}
		$res = staticRecord::newFromArray($res);
		$res->extend('dcSearchEngine');
		$res->extend('dcOpenSearchRsExtensions');
		
		return $res;
	}
}

?>