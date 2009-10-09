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
	# $core : dcCore
	# $type : type of module, could be plugin or theme
	# $id : module id
	# $info : module info from dcModules
	# $exts : array of files extensions to parse
	# $license : license type (ex: gpl2)
	# $header : special license header (used default if empty)
	# $overwite : overwrite existing license in module
	#
	public static function license($core,$type,$id,$info,$exts=array('php','js'),$license='gpl2',$header='',$overwrite=false)
	{
		$info['id'] = $id;
		$info['type'] = $type;
		$info['user_cn'] = $core->auth->getInfo('user_cn');
		$info['user_name'] = $core->auth->getInfo('user_name');
		$info['user_email'] = $core->auth->getInfo('user_email');

		# Try if root is writable
		if (!isset($info['root']) || !is_writable($info['root']))
		{
			throw new Exception('Failed to get module directory');
		}
		$root = $info['root'];

		# Try if license exists
		$licenses_dir = dirname(__FILE__).'/licenses';
		if (!file_exists($licenses_dir.'/'.$license.'.head.txt')
		 || !file_exists($licenses_dir.'/'.$license.'.full.txt'))
		{
			throw new Exception('Unknow license type');
		}

		# Get license text
		$head = empty($header) ? 
			file_get_contents($licenses_dir.'/'.$license.'.head.txt') : $header;
		$head = self::parseInfo($info,$head);
		$full = file_get_contents($licenses_dir.'/'.$license.'.full.txt');
		$full = self::parseInfo($info,$full);

		# Get files from module root
		$files = self::scandir($root);
		if (empty($files))
		{
			throw new Exception('Nothing to do');
		}

		# Write full license file
		if (!file_exists($root.'/LICENSE') || $overwrite)
		{
			file_put_contents($root.'/LICENSE',$full);
		}

		# Write license header in files
		foreach($files as $file)
		{
			if (!is_file($root.'/'.$file)) continue;

			$ext = files::getExtension($file) ;
			$func = 'filesLicenseBootstrap::'.$ext.'File';
			if (!in_array($ext,$exts) || !is_callable($func)) continue;

			$content = file_get_contents($root.'/'.$file);

			$content = call_user_func($func,$content,$head,$overwrite);

			if (!empty($content))
			{
				file_put_contents($root.'/'.$file,$content);
			}
		}
		return true;
	}

	public static function getDefaultExts()
	{
		return array('php','js');
	}

	public static function getDefaultHeaders()
	{
		$rs = array();
		$files = files::scandir(dirname(__FILE__).'/licenses');
		foreach($files as $file)
		{
			if (preg_match('/^(.*?)\.head\.txt$/',$file,$m))
			{
				$rs[$m[1]] = file_get_contents(dirname(__FILE__).'/licenses/'.$m[0]);
			}
		}
		return $rs;
	}

	private function parseInfo($info,$text)
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
				$info['id'],
				$info['name'],
				$info['author'],
				$info['type'],
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

	public static function packmanBeforeCreatePackage($info,$a,$b)
	{
		global $core;
		$s =& $core->blog->settings;
		$overwrite = (boolean) $s->licensebootstrap_overwrite;
		$license = $s->licensebootstrap_license;
		if (empty($license)) $license = 'gpl2';
		$exts = licenseBootstrap::decode($s->licensebootstrap_files_exts);
		if (!is_array($exts)) $exts = self::getDefaultExts();
		$headers = licenseBootstrap::decode($s->licensebootstrap_licenses_headers);
		if (!is_array($headers)) $headers = self::getDefaultheaders();
		
		self::license($core,$info['type'],$info['id'],$info,$exts,$license,$headers[$license],$overwrite);
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