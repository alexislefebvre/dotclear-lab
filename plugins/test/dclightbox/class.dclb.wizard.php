<?php
class dclbWizard
{
	private $source = array();
	private $destination = array();
	private $error = array();


	public function __construct()
	{
		$this->source['path'] = dirname(__FILE__).'/to_admin';
		$this->destination['path'] = dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']) ;

		$this->listItems();
	}


	private function listItems($cursor = false)
	{
		$root = $this->source['path'];
		$cursor = ($cursor === false) ? $root : $cursor;

		if(($d = @dir($cursor)) === false)
			throw new Exception(__('Unable to open directory.'));

		while(($item = $d->read()) !== false)
		{
			if($item == "." || $item =="..") continue;

			$item = $cursor."/".$item;

			if(is_dir($item))
			{
				$this->source['dir'][] = preg_replace('#^'.$root.'/#','',$item);
				$this->listItems($item);
			}
			else if(is_file($item))
			{
				$this->source['file'][] = preg_replace('#^'.$root.'/#','',$item);
			}
		}
	}


	public function checkHeaderFile()
	{
		$cur_theme_path = $GLOBALS['core']->blog->themes_path.'/'.$GLOBALS['core']->blog->settings->theme.'/_head.html';
		$def_theme_path = $GLOBALS['core']->blog->themes_path.'/default/_head.html';

		if (file_exists($cur_theme_path))
			return $cur_theme_path;
		else
			return $def_theme_path;
	}


	public function checkWritableDir()
	{
		if (is_writable($this->destination['path']))
			return '<p class="writable"><code>'.$this->destination['path'].'</code> : <strong>'.__('writable').'</strong></p>';
		else
			return '<p class="unwritable"><code>'.$this->destination['path'].'</code> : <strong>'.__('not writable').'</strong> '.__('you have to copy the following files manually').'</p>';
	}


	public function checkInstalledFiles()
	{
		$list = '<ul>';
		foreach($this->source['file'] as $file)
		{
			$destination_file = $this->destination['path'].'/'.$file;

			if(file_exists($destination_file))
				$list .= '<li class="installed"><code>'.$destination_file.'</code> : <strong>'.__('installed').'</strong></li>';
			else
				$list .= '<li class="uninstalled"><code>'.$destination_file.'</code> : <strong>'.__('not installed').'</Strong></li>';
		}
		$list .= '</ul>';

		return $list ;
	}


	public function copyFiles()
	{
		foreach($this->source['dir'] as $dir)
		{
			$destination_dir = $this->destination['path'].'/'.$dir;

			if(!@mkdir($destination_dir) && !file_exists($destination_dir))
				$this->error[] = 
				__('Unable to create directory:').' '.$destination_dir.
				'<br />'.__('create it manually');
		}
		foreach($this->source['file'] as $file)
		{
			$source_file =$this->source['path'].'/'.$file;
			$destination_file = $this->destination['path'].'/'.$file;

			if(!@copy($source_file,$destination_file))
				$this->error[] = 
				__('Unable to copy file:').' '.$destination_file.
				'<br />'.__('copy it manually from:').' '.$source_file;
		}

		if($this->error)
		{
			$msg = implode('#@;',$this->error);
			throw new Exception(str_replace('#@;','</li><li>',$msg));
		}
	}


	public function deleteFiles()
	{
		foreach($this->source['file'] as $file)
		{
			$destination_file = $this->destination['path'].'/'.$file;

			if(!@unlink($destination_file))
			$this->error[] = 
				__('Unable to delete file:').' '.$destination_file.
				'<br />'.__('delete it manually');
		}

		foreach($this->source['dir'] as $dir)
		{
			$destination_dir = $this->destination['path'].'/'.$dir;
			if(!@rmdir($destination_dir))
			$this->error[] = 
				__('Unable to delete directory:').' '.$destination_dir.
				'<br />'.__('delete it manually');
		}

		if($this->error)
		{
			$msg = implode('#@;',$this->error);
			throw new Exception(str_replace('#@;','</li><li>',$msg));
		}
	}
}
?>