<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of fancybox, a plugin for Dotclear 2.
#
# Copyright (c)  2009 -2010 Osku and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('publicHeadContent',array('FancyBoxPublic','publicHeadContent'));

class FancyBoxPublic
{
	public static function publicHeadContent($core)
	{
		if (!$core->blog->settings->fancybox_enabled	) {
			return;
		}
		
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		echo
		'<link rel="stylesheet" href="/?pf=fancybox/css/jquery.fancybox-1.3.1.css" type="text/css" media="screen" />'."\n".
		'<script type="text/javascript" src="'.$url.'/js/jquery.mousewheel-3.0.2.pack.js"></script>'."\n".
		'<script type="text/javascript" src="'.$url.'/js/jquery.fancybox-1.3.1.js"></script>'."\n".
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		'$(function() {'."\n".
			'$("div.post").each(function() {'."\n".
					'$(this).find("a[href$=.jpg],a[href$=.jpeg],a[href$=.png],a[href$=.gif],'.
					'a[href$=.JPG],a[href$=.JPEG],a[href$=.PNG],a[href$=.GIF]").attr("rel","fancy").fancybox({'.
						'"titlePosition" : "over"'.
					"});\n".
			"})\n".
		"});\n".
		"\n//]]>\n".
		"</script>\n";
	}
}
?>