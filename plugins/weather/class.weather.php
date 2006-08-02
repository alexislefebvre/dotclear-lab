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
	private static $cache_file = '%s/dc_weather_%s.xml';
	private static $city_url = 'http://xoap.weather.com/search/search?where=%s';
	private static $forecast_url = 'http://xoap.weather.com/weather/local/%s?cc=*&unit=m';
	
	private static function writeData($code)
	{
		$xml = HttpClient::quickGet(sprintf(self::$forecast_url,$code));
		if ($xml) {
			if (($fp = @fopen(sprintf(self::$cache_file,DC_TPL_CACHE,$code),'wb')) !== false) {
				fwrite($fp,$xml);
				fclose($fp);
			}
		}
	}
	
	public static function getData($code)
	{	
		global $core;
		 
		$file = sprintf(self::$cache_file,DC_TPL_CACHE,$code);
		
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
		$xml = HttpClient::quickGet(sprintf(self::$city_url,rawurlencode($name)));
		
		if (preg_match('%<loc(?:\s+?)id="(.+?)"%msu',$xml,$m))
		{
			return $m[1];
		}
		
		return null;
	}
}
?>