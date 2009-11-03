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

class rsExtagora
{
	public static function getURL($rs)
	{
		return $rs->core->blog->url.$rs->core->url->getBase('forum').'/'.
		html::sanitizeURL($rs->post_url);
	}

	public static function NewPosts($rs)
	{
		$now = dt::toUTC(time());
		
		// to be continued
		
	}

	public static function getThreadURL($rs)
	{


	}
}
?>