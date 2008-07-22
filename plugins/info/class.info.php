<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Informations.
# Copyright 2007 Moe (http://gniark.net/)
#
# Informations is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Informations is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

class info
{

	public static function yes()
	{
		return('<img src="index.php?pf=info/images/accept.png" alt="ok" /> ');
	}

	public static function no()
	{
		return('<img src="index.php?pf=info/images/error.png" alt="error" /> ');
	}

	/*public static function warning()
	{
		return('<img src="index.php?pf=info/images/exclamation.png" alt="error" /> ');
	}*/

	public static function img($bool)
	{
		if ($bool === null) {return('-');}
		return(($bool) ? self::yes() : self::no());
	}

	# thanks to php at mole.gnubb.net http://fr.php.net/manual/fr/function.printf.php#51763
	public static function printf_array($format, $arr)
	{
	    return call_user_func_array('sprintf', array_merge((array)$format, $arr));
	}

	public static function printf($format,$array)
	{
		array_walk($array,create_function('&$str','$str = \'<strong>\'.$str.\'</strong>\';'));
		return(self::printf_array($format,$array));
	}

	public static function args($args,&$format,&$array) {
		$array = $args; 
		$format = array_shift($array);
	}

	public static function f($p=false)
	{
		self::args(func_get_args(),$format,$array);
		echo(self::printf($format,$array));
	}

	public static function fp()
	{
		self::args(func_get_args(),$format,$array);
		echo('<p>'.self::printf($format,$array).'</p>');
	}

	public static function tables()
	{
		global $core;

		# first comment at http://www.postgresql.org/docs/8.0/interactive/tutorial-accessdb.html
		$query = ($core->con->driver() == 'pgsql')
			? 'SELECT table_name FROM information_schema.tables WHERE table_schema = \'public\' '.
				'AND table_name LIKE \''.$core->prefix.'%\' ORDER BY table_name;'
			: 'SHOW TABLE STATUS LIKE \''.$core->prefix.'%\'';
		$rs = $core->con->select($query);

		# table
		$table = new table('class="clear"');

		# thead
		$table->part('head');
		$table->row();
		$table->header(__('Name'));
		$table->header(__('Records'));
		$table->header(__('Size'));
		# /thead

		# tbody
		$table->part('body');

		$total_size = 0;

		while ($rs->fetch())
		{
			$name = ($core->con->driver() == 'pgsql') ? $rs->f('table_name') : $rs->f('Name');
			$rows = $core->con->select('SELECT COUNT(*) AS rows FROM '.$name.';')->f('rows');
			$size = ($core->con->driver() == 'pgsql')
				? $core->con->select('SELECT relpages * 8192 AS length '.
					'FROM pg_class WHERE relname = \''.$name.'\';')->f('length')
				: $rs->f('Data_length')+$rs->f('Index_length');
			$total_size += $size;
			$size = files::size($size);
			
			# row
			$table->row();
			$table->cell($name);
			$table->cell($rows);
			$table->cell($size);
			# /row
		}
		# /tbody

		# tfoot
		$table->part('foot');
		$table->row();
		$table->cell(__('Total :'),'colspan="2"');
		$table->cell(files::size($total_size));
		# /tfoot

		# /table

		return($table);
	}

	public static function directories()
	{
		global $core, $errors;

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
		$table->header(__('Path'));
		if ($get_owner) {$table->header(__('Owner'));}
		$table->header(__('Permissions'));
		# /thead

		# tbody
		$table->part('body');

		# http://dev.dotclear.net/2.0/changeset/680
		$plugins_dirs = explode(PATH_SEPARATOR,DC_PLUGINS_ROOT);
		if (count($plugins_dirs) < 2)
		{
			$plugins_paths[__('plugins')]['path'] = implode('',$plugins_dirs);
		}
		else
		{
			$i = 1;
			foreach ($plugins_dirs as $path)
			{
				$plugins_paths[__('plugins').' ('.$i++.')']['path'] = $path;
			}
		}

		$dirs = array(
			__('cache') => array('path'=>DC_TPL_CACHE),
			__('public') => array('path'=>path::fullFromRoot($core->blog->settings->public_path,DC_ROOT)),
			__('themes') => array('path'=>path::fullFromRoot($core->blog->settings->themes_path,DC_ROOT)),
			__('theme') => array('path'=>path::fullFromRoot($core->blog->settings->themes_path.'/'.
				$core->blog->settings->theme,DC_ROOT)),
		);

		$dirs = array_merge($plugins_paths,$dirs);

		foreach ($dirs as $name => $v)
		{
			$path = path::real($v['path'],false);

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
			
			# row
			$table->row();
			$table->cell($name,'class="nowrap"');
			$table->cell(self::img($is_dir),'class="status center"');
			$table->cell(self::img($is_writable),'class="status center"');
			$table->cell(self::img($is_readable),'class="status center"');
			$table->cell($path,'class="nowrap"');

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
				$fileperms = substr(sprintf('%o', @fileperms($path)),-4);
				$table->cell($fileperms);
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
				array_push($errors,sprintf(__('%1$s is not a valid directory, create the directory %2$s'),
					$name,$path));
			}
			else
			{
				if (!$is_writable)
				{
					array_push($errors,sprintf(
						__('%1$s directory is not writable, its path is %2$s'),$name,$path));
					$dir_errors = true;
				}
				if (!$is_readable)
				{
					array_push($errors,sprintf(
						__('%1$s directory is not readable, its path is %2$s'),$name,$path));
					$dir_errors = true;
				}
			}

			if ($dir_errors)
			{
				if ($owner != '')
				{
					array_push($errors,sprintf(
						__('%1$s directory\'s owner is %2$s'),$name,$owner));
				}
				array_push($errors,sprintf(
					__('%1$s directory\'s permissions are %2$s'),$name,$fileperms));
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
	        E_ERROR => 'E_ERROR',E_WARNING => 'E_WARNING',
	        E_PARSE => 'E_PARSE', E_NOTICE => 'E_NOTICE',
	        E_CORE_ERROR => 'E_CORE_ERROR', E_CORE_WARNING => 'E_CORE_WARNING',
	        E_COMPILE_ERROR => 'E_COMPILE_ERROR', E_COMPILE_WARNING => 'E_COMPILE_WARNING',
	        E_USER_ERROR => 'E_USER_ERROR', E_USER_WARNING => 'E_USER_WARNING',
	        E_USER_NOTICE => 'E_USER_NOTICE' );
	    if(defined('E_STRICT')) $level_names[E_STRICT]='E_STRICT';
	    $levels=array();
	    if(($value&E_ALL)==E_ALL)
	    {
	        $levels[]='E_ALL';
	        $value&=~E_ALL;
	    }
	    foreach($level_names as $level=>$name)
	        if(($value&$level)==$level) $levels[]=$name;
	    return implode(', ',$levels);
	}
}

?>