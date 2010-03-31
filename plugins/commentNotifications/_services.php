<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of commentNotifications, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (defined('DC_CONTEXT_ADMIN')) { return; }

class commentNotificationsRestMethods
{
	public static function getNbComments()
	{
		global $core;
		
		$params = array();
		
		$nb_comment = $core->blog->getComments($params,true)->f(0);
		
		$rsp = new xmlTag();
		$rsp->comments($nb_comment);
		
		return $rsp;
	}
}

?>