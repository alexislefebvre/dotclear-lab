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
if (!defined('DC_RC_PATH')) { return; }
		
$core->addBehavior('initWidgets',array('WeatherBehavior','initWidgets'));

class WeatherBehavior {
	
	public static function initWidgets($w)
	{
		$w->create('weather',__('Weather'),array('publicWeather','WeatherWidget'),array('WeatherBehavior','appendWidget'));
		$w->weather->setting('title',__('Title:'), __('Weather'));
		$w->weather->setting('cities',__('City, Country (one place per line):'),'','textarea');
		$w->weather->setting('theme',__('Icons theme:'),'liquid','combo',
			array('liquid' => 'liquid', 'flat' => 'flat', 'um' => 'um'));
		$w->weather->setting('clock',__('Display local time'),1, 'check');
		$w->weather->setting('homeonly',__('Home page only'),1,'check');
		$w->weather->setting('citycodes','','','hidden');
	}
	
	public static function appendWidget($w)
	{
		$cities = str_replace("\r",'',$w->cities);
		$cities = explode("\n",$cities);
		$w->citycodes = new ArrayObject();
		
		foreach ($cities as $c)
		{
			if (preg_match('/^[A-Z]{4}\d{4}$/',$c)) {
				$w->citycodes[] = $c;
			} elseif (($city = dcWeather::searchCity($c)) && !empty($city->loc)) {
					$w->citycodes[] = (string)$city->loc[0]['id'];
			}
		}
	}
}
?>