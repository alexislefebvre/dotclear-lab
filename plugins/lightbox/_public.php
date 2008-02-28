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
		'@import url('.$url.'/css/jquery.lightbox-0.4.css);'."\n".
		"</style>\n".
		'<script type="text/javascript" src="'.$url.'/js/jquery.lightbox-0.4.js"></script>'."\n".
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		//"var lightBoxSettings = {};\n".
		'$(function() {'."\n".
			'$("div.post a:has(img)").lightBox({'."\n".
				"imageLoading  : '".html::escapeJS($url)."/images/lightbox-ico-loading.gif',\n".
				"imageBtnPrev  : '".html::escapeJS($url)."/images/lightbox-btn-prev.gif',\n".
				"imageBtnNext  : '".html::escapeJS($url)."/images/lightbox-btn-next.gif',\n".
				"imageBtnClose : '".html::escapeJS($url)."/images/lightbox-btn-close.gif',\n".
				"imageBlank    : '".html::escapeJS($url)."/images/lightbox-blank.gif',\n".
			"});\n".
		"});\n".
		"\n//]>\n".
		"</script>\n";
	}
}
?>