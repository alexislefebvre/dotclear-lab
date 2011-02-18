<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibTwitter, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# usefull func
class twitterUtils
{
	# Twitter web page URL
	public static $web_URL = 'http://twitter.com';
	
	# Patterns
	protected static $pattern_URL = array(
		"/(http|mailto|news|ftp|https):\/\/(([-éa-z0-9\/\.\?_=#@:~])*)/i",
		"<a href=\"\\1://\\2\">\\1://\\2</a>"
	);
	protected static $pattern_User = array(
		"/@([^\s]+)/",
		"@<a href=\"http://twitter.com/\\1\">\\1</a>"
	);
	protected static $pattern_Topic = array(
		"/^#([^\s]+)/",
		"#<a href=\"http://twitter.com/search/%23\\1\">\\1</a>"
	);
	
	# Get real time from twitter date
	public static function dateToTime($status_date,$user_timezone='')
	{
		// Get user timezone
		$tz = new DateTimeZone("UTC");
		foreach(DateTimeZone::listIdentifiers() as $tz_name)
		{
			if(substr($tz_name, -strlen($user_timezone)) != $user_timezone) continue;
			
			$tz = new DateTimeZone($tz_name);
		}
		
		// Convert date to time
		$time = (int) strtotime($status_date);
		
		// Get time offset
		$offset = $tz->getOffset(new DateTime($status_date, new DateTimeZone("UTC")));
		
		// Return time
		return $time + $offset;
	}	
	
	# Format a Twitter timeline status string to HTML string
	public static function textToHTML($str)
	{
		// Replace URLs
		$str = preg_replace(self::$pattern_URL[0],self::$pattern_URL[1],$str);
		
		// Replace 'reply' @
		$str = preg_replace(self::$pattern_User[0],self::$pattern_User[1],$str);
		
		// Replace 'topic' #
		$str = preg_replace(self::$pattern_Topic[0],self::$pattern_Topic[1],$str);
		
		return $str;
	}
	
	# Return a user profile image URL
	public static function profileImgURL($profile_image_url,$small=false)
	{
		return $small ?
			preg_replace("@_normal.@i", "_mini.",$profile_image_url) :
			$profile_image_url;
	}
	
	# Query Twitter search API
	public static function search()
	{
		$args = func_get_args();
		$q = implode(' OR ',$args);
		if (empty($q)) return null;
		
		try
		{
			# Config
			$api = 'http://search.twitter.com/search.json';
			$path = '';
			$data = array('q' => $q);
			
			# Send request
			$client = netHttp::initClient($api,$path);
			$client->setUserAgent('soCialMeDotclear');
			$client->setPersistReferers(false);
			$client->get($path,$data);
			
			# Receive json response
			if ($client->getStatus() == 200)
			{
				return json_decode($client->getContent());
			}
		}
		catch (Exception $e) {}
		
		return null;
	}
}
?>