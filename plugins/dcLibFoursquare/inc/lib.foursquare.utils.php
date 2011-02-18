<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibFoursquare, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class foursquareUtils
{
	public static $default_app = array('client_id'=>'','client_secret'=>'','redirect_uri'=>'');
	
	public static function decodeApp($side)
	{
		global $core;
		
		if ($side == 'admin') {
			$enc = $core->blog->settings->dcLibFoursquare->oauth_admin;
			$u = DC_ADMIN_URL;
		}
		else {
			$enc = $core->blog->settings->dcLibFoursquare->oauth_public;
			$u = $core->blog->url;
		}
		
		$s = @unserialize(base64_decode($enc));
		if (!is_array($s)) $s = array();
		
		if (empty($s['client_id']) || empty($s['client_secret']) || empty($s['redirect_uri']) || substr($s['redirect_uri'],0,strlen($u)) != $u)
		{
			$s = self::$default_app;
		}
		return array_merge(self::$default_app,$s);
	}
	
	public static function encodeApp($side,$id,$secret,$uri)
	{
		global $core;
		
		if ($side == 'admin') {
			$u = DC_ADMIN_URL;
		}
		else {
			$u = $core->blog->url;
		}
		
		$s = array(
			'client_id' => $id,
			'client_secret' => $secret,
			'redirect_uri' => $uri
		);
		if (empty($s['client_id']) || empty($s['client_secret']) || empty($s['redirect_uri']) || substr($s['redirect_uri'],0,strlen($u)) != $u)
		{
			$s = self::$default_app;
		}
		$enc = base64_encode(serialize($s));
		
		if ($side == 'admin') {
			$core->blog->settings->dcLibFoursquare->put('oauth_admin',$enc,'string','',true,true);
		}
		else {
			$core->blog->settings->dcLibFoursquare->put('oauth_public',$enc);
		}
		return $s;
	}
	
	//obj = oAuthClient20Foursquare object
	public static function api($obj,$url,$params=array(),$method='GET')
	{
		$rsp = $obj->query($url,$method,$params);
		if ($obj->http_code != 200) {
			throw new Exception('Failed to query service');
		}
		
		$rsp = json_decode($rsp);
		
		if ($rsp->meta->code != 200) {
			throw new Exception('Bad query on service');
		}
		
		return $rsp->response;
	}
	
	//todo: parser for items (checkins, badges, etc)
}

?>