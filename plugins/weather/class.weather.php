<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Weather for Dotclear 2 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class dcWeather
{
	private static $cache_file = '%s/dcweather/%s/%s.xml';
	
	private static $w_server = 'xoap.weather.com';
	private static $w_port = 80;
	private static $city_url = '/search/search?where=%s';
	private static $forecast_url = '/weather/local/%s?cc=*&unit=m';
	
	private static function cacheFileName($code)
	{
		return sprintf(self::$cache_file,DC_TPL_CACHE,substr($code,0,2),$code);
	}
	
	private static function writeData($code)
	{
		$xml = self::fetchData(sprintf(self::$forecast_url,$code));
		
		if ($xml) {
			$cache_file = self::cacheFileName($code);
			try {
				files::makeDir(dirname($cache_file),true);
				if (($fp = @fopen($cache_file,'wb')) !== false) {
					fwrite($fp,$xml);
					fclose($fp);
					files::inheritChmod($cache_file);
				}
			} catch (Exception $e) {}
		}
	}
	
	private static function fetchData($url)
	{
		$o = new netHttp(self::$w_server,self::$w_port,2);
		
		try {
			$o->get($url);
			return $o->getContent();
		} catch (Exception $e) {
			echo $e->getMessage();exit;
			return null;
		}
	}
	
	public static function getData($code)
	{	
		global $core;
		 
		$file = self::cacheFileName($code);
		
		if (file_exists($file) && (filemtime($file) + 3600) > time()) {
			$xml = @simplexml_load_file($file);
		}
		else {
			dcWeather::writeData($code);
			if (file_exists($file)) {
				$xml = simplexml_load_file($file);
				$core->blog->triggerBlog();
			}
		}
		return $xml;
	}
	
	public static function searchCity($name)
	{
		$xml = self::fetchData(sprintf(self::$city_url,rawurlencode($name)));
		
		if (preg_match('%<loc(?:\s+?)id="(.+?)"%msu',$xml,$m)) {
			return $m[1];
		}
		
		return null;
	}
}
?>