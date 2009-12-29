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
	
	public static function search($q,$filters = null,$limit = null,$count_only = false)
	{
		self::initEngines();
		
		self::$engines->setFilters($filters);
		
		$res = staticRecord::newFromArray(self::$engines->search($q,$limit,$count_only));
		$res->extend('dcSearchEngine');
		$res->extend('dcOpenSearchRsExtensions');
		
		return $res;
	}
	
	public static function getLabel($rs)
	{
		$e = self::$engines->getEngines();
		
		foreach ($e as $k => $v) {
			if ($v->type === $rs->search_type) { return $v->label; }
		}
	}
}

?>