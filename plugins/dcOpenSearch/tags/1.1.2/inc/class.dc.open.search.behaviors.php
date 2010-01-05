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

class dcOpenSearchBehaviors
{
	public static function addTplPath()
	{
		global $core;
		
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/../default-templates');
	}
	
	public static function addJS()
	{
		echo '<script type="text/javascript" src="'.$GLOBALS['core']->blog->url.'pf=dcOpenSearch/js/public.min.js"></script>'."\n";
	}
}

class dcOpenSearchURL extends dcUrlHandlers
{	
	public static function getResults($core)
	{
		global $core, $_ctx, $_page_number;
		
		$GLOBALS['_filter'] = isset($_GET['f']) ? $_GET['f'] : array();
		
		$GLOBALS['_search'] = isset($_GET['qos']) ? rawurldecode($_GET['qos']) : '';
		
		if (isset($_GET['qos']) && !empty($_GET['qos'])) {
			$GLOBALS['_search_count'] = dcOpenSearch::search($GLOBALS['_search'],$GLOBALS['_filter'],null,true)->f(0);
			
			$part = $core->url->mode == 'path_info' ? substr($_SERVER['PATH_INFO'],1) : $_SERVER['QUERY_STRING'];
			
			if (preg_match('#(^|/)page/([0-9]+)(&.*)?$#',$part,$m)) {
				$_page_number = (integer) $m[2];
			}
			
			self::serveDocument('dcOpenSearch.html');
			exit;
		}
	}
}

?>