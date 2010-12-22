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

abstract class dcDevModule
{
	# Module public parameters
	public $name;
	public $icon;
	public $description;
	
	# Module internal parameters
	protected $id;
	protected $core;
	protected $has_gui = false;
	protected $gui_url = null;
	
	# Abstract methods
	abstract public function gui($url);
	abstract public function getConfigForm();
	abstract public function saveConfig();
	
	#Public methods
	public function __construct($core)
	{
		$this->core = $core;
		$this->setInfo();
		
		$this->id = get_class($this);
		$this->gui_url = 'plugin.php?p=dcDevKit&id='.$this->id;
	}
	
	public function hasGUI()
	{
		if (!$this->core->auth->isSuperAdmin()) {
			return false;
		}
		
		if (!$this->has_gui) {
			return false;
		}
		
		return true;
	}
	
	public function guiURL()
	{
		if (!$this->hasGui()) {
			return false;
		}
		
		return $this->gui_url;
	}

	public function guiLink()
	{
		if (($url = $this->guiURL()) !== false) {
			$url = html::escapeHTML($url);
			$link = '<a href="%2$s">%1$s</a>';
		} else {
			$link = '%1$s';
		}
		
		return sprintf($link,$this->name,$url);
	}
	
	# Protected methods	
	protected function setInfo()
	{
		$this->name = __('Default module');
		$this->icon = 'index.php?pf='.$this->id.'/icon.png';
		$this->description = null;
	}
}

?>