<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of CompreSS, a plugin for Dotclear 2
# Copyright (c) 2008,2009,2010 Moe (http://gniark.net/)
#
# CompreSS is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# CompreSS is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons :
#	<http://www.famfamfam.com/lab/icons/silk/>
#
# ***** END LICENSE BLOCK *****

class compress
{
	public static $file_ext = '.css';
	public static $backup_ext = '.bak.css';
	public static $dated_backup_ext = '.bak.css.gz';
	public static $file_ext_len = 4; # strlen(self::$file_ext)
	public static $backup_ext_len = 8; # strlen(self::$backup_ext)
	public static $dated_backup_ext_len = 11; # strlen(self::$dated_backup_ext)

	public static function backupFilename($file)
	{
		return(substr($file,0,(strlen($file)-4)).self::$backup_ext);
	}

	public static function check_backup($file)
	{
		return(file_exists(self::backupFilename($file)));
	}
	
	public static function create_backup($file)
	{
		if (!is_writable(dirname($file)))
		{
			throw new Exception(sprintf(__('%s is not writable'),dirname($file)));
		}
		copy($file,self::backupFilename($file));
		return(true);
	}

	public static function is_css($file)
	{
		return((substr($file,(-1*self::$file_ext_len)) == self::$file_ext)
			AND (substr($file,(-1*self::$backup_ext_len)) != self::$backup_ext));
	}

	public static function is_backup($file)
	{
		return(
			((substr($file,(-1*self::$backup_ext_len)) == self::$backup_ext)
			AND (!is_numeric(str_replace('-','',substr($file,-18,15)))))
		);
	}

	public static function is_dated_backup($file)
	{
		$date_len = strlen('20080405-184111');
		if ((substr($file,(-1*self::$dated_backup_ext_len)) == self::$dated_backup_ext)
			AND (is_numeric(str_replace('-','',
				substr($file,(-1*($date_len+self::$dated_backup_ext_len)),$date_len)))))
		{
			return(true);
		}
		elseif ((substr($file,(-1*self::$backup_ext_len)) == self::$backup_ext)
			AND (is_numeric(str_replace('-','',
				substr($file,(-1*($date_len+self::$backup_ext_len)),$date_len)))))
		{
			return(true);
		}
		# else
		return(false);
	}

	public static function get_date($file)
	{
		$date_len = strlen('20080405-184111');
		if (substr($file,(-1*self::$dated_backup_ext_len)) == self::$dated_backup_ext)
		{
			return(substr($file,(-1*($date_len+self::$dated_backup_ext_len)),$date_len));
		}
		//return(substr($file,-23,15));
	}

	public static function percent($file)
	{
		if (self::is_backup($file))
		{
			return(round((filesize(self::get_original_filename($file)) / 
			(filesize($file))*100),2));
		}
		elseif ((self::is_css($file)) AND (self::check_backup($file)))
		{
			$backup_size = filesize(self::backupFilename($file));
			# avoid division by zero
			if ($backup_size == 0) {return(false);}
			return(
				round((filesize($file) / ($backup_size)*100),2)
			);
		}
		# else
		return(false);
	}

	public static function get_original_filename($file)
	{
		if (self::is_backup($file))
		{
			return(substr($file,0,(strlen($file)-8)).self::$file_ext);
		}
		# else
		return(false);
	}

	public static function get_themes_list()
	{
		global $core;
	
		if (empty($core->themes)) {
			$core->themes = new dcModules($core);
			$core->themes->loadModules($core->blog->themes_path,null);
		}

		return($core->themes->getModules());
	}

	public static function compress_file($file)
	{
		global $core;

		if (!is_writable(dirname($file)))
		{
			throw new Exception(sprintf(__('%s is not writable'),$file));
		}

		$is_backup = false; 
		# if is backup
		if (self::is_backup($file))
		{
			$compressed_file = self::get_original_filename($file);
			$is_backup = true;
		}
		else
		{
			if (self::check_backup($file) !== true)
			{
				self::create_backup($file);
			}
			$compressed_file = $file;
		}

		if (!is_writable($compressed_file))
		{
			throw new Exception(sprintf(__('%s is not writable'),$compressed_file));
		}
		if (!is_readable($file))
		{
			throw new Exception(sprintf(__('%s is not readable'),$compressed_file));
		}

		$content = file_get_contents($file);
		
		if ($core->blog->settings->compress_create_backup_every_time)
		{
			$ext_len = self::$file_ext_len;
			if ($is_backup) {$ext_len = self::$backup_ext_len;}
			$file_without_ext = substr($file,0,(strlen($file)-$ext_len));
			if (function_exists('gzopen'))
			{
				$gz_file = gzopen($file_without_ext.'.'.
					dt::str('%Y%m%d-%H%M%S',null,$core->blog->settings->blog_timezone).
					self::$dated_backup_ext,'wb9');
				gzwrite($gz_file,$content,strlen($content));
				gzclose($gz_file);
			}
			else
			{
				copy($file,$file_without_ext.'.'.
					dt::str('%Y%m%d-%H%M%S',null,$core->blog->settings->blog_timezone).
					self::$backup_ext);
			}
		}
		# remove multiple spaces 
		# http://bytes.com/forum/thread160400.html
		$content = preg_replace('` {2,}`', ' ', $content);
		# remove comments		# http://www.webmasterworld.com/forum88/11584.htm		if (!$core->blog->settings->compress_keep_comments)
		{
			$content = preg_replace('/(\/\*[\s\S]*?\*\/)/', '', $content);
		}
		# remove tabs, carriage returns and new lines 
		$content = preg_replace('/(\t|\r|\n)/', '', $content);
		
		# ' { ' => '{'
		$content = str_replace(array(' { ',' {','{ '),'{', $content);
		# ' } ' => '}'
		$content = str_replace(array(' } ',' }','} '),'}', $content);
		# ' : ' => ':'
		$content = str_replace(array(' : ',' :',': '),':', $content);
		# ' ; ' => ';'
		$content = str_replace(array(' ; ',' ;','; '),';', $content);
		# ' , ' => ','
		$content = str_replace(array(' , ',' ,',', '),',', $content);
		$content = $core->blog->settings->compress_text_beginning.$content;
		files::putContent($compressed_file,$content);
		return(true);
	}

	public static function delete($file)
	{
		if (!is_readable($file))
		{
			throw new Exception(sprintf(__('%s is not readable'),$file));
		}
		if (!is_writable(dirname($file)))
		{
			throw new Exception(sprintf(__('%s is not writable'),$file));
		}
		if (self::is_backup($file))
		{
			if (!is_writable(self::get_original_filename($file)))
			{
				throw new Exception(sprintf(__('%s is not writable'),
					self::get_original_filename($file)));
			}
			copy($file,self::get_original_filename($file));
			if (!files::isDeletable($file))
			{
				throw new Exception(sprintf(__('%s is not deletable'),$file));
			}
			unlink($file);
			return(true);
		}
		elseif (self::is_dated_backup($file))
		{
			if (!files::isDeletable($file))
			{
				throw new Exception(sprintf(__('%s is not deletable'),$file));
			}
			unlink($file);
			return(true);
		}
		else
		{
			throw new Exception(sprintf(__('%s is not a backup file'),$file));
		}
	}
	
	public static function scanTheme($root,$path='',$nodes,$action)
	{
		foreach ($nodes as $node => $value)
		{
			if (is_array($value) && !empty($value))
			{
				$node = ((empty($path)) ? $node : $path.'/'.$node);
				self::scanTheme($root,$node,$value,$action);
			}
			else
			{
				# file
				$file_absolute_path = $root.'/'.$path.'/'.$value;

				if ($action == 'compress')
				{
					if ((self::is_css($value)) OR (self::is_backup($value)))
					{
						self::compress_file($file_absolute_path);
					}
				}
				elseif ($action == 'replace_compressed_files')
				{
					if (self::is_backup($value))
					{
						self::delete($file_absolute_path);
					}
				}
				elseif ($action == 'delete_backups')
				{
					if (self::is_dated_backup($value))
					{
						self::delete($file_absolute_path);
					}
				}
			}
		}
	}
	
	public static function compress_all()
	{
		$themes_list = self::get_themes_list();

		foreach ($themes_list as $theme)
		{
			$theme_root = path::real($theme['root']);
			
			$tree = self::getTree($theme_root);
			self::scanTheme($theme_root,'',$tree,'compress');
		}
	}
	
	public static function replace_compressed_files()
	{
		$themes_list = self::get_themes_list();

		foreach ($themes_list as $theme)
		{
			$theme_root = path::real($theme['root']);
			
			$tree = self::getTree($theme_root);
			self::scanTheme($theme_root,'',$tree,'replace_compressed_files');
		}
	}
	
	public static function delete_all_backups()
	{
		$themes_list = self::get_themes_list();

		foreach ($themes_list as $theme)
		{
			$theme_root = path::real($theme['root']);
			
			$tree = self::getTree($theme_root);
			self::scanTheme($theme_root,'',$tree,'delete_backups');
		}
	}

	public static function compress_theme($dir)
	{
		$themes_list = self::get_themes_list();

		$theme_root = path::real($themes_list[$dir]['root']);
		
		$tree = self::getTree($theme_root);
		self::scanTheme($theme_root,'',$tree,'compress');
	}

	public static function replace_compressed_files_in_theme($dir)
	{
		$themes_list = self::get_themes_list();

		$theme_root = path::real($themes_list[$dir]['root']);
			
		$tree = self::getTree($theme_root);
		self::scanTheme($theme_root,'',$tree,'replace_compressed_files');
	}

	public static function delete_all_backups_in_theme($dir)
	{
		$themes_list = self::get_themes_list();
		
		$theme_root = path::real($themes_list[$dir]['root']);
		
		$tree = self::getTree($theme_root);
		self::scanTheme($theme_root,'',$tree,'delete_backups');
	}
	
	public static function css_table()
	{
		global $core, $p_url;

		$list = self::get_themes_list();

		foreach ($list as $theme)
		{
			$info = '';

			$theme_root = path::real($theme['root']);
			$dirname = basename($theme_root);
			
			if ($dirname == 'default') {$info .= ' (<strong>'.__('default theme').'</strong>)';}
			if ($core->blog->settings->theme == $dirname)
			{
				$info .= ' (<strong>'.__('blog theme').'</strong>)';
			}
			$title = '<div class="folder"><h3>'.__('Theme&nbsp;:').' '.
				$theme['name'].$info.'</h3>'.
				'<form action="'.
					$p_url.'" method="post"><p>'.
					form::hidden(array('dir',''),$dirname).
					'<input type="submit" name="compress" value="'.
						__('Compress CSS files').'" /> '.
					'<input type="submit" name="replace_compressed_files" value="'.
						__('Replace compressed files').'" /> '.
					'<input type="submit" name="delete_all_backups" value="'.
						__('Delete backups files').'" />'.
					$core->formNonce().'</p></form>'.
				'</div>';

			# table
			$table = new table('class="clear" cellspacing="0" cellpadding="1" summary="CSSs"');
			$table->headers(__('file'),__('type'),__('size'),__('actions'));
			$table->part('body');

			# table content
			$tree = self::getTree($theme_root);

			self::nodes2Table($theme_root,'',$tree,$table);
			# /table content
			
			echo($title.$table->get());
		}
	}

	public static function nodes2Table($root,$path='',$nodes,&$table)
	{
		global $core, $p_url;
		
		foreach ($nodes as $node => $value)
		{
			if (is_array($value) && !empty($value))
			{
				$node = ((empty($path)) ? $node : $path.'/'.$node);
				self::nodes2Table($root,$node,$value,$table);
			}
			else
			{
				$file_absolute_path = $root.'/'.$path.'/'.$value;
				
				if ((is_file($file_absolute_path)) AND ((self::is_css($value))
					 OR (self::is_backup($value)) OR (self::is_dated_backup($value))))
				{
					$url = $core->blog->settings->themes_url.'/'.
						basename($root).'/'.$path.'/'.$value;

					# initialize values
					$class = $info = $percent = $actions = $tr_class = '';
					$filesize = files::size(filesize($file_absolute_path));
					# CSS file
					if (self::is_css($value))
					{
						$percent = self::percent($file_absolute_path);
						if ($percent !== false)
						{
							$percent = sprintf(__('(%s%% of the original size)'),$percent);
						}
					}
					# CSS file without backup file
					if ((self::is_css($value))
						AND (!self::check_backup($file_absolute_path)))
					{
						$class = 'css';
						$info = __('uncompressed file');
						$actions = '<input type="submit" name="compress" value="'.
							__('compress').'" />';
					}
					# CSS file with backup file
					elseif (self::is_css($file_absolute_path))
					{
						$class = 'css';
						$info = __('compressed file');
					}
					# backup file
					elseif (self::is_backup($value))
					{
						$tr_class = 'backup';
						$class = 'backup';
						$info = __('original file');
						$actions = '<input type="submit" name="compress" value="'.
							__('compress to').' '.self::get_original_filename($value).'" />';
						$actions .= ' '.'<input type="submit" name="delete" value="'.
							__('delete').'" />';
					}
					# dated backup file 
					elseif (self::is_dated_backup($value))
					{
						$tr_class = 'backup';
						$class = 'dated_backup';
						$info = __('backup file').'<br />('.self::get_date($file_absolute_path).')';
						$actions = '<input type="submit" name="delete" value="'.__('delete').'" />';
					}

					$actions = (!empty($actions)) ? '<form action="'.
						$p_url.'" method="post">'.
						'<p>'.form::hidden(array('file',''),$file_absolute_path).
						$actions.$core->formNonce().'</p></form>' : '';

					$name = ((empty($path)) ? $value : $path.'/'.$value);
					
					$table->row('class="'.$tr_class.'"');
					if (!empty($info)) {$info = '<br />'.$info;}
					$table->cell('<a href="'.$url.'">'.$name.'</a>','class="'.$class.'"');
					if (!empty($percent)) {$percent = '<br />'.$percent;}
					$table->cell($info);
					$table->cell($filesize.$percent);
					$table->cell($actions);
				}
			}
		}
	}
	
	public static function getTree($root,&$tree=array())
	{
		$nodes = scandir($root);

		$dir = basename($root);

		foreach ($nodes as $node)
		{
			$full_path = $root.'/'.$node;
			
			if (substr($node,0,1) == '.')
			{
				continue;
			}
			elseif (is_dir($full_path))
			{
				self::getTree($full_path,$tree[$node]);
			}
			# get only CSS files
			elseif ((is_file($full_path)) AND ((self::is_css($node))
				OR (self::is_backup($node)) OR (self::is_dated_backup($node))))
			{
				$tree[] = $node;
			}
		}

		return($tree);
	}
}
?>