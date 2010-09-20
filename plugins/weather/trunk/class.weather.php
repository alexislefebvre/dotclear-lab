<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Weather for Dotclear.
# Copyright (c) 2006 Christophe Meyer and contributors. All rights
# reserved.
#
# Weather for Dotclear is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# Weather for Dotclear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

class dcWeather
{
	private static $cache_file = '%s/dcweather/%s/%s.xml';
	
	private static $w_server = 'xoap.weather.com';
	private static $w_port = 80;
	private static $city_url = '/search/search?where=%s';
	private static $forecast_url = '/weather/local/%s?cc=*&unit=m&link=xoap&prod=xoap&par=%s&key=%s';
	
	private static function cacheFileName($code)
	{
		return sprintf(self::$cache_file,DC_TPL_CACHE,substr($code,0,2),$code);
	}
	
	private static function writeData($code)
	{
		global $core;

		$wp_id = $core->blog->settings->weatherplus->wp_id;
		$wp_key = $core->blog->settings->weatherplus->wp_key;

		$xml = self::fetchData(sprintf(self::$forecast_url,$code,$wp_id,$wp_key));
		
		if ($xml) {
			$cache_file = self::cacheFileName($code);
			try {
				files::makeDir(dirname($cache_file),true);
				if (($fp = @fopen($cache_file,'wb')) !== false) {
					fwrite($fp,$xml);
					fclose($fp);
					chmod($cache_file,fileperms(dirname($cache_file)));
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
		
		return simplexml_load_string($xml);
	}
}
?>