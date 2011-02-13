<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# Generic class to play easily with services
class kUtRL
{
	# Load services list from behavior
	public static function getServices($core)
	{
		$list = $core->getBehaviors('kutrlService');
		
		if (empty($list)) return array();
	
		$service = array();	
		foreach($list as $k => $callback)
		{
			try
			{
				list($service_id,$service_class) = call_user_func($callback);
				$services[(string) $service_id] = (string) $service_class;
			}
			catch (Exception $e) {}
		}
		return $services;
	}
	
	# Get service for a place
	public static function getPlace($core,$place='plugin')
	{
		$id = 'default';
		if (in_array($place,array('tpl','wiki','admin','plugin'))) {
			
			$p_name = 'kutrl_'.$place.'_service';
			if ($core->blog->settings->kUtRL->{$p_name}) {
				$id = $core->blog->settings->kUtRL->{$p_name};
			}
		}
		return $id;
	}
	
	# Silently try to load a service according to its id
	# Return null on error else service on success
	public static function quickService($id='')
	{
		global $core;
		
		try
		{
			$services = self::getServices($core);
			if (!isset($services[$id])) return null;
			
			return new $services[$id]($core);
		}
		catch(Exception $e) { return null; }
	}
	
	# Silently try to load a service according to its place
	# Return null on error else service on success
	public static function quickPlace($place='plugin')
	{
		global $core;
		
		try
		{
			$id = self::getPlace($core,$place);
			if (empty($id)) return null;
			
			$services = self::getServices($core);
			if (!isset($services[$id])) return null;
			
			return new $services[$id]($core);
		}
		catch(Exception $e) { return null; }
	}
	
	# Silently try to reduce url (using 'plugin' place)
	# return long url on error else short url on success
	public static function quickReduce($url,$custom=null,$place='plugin')
	{
		global $core;
		
		try
		{
			$srv = self::quickPlace($place);
			if (empty($srv)) return $url;
			
			$rs = $srv->hash($url,$custom);
			if (empty($rs)) return $url;
			
			return $srv->url_base.$rs->hash;
		}
		catch(Exception $e) { return $url; }
	}
}
?>