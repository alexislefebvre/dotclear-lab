<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Informations, a plugin for Dotclear 2
# Copyright 2007-2015 Moe (http://gniark.net/)
#
# Informations is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Informations is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
#
# ***** END LICENSE BLOCK *****

class myUrlHandlers
{
	private $sets;
	private $handlers = array();
	
	private static $defaults = array();
	private static $url2post = array();
	private static $post_adm_url = array();
	
	public static function init($core)
	{
		# Set defaults
		foreach ($core->url->getTypes() as $k=>$v)
		{
			if (empty($v['url'])) {
				continue;
			}

			$p = '/'.preg_quote($v['url'],'/').'/';
			$v['representation'] = str_replace('%','%%',$v['representation']);
			$v['representation'] = preg_replace($p,'%s',$v['representation'],1,$c);
			
			if ($c) {
				self::$defaults[$k] = $v;
			}
		}
		
		foreach ($core->getPostTypes() as $k=>$v)
		{
			self::$url2post[$v['public_url']] = $k;
			self::$post_adm_url[$k] = $v['admin_url'];
		}
		
		# Read user settings
		$handlers = (array) @unserialize($core->blog->settings->url_handlers);
		foreach ($handlers as $name => $url)
		{
			self::overrideHandler($name,$url);
		}
	}
	
	public static function overrideHandler($name,$url)
	{
		global $core;
		
		if (!isset(self::$defaults[$name])) {
			return;
		}
		
		$core->url->register($name,$url,
			sprintf(self::$defaults[$name]['representation'],$url),
			self::$defaults[$name]['handler']);
		
		$k = isset(self::$url2post[self::$defaults[$name]['url'].'/%s'])
			? self::$url2post[self::$defaults[$name]['url'].'/%s'] : '';
		
		if ($k) {
			$core->setPostType($k,self::$post_adm_url[$k],$core->url->getBase($name).'/%s');
		}
	}
	
	public static function getDefaults()
	{
		$res = array();
		foreach (self::$defaults as $k=>$v)
		{
			$res[$k] = $v['url'];
		}
		return $res;
	}
}