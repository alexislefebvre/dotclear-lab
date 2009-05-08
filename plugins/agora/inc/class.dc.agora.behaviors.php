<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku , Tomtom and contributors
## Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class agoraBehaviors
{
	public static function coreInitWikiPost()
	{
		global $core;
		
		// récupérer les options Wiki en desrialisant le setting ...
		return;
	}
	
	public static function coreBeforePostCreate(&$blog,&$cur)
	{
		$cur->thread_id = $cur->post_id;
		$cur->post_url = $cur->post_id;

		die('coreBeforePostCreate');
	}
	
}
?>
