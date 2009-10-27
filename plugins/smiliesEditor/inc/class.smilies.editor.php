<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of smiliesEditor, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

class smiliesEditor
{
	protected $smilies_dir = 'smilies';
	protected $smilies_file_name = 'smilies.txt';
	
	protected $smilies_desc_file ;
	public $smilies_base_url;
	public $smilies_path;

	public $smilies_config;
	public $smilies_list = array();
	
	public $filemanager;
	public $files_list = array();

	public function __construct($core)
	{
		$this->core = $core;

		$this->smilies_desc_file = $this->core->blog->themes_path.'/'.$this->core->blog->settings->theme.'/'.
								$this->smilies_dir.'/'.$this->smilies_file_name;
		$this->smilies_base_url = $this->core->blog->settings->themes_url.'/'.$this->core->blog->settings->theme.'/'.$this->smilies_dir.'/';

		$this->smilies_path = $this->core->blog->themes_path.'/'.$this->core->blog->settings->theme.'/'.
								$this->smilies_dir;
		$this->smilies_config = unserialize($this->core->blog->settings->smilies_toolbar);
	}

	public function getSmilies()
	{
		if (file_exists($this->smilies_desc_file))
		{
			$rule = file($this->smilies_desc_file);

			foreach ($rule as $v)
			{
				$v = trim($v);
				if (preg_match('|^([^\t]*)[\t]+(.*)$|', $v, $m)) {
					$this->smilies_list[] = array(
						'code' => $m[1], 
						'name' => $m[2] ,
						//'url' => $this->smilies_base_url.$m[2], 
						'onSmilebar' => !is_array($this->smilies_config) || in_array($m[1], $this->smilies_config));
				}
			}
		}

		return $this->smilies_list;
	}
	
	public function setSmilies($smilies)
	{
		if (is_array($smilies)) {
		
			if (!is_writable($this->smilies_path)) {
				throw new Exception(__('Configuration file is not writable.'));
			}
			
			if (is_writable($this->smilies_desc_file) || (!file_exists($this->smilies_desc_file) && is_writable($this->smilies_path))) {
				try {
					$fp = @fopen($this->smilies_desc_file,'wb');
					if (!$fp) {
						throw new Exception('tocatch');
					}
					$fcontent = '';
					
					foreach ($smilies as $smiley) {
						$fcontent .= $smiley['code']."\t\t".$smiley['name']."\r\n";
					}
					fwrite($fp,$fcontent);
					fclose($fp);
				}
				catch (Exception $e)
				{
					throw new Exception(sprintf(__('Unable to write file %s. Please check your theme file and folders permissions.'),$this->smilies_desc_file));
				}
			}
		}
		
		return false;
	}
	
	public function setConfig($smilies) 
	{
		if (is_array($smilies)) {
			
			$config = array();
			
			foreach ($smilies as $smiley) {
				if ($smiley['onSmilebar']) {
					$config[] = $smiley['code'];
				}
			}
			$this->core->blog->settings->setNamespace('smilieseditor');
			$this->core->blog->settings->put('smilies_toolbar',serialize($config),'string');
			$this->core->blog->triggerBlog();
			
			return true;
		}
		
		return false;
	}
	
	public function uploadSmile($tmp,$name)
	{
		$name = files::tidyFileName($name);
		
		$file =  $this->filemanager->uploadFile($tmp,$name);
		 
		$type = files::getMimeType($name);
		if ($type != 'image/jpeg' && $type != 'image/png') {
			$this->filemanager->removeItem($name);
			throw new Exception(sprintf(__('This file %s is not an image. It would be difficult to use it for a smiley.'),$name));
		}
		else 
		{
			return $name;
		}
	}
	
	public function getFiles()
	{
		try
		{
			$this->filemanager = new filemanager ($this->smilies_path,$this->smilies_base_url);
			$this->filemanager->getDir();
			//$smg_writable = $smg->writable();
			// $smilies_files['smile.png'] = array ("smile.png" => 'name'  ,  /file/to/smile.png"  => 'url' ) 
			//$smilies_files = $smileys_img =  array();
			foreach ($this->filemanager->dir['files'] as $k => $v) 
			{
				$this->files_list[$v->basename] = array( $v->basename =>  'name'  ,  $v->file_url => 'url', $v->type => 'type');
			
				if (preg_match('/^(image)(.+)$/',$v->type))
				{
					$this->images_list[$v->basename] = array( 'name' => $v->basename ,  'url' => $v->file_url );
				}
			}
		}
		catch (Exception $e)
		{
			throw new Exception(sprintf(__('Unable to access subfolder %s. Does it exist ?'),$this->smilies_dir));
		}
	}
	
	public function createDir()
	{
		try 
		{
			//die($this->core->blog->themes_path.'/'.$this->core->blog->settings->theme.'/'.path::clean($this->smilies_dir));
			files::makeDir($this->core->blog->themes_path.'/'.$this->core->blog->settings->theme.'/'.path::clean($this->smilies_dir)); 
		}
		catch (Exception $e)
		{
			throw new Exception(sprintf(__('Unable to create subfolder %s in your theme. Please check your folder permissions.'),$this->smilies_dir));
		}
	}
}
?>