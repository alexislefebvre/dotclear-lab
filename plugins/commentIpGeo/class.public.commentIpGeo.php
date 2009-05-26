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

if ($core->blog->settings->commentIpGeo_active) {
	$core->addBehavior('coreBlogGetComments',array('publicCommentIpGeo','coreBlogGetComments'));
}


class publicCommentIpGeo {
	public static $c_info = array();
	
	public static function coreBlogGetComments(&$c_rs) {
		$ids = array();
		while ($c_rs->fetch())
			$ids[] = $c_rs->comment_id;
		if (empty($ids)) return;
		
		$ids = implode(', ',$ids);
		
		$strReq = 'SELECT comment_id, comment_ip, comment_ip_geo FROM ' . $c_rs->core->prefix . 'comment WHERE comment_id IN (' . $ids .')';
		$rs = $c_rs->core->con->select($strReq);
		while($rs->fetch()) {
			if ($rs->comment_ip_geo === false or $rs->comment_ip_geo === "" or $rs->comment_ip_geo === null) {
				$ip_geo = commentIpGeo::ip2country($rs->comment_ip);
				if ($ip_geo === false) {
    					$ip_geo = "XX";
    				} else {
    					commentIpGeo::update($c_rs, $rs->comment_id,$ip_geo);
    				}
			} else {
				$ip_geo = $rs->comment_ip_geo;
			}
				
			self::$c_info[$rs->comment_id] = array(
				'comment_ip_geo' => $ip_geo
				);
		}
		$c_rs->extend('rsExtCommentIpGeo');
	}
	
}
