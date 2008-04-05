<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of CompreSS.
# Copyright 2008 Moe (http://gniark.net/)
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
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

class compress
{
	public static function check_backup($file)
	{
		return(file_exists($file.'.bak'));
	}
	
	public static function create_backup($file)
	{
		if (!is_writable(dirname($file)))
		{
			return(dirname($file).' '.__('is not writable'));
		}
		copy($file,$file.'.bak');
		return(true);
	}

	public static function is_css($file)
	{
		return((substr($file,-4) == '.css'));
	}

	public static function is_backup($file)
	{
		return(
			((substr($file,-8) == '.css.bak') AND (!is_numeric(str_replace('-','',substr($file,-23,15)))))
		);
	}

	public static function is_dated_backup($file)
	{
		if ((substr($file,-7) == '.css.gz') AND (is_numeric(str_replace('-','',substr($file,-22,15)))))
		{
			return(true);
		}
		elseif ((substr($file,-8) == '.css.bak') AND (is_numeric(str_replace('-','',substr($file,-23,15)))))
		{
			return(true);
		}
		else
		{
			return(false);
		}
	}

	public static function get_date($file)
	{
		if (substr($file,-7) == '.css.gz')
		{
			return(substr($file,-22,15));
		}
		return(substr($file,-23,15));
	}

	public static function percent($file)
	{
		if (self::is_backup($file))
		{
			return(round((filesize(self::get_original_filename($file)) / (filesize($file))*100),2));
		}
		elseif ((self::is_css($file)) AND (self::check_backup($file)))
		{
			return(round((filesize($file) / (filesize($file.'.bak'))*100),2));
		}
		return(false);
	}

	public static function get_original_filename($file)
	{
		if (self::is_backup($file))
		{
			return(substr($file,0,(strlen($file)-4)));
		}
		return(false);
	}

	# changer "chaine" en "<td>chaine</td>", et l'afficher
	public static function str2td($str = '', $class = '')
	{
	 	/* IE n'affiche pas de case si la case est vide ? */
  		if ($str == '') {$str = '&nbsp;';}
  		if ($class != '') {$class = ' class="'.$class.'"';}
  		return("\t\t\t".'<td'.$class.'>'.$str.'</td>'."\n");
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

		if (!is_writable(dirname($file))) {return(dirname($file).' '.__('is not writable'));}

		# if is backup
		if (self::is_backup($file))
		{
			$compressed_file = self::get_original_filename($file);
		}
		else
		{
			if (self::check_backup($file) !== true)
			{
				$create_backup = self::create_backup($file);
				if ($create_backup !== true) {return($create_backup);}
			}
			$compressed_file = $file;
		}

		if (!is_writable($compressed_file)) {return($compressed_file.' '.__('is not writable'));}
		if (!is_readable($file)) {return($file.' '.__('is not readable'));}

		$compressed_file_content = file_get_contents($file);
		if ($core->blog->settings->compress_create_backup_every_time)
		{
			if (function_exists('gzopen'))
			{
				$gz_file = gzopen($file.'.'.date('Ymd-His').'.css.gz','wb9');
				gzwrite($gz_file,$compressed_file_content,strlen($compressed_file_content));
				gzclose($gz_file);
			}
			else
			{
				copy($file,$file.'.'.
					date('Ymd-His',dt::addTimeZone($core->blog->settings->blog_timezone)).'.css.bak');
			}
		}
		# remove comments		# http://www.webmasterworld.com/forum88/11584.htm		if (!$core->blog->settings->compress_keep_comments)
		{
			$compressed_file_content = preg_replace('/(\/\*[\s\S]*?\*\/)/', '', $compressed_file_content);
		}
		$compressed_file_content = preg_replace('/(\t|\r|\n)/', '', $compressed_file_content);
		# remove multiple spaces 
		# http://bytes.com/forum/thread160400.html
		$compressed_file_content = preg_replace('` {2,}`', ' ', $compressed_file_content);
		# '{' => '{'
		$compressed_file_content = str_replace(array(' { ',' {','{ '),'{', $compressed_file_content);
		# ' } ' => '}'
		$compressed_file_content = str_replace(array(' } ',' }','} '),'}', $compressed_file_content);
		# ' : ' => ':'
		$compressed_file_content = str_replace(array(' : ',' :',': '),':', $compressed_file_content);
		# ' ; ' => ';'
		$compressed_file_content = str_replace(array(' ; ',' ;','; '),';', $compressed_file_content);
		# ' , ' => ','
		$compressed_file_content = str_replace(array(' , ',' ,',', '),',', $compressed_file_content);
		$compressed_file_content = $core->blog->settings->compress_text_beginning.$compressed_file_content;
		files::putContent($compressed_file,$compressed_file_content);
		return(true);
	}

	public static function delete($file)
	{
		if (!is_readable($file)) {return($file.' '.__('is not readable'));}
		if (!is_writable(dirname($file))) {return(dirname($file).' '.__('is not writable'));}
		if (self::is_backup($file))
		{
			if (!is_writable(self::get_original_filename($file)))
			{
				return(self::get_original_filename($file).' '.__('is not writable'));
			}
			copy($file,self::get_original_filename($file));
			if (!files::isDeletable($file)) {return($file.' '.__('is not deletable'));}
			unlink($file);
			return(true);
		}
		elseif (self::is_dated_backup($file))
		{
			if (!files::isDeletable($file)) {return($file.' '.__('is not deletable'));}
			unlink($file);
			return(true);
		}
		else {return($file.' '.__('is not a backup file'));}
	}

	public static function compress_all()
	{
		$themes_list = self::get_themes_list();
		$compress = true;

		foreach ($themes_list as $theme)
		{
			$dir_absolute_path = path::real($theme['root']);
			$list_files = scandir($dir_absolute_path);

			foreach ($list_files as $file)
			{
				$file_absolute_path = $dir_absolute_path.'/'.$file;
				if ((is_file($file_absolute_path)) AND ((self::is_css($file)) OR (self::is_backup($file))))
				{
					$compress = self::compress_file($file_absolute_path);
					if ($compress !== true) {return($compress);}
				}
			}
		}
		return($compress);
	}

	public static function delete_all_backups()
	{
		global $core;
	
		$themes_list = self::get_themes_list();
		$delete = true;
		foreach ($themes_list as $theme)
		{
			$dir_absolute_path = path::real($theme['root']);
			$list_files = scandir($dir_absolute_path);

			foreach ($list_files as $file)
			{
				$file_absolute_path = $dir_absolute_path.'/'.$file;
				if ((is_file($file_absolute_path)) AND (self::is_dated_backup($file)))
				{
					$delete = self::delete($file_absolute_path);
					if ($delete !== true) {return($delete);}		
				}
			}
		}
		return($delete);
	}

	public static function replace_compressed_files()
	{
		global $core;
	
		$themes_list = self::get_themes_list();
		$replace = true;
		foreach ($themes_list as $theme)
		{
			$dir_absolute_path = path::real($theme['root']);
			$list_files = scandir($dir_absolute_path);

			foreach ($list_files as $file)
			{
				$file_absolute_path = $dir_absolute_path.'/'.$file;
				if ((is_file($file_absolute_path)) AND (self::is_backup($file)))
				{
					$replace = self::delete($file_absolute_path);
					if ($replace !== true) {return($replace);}		
				}
			}
		}
		return($replace);
	}

	
	public static function css_table()
	{
		global $core;

		$list = self::get_themes_list();

		foreach ($list as $theme)
		{
			$dir_absolute_path = path::real($theme['root']);			$dirname = substr($dir_absolute_path,(strrpos($dir_absolute_path,'/')+1)); 
			$table = new table('class="clear" cellspacing="0" cellpadding="1" summary="CSSs"');
			$info = '';
			if ($dirname == 'default') {$info .= ' (<strong>'.__('default theme').'</strong>)';}
			if ($core->blog->settings->theme == $dirname) {$info .= ' (<strong>'.__('blog theme').'</strong>)';}
			$table->caption('<h3 class="folder">'.__('Theme&nbsp;:').' '.
				$theme['name'].$info.'</h3>');
			$table->headers(__('file'),__('size'),__('actions'));
			$table->part('body');
			$list_files = scandir($dir_absolute_path);

			foreach ($list_files as $file)
			{
				$file_absolute_path = $dir_absolute_path.'/'.$file;
				if ((is_file($file_absolute_path)) AND ((self::is_css($file))
					 OR (self::is_backup($file)) OR (self::is_dated_backup($file))))
				{
					$url = http::getHost().path::clean($core->blog->settings->themes_url.'/'.$dirname.'/'.$file);

					$class = $info = $percent = $actions = $tr_class = '';
					$filesize = files::size(filesize($file_absolute_path));
					if (self::is_css($file_absolute_path))
					{
						$percent = self::percent($file_absolute_path);
						if ($percent !== false) {$percent = ' ('.$percent.'% '.__('of the original size').')';}
					}
					# CSS file without backup file
					if ((self::is_css($file_absolute_path)) AND (!self::check_backup($file_absolute_path)))
					{
						$class = 'css';
						$info = ' ('.__('uncompressed file').') ';
						$actions = '<input type="submit" name="compress" value="'.__('compress').'" />';
					}
					# CSS file with backup file
					elseif (self::is_css($file_absolute_path))
					{
						$class = 'css';
						$info = ' ('.__('compressed file').') ';
					}
					# backup file
					elseif (self::is_backup($file_absolute_path))
					{
						$tr_class = 'backup';
						$class = 'backup';
						$info = ' ('.__('original file').') ';
						$actions = '<input type="submit" name="compress" value="'.
							__('compress to').' '.self::get_original_filename($file).'" />';
						$actions .= ' '.'<input type="submit" name="delete" value="'.__('delete').'" />';
					}
					# dated backup file 
					elseif (self::is_dated_backup($file_absolute_path))
					{
						$tr_class = 'backup';
						$class = 'dated_backup';
						$info = ' ('.__('backup file').') ('.self::get_date($file_absolute_path).')';
						$actions = '<input type="submit" name="delete" value="'.__('delete').'" />';
					}

					$actions = (!empty($actions)) ? '<form action="'.
						http::getSelfURI().'" method="post">'.
						form::hidden('file',$file_absolute_path).$actions.
						'<p>'.$core->formNonce().'</p></form>' : ''; 

					if (!empty($tr_class)) {$tr_class = ' '.$tr_class;}
					$table->row('class="line'.$tr_class.'"');
					if (!empty($info)) {$info = '<br />'.$info;}
					$table->cell('<a href="'.$url.'">'.$file.'</a>'.$info,'class="'.$class.'"');
					if (!empty($percent)) {$percent = '<br />'.$percent;}
					$table->cell($filesize.$percent);
					$table->cell($actions);

				}
			}
			echo($table->get());
		}
	}
}
?>