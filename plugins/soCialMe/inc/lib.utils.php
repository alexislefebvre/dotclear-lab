<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMe, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class soCialMeUtils
{
	public static function getParts()
	{
		global $core;
		
		$rs = array();
		$list = $core->getBehaviors('soCialMePart');
		
		if (empty($list)) return $rs;
		
		foreach($list as $k => $callback)
		{
			try
			{
				list($part,$ns) = call_user_func($callback);
				
				if (empty($part) || empty($ns)) continue;
				$rs[$part] = $ns;
			}
			catch (Exception $e) {}
		}
		
		return $rs;
	}
	
	public static $record = array(
		'author' => '',
		'avatar' => '',
		'category' => '',
		'content' => '',
		'date' => '',
		'email' => '',
		'excerpt' => '',
		'icon' => '',
		'me' => false,
		'preload' => 0,
		'service' => 'unknow',
		'shorturl' => '',
		'source_name' => '',
		'source_url' => '',
		'source_img' => '',
		'tags' => '',
		'title' => '',
		'type' => '',
		'url' => ''
	);
	
	# Reduce link
	public static function reduceURL($url,$custom=null)
	{
		global $core;
		$shorturl = false;
		
		# Reduce URL using plugin kUtRL
		if (version_compare(str_replace("-r","-p",$core->plugins->moduleInfo('kUtRL','version')),'1.0-alpha1','>='))
		{
			$kutrl = kutrl::quickReduce($url,$custom);
			$shorturl = !empty($kutrl) ? $kutrl : $url;
		}
		
		# Reduce URL using quick service
		if (!$shorturl)
		{
			try
			{
				# Config
				$enc = SHORTEN_SERVICE_ENCODE ? urlencode($url) : $url;
				$api = SHORTEN_SERVICE_API;
				$path = '';
				$data = array(SHORTEN_SERVICE_PARAM => $enc);
				
				# Send request
				$client = netHttp::initClient($api,$path);
				$client->setUserAgent('soCialMeDotclear');
				$client->setPersistReferers(false);
				$client->get($path,$data);
				
				# Receive short url
				if ($client->getStatus() == 200)
				{
					$shorturl = (string) $client->getContent();
					$shorturl = SHORTEN_SERVICE_BASE.str_replace(SHORTEN_SERVICE_BASE,'',$shorturl);
				}
			}
			catch (Exception $e) {}
		}
		
		return $shorturl ? $shorturl : $url;
	}
	
	# Cut on word message $str into sub messages less than $len chars long
	public static function splitString($str,$len=140)
	{
		$split = array(0=>'');
		$j = 0;
		if (strlen($str) < $len)
		{
			$words = explode(' ',$str);
			for($i = 0; $i < count($words); $i++)
			{
				$s = empty($split[$j]) ? '' : ' ';

				$next_len = $split[$j].$s.$words[$i];
				if (strlen($next_len) < $len)
				{
					$split[$j] .= $s.$words[$i];
				}
				else
				{
					$j++;
					$split[$j] = $words[$i];
				}
			}
		}
		else
		{
			$split[0] = $str;
		}
		return $split;
	}
	
	# Check related plugin version
	public static function checkPlugin($n,$v)
	{
		global $core;
		return $core->plugins->moduleExists($n) && version_compare(str_replace("-r","-p",(string) $core->plugins->moduleInfo($n,'version')),$v,'>=');
	}
	
	# Shortcut for a standard link with an image
//deprecated
	public static function easyLink($href,$title,$src,$type='sharer')
	{
		if ($type == 'sharer') {
			$title = sprintf(__('Share on %s'),$title);
		}
		elseif ($type == 'profil') {
			$title = sprintf(__('View my profil on %s'),$title);
		}
		
		return 
		'<a href="'.$href.'" title="'.$title.'">'.
		'<img src="'.$src.'" alt="'.$title.'" />'.
		'</a>';
	}
	
	//not always so speed, bug on some case (if there's an onload event)
//deprecated
	public static function preloadBox($content)
	{
		if (!isset($GLOBALS['soCialMeOldPreloadBoxNumber'])) $GLOBALS['soCialMeOldPreloadBoxNumber'] = 0;
		
		$GLOBALS['soCialMeOldPreloadBoxNumber'] += 1;
		
		return
		'<div id="social-oldpreloadbox'.$GLOBALS['soCialMeOldPreloadBoxNumber'].'"></div>'.
		'<script type="text/javascript">'.
		"\n//<![CDATA[ \n".
		'$(\'#social-oldpreloadbox'.$GLOBALS['soCialMeOldPreloadBoxNumber'].'\').hide(); '.
		'$(document).ready(function(){ '.
		'$(\'#social-oldpreloadbox'.$GLOBALS['soCialMeOldPreloadBoxNumber'].'\').show().replaceWith($(\''.$content.'\')); '.
		"}); ".
		"\n//]]> \n".
		'</script> ';
	}
	
	# clean the $record array passed to the play() func.
	// This is a standard array, not all fields are used each times
	public static function fillPlayRecord($partial)
	{
		if (!is_array($partial)) $partial = array();
		
		if (!empty($partial['url']) && empty($partial['shorturl'])) {
			$partial['shorturl'] = soCialMeUtils::reduceURL($partial['url']);
		}
		
		return new arrayObject(array_merge(self::$record,$partial));
	}
	
	# Turn array from play() func into a record
	public static function arrayToRecord($array)
	{
		foreach($array as $k => $record)
		{
			$array[$k] = socialMeUtils::fillPlayRecord($record);
		}
		return staticRecord::newFromArray($array);
	}
	
	# Test if a service is in a things list
	public static function thingsHasService($things,$service)
	{
		foreach($things as $thing => $services)
		{
			if (in_array($service,$services)) return true;
		}
		return false;
	}
	
	# Encode array to base64
	public static function encode($array=array())
	{
		if (!is_array($array)) $array = array();
		
		return base64_encode(serialize($array));
	}
	
	# Decode array from base64
	public static function decode($str='')
	{
		$array = @unserialize(base64_decode($str));
		
		return is_array($array) ? $array : array();
	}
}
?>