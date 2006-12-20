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
		'<div id="weather" style="padding-bottom:5px;">'.
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