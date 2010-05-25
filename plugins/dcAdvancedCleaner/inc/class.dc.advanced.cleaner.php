<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcAdvancedCleaner, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_ADMIN_CONTEXT')){return;}

class dcAdvancedCleaner
{
	protected static $errors = array(
		'settings' => array(
			'delete_global' => 'Failed to delete global settings',
			'delete_local' => 'Failed to delete local settings',
			'delete_all' => 'Failed to delete all settings'
		),
		'tables' => array(
			'empty' => 'Failed to empty table',
			'delete' => 'Failed to delete table'
		),
		'plugins' => array(
			'empty' => 'Failed to empty plugin folder',
			'delete' => 'Failed to delete plugin folder'
		),
		'themes' => array(
			'empty' => 'Failed to empty themes folder',
			'delete' => 'Failed to delete themes folder'
		),
		'caches' => array(
			'empty' => 'Failed to empty cache folder',
			'delete' => 'Failed to delete cache folder'
		),
		'versions' => array(
			'delete' => 'Failed to delete version'
		)
	);

	public static $dotclear = array(
		'settings' => array(
			'antispam','noviny','system','themes','widgets'
		),
		'tables' => array(
			'blog','category','comment','link','log','media',
			'meta','permissions','ping','post','post_media','session',
			'setting','spamrule','user','version'
		),
		'plugins' => array(
			'aboutConfig','akismet','antispam','blogroll','blowupConfig',
			'externalMedia','fairTrackbacks','importExport','maintenance',
			'metadata','pings','themeEditor','widgets'
		),
		'themes' => array(
			'default','customCSS','blueSilence','noviny'
		),
		'caches' => array(
			'cbfeed','cbtpl','versions'
		),
		'versions' => array(
			'antispam','blogroll','core','metadata'
		)
	);

	public static $exclude = array(
		'.','..','__MACOSX','.svn','CVS','.DS_Store','Thumbs.db'
	);

	public static function getSettings($core)
	{
		$res = $core->con->select(
			'SELECT setting_ns '.
			'FROM '.$core->prefix.'setting '.
			'WHERE blog_id IS NULL '.
			"OR blog_id IS NOT NULL ".
			'GROUP BY setting_ns');

		$rs = array();
		$i = 0;
		while($res->fetch()) {

			$rs[$i]['key'] = $res->setting_ns;
			$rs[$i]['value'] = $core->con->select(
				'SELECT count(*) FROM '.$core->prefix.'setting '.
				"WHERE setting_ns = '".$res->setting_ns."' ".
				"AND (blog_id IS NULL OR blog_id IS NOT NULL) ".
				"GROUP BY setting_ns ")->f(0);
			$i++;
		}
		return $rs;
	}

	protected static function deleteGlobalSettings($core,$entry)
	{
		$core->con->execute(
			'DELETE FROM '.$core->prefix.'setting '.
			'WHERE blog_id IS NULL '.
			"AND setting_ns = '".$core->con->escape($entry)."' "
		);
	}

	protected static function deleteLocalSettings($core,$entry)
	{
		$core->con->execute(
			'DELETE FROM '.$core->prefix.'setting '.
			"WHERE blog_id = '".$core->con->escape($core->blog->id)."' ".
			"AND setting_ns = '".$core->con->escape($entry)."' "
		);
	}

	protected static function deleteAllSettings($core,$entry)
	{
		$core->con->execute(
			'DELETE FROM '.$core->prefix.'setting '.
			"WHERE setting_ns = '".$core->con->escape($entry)."' ".
			"AND (blog_id IS NULL OR blog_id != '') "
		);
	}

	public static function getTables($core)
	{
		$object = dbSchema::init($core->con);
		$res = $object->getTables();

		$rs = array();
		$i = 0;
		foreach($res as $k => $v)
		{
			if ('' != $core->prefix)
			{
				if (!preg_match('/^'.preg_quote($core->prefix).'(.*?)$/',$v,$m)) continue;
				$v = $m[1];
			}
			$rs[$i]['key'] = $v;
			$rs[$i]['value'] = $core->con->select('SELECT count(*) FROM '.$res[$k])->f(0);
			$i++;
		}
		return $rs;
	}

	protected static function emptyTable($core,$entry)
	{
		$core->con->execute(
			'DELETE FROM '.$core->con->escapeSystem($core->prefix.$entry)
		);
	}

	protected static function deleteTable($core,$entry)
	{
		self::emptyTable($core,$entry);

		$core->con->execute(
			'DROP TABLE '.$core->con->escapeSystem($core->prefix.$entry)
		);
	}

	public static function getVersions($core)
	{
		$res = $core->con->select('SELECT * FROM '.$core->prefix.'version');

		$rs = array();
		$i = 0;
		while ($res->fetch()) {

			$rs[$i]['key'] = $res->module;
			$rs[$i]['value'] = $res->version;
			$i++;
		}
		return $rs;
	}

	protected static function deleteVersion($core,$entry)
	{
		$core->con->execute(
			'DELETE FROM '.$core->prefix.'version '.
			"WHERE module = '".$core->con->escape($entry)."' "
		);
	}

	public static function getPlugins($core)
	{
		$res = explode(PATH_SEPARATOR,DC_PLUGINS_ROOT);
		return self::getDirs($res);
	}

	protected static function emptyPlugin($core,$entry)
	{
		$res = explode(PATH_SEPARATOR,DC_PLUGINS_ROOT);
		self::delDir($res,$entry,false);
	}

	protected static function deletePlugin($core,$entry)
	{
		$res = explode(PATH_SEPARATOR,DC_PLUGINS_ROOT);
		self::delDir($res,$entry,true);
	}

	public static function getThemes($core)
	{
		return self::getDirs($core->blog->themes_path);
	}

	protected static function emptyTheme($core,$entry)
	{
		self::delDir($core->blog->themes_path,$entry,false);
	}

	protected static function deleteTheme($core,$entry)
	{
		self::delDir($core->blog->themes_path,$entry,true);
	}

	public static function getCaches($core)
	{
		return self::getDirs(DC_TPL_CACHE);
	}

	protected static function emptyCache($core,$entry)
	{
		self::delDir(DC_TPL_CACHE,$entry,false);
	}

	protected static function deleteCache($core,$entry)
	{
		self::delDir(DC_TPL_CACHE,$entry,true);
	}

	public static function execute($core,$type,$action,$ns)
	{
		if (strtolower($ns) == 'dcadvancedcleaner')
			throw new exception("dcAdvancedCleaner can't remove itself");

		# BEHAVIOR dcAdvancedCleanerBeforeAction
		$core->callBehavior('dcAdvancedCleanerBeforeAction',$type,$action,$ns);

		try {
			# Delete global settings
			if ($type == 'settings' && $action == 'delete_global')
				self::deleteGlobalSettings($core,$ns);

			# Delete local settings
			if ($type == 'settings' && $action == 'delete_local')
				self::deleteLocalSettings($core,$ns);

			# Delete all settings
			if ($type == 'settings' && $action == 'delete_all')
				self::deleteAllSettings($core,$ns);

			# Empty tables
			if ($type == 'tables' && $action == 'empty')
				self::emptyTable($core,$ns);

			# Delete tables
			if ($type == 'tables' && $action == 'delete')
				self::deleteTable($core,$ns);

			# Delete versions
			if ($type == 'versions' && $action == 'delete')
				self::deleteVersion($core,$ns);

			# Empty plugins
			if ($type == 'plugins' && $action == 'empty')
				self::emptyPlugin($core,$ns);

			# Delete plugins
			if ($type == 'plugins' && $action == 'delete')
				self::deletePlugin($core,$ns);

			# Empty themes
			if ($type == 'themes' && $action == 'empty')
				self::emptyTheme($core,$ns);

			# Delete themes
			if ($type == 'themes' && $action == 'delete')
				self::deleteTheme($core,$ns);

			# Empty caches
			if ($type == 'caches' && $action == 'empty')
				self::emptyCache($core,$ns);

			# Delete caches
			if ($type == 'caches' && $action == 'delete')
				self::deleteCache($core,$ns);

			return true;
		}
		catch(Exception $e) {
			$errors = self::$errors;
			if (isset($errors[$type][$action])) {
				throw new Exception(__($errors[$type][$action]));
			}
			else {
				throw new Exception(sprintf(__('Cannot execute "%s" of type "%s"'),$action,$type));
			}
			return false;
		}
	}

	protected static function getDirs($roots)
	{
		if (!is_array($roots))
			$roots = array($roots);

		$rs = array();
		$i = 0;
		foreach ($roots as $root) {

			$dirs = files::scanDir($root);
			foreach($dirs as $k) {

				if ('.' == $k || '..' == $k || !is_dir($root.'/'.$k)) continue;

				$rs[$i]['key'] = $k;
				$rs[$i]['value'] = count(self::scanDir($root.'/'.$k));
				$i++;
			}
		}
		return $rs;
	}

	protected static function delDir($roots,$folder,$delfolder=true)
	{
		if (strpos($folder,'/'))
			return false;

		if (!is_array($roots))
			$roots = array($roots);

		foreach ($roots as $root)
		{
			if (file_exists($root.'/'.$folder))
				return self::delTree($root.'/'.$folder,$delfolder);
		}
		return false;
	}

	protected static function scanDir($path,$dir='',$res=array())
	{
		$exclude = self::$exclude;

		$path = path::real($path);
		if (!is_dir($path) || !is_readable($path)) return array();

		$files = files::scandir($path);

		foreach($files AS $file) {
			if (in_array($file,$exclude)) continue;

			if (is_dir($path.'/'.$file)) {

				$res[] = $file;
				$res = self::scanDir($path.'/'.$file,$dir.'/'.$file,$res);
			} else {

				$res[] = empty($dir) ? $file : $dir.'/'.$file;
			}
		}
		return $res;
	}

	protected static function delTree($dir,$delroot=true)
	{
		if (!is_dir($dir) || !is_readable($dir)) return false;

		if (substr($dir,-1) != '/') $dir .= '/';

		if (($d = @dir($dir)) === false) return false;

		while (($entryname = $d->read()) !== false)
		{
			if ($entryname != '.' && $entryname != '..')
			{
				if (is_dir($dir.'/'.$entryname))
				{
					if (!self::delTree($dir.'/'.$entryname)) return false;
				}
				else
				{
					if (!@unlink($dir.'/'.$entryname)) return false;
				}
			}
		}
		$d->close();

		if ($delroot)
			return @rmdir($dir);
		else
			return true;
	}
}
?>