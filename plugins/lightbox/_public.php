<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2008 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

$core->addBehavior('publicHeadContent',array('lightBoxPublic','publicHeadContent'));

class lightBoxPublic
{
	public static function publicHeadContent(&$core)
	{
		if (!$core->blog->settings->lightbox_enabled) {
			return;
		}
		
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		echo
		'<style type="text/css">'."\n".
		'@import url('.$url.'/css/modal.css);'."\n".
		"</style>\n".
		'<script type="text/javascript" src="'.$url.'/js/modal.js"></script>'."\n".
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		'$(function() {'."\n".
			'var lb_settings = {'."\n".
				"loader_img : '".html::escapeJS($url)."/img/loader.gif',\n".
				"prev_img   : '".html::escapeJS($url)."/img/prev.png',\n".
				"next_img   : '".html::escapeJS($url)."/img/next.png',\n".
				"close_img  : '".html::escapeJS($url)."/img/close.png',\n".
				"blank_img  : '".html::escapeJS($url)."/img/blank.gif'\n".
			"};".
			'$("div.post").each(function() {'."\n".
					'$(this).find("a[href$=.jpg],a[href$=.jpeg],a[href$=.png],a[href$=.gif],'.
					'a[href$=.JPG],a[href$=.JPEG],a[href$=.PNG],a[href$=.GIF]").modalImages(lb_settings);'."\n".

			"})\n".
		"});\n".
		"\n//]]>\n".
		"</script>\n";
	}
}
?>
