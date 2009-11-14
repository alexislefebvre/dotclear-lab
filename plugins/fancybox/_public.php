<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of fancybox, a plugin for Dotclear 2.
#
# Copyright (c)  2009 Osku and contributors
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
		'<style type="text/css">'."\n".
		'@import url('.$url.'/css/jquery.fancybox-1.2.5.css);'."\n".
		"</style>\n".
		'<script type="text/javascript" src="'.$url.'/js/jquery.fancybox-1.2.5.pack.js"></script>'."\n".
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		'$(function() {'."\n".
			'$("div.post").each(function() {'."\n".
					'$(this).find("a[href$=.jpg],a[href$=.jpeg],a[href$=.png],a[href$=.gif],'.
					'a[href$=.JPG],a[href$=.JPEG],a[href$=.PNG],a[href$=.GIF]").attr("rel","fancy").fancybox({'.
						'"zoomSpeedIn"		:	500,'.
						'"zoomSpeedOut"		:	500,'.
						'"overlayShow"		:	false	'.
					"});\n".
			"})\n".
		"});\n".
		"\n//]]>\n".
		"</script>\n";
	}
}
?>