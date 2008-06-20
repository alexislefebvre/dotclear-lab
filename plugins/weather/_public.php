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

$core->url->register('weather-icons','weather/icons','^weather/icons/([a-zA-Z0-9_-]+/\d{1,2}).png',array('publicWeather','iconURL'));

class publicWeather
{
	public static function WeatherWidget(&$w)
	{
		global $core;
		
		if (count($w->citycodes) == 0) {
			return;
		}
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$res =
		'<div class="weather" style="padding-bottom:5px;">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '');
		
		foreach($w->citycodes as $code)
		{
			if ($xml = dcWeather::getData($code) )
			{
				$icon = (string) $xml->cc->icon == '-' ? '44' : (string) $xml->cc->icon;
				$icon = $core->blog->url.$core->url->getBase('weather-icons').'/'.$w->theme.'/'.$icon.'.png';
				$icon = '<img src="'.$icon.'" alt="" style="display:inline; vertical-align:middle;" />';
				
				$city = explode(',',(string) $xml->loc->dnam);
				$city = $city[0];
				$temp = (string) $xml->cc->tmp;
				
				$res .=
				'<h3>'.$icon.' '.$city.' ('.$temp.'&deg;C)</h3>';
			}
		}
		return $res.'</div>';
	}
	
	public static function iconURL($arg)
	{
		$file = dirname(__FILE__).'/icons/'.$arg.'.png';
		if (!file_exists($file)) {
			http::head(404,'Not Found');
			exit;
		}
		
		http::cache(array_merge(array($file),get_included_files()));
		
		header('Content-Type: image/png');
		readfile($file);
		exit;
	}
}
?>