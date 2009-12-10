<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of commentIpGeo, a plugin for Dotclear.
#
# Copyright (c) 2007-2009 Frederic PLE
# dotclear@frederic.ple.name
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class rsExtCommentIpGeo {
	public static function __process(&$rs,$i) {
		global $core;
		$t = explode(':',publicCommentIpGeo::$c_info[$rs->comment_id]['comment_ip_geo'],3);
		return $t[$i - 1];
	}

	public static function getIpGeoCountryCode(&$rs) {
		global $core;
		return rsExtCommentIpGeo::__process($rs,1);
	}
	
	public static function getIpGeoCountryCity(&$rs) {
		global $core;
		return rsExtCommentIpGeo::__process($rs,2);
	}

	public static function getIpGeoIsp(&$rs) {
		global $core;
		return rsExtCommentIpGeo::__process($rs,3);
	}
	
	public static function debug(&$rs) {
		global $core;
		return var_export(publicCommentIpGeo::$c_info,true);
	}
}
?>
