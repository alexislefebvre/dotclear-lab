<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of commentIpGeo, a plugin for Dotclear.
#
# Copyright (c) 2009 Frederic PLE
# dotclear@frederic.ple.name
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class commentIpGeo
{
	public static function ip2country(&$rs,$ip) {
		if ($rs->core->blog->settings->commentIpGeo_db == "wipmania")
			$ip_geo = netHttp::quickGet("http://api.wipmania.com/" . $ip . "?" . $_SERVER["SERVER_NAME"]);
		else
			$ip_geo = netHttp::quickGet("http://frederic.ple.name/tools/inc_geoip.php?ip=" . $ip . "&from=" . urlencode(str_replace('http://','',$rs->core->blog->url)));
		if ($ip_geo === false or $ip_geo === null or strpos($ip_geo,'<') !== false)
			return false;
		else
			return $ip_geo;
	}
	
	public static function update(&$rs, $comment_id,$comment_ip_geo)
	{
		return $rs->core->con->execute('UPDATE ' . $rs->core->prefix .
			'comment SET comment_ip_geo="' . $comment_ip_geo .
			'" WHERE comment_id=' . $comment_id);
	}
}
?>
