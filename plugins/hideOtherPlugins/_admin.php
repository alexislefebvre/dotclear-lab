<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Hide Other Plugins, a plugin for Dotclear 2
# Copyright 2010 Moe (http://gniark.net/)
#
# Hide Other Plugins is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Hide Other Plugins is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {exit;}

$core->addBehavior('adminPageHTMLHead',
	array('hideOtherPlugins','adminPageHTMLHead'));

class hideOtherPlugins
{
	public static function removeOtherPlugins($items)
	{
		$plugins = array('antispam','importExport','aboutConfig','blogroll',
			'maintenance','pings','pages','metadata','widgets',
			'tags');
		
		if (preg_match('/plugin.php\?p=(\w+)/',
			$_SERVER['REQUEST_URI'],$plugin))
		{
			# ignore Pages, Tags and Widgets
			if (in_array($plugin[1],$plugins))
			{
				return($items);
			}
			else
			{
				$plugins[] = $plugin[1];
			}
		}
		else
		{
			return($items);
		}
		
		$new_items = array();
		
		foreach ($items as $key => $item)
		{
			preg_match('/href="plugin.php\?p=(.*?)"/',$item,$plugin);
			$plugin = $plugin[1];
			
			if (in_array($plugin,$plugins))
			{
				$new_items[] = $item;
			}
		}
		
		return($new_items);
	}
	
	public static function adminPageHTMLHead()
	{
		global $_menu;
		
		$_menu['Plugins']->items =
			self::removeOtherPlugins($_menu['Plugins']->items);
	}

}

?>