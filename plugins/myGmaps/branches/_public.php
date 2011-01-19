<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of myGmaps, a plugin for Dotclear 2.
#
# Copyright (c) 2010 Philippe aka amalgame
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('coreBlogGetPosts',array('myGmapsPublic','coreBlogGetPosts'));
$core->addBehavior('publicFooterContent',array('myGmapsPublic','publicFooterContent'));
$core->addBehavior('publicEntryAfterContent',array('myGmapsPublic','publicMapContent'));
$core->addBehavior('publicPageAfterContent',array('myGmapsPublic','publicMapContent'));

class myGmapsPublic
{
	public static function coreBlogGetPosts($cur)
	{
		$cur->extend('myGmapsUtilsRsExt');
	}
	
	public static function publicFooterContent($core,$_ctx)
	{
		echo myGmapsUtils::jsCommon();
	}
	
	public static function publicMapContent($core,$_ctx)
	{
		if ($_ctx->posts->hasMap()) {
			$map_id = sprintf('map_canvas_%s',$_ctx->posts->post_id);
			echo
			'<div id="'.$map_id.'" style="with:100%; height:400px;"></div>'."\n".
			'<script type="text/javascript">'."\n".
			"$(function(){\n".
			"var opts = {target:'#".$map_id."'};\n".
			"myGmaps.init(opts);\n".
			"});\n".
			"</script>\n".
			myGmapsUtils::jsData($_ctx->posts->getMapsId());
		}
	}
}

?>