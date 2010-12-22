<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcDevKit, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom, Dsls and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class moduleTranslater
{
	protected $root;
	protected $strings;
	
	public function __construct($root)
	{
		if (!is_dir($root)) {
			throw new Exception(__('Directory does not exist'));
		}
		if (!is_readable($root)) {
			throw new Exception(__('Cannot read directory'));
		}
		
		$this->root = $root;
		$this->strings = array();
	}
	
	public function setStrings($dir = null)
	{
		$res = array();
		
		$dir = is_null($dir) ? $this->root : $dir;
		
		$dir = preg_replace('#[\\\/]+#','/',$dir);
		if (substr($dir,-1-1) != '/') {
			$dir .= '/';
		}
		
		$D = dir($dir);
		while (($e = $D->read()) !== false)
		{
			if ($e == '.' || $e == '..') {
				continue;
			}
			
			if (is_dir($dir.'/'.$e)) {
				$this->setStrings($dir.$e);
			} elseif (is_file($dir.'/'.$e)) {
				preg_match_all("#(__\('([^']+)'\)|\{\{\tpl:lang\s+([^\}]+)}\})#",file_get_contents($dir.'/'.$e),$m);
				foreach ($m[2] as $s) {
					$file = array_key_exists($s,$this->strings) ? $this->strings[$s].','.$e : $e;
					$this->strings[$s] = $file;
				}
				foreach ($m[3] as $s) {
					$file = array_key_exists($s,$this->strings) ? $this->strings[$s].','.$e : $e;
					$this->strings[$s] = $file;
				}
			}
		}
	}
	
	public function getStrings()
	{
		return $this->strings;
	}
}

?>
