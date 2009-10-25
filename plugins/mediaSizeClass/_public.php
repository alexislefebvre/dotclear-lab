<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of mediaSizeClass, a plugin for Dotclear.
# 
# Copyright (c) 2009 Kozlika and kindly contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('publicHeadContent',array('addClassPublic','publicHeadContent'));

class addClassPublic
{
	public static function publicHeadContent(&$core)
	{
		if (!$core->blog->settings->addclass_enabled) {
			return;
		}

		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		echo
		
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		'$(function() {'."\n".
		"\t".'$("div.post").each(function() {'."\n".
		"\t\t".'$(this).find("img[src$=_t.jpg]").addClass("thumbnail-img");'."\n".
		"\t\t".'$(this).find("img[src$=_sq.jpg]").addClass("square-img");'."\n".
		"\t\t".'$(this).find("img[src$=_s.jpg]").addClass("small-img");'."\n".
		"\t\t".'$(this).find("img[src$=_m.jpg]").addClass("medium-img");'."\n".
		"\t".'});'."\n".
		'});'."\n".
		"//]]>\n".
		"</script>\n";
	}
}
?>