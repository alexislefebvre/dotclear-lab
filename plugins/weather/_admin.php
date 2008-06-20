<?php
# -- BEGIN LICENSE BLOCK ---------------------------------
#
# This file is part of Weather for Dotclear 2.
#
# Copyright (c) 2003-2008 Christophe Meyer and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ---------------------------------- */

$core->addBehavior('initWidgets',array('WeatherBehavior','initWidgets'));

class WeatherBehavior {
	
	public static function initWidgets(&$widgets)
	{
		$widgets->create('weather',__('Weather'),array('publicWeather','WeatherWidget'),array('WeatherBehavior','appendWidget'));
		$widgets->weather->setting('title',__('Title:'), __('Weather'));
		$widgets->weather->setting('cities',__('City, Country (one place per line):'),'','textarea');
		$widgets->weather->setting('theme',__('Icons theme:'),'liquid','combo',
			array('liquid' => 'liquid', 'flat' => 'flat', 'um' => 'um'));
		$widgets->weather->setting('homeonly',__('Home page only'),1,'check');
		$widgets->weather->setting('citycodes','','','hidden');
	}
	
	public static function appendWidget(&$w)
	{
		$cities = str_replace("\r",'',$w->cities);
		$cities = explode("\n",$cities);
		$w->citycodes = new ArrayObject();
		
		foreach ($cities as $c)
		{
			if (preg_match('/^[A-Z]{4}\d{4}$/',$c)) {
				$w->citycodes[] = $c;
			} elseif ($code = dcWeather::searchCity($c)) {
				$w->citycodes[] = $code;
			}
		}
	}
}
?>