<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Popularity Contest.
# Copyright 2007,2009 Moe (http://gniark.net/)
#
# Popularity Contest is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Popularity Contest is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

class popularityContest
{
	public static $send_url = 'http://popcon.gniark.net/send.php';
	public static $plugins_xml_url = 'http://popcon.gniark.net/raw.xml';
	
	public static function getComboOptions()
	{
		$day_seconds = 24*3600;
		$array = array(
			__('weekly') => (7*$day_seconds),
			__('every 3 days') => (3*$day_seconds),
			__('daily') => ($day_seconds),
		);
		return($array);
	}

	# use : getPluginsArray(array('name','root','version')
	public static function getPluginsArray($array_keys,$array_hidden_plugins=array())
	{
		$modules = $GLOBALS['core']->plugins->getModules();
		$array = array();

		foreach ($modules as $module => $module_values)
		{
			if (!in_array($module,$array_hidden_plugins))
			{
				foreach ($array_keys as $key)
				{
					$array[$module][$key] = $module_values[$key];
				}
			}
		}

		return($array);
	}
			
	# send to Dotclear Popularity Contest
	public static function send()
	{
		$settings =& $GLOBALS['core']->blog->settings;

		$time_interval_last_try =
			$_SERVER['REQUEST_TIME'] - $settings->popularityContest_last_try;
		if ($time_interval_last_try < (30*60))
		{
			return(false);
		}

		$hidden_plugins = array();
		if (strlen($settings->popularityContest_hidden_plugins) > 0)
		{
			$hidden_plugins = unserialize(base64_decode($settings->popularityContest_hidden_plugins));
		}
		if (!is_array($hidden_plugins)) {$hidden_plugins = array();}
		
		$settings->setNameSpace('popularitycontest');
		# Popularity Contest last try
		$settings->put('popularityContest_last_try',
			$_SERVER['REQUEST_TIME'],'integer','Popularity Contest last try (Unix timestamp)',
			true,true);

		$url = self::$send_url;

		# inspired by /dotclear/inc/core/class.dc.trackback.php
		$client = netHttp::initClient($url,$path);
		$client->setUserAgent('Dotclear - http://www.dotclear.net/');
		$client->setPersistReferers(false);
		$infos = array();
		$infos['id'] = md5(DC_ADMIN_URL);
		$infos['dc_version'] = DC_VERSION;
		$data = self::getPluginsArray(array('name'),$hidden_plugins);
		$data = array_merge($infos,$data);
		$client->post($url,$data);
		$content = $client->getContent();
		
		if (substr($content,0,4) == 'ok: ')
		{
			# ok
			if (substr($content,4,4) == 'sent')
			{
				$settings->setNameSpace('popularitycontest');
				# success : update Popularity Contest last report
				$settings->put('popularityContest_last_report',
					$_SERVER['REQUEST_TIME'],'integer',
					'Popularity Contest last report (Unix timestamp)',true,true);
				return(true);
			}
			return(false);
		}
		
		return(false);
	}

	# create table
	public static function getPluginsTable($editable=false)
	{
		global $hidden_plugins;

		if (!is_array($hidden_plugins)) {$hidden_plugins = array();}
		
		$plugins_XML = self::getPluginsXML();
		
		$show_popularity = false;
		
		if (($plugins_XML !== false))
		{
			$show_popularity = true;
			
			$attr = $plugins_XML->attributes();
			
			$plugins_popularity = array();
			
			# inspired by daInstaller/inc/class.da.modules.parser.php
			foreach ($plugins_XML->plugin as $p)
			{
				$attrs = $p->attributes();
				
				$id = (string) $attrs['id'];
				$name = (string) $attrs['name'];
				$popularity = (string) $attrs['popularity'];
				
				$plugins_popularity[$id] = array(
					'name' => $name,
					'popularity' => $popularity
				);
			}
		}
		
		# don't select hidden plugins or select all of it's editable
		$array = self::getPluginsArray(array('name','root','version'),
			(($editable) ? array() : $hidden_plugins));

		$table = new table('class="clear" summary="'.
			__('Installed plugins:').'"');
		$table->part('head');
		$table->row();
		if ($editable)
		{
			$table->header(__('Hide'),'class="nowrap"');
		}
		$table->header(__('Icon'),'class="nowrap"');
		$table->header(__('Plugin'),'class="nowrap"');
		$table->header(__('Name'),'class="nowrap"');
		$table->header(__('Version'),'class="nowrap"');
		if ($show_popularity) {$table->header(__('Popularity'),'class="nowrap"');}

		$table->part('body');

		foreach ($array as $k => $v)
		{
			$table->row();
			if ($editable)
			{
				$table->cell(form::checkbox(array('hidden_plugins[]'),$k,
					in_array($k,$hidden_plugins)));
			}
			$icon = (file_exists($v['root'].'/icon.png')) ? 
				'<img src="index.php?pf='.$k.'/icon.png" style="height:16px;" alt="" />' : '';
			$table->cell($icon);
			$table->cell($k);
			$table->cell($v['name']);
			$table->cell($v['version']);
			
			if ($show_popularity) {
				$popularity = '&nbsp;';
				if (array_key_exists($k,$plugins_popularity))
				{
					$popularity = $plugins_popularity[$k]['popularity'].'%';
				}
				$table->cell($popularity);
			}
		}

		return($table->get());
	}

	# return an human readable string of $diff
	public static function getDiff($diff)
	{
		if ($diff == 0) {return('0'.' '.__('second'));}
		
		$diff = abs($diff);
		
		$times = array();

		$intervals = array
		(
			(3600*24*365.24) => array('one'=>__('year'),'more'=>__('years')),
			(3600*24*30.4) => array('one'=>__('month'),'more'=>__('months')),
			(3600*24) => array('one'=>__('day'),'more'=>__('days')),
			(3600) => array('one'=>__('hour'),'more'=>__('hours')),
			(60) => array('one'=>__('minute'),'more'=>__('minutes')),
			(1) => array('one'=>__('second'),'more'=>__('seconds')),
		);

		foreach ($intervals as $k => $v)
		{
			if ($diff >= $k)
			{
				$time = floor($diff/$k);
				$times[] = $time.' '.(($time <= 1) ? $v['one'] : $v['more']);
				$diff = $diff%$k;
			}
		}

		# get times and make a string
		if (count($times) > 1)
		{
			$last = array_pop($times);
			$str = implode(__(', '),$times).' '.__('and').' '.$last;
		}
		else {$str = implode('',$times);}

		return($str);
	}
	
	public static function getPluginsXML()
	{
		$file = dirname(__FILE__).'/../xml/plugins.xml';
		$dir = dirname($file);
		
		if (!is_writable($dir))
		{
			return(false);
		}
		
		# update the file if it's older than one day
		if ((!file_exists($file)) OR 
			(filemtime($file) < ($_SERVER['REQUEST_TIME'] - 3600*24)))
		{
			try
			{
				netHttp::quickGet(self::$plugins_xml_url,$file);
			}
			catch (Exception $e) {}
		}
		
		if (file_exists($file) && is_readable($file))
		{
			$simple_xml = simplexml_load_file($file);
			
			return($simple_xml);
		}
		else
		{
			return(false);
		}
	}
}

?>