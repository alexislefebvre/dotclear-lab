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
	public static function ip2country($ip) {
		$ip_geo = netHttp::quickGet("http://api.wipmania.com/" . $ip . "?" . $_SERVER["SERVER_NAME"]);
		if ($ip_geo === false or $ip_geo === null)
			return false;
		else
			return substr($ip_geo,-2);
	}
	
	public static function update(&$rs, $comment_id,$comment_ip_geo)
	{
		$strReq = 'UPDATE ' . $rs->core->prefix . 'comment SET comment_ip_geo="' . $comment_ip_geo . '" WHERE comment_id=' . $comment_id;
		echo '<hr /> REQ = ' . $strReq . '<hr />';
		return $rs->core->con->execute($strReq);
	}
}
?>