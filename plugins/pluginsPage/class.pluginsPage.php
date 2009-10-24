<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Plugins Page.
# Copyright 2007 Moe (http://gniark.net/)
#
# Plugins Page is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Plugins Page is distributed in the hope that it will be useful,
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

class pluginsPage
{

	# use : getPluginsArray(array('name','root','version')
	public static function getPluginsArray($array_keys,$array_hidden_plugins=array())
	{
		global $core;

		$modules = $core->plugins->getModules();
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

	public static function getPluginsTable($public = false,$editable=false)
	{
		global $core;

		$default_plugins = array('metadata','widgets','antispam','maintenance','blogroll','aboutConfig','pings','akismet','importExport');

		$hidden_plugins = array();
		if (strlen($core->blog->settings->pluginsPage_hidden_plugins) > 0)
		{
			$hidden_plugins = unserialize(base64_decode($core->blog->settings->pluginsPage_hidden_plugins));
		}
		elseif (isset($_POST['hidden_plugins']))
		{
			$hidden_plugins = $_POST['hidden_plugins'];
		}

		if (!is_array($hidden_plugins)) {$hidden_plugins = array();}

		if ($editable)
		{
			$array = self::getPluginsArray(array('name','root','version','desc'));
		}
		else
		{
			$array = self::getPluginsArray(array('name','root','version','desc'),$hidden_plugins);
		}

		$table = new table('class="clear" cellspacing="0" id="plugins" summary="'.__('Installed plugins:').'"');
		$table->part('head');
		$table->row();
		if ($editable)
		{
			$table->header(__('Hide'),'class="nowrap"');
		}
		if ($core->blog->settings->pluginsPage_show_icons)
		{
			$table->header(__('Icon'));
		}
		$table->header(__('Name'),'title="'.__('Plugin').'"');
		$table->header(__('Version'));
		$table->header(__('Description'));
		
		$table->part('body');

		foreach ($array as $k => $v)
		{
			# public
			if ($public)
			{
				if ($core->blog->settings->url_scan == 'path_info')
				{
					$icon = (file_exists($v['root'].'/icon.png')) ? '<img src="?pf='.$k.'/icon.png" style="height:16px;" alt="" />' : '';
				}
				else
				{
					$icon = (file_exists($v['root'].'/icon.png')) ? '<img src="'.$core->blog->url.'pf='.$k.'/icon.png" style="height:16px;" alt="" />' : '';
				}
			}
			# admin
			else
			{
				$icon = (file_exists($v['root'].'/icon.png')) ? '<img src="index.php?pf='.$k.'/icon.png" style="height:16px;" alt="" />' : '';
			}

			$table->row((in_array($k,$default_plugins)) ? 'class="default"' : '');
			if ($editable)
			{
				$table->cell(form::checkbox(array('hidden_plugins[]'),$k,in_array($k,$hidden_plugins)));
			}
			if ($core->blog->settings->pluginsPage_show_icons)
			{
				$table->cell($icon,'class="icon"');
			}
			$table->cell('<span title="'.$k.'">'.$v['name'].'</span>');
			$table->cell($v['version']);
			$table->cell($v['desc']);
		}

		return($table->get());
	}

	public static function getCacheFile()
	{
		# from $core->tpl->getFilePath($tpl) in /dotclear/inc/clearbricks/template/class.template.php
		$file_md5 = md5(dirname(__FILE__).'/template/plugins.html');
		$dest_file = sprintf('%s/%s/%s/%s/%s.php',
			DC_TPL_CACHE,
			'cbtpl',
			substr($file_md5,0,2),
			substr($file_md5,2,2),
			$file_md5
		);
		return(file_exists($dest_file) ? $dest_file : null);
	}

	public static function deleteCache()
	{
		$dest_file = self::getCacheFile();
		if ($dest_file === null)
		{
			throw new Exception(__('Cache file does not exist'));
		}
		unlink($dest_file);
	}
}

class pluginsDocument extends dcUrlHandlers
{
	public static function showPage()
	{
		self::serveDocument('plugins.html');
	}

	public static function showTable()
	{
		return(pluginsPage::getPluginsTable(true));
	}

}

?>