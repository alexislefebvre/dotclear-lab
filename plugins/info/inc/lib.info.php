<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Informations, a plugin for Dotclear 2
# Copyright 2007,2008,2009,2010 Moe (http://gniark.net/)
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

class info
{
	public static function yes()
	{
		return('<img src="index.php?pf=info/images/accept.png" '.
			'alt="ok" /> ');
	}

	public static function no()
	{
		return('<img src="index.php?pf=info/images/error.png" '.
			'alt="error" /> ');
	}

	/*public static function warning()
	{
		return('<img src="index.php?pf=info/images/exclamation.png" '.
			'alt="error" /> ');
	}*/

	public static function img($bool)
	{
		if ($bool === null) {return('-');}
		return(($bool) ? self::yes() : self::no());
	}

	# thanks to php at mole.gnubb.net http://fr.php.net/manual/fr/function.printf.php#51763
	public static function printf_array($format,$arr)
	{
	    return call_user_func_array('sprintf',
	    	array_merge((array)$format,$arr));
	}

	public static function printf($format,$array)
	{
		array_walk($array,
			create_function('&$str','$str = \'<strong>\'.$str.\'</strong>\';'));
		return(self::printf_array($format,$array));
	}

	public static function args($args,&$format,&$array) {
		$array = $args; 
		$format = array_shift($array);
	}
	
	public static function f_return($p=false)
	{
		self::args(func_get_args(),$format,$array);
		return(self::printf($format,$array));
	}

	public static function f()
	{
		self::args(func_get_args(),$format,$array);
		echo(self::printf($format,$array));
	}

	public static function fp()
	{
		self::args(func_get_args(),$format,$array);
		echo('<p>'.self::printf($format,$array).'</p>');
	}
	
	public static function urls()
	{
		global $core;
		
		try
		{
			# Read default handlers
			$handlers = myUrlHandlers::getDefaults();
			
			# Overwrite with user settings
			$settings = @unserialize($core->blog->settings->url_handlers);
			if (is_array($settings)) {
				foreach ($settings as $name=>$url)
				{
					if (isset($handlers[$name])) {
						$handlers[$name] = $url;
					}
				}
			}
			unset($settings);
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
		
		# table
		$table = new table('class="clear"');

		# thead
		$table->part('head');
		$table->row();
		$table->header(__('Type'));
		$table->header(__('URL'));
		# /thead

		# tbody
		$table->part('body');
		
		foreach ($handlers as $name=>$url)
		{
			# row
			$table->row();
			$table->cell(html::escapeHTML($name));
			$table->cell(html::escapeHTML($url));
			# /row
		}
		# /tbody
		
		# /table

		return($table);
	}
	
	public static function tables()
	{
		global $core;
		
		if ($core->con->driver() == 'sqlite')
		{
			return('<p>'.__('SQLite is not supported').'</p>');
		}
		
		$dotclear_tables = array('blog','category','session',
			'setting','user','permissions','post','media','post_media',
			'log','version','ping','comment','meta');
		
		$default_plugins_tables = array(
			'spamrule' => array(
				'plugin' => 'antispam',
				'name' => __('Antispam')),
			'link' => array(
				'plugin' => 'blogroll',
				'name' => __('Blogroll'))
		);
		
		# first comment at http://www.postgresql.org/docs/8.0/interactive/tutorial-accessdb.html
		$query = ($core->con->driver() == 'pgsql')
			# PostgreSQL
			? 'SELECT table_name FROM information_schema.tables '.
				'WHERE table_schema = \'public\' '.
				# _ is a special character
				# \see http://www.postgresql.org/docs/8.3/static/functions-matching.html#FUNCTIONS-LIKE
				'AND table_name LIKE \''.str_replace('_','\\\\_',
					$core->prefix).'%\''.
				' ORDER BY table_name;'
			# MySQL
			# _ is a special character
			# \see http://dev.mysql.com/doc/refman/5.0/en/string-comparison-functions.html
			: 'SHOW TABLE STATUS LIKE \''.str_replace('_','\\_',
				$core->prefix).'%\'';
		$rs = $core->con->select($query);

		# table
		$table = new table('class="clear"');

		# thead
		$table->part('head');
		$table->row();
		$table->header(__('Name'));
		$table->header(__('Added by'));
		$table->header(__('Records'));
		$table->header(__('Size'));
		# /thead

		# tbody
		$table->part('body');

		$total_size = 0;

		while ($rs->fetch())
		{
			$name = ($core->con->driver() == 'pgsql') ?
				$rs->f('table_name') : $rs->f('Name');
			$rows = $core->con->select('SELECT COUNT(*) AS rows '.
				'FROM '.$name.';')->f('rows');
			$size = ($core->con->driver() == 'pgsql')
				? $core->con->select('SELECT relpages * 8192 AS length '.
					'FROM pg_class WHERE relname = \''.$name.'\';')->f('length')
				: $rs->f('Data_length')+$rs->f('Index_length');
			$total_size += $size;
			$size = files::size($size);
			
			$default = '';
			
			$suffix = substr($name,strlen($core->prefix));
			
			if (in_array($suffix,$dotclear_tables))
			{
				$added_by = '<img src="index.php'.
					'?pf=info/images/icons/dotclear.png" '.
					'alt="'.__('Dotclear').'" /> '.__('Dotclear'); 
			}
			elseif (array_key_exists($suffix,$default_plugins_tables))
			{
				$added_by = '<img src="index.php?pf=info/images/icons/'.
					$default_plugins_tables[$suffix]['plugin'].'.png" alt="'.
					$default_plugins_tables[$suffix]['name'].'" /> '.
					self::f_return(__('the %s plugin (provided with Dotclear)'),
					$default_plugins_tables[$suffix]['name']); 
			}
			else
			{
				$added_by = __('a plugin?'); 
			}
			
			# row
			$table->row();
			$table->cell($name);
			$table->cell($added_by);
			$table->cell($rows);
			$table->cell($size);
			# /row
		}
		# /tbody

		# tfoot
		$table->part('foot');
		$table->row();
		$table->cell(__('Total:'),'colspan="3"');
		$table->cell(files::size($total_size));
		# /tfoot

		# /table

		return($table);
	}

	public static function directories($system=false)
	{
		global $core,$errors;
		
		$settings = $core->blog->settings;

		$plugins_dirs = $plugins_paths = $dirs = array();

		$fileowner = @fileowner(__FILE__);
		$get_owner = (empty($fileowner)) ? false : true;

		# table
		$table = new table('class="clear"');

		# thead
		$table->part('head');
		$table->row();
		$table->header(__('Directory'));
		$table->header(__('Is a directory'));
		$table->header(__('Is writable'));
		$table->header(__('Is readable'));
		if (!$system) {$table->header(__('Relative path'));}
		$table->header(__('Absolute path'));
		if (!$system) {$table->header(__('URL'));}
		if ($get_owner) {$table->header(__('Owner'));}
		$table->header(__('Permissions'));
		# /thead

		# tbody
		$table->part('body');
		
		if ($system)
		{
			$dirs = array(__('Dotclear') => array(
				'path' => path::real(DC_ROOT))
			);
			
			# http://dev.dotclear.net/2.0/changeset/680
			$plugins_dirs = explode(PATH_SEPARATOR,DC_PLUGINS_ROOT);
			if (count($plugins_dirs) < 2)
			{
				$dirs[__('plugins')] = array(
					'path' => implode('',$plugins_dirs)
				);
			}
			else
			{
				$i = 1;
				foreach ($plugins_dirs as $path)
				{
					$dirs[__('plugins').' ('.$i++.')'] =
						array('path' => $path);
				}
			}
			
			$dirs[__('cache')] = array('path'=>DC_TPL_CACHE);
		} else {
			$dirs = array(
				__('public') => array(
					'relative_path' => $settings->system->public_path,
					'absolute_path' => $core->blog->public_path,
					'url' => $settings->system->public_url),
				__('themes') => array(
					'relative_path' => $settings->system->themes_path,
					'absolute_path' => $core->blog->themes_path,
					'url' => $settings->system->themes_url),
				__('theme') => array(
					'relative_path' => $settings->system->themes_path.'/'.
						$settings->system->theme,
					'absolute_path' => $core->blog->themes_path.'/'.
						$settings->system->theme,
					'url' => $settings->system->themes_url.'/'.
						$settings->system->theme)
			);
		}
		
		foreach ($dirs as $name => $v)
		{
			if ($system)
			{
				$path = path::real($v['path'],false);
			}
			else
			{
				$relative_path = $v['relative_path'];
				$path = path::real($v['absolute_path'],false);
			}

			if (is_dir($path))
			{
				$is_dir = (bool)true;
				$is_writable = is_writable($path);
				$is_readable = is_readable($path);
			}
			else
			{
				$is_dir = false;
				$is_writable = $is_readable = null;
			}
			
			$url = '';
			if (isset($v['url']))
			{
				$url = $v['url'];
				if (substr($url,0,1) == '/')
				{
					# public_path is located at the root of the website
					$url = $core->blog->host.$url;
				}
				
				$url = '<a href="'.$url.'">'.__('URL').'</a>';
			}
			
			# row
			$table->row();
			$table->cell($name,'class="nowrap"');
			$table->cell(self::img($is_dir),'class="status center"');
			$table->cell(self::img($is_writable),'class="status center"');
			$table->cell(self::img($is_readable),'class="status center"');
			
			if (!$system)
			{
				$table->cell($relative_path,'class="nowrap"');
			}
			
			$table->cell($path,'class="nowrap"');
			
			if (!$system) {$table->cell($url);}
			
			$owner = '';
			if ($is_dir)
			{
				if ($get_owner)
				{
					$owner = fileowner($path);
					if (function_exists('posix_getpwuid'))
					{
						$owner = posix_getpwuid($owner);
						$owner = $owner['name'];
					}
					$table->cell($owner);
				}
				# http://fr.php.net/manual/en/function.fileperms.php#id2758397
				$fileperms = substr(sprintf('%o',@fileperms($path)),-4);
				$table->cell($fileperms);
				/* for your information :
				according to http://www.delphifaq.com/faq/f1380.shtml :
				
				Question:
				What does chmod 1777 on a folder mean?
				
				Answer:
				The 1 is the "stickc bit". If you perform an ls -l on that folder,
				you will see a 't' next to it.
				The sticky bit 't' means:
				'do not let anyone delete this folder or change it's permissions' */ 
			}
			else
			{
				if ($get_owner)
				{
					# owner
					$table->cell('-');
				}
				# file perms
				$table->cell('-');
			}

			# /row

			# errors
			$dir_errors = false;
			if (!$is_dir)
			{
				$errors[] = self::f_return(
				__('%1$s is not a valid directory, create the directory %2$s or change the settings'),
					$name,$path);
			}
			else
			{
				if (!$is_writable)
				{
					$errors[] = self::f_return(
					__('%1$s directory is not writable, its path is %2$s'),
						$name,$path);
					$dir_errors = true;
				}
				if (!$is_readable)
				{
					$errors[] = self::f_return(
						__('%1$s directory is not readable, its path is %2$s'),
						$name,$path);
					$dir_errors = true;
				}
			}

			if ($dir_errors)
			{
				if ($owner != '')
				{
					$errors[] = self::f_return(__('%1$s directory\'s owner is %2$s'),
						$name,$owner);
				}
				$errors[] = self::f_return(
					__('%1$s directory\'s permissions are %2$s'),
					$name,$fileperms);
			}
			# /errors
		}
		# /tbody

		# /table
		return($table);
	}

	# thanks to Chris http://fr.php.net/manual/en/function.error-reporting.php#65884
	public static function error2string($value)
	{
		$level_names = array(
			E_ERROR => 'E_ERROR',
			E_WARNING => 'E_WARNING',
			E_PARSE => 'E_PARSE',
			E_NOTICE => 'E_NOTICE',
			E_CORE_ERROR => 'E_CORE_ERROR',
			E_CORE_WARNING => 'E_CORE_WARNING',
			E_COMPILE_ERROR => 'E_COMPILE_ERROR',
			E_COMPILE_WARNING => 'E_COMPILE_WARNING',
			E_USER_ERROR => 'E_USER_ERROR',
			E_USER_WARNING => 'E_USER_WARNING',
			E_USER_NOTICE => 'E_USER_NOTICE'
		);
		
		if (defined('E_STRICT')) {$level_names[E_STRICT]='E_STRICT';}
		
		$levels=array();
		if (($value&E_ALL)==E_ALL)
		{
			$levels[]='E_ALL';
			$value&=~E_ALL;
		}
		
		foreach($level_names as $level=>$name)
		{
			if(($value&$level)==$level) $levels[]=$name;
		}
		
		return implode(', ',$levels);
	}
}

?>