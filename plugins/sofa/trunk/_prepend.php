<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of sofa, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('urlHandlerGetArgsDocument',array('sofaBehaviors','urlHandlerGetArgsDocument'));
$core->addBehavior('templateBeforeBlock',array('sofaBehaviors','templateBeforeBlock'));
$core->addBehavior('publicHeadContent',array('sofaBehaviors','publicHeadContent'));

require dirname(__FILE__).'/_widgets.php';

class sofaBehaviors
{
	public static function urlHandlerGetArgsDocument($url)
	{
		# Sort by
		$p = '#/sort/([^/]+)/(asc|desc)#i';
		if (preg_match($p,$url->args,$m)) {
			$url->args = preg_replace($p,'',$url->args); 
			$GLOBALS['_sortby'] = $m[1];
			$GLOBALS['_order'] = $m[2];
		}
		#Filter by
		$p = '#/filter/([^/]+)/(\w+)#i';
		if (preg_match($p,$url->args,$m)) {
			$url->args = preg_replace($p,'',$url->args); 
			$GLOBALS['_filterby'] = $m[1];
			$GLOBALS['_filterid'] = $m[2];
		}
	}
	
	public static function templateBeforeBlock($core,$b,$attr)
	{
		global $_ctx;
		
		if ($b === 'Entries') {
			if (isset($GLOBALS['_sortby']) && isset($GLOBALS['_order'])) {
				$attr['sortby'] = $GLOBALS['_sortby'];
				$attr['order'] = $GLOBALS['_order'];
			}
			if (isset($GLOBALS['_filterby']) && isset($GLOBALS['_filterid'])) {
				switch ($GLOBALS['_filterby']) {
					case 'category':
						$attr['category'] = $GLOBALS['_filterid'];
						break;
					case 'author':
						$attr['author'] = $GLOBALS['_filterid'];
						break;
					case 'selected':
						$attr['selected'] = true;
						break;
				}
			}
		}
	}
	
	public static function publicHeadContent($core)
	{
		$css = $core->blog->settings->sofa->css;
		
		if ($css !== '' && netHttp::readURL($css)) {
			echo
			"<style type=\"text/css\">\n".
			"@import url(".$css.");\n".
			"</style>\n";
		}
	}
}

?>