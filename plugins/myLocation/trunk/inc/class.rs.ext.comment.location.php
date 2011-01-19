<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of myLocation, a plugin for Dotclear.
#
# Copyright (c) 2010 Tomtom and contributors
# http://blog.zenstyle.fr/
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class rsExtCommentLocation
{
	public static function hasLocation($rs)
	{
		try {
			$location = unserialize($rs->comment_location);
			return $location['longitude'] === '' && $location['latitude'] === '' ? false : true;
		} catch (Exception $e) {
			return false;
		}
	}
	
	public static function getLocation($rs)
	{
		$location = unserialize($rs->comment_location);
		
		$address =
		strlen($location['address']) > 0 ? 
		'('.$location['address'].')' :
		__('See on map');
		
		$url = strlen($location['address']) > 0 ?
		sprintf('http://maps.google.com/maps?q=%s,+%s+%s',$location['latitude'],$location['longitude'],$address) : 
		sprintf('http://maps.google.com/maps?q=%s,+%s+%s',$location['latitude'],$location['longitude'],'');
	
		return sprintf($rs->core->blog->settings->myLocation->mask,$url,$address);
	}
}

?>