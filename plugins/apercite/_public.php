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
			"\t".'@import url('.$url.'/css/apercite.css);'."\n".
		'</style>'."\n".
		'<script type="text/javascript" src="'.$url.'/js/apercite.js"></script>'."\n".
		'<script type="text/javascript">'."\n".
			"\t".'//<![CDATA['."\n".
				"\t\t".'$(function() {'."\n".
					"\t\t\t".'$("div#content").apercite({'."\n".
						"\t\t\t\t".'"baseURL":"'.substr($core->blog->url, 0, -1).'",'."\n".
						"\t\t\t\t".'"sizeX":'.$size[0].','."\n".
						"\t\t\t\t".'"sizeY":'.$size[1].','."\n".
						"\t\t\t\t".'"javascript":"'.$javascript.'",'."\n".
						"\t\t\t\t".'"java":"'.$java.'"'."\n".
					"\t\t\t".'});'."\n".
				"\t\t".'});'."\n".
			"\t".'//]]>'."\n".
		'</script>'."\n";
	}
}
?>
