<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of My Dashboard, a plugin for Dotclear 2
# Copyright (C) 2009 Moe (http://gniark.net/)
#
# My Dashboard is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# My Dashboard is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

class myDashboard {
	public static function loadLinks()
	{
		$links = $GLOBALS['core']->blog->settings->mydashboard_links;
		
		$links = @unserialize(base64_decode($links));
		
		if (!is_array($links) OR empty($links)) {return(array());}
		
		return($links);
	}
	
	public static function saveLinks($links)
	{
		global $core;
		
		# restart order from 0
		$new_links = array();
		
		$i = 0;
		
		foreach ($links as $k => $v)
		{
			$new_links[$i] = array(
				'title' => $v['title'],
				'url' => $v['url'],
				'icon' => $v['icon']
			);
			
			$i++;
		}
		
		$setting = base64_encode(serialize($new_links));
		
		$core->blog->settings->setNameSpace('myDashboard');
		
		$core->blog->settings->put('mydashboard_links',$setting,'string',
			'My Dashboard links');
		
		$core->blog->settings->setNameSpace('system');
	}
	
	public static function AddLink(&$links,$title,$url,$icon)
	{		
		$links[] = array(
			'title' => $title,
			'url' => $url,
			'icon' => $icon
		);
		
		self::saveLinks($links);
	}
	
	public static function adminDashboardIcons($core,$icons)
	{
		$links = self::loadLinks();
		
		if (!is_array($links)) {return;}
		
		foreach ($links as $link => $values)
		{
			$icon = $values['icon'];
			
			if (empty($icon))
			{
				$icon = 'index.php?pf=myDashboard/images/insert-image.png';
			}
			
			$icons[$link] = array($values['title'],$values['url'],$icon);
		}
	}
}

?>