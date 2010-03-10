<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Mystique Config plugin.
#
# Copyright (c) 2010 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# This file is hugely inspired from blowupConfig admin page
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class mystiqueConfig
{
	protected static $fonts = array(
		'sans-serif' => array(
			'ss1' => 'Arial, Helvetica, sans-serif',
			'ss2' => 'Verdana,Geneva, Arial, Helvetica, sans-serif',
			'ss3' => '"Lucida Grande", "Lucida Sans Unicode", sans-serif',
			'ss4' => '"Trebuchet MS", Helvetica, sans-serif',
			'ss5' => 'Impact, Charcoal, sans-serif'
		),
		
		'serif' => array(
			's1' => 'Times, "Times New Roman", serif',
			's2' => 'Georgia, serif',
			's3' => 'Baskerville, "Palatino Linotype", serif'
		),
		
		'monospace' => array(
			'm1' => '"Andale Mono", "Courier New", monospace',
			'm2' => '"Courier New", Courier, mono, monospace'
		)
	);
	
	protected static $fonts_combo = array();
	protected static $fonts_list = array();

	
	public static function fontsList()
	{
		if (empty(self::$fonts_combo))
		{
			self::$fonts_combo[__('default')] = '';
			foreach (self::$fonts as $family => $g)
			{
				$fonts = array();
				foreach ($g as $code => $font) {
					$fonts[str_replace('"','',$font)] = $code;
				}
				self::$fonts_combo[$family] = $fonts;
			}
		}
		
		return self::$fonts_combo;
	}
	
	public static function fontDef($c)
	{
		if (empty(self::$fonts_list))
		{
			foreach (self::$fonts as $family => $g)
			{
				foreach ($g as $code => $font) {
					self::$fonts_list[$code] = $font;
				}
			}
		}
		
		return isset(self::$fonts_list[$c]) ? self::$fonts_list[$c] : null;
	}
	
	public static function adjustFontSize($s)
	{
		if (preg_match('/^([0-9.]+)\s*(%|pt|px|em|ex)?$/',$s,$m)) {
			if (empty($m[2])) {
				$m[2] = 'px';
			}
			return $m[1].$m[2];
		}
		
		return null;
	}
	
	public static function adjustPosition($p)
	{
		if (!preg_match('/^[0-9]+(:[0-9]+)?$/',$p)) {
			return null;
		}
		
		$p = explode(':',$p);
		
		return $p[0].(count($p) == 1 ? ':0' : ':'.$p[1]);
	}
	
	public static function adjustColor($c)
	{
		if ($c === '') {
			return '';
		}
		
		$c = strtoupper($c);
		
		if (preg_match('/^[A-F0-9]{3,6}$/',$c)) {
			$c = '#'.$c;
		}
		
		if (preg_match('/^#[A-F0-9]{6}$/',$c)) {
			return $c;
		}
		
		if (preg_match('/^#[A-F0-9]{3,}$/',$c)) {
			return '#'.substr($c,1,1).substr($c,1,1).substr($c,2,1).substr($c,2,1).substr($c,3,1).substr($c,3,1);
		}
		
		return '';
	}
	
	public static function imagesPath()
	{
		global $core;
		return path::real($core->blog->public_path).'/mystique-images';
	}
	
	public static function imagesURL()
	{
		global $core;
		return $core->blog->settings->system->public_url.'/mystique-images';
	}
	
	public static function canWriteImages($create=false)
	{
		global $core;
		
		$public = path::real($core->blog->public_path);
		$imgs = self::imagesPath();
		
		if (!function_exists('imagecreatetruecolor') || !function_exists('imagepng') || !function_exists('imagecreatefrompng')) {
			return false;
		}
		
		if (!is_dir($public)) {
			return false;
		}
		
		if (!is_dir($imgs)) {
			if (!is_writable($public)) {
				return false;
			}
			if ($create) {
				files::makeDir($imgs);
			}
			return true;
		}
		
		if (!is_writable($imgs)) {
			return false;
		}
		
		return true;
	}
	
	public static function uploadImage($f,$basename)
	{
		if (!self::canWriteImages(true)) {
			throw new Exception(__('Unable to create images.'));
		}
		
		$name = $f['name'];
		$type = files::getMimeType($name);
		
		if ($type != 'image/jpeg' && $type != 'image/png') {
			throw new Exception(__('Invalid file type.'));
		}
		
		$dest = self::imagesPath().'/'.$basename.($type == 'image/png' ? '.png' : '.jpg');
		
		if (@move_uploaded_file($f['tmp_name'],$dest) === false) {
			throw new Exception(__('An error occurred while writing the file.'));
		}
		
		$s = getimagesize($dest);
		
		return $dest;
	}
	

	
}
?>