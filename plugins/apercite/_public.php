<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Apercite, a plugin for Dotclear.
# 
# Copyright (c) 2009 Francis Besset
# francis.besset@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('publicHeadContent',array('apercitePublic','publicHeadContent'));

class apercitePublic
{
	public static function publicHeadContent(&$core)
	{
		if (!$core->blog->settings->apercite_enabled) {
			return;
		}
		
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		
		$size = ($core->blog->settings->apercite_size || $core->blog->settings->apercite_size === null ? explode('x',$core->blog->settings->apercite_size) : array(120,90));
		$javascript = ($core->blog->settings->apercite_javascript || $core->blog->settings->apercite_javascript === null ? 'oui' : 'non');
		$java = ($core->blog->settings->apercite_java || $core->blog->settings->apercite_java === null ? 'oui' : 'non');
		
		echo
		'<style type="text/css">'."\n".
		'@import url('.$url.'/css/apercite.css);'."\n".
		'</style>'."\n".
		'<script type="text/javascript" src="'.$url.'/js/apercite.js"></script>'."\n".
		'<script type="text/javascript">'."\n".
		'//<![CDATA['."\n".
		'$(function() {'."\n".
		'$("div#content").apercite({'."\n".
		'"baseURL":"'.substr($core->blog->url, 0, -1).'",'."\n".
		'"localLink":"'.($core->blog->settings->apercite_local_link || $core->blog->settings->apercite_local_link === null ? 'oui' : 'non').'",'."\n".
		'"sizeX":'.$size[0].','."\n".
		'"sizeY":'.$size[1].','."\n".
		'"javascript":"'.$javascript.'",'."\n".
		'"java":"'.$java.'"'."\n".
		'});'."\n".
		'});'."\n".
		'//]]>'."\n".
		'</script>'."\n";
	}
}
?>
