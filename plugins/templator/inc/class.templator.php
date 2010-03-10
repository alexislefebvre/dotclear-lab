<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of templator a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class dcTemplator
{
	protected $core;
	
	protected $self_name = 'templator';
	protected $post_default_name = 'post.html';
	protected $page_default_name = 'page.html';
	protected $template_dir_name = 'default-templates';
	
	public $tpl = array();

	public function __construct($core)
	{
		$this->core =& $core;

		//$this->user_theme = path::real($this->core->blog->themes_path.'/'.$this->core->blog->settings->system->theme);
		$this->post_tpl = path::real($this->core->blog->themes_path.'/default/tpl/'.$this->post_default_name);
		$plugin_page = $this->core->plugins->getModules('pages');
		$this->page_tpl = path::real($plugin_page['root'].'/'.$this->template_dir_name.'/'.$this->page_default_name);
		
		$this->findTemplates();

	}

	public function canUseRessources($create=false)
	{
		$path = $this->core->plugins->moduleInfo($this->self_name,'root') ;

		$path_tpl = $this->core->plugins->moduleInfo($this->self_name,'root').'/'.$this->template_dir_name ;

		if (!is_dir($path)) {
			return false;
		}
		
		if (!is_dir($path_tpl)) {
			if (!is_writable($path)) {
				return false;
			}
			if ($create) {
				files::makeDir($path_tpl);
			}
			return true;
		}
		
		if (!is_writable($path_tpl)) {
			return false;
		}
		
		return true;
	}

	public function getSourceContent($f)
	{
		$source = $this->tpl;
		
		if (!isset($source[$f])) {
			throw new Exception(__('File does not exist.'));
		}
		
		$F = $source[$f];
		if (!is_readable($F)) {
			throw new Exception(sprintf(__('File %s is not readable'),$f));
		}
		
		//throw new Exception(sprintf(__(' %s'),$source[$f]));
		
		return array(
			'c' => file_get_contents($source[$f]),
			'w' => $this->getDestinationFile($f) !== false,
			//'type' => $type,
			'f' => $f
		);
	}

	public function filesList($item='%1$s')
	{
		$files = $this->tpl;
		
		if (empty($files)) {
			return '<p>'.__('No file').'</p>';
		}
		
		$list = '';
		foreach ($files as $k => $v)
		{
			$li = sprintf('<li>%s</li>',$item);

			$list .= sprintf($li,$k,html::escapeHTML($k));
		}
		
		return sprintf('<ul>%s</ul>',$list);
	}
	
	public function initializeTpl($name,$type)
	{
		if  ($type == 'page')
		{
			$base =  $this->page_tpl ;
		}
		else {
			$base =  $this->post_tpl ;
		}
		
		$source = array(
			'c' => file_get_contents($base),
			'w' => $this->getDestinationFile($name) !== false,
			//'type' => $type,
			'f' => $f);
		
		if (!$source['w'])
		{
			throw new Exception(sprintf(__('File %s is not readable'),$source));
		}

		try
		{
			$dest = $this->getDestinationFile($name);

			if ($dest == false) {
				throw new Exception();
			}
			
			$content = $source['c'];
			
			if (!is_dir(dirname($dest))) {
				files::makeDir(dirname($dest));
			}
			
			$fp = @fopen($dest,'wb');
			if (!$fp) {
				throw new Exception('tocatch');
			}
			
			$content = preg_replace('/(\r?\n)/m',"\n",$content);
			$content = preg_replace('/\r/m',"\n",$content);
			
			fwrite($fp,$content);
			fclose($fp);
		}
		catch (Exception $e)
		{
			throw $e;
		}
	
	}

	public function writeTpl($name,$content)
	{
		try
		{
			$dest = $this->getDestinationFile($name);
			
			if ($dest == false) {
				throw new Exception();
			}
			
			if (!is_dir(dirname($dest))) {
				files::makeDir(dirname($dest));
			}
			
			$fp = @fopen($dest,'wb');
			if (!$fp) {
				throw new Exception('tocatch');
			}
			
			$content = preg_replace('/(\r?\n)/m',"\n",$content);
			$content = preg_replace('/\r/m',"\n",$content);
			
			fwrite($fp,$content);
			fclose($fp);
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	protected function getDestinationFile($f)
	{
		$dest = $this->core->plugins->moduleInfo($this->self_name,'root').'/'.$this->template_dir_name.'/'.$f ;
		
		if (file_exists($dest) && is_writable($dest)) {
			return $dest;
		}
		
		if (!is_dir(dirname($dest))) {
			if (is_writable($this->core->blog->public_path)) {
				return $dest;
			}
		}
		
		if (is_writable(dirname($dest))) {
			return $dest;
		}

		return false;
	}
	
	protected function findTemplates()
	{
		$this->tpl = $this->getFilesInDir($this->core->plugins->moduleInfo($this->self_name,'root').'/'.$this->template_dir_name);
		
		uksort($this->tpl,array($this,'sortFilesHelper'));
	}
	
	protected function getFilesInDir($dir)
	{
		$dir = path::real($dir);
		if (!$dir || !is_dir($dir) || !is_readable($dir)) {
			return array();
		}
		
		$d = dir($dir);
		$res = array();
		while (($f = $d->read()) !== false)
		{
			if (is_file($dir.'/'.$f) && !preg_match('/^\./',$f)) {
				$res[$f] = $dir.'/'.$f;
			}
		}
		
		return $res;
	}
	
	protected function sortFilesHelper($a,$b)
	{
		if ($a == $b) {
			return 0;
		}
		
		$ext_a = files::getExtension($a);
		$ext_b = files::getExtension($b);
		
		return strcmp($ext_a.'.'.$a,$ext_b.'.'.$b);
	}
}
?>
