<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of licenseBootstrap, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

class licenseBootstrap
{
	public $core;
	public $extensions;
	public $license;
	public $head;
	public $full;
	public $addfull;
	public $overwrite;
	public $exclusion;

	public function __construct($core,$extensions=null,$license=null,$head=null,$addfull=true,$overwrite=false,$exclusion='')
	{
		$this->core = $core;
		$this->overwrite = (boolean) $overwrite;
		$this->exclusion = (string) $exclusion;
		$this->license = (string) $license;

		$this->extensions = self::getDefaultExtensions($extensions);
		$this->head = self::getDefaultLicenses('head',$license,$head);
		$this->full = self::getDefaultLicenses('full',$license);
	}

	public function moduleInfo($type,$id)
	{
		$info = array();

		if ($type == 'plugin')
		{
			$modules = $this->core->plugins;
		}
		elseif ($type = 'theme')
		{
			$modules = new dcModules($core);
			$modules->loadModules($this->core->blog->themes_path,null);
		}
		else
		{
			$type = '';
		}

		if ($type && $modules->moduleExists($id))
		{
			$info = $modules->getModules($id);
			$info['user_cn'] = $this->core->auth->getInfo('user_cn');
			$info['user_name'] = $this->core->auth->getInfo('user_name');
			$info['user_email'] = $this->core->auth->getInfo('user_email');
		}

		return $info;
	}

	public function writeModuleLicense($type,$id)
	{
		$info = $this->moduleInfo($type,$id);

		# Try if root is writable
		if (!isset($info['root']) || !is_writable($info['root']))
		{
			throw new Exception('Failed to get module directory');
		}
		$root = $info['root'];

		# Get license text
		$head = self::parseInfo($type,$id,$info,$this->head[$this->license]);
		$full = self::parseInfo($type,$id,$info,$this->full[$this->license]);

		# Get files from module root
		$files = self::scandir($root);
		if (empty($files))
		{
			throw new Exception('Nothing to do');
		}

		# Write full license file
		if (!file_exists($root.'/LICENSE') || $overwrite)
		{
			files::putContent($root.'/LICENSE',$full);
		}

		# Write license header in files
		foreach($files as $file)
		{
			if (!is_file($root.'/'.$file)) continue;

			if (!empty($this->exclusion))
			{
				if (preg_match($this->exclusion,$file)) continue;
			}

			$ext = files::getExtension($file) ;
			$func = 'filesLicenseBootstrap::'.$ext.'File';
			if (!in_array($ext,$this->extensions) || !is_callable($func)) continue;

			$content = file_get_contents($root.'/'.$file);

			$content = call_user_func($func,$content,$head,$this->overwrite);

			if (!empty($content))
			{
				files::putContent($root.'/'.$file,$content);
			}
		}
		return true;
	}

	public function addFileLicense($type,$id,$file,$content)
	{
		$info = $this->moduleInfo($type,$id);

		$head = self::parseInfo($type,$id,$info,$this->head);

		if (!empty($this->exclusion))
		{
			if (preg_match($this->exclusion,$file)) return $content;
		}

		$ext = files::getExtension($file) ;
		$func = 'filesLicenseBootstrap::'.$ext.'File';
		if (!in_array($ext,$this->extensions) || !is_callable($func)) return $content;

		return call_user_func($func,$content,$head[$this->license],$this->overwrite);
	}

	public static function getDefaultExtensions($exts=null)
	{
		$default = array('php','js');
		if (null === $exts) $exts = $default;
		elseif (!is_array($exts)) $exts = array($exts);

		return array_intersect($default,$exts);
	}

	public static function getDefaultLicenses($type='head',$license=null,$content=null)
	{
		$rs = array();

		if ($license && $content)
		{
			$rs = array($license => $content);
		}

		if ($license && !$content)
		{
			$f = dirname(__FILE__).'/licenses/'.$license.'.'.$type.'.txt';
			if (file_exists($f))
			{
				$rs = array($license => file_get_contents($f));
			}
		}

		if (!$license)
		{
			$files = files::scandir(dirname(__FILE__).'/licenses');
			foreach($files as $file)
			{
				if (preg_match('/^(.*?)\.'.$type.'\.txt$/',$file,$m))
				{
					$rs[$m[1]] = file_get_contents(dirname(__FILE__).'/licenses/'.$m[0]);
				}
			}
		}
		return $rs;
	}

	private function parseInfo($type,$id,$info,$text)
	{
		return str_replace(
			array(
				'%year%',
				'%module_id%',
				'%module_name%',
				'%module_author%',
				'%module_type%',
				'%user_cn%',
				'%user_name%',
				'%user_email%'
			),
			array(
				date('Y'),
				$id,
				$info['name'],
				$info['author'],
				$type,
				$info['user_cn'],
				$info['user_name'],
				$info['user_email']
			),
			$text
		);
	}

	private static function scandir($path,$dir='',$res=array())
	{
		$path = path::real($path);
		if (!is_dir($path) || !is_readable($path)) return array();

		$files = files::scandir($path);

		foreach($files AS $file)
		{
			if ($file == '.' || $file == '..') continue;

			if (is_dir($path.'/'.$file))
			{
				$res[] = $file;
				$res = self::scandir($path.'/'.$file,$dir.'/'.$file,$res);
			}
			else
			{
				$res[] = empty($dir) ? $file : $dir.'/'.$file;
			}
		}
		return $res;
	}

	public static function encode($a)
	{
		return base64_encode(serialize($a));
	}

	public static function decode($a)
	{
		return unserialize(base64_decode($a));
	}

	public static function packmanBeforeCreatePackage($info,$a,$b,$c,$d)
	{
		global $core;

		$s =& $core->blog->settings;

		$addfull = (boolean) $s->licensebootstrap_addfull;
		$overwrite = (boolean) $s->licensebootstrap_overwrite;
		$exclusion = $s->licensebootstrap_exclusion;

		$license = $s->licensebootstrap_license;
		if (empty($license)) $license = 'gpl2';

		$exts = licenseBootstrap::decode($s->licensebootstrap_files_exts);
		if (!is_array($exts)) $exts = self::getDefaultExtensions();

		$headers = licenseBootstrap::decode($s->licensebootstrap_licenses_headers);
		if (!is_array($headers)) $headers = self::getDefaulLicenses();

		$license = new licenseBootstrap($core,$exts,$license,$headers[$license],$addfull,$overwrite,$exclusion);
		$license->writeModuleLicense($info['type'],$info['id']);
	}

	public static function dcTranslaterAfterWriteLangFile($f,$file,$content,$throw)
	{
		if (!$f) return;

		global $core;

		$id = '';
		$type = 'theme';
		$dir = path::real($file);

		# Is a plugin
		$plugins_roots = explode(PATH_SEPARATOR,DC_PLUGINS_ROOT);
		foreach($plugins_roots as $path)
		{
			$root = path::real($path);
			if ($root == substr($dir,0,strlen($root)))
			{
				$type = 'plugin';
				$reg = '/'.preg_quote($root,'/').'\/([^\/]+)\/(.*?)/';
				if (!preg_match($reg,$dir,$m))
				{
					if ($throw)
					{
						throw new Exception('Failed to retreive module id for adding license');
					}
					//return;
				}
				$id = $m[1];
				break;
			}
		}
		# Not a plugin try a theme
		if ($type == 'theme')
		{
			$path = $core->blog->themes_path;
			$root = path::real($path);
			if ($root == substr($dir,0,strlen($root)))
			{
				$reg = '/'.preg_quote($root,'/').'\/([^\/]+)\/(.*?)/';
				if (!preg_match($reg,$dir,$m))
				{
					if ($throw)
					{
						throw new Exception('Failed to retreive module id for adding license');
					}
					//return;
				}
				$id = $m[1];
			}
		}
		# Not a plugin nor theme
		if (empty($id)) return;

		$s =& $core->blog->settings;

		$addfull = (boolean) $s->licensebootstrap_addfull;
		$overwrite = (boolean) $s->licensebootstrap_overwrite;
		$exclusion = $s->licensebootstrap_exclusion;

		$license = $s->licensebootstrap_license;
		if (empty($license)) $license = 'gpl2';

		$exts = licenseBootstrap::decode($s->licensebootstrap_files_exts);
		if (!is_array($exts)) $exts = self::getDefaultExtensions();

		$headers = licenseBootstrap::decode($s->licensebootstrap_licenses_headers);
		if (!is_array($headers)) $headers = self::getDefaultLicenses();

		$license = new licenseBootstrap($core,$exts,$license,$headers[$license],$addfull,$overwrite,$exclusion);
		$content = $license->addFileLicense($type,$id,$file,$content);

		files::putContent($file,$content);
	}
}

class filesLicenseBootstrap
{
	public static function phpFile($content,$license,$overwrite=false)
	{
		$c = preg_replace('/((# -- BEGIN LICENSE BLOCK ([-]+))(.*?)(# -- END LICENSE BLOCK ([-]+))([\n|\r\n]+))/msi','',$content);

		if ($c != $content && !$overwrite) return $content;
		$content = $c;

		$block = 
		"\r\n# -- BEGIN LICENSE BLOCK ----------------------------------\r\n".
		'# '.str_replace("\n","\n# ",$license).
		"\r\n# -- END LICENSE BLOCK ------------------------------------\r\n";

		$content = preg_replace('/(\<\?php)/','<?php'.$block,$content,1);
		return $content;
	}

	public static function jsFile($content,$license,$overwrite=false)
	{
		$c = preg_replace('/((\/\* -- BEGIN LICENSE BLOCK ([-]+))(.*?)(\* -- END LICENSE BLOCK ([-]+)\*\/)([\n|\r\n]+))/msi','',$content);

		if ($c != $content && !$overwrite) return $content;
		$content = $c;

		$block = 
		"/* -- BEGIN LICENSE BLOCK ----------------------------------\r\n".
		' * '.str_replace("\n","\n * ",$license).
		"\r\n * -- END LICENSE BLOCK ------------------------------------*/\r\n\r\n";

		$content = $block.$content;
	
		return $content;
	}
}
?>